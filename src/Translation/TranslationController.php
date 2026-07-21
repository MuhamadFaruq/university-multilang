<?php

declare(strict_types=1);

namespace UniversityMultilang\Translation;

use UniversityMultilang\Language\LanguageManager;

class TranslationController
{
    private TranslationManager $translationManager;
    private LanguageManager $languageManager;
    private MachineTranslator $machineTranslator;
    private TermTranslationManager $termTranslationManager;

    public function __construct(
        TranslationManager $translationManager,
        LanguageManager $languageManager,
        MachineTranslator $machineTranslator,
        TermTranslationManager $termTranslationManager
    ) {
        $this->translationManager = $translationManager;
        $this->languageManager = $languageManager;
        $this->machineTranslator = $machineTranslator;
        $this->termTranslationManager = $termTranslationManager;
    }

    /**
     * Hooked to 'wp_insert_post'.
     * Runs when a new post (including auto-draft) is created in the database.
     */
    public function linkNewTranslation(int $postId, \WP_Post $post, bool $update): void
    {
        // Only run for new posts (not updates)
        if ($update) {
            return;
        }

        // We only care if the request comes from our "Add Translation" link
        if (!isset($_GET['from_post']) || !isset($_GET['new_lang'])) {
            return;
        }

        $fromPostId = (int) $_GET['from_post'];
        $newLang = sanitize_title($_GET['new_lang']);

        // Set the language for the new post
        $this->translationManager->setPostLanguage($postId, $newLang);

        // Link the translation
        $this->translationManager->linkTranslations($fromPostId, $postId);
    }

    private static bool $isAutoDuplicating = false;

    /**
     * Hooked to 'save_post'.
     * Automatically duplicate published posts to other languages as drafts.
     */
    public function autoDuplicateTranslations(int $postId, \WP_Post $post, bool $update): void
    {
        // Only duplicate if it's published
        if ($post->post_status !== 'publish') {
            return;
        }

        // We only auto-duplicate standard posts and pages for now
        if (!in_array($post->post_type, ['post', 'page'])) {
            return;
        }

        // Ignore autosaves
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Prevent infinite loops if we are programmatically inserting a post
        if (self::$isAutoDuplicating) {
            return;
        }

        $sourceLang = $this->translationManager->getPostLanguage($post->ID);
        if (!$sourceLang) {
            return; // If post has no language, we can't duplicate
        }

        $allLangs = $this->languageManager->getLanguages();
        $translations = $this->translationManager->getTranslations($post->ID);

        self::$isAutoDuplicating = true;

        foreach ($allLangs as $lang) {
            $langSlug = $lang->slug;
            
            // If the post doesn't exist in this language yet, duplicate it as draft
            if (!isset($translations[$langSlug])) {
                // Perform translation
                $translatedTitle = $this->machineTranslator->translate($post->post_title, $sourceLang, $langSlug);
                $translatedContent = $this->machineTranslator->translate($post->post_content, $sourceLang, $langSlug);
                
                // If it fails, fallback to [LANG] prefix
                if ($translatedTitle === $post->post_title) {
                    $translatedTitle = $post->post_title . ' [' . strtoupper($langSlug) . ']';
                }

                $newPostData = [
                    'post_title'   => $translatedTitle,
                    'post_content' => $translatedContent,
                    'post_status'  => 'draft', // SEO SAFETY: Keep it draft
                    'post_type'    => $post->post_type,
                    'post_author'  => $post->post_author,
                ];

                $newPostId = wp_insert_post($newPostData);

                if (!is_wp_error($newPostId)) {
                    $this->translationManager->setPostLanguage($newPostId, $langSlug);
                    $this->translationManager->linkTranslations($post->ID, $newPostId);
                    
                    // Map Taxonomies (Categories, Tags)
                    $taxonomies = get_object_taxonomies($post->post_type);
                    foreach ($taxonomies as $taxonomy) {
                        if ($taxonomy === LanguageManager::TAXONOMY) continue;
                        
                        $terms = wp_get_object_terms($post->ID, $taxonomy);
                        if (!empty($terms) && !is_wp_error($terms)) {
                            $mappedTermIds = [];
                            foreach ($terms as $term) {
                                // Find translation of this term
                                $termTranslations = $this->termTranslationManager->getTranslations((int) $term->term_id);
                                if (isset($termTranslations[$langSlug])) {
                                    $mappedTermIds[] = (int) $termTranslations[$langSlug];
                                }
                            }
                            if (!empty($mappedTermIds)) {
                                wp_set_object_terms($newPostId, $mappedTermIds, $taxonomy, false);
                            }
                        }
                    }

                    // Copy all Post Meta (Elementor Data, Thumbnail, SEO fields)
                    $allMeta = get_post_meta($post->ID);
                    if (!empty($allMeta)) {
                        $ignoredKeys = [
                            TranslationManager::META_GROUP_ID,
                            '_edit_lock',
                            '_edit_last'
                        ];
                        foreach ($allMeta as $metaKey => $metaValues) {
                            if (in_array($metaKey, $ignoredKeys, true)) continue;
                            
                            // Delete default generated meta if any, then copy
                            delete_post_meta($newPostId, $metaKey);
                            foreach ($metaValues as $metaValue) {
                                // post_meta is returned as serialized strings by get_post_meta when not specifying a single key,
                                // we need to check if it's serialized and maybe unserialize it before update, 
                                // actually add_post_meta handles serialization automatically if we pass the raw value.
                                // wait, get_post_meta($id) returns arrays of strings. 
                                $value = maybe_unserialize($metaValue);
                                add_post_meta($newPostId, $metaKey, $value);
                            }
                        }
                    }
                }
            }
        }
        
        self::$isAutoDuplicating = false;
    }

    /**
     * Hooked to 'before_delete_post'.
     * Clears translation group cache when a post is permanently deleted.
     */
    public function handlePostDeletion(int $postId): void
    {
        // Don't run on autosaves or revisions
        if (wp_is_post_revision($postId) || wp_is_post_autosave($postId)) {
            return;
        }

        $groupId = get_post_meta($postId, TranslationManager::META_GROUP_ID, true);
        if (!empty($groupId)) {
            // Unlink this post by deleting its group meta BEFORE it's actually deleted
            delete_post_meta($postId, TranslationManager::META_GROUP_ID);
            
            // Clear cache for ALL posts in this group
            global $wpdb;
            $postIds = $wpdb->get_col($wpdb->prepare(
                "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value = %s",
                TranslationManager::META_GROUP_ID,
                $groupId
            ));

            wp_cache_delete('uml_translations_' . $postId, TranslationManager::CACHE_GROUP);
            foreach ($postIds as $id) {
                wp_cache_delete('uml_translations_' . $id, TranslationManager::CACHE_GROUP);
            }
        }
    }
}
