<?php

declare(strict_types=1);

namespace UniversityMultilang\Translation;

use UniversityMultilang\Language\LanguageManager;

class TranslationController
{
    private TranslationManager $translationManager;
    private LanguageManager $languageManager;

    public function __construct(TranslationManager $translationManager, LanguageManager $languageManager)
    {
        $this->translationManager = $translationManager;
        $this->languageManager = $languageManager;
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

    /**
     * Hooked to 'transition_post_status'.
     * Automatically duplicate published posts to other languages as drafts.
     */
    public function autoDuplicateTranslations(string $newStatus, string $oldStatus, \WP_Post $post): void
    {
        // We only want to duplicate when a post is FIRST published
        if ($newStatus !== 'publish' || $oldStatus === 'publish') {
            return;
        }

        // We only auto-duplicate standard posts and pages for now
        if (!in_array($post->post_type, ['post', 'page'])) {
            return;
        }

        // Prevent infinite loops if we are programmatically inserting a published post
        if (did_action('uml_is_auto_duplicating')) {
            return;
        }

        $sourceLang = $this->translationManager->getPostLanguage($post->ID);
        if (!$sourceLang) {
            return; // If post has no language, we can't duplicate
        }

        $allLangs = $this->languageManager->getLanguages();
        $translations = $this->translationManager->getTranslations($post->ID);

        do_action('uml_is_auto_duplicating');

        foreach ($allLangs as $lang) {
            $langSlug = $lang->slug;
            
            // If the post doesn't exist in this language yet, duplicate it as draft
            if (!isset($translations[$langSlug])) {
                $newPostData = [
                    'post_title'   => $post->post_title . ' [' . strtoupper($langSlug) . ']',
                    'post_content' => $post->post_content,
                    'post_status'  => 'draft', // SEO SAFETY: Keep it draft
                    'post_type'    => $post->post_type,
                    'post_author'  => $post->post_author,
                ];

                $newPostId = wp_insert_post($newPostData);

                if (!is_wp_error($newPostId)) {
                    $this->translationManager->setPostLanguage($newPostId, $langSlug);
                    $this->translationManager->linkTranslations($post->ID, $newPostId);
                }
            }
        }
    }
}
