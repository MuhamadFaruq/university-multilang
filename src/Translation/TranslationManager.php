<?php

declare(strict_types=1);

namespace UniversityMultilang\Translation;

use UniversityMultilang\Language\LanguageManager;

class TranslationManager
{
    public const META_GROUP_ID = '_uml_translation_group_id';

    /**
     * Set the language of a post.
     */
    public function setPostLanguage(int $postId, string $languageSlug): void
    {
        wp_set_object_terms($postId, $languageSlug, LanguageManager::TAXONOMY, false);
    }

    /**
     * Get the language slug of a post.
     */
    public function getPostLanguage(int $postId): ?string
    {
        $terms = wp_get_object_terms($postId, LanguageManager::TAXONOMY);
        if (!empty($terms) && !is_wp_error($terms)) {
            return $terms[0]->slug;
        }
        return null;
    }

    /**
     * Get or create a translation group ID for a post.
     */
    public function getTranslationGroupId(int $postId): string
    {
        $groupId = get_post_meta($postId, self::META_GROUP_ID, true);
        if (empty($groupId)) {
            $groupId = wp_generate_uuid4();
            update_post_meta($postId, self::META_GROUP_ID, $groupId);
        }
        return $groupId;
    }

    /**
     * Link two posts as translations of each other.
     * 
     * @param int $sourcePostId The existing post ID.
     * @param int $translatedPostId The new post ID.
     */
    public function linkTranslations(int $sourcePostId, int $translatedPostId): void
    {
        $groupId = $this->getTranslationGroupId($sourcePostId);
        update_post_meta($translatedPostId, self::META_GROUP_ID, $groupId);
        
        // Invalidate cache for all posts in this group
        global $wpdb;
        $postIds = $wpdb->get_col($wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value = %s",
            self::META_GROUP_ID,
            $groupId
        ));
        
        foreach ($postIds as $id) {
            wp_cache_delete('uml_translations_' . $id, self::CACHE_GROUP);
        }
    }

    public const CACHE_GROUP = 'uml_translation_cache';

    /**
     * Get all translations of a given post, including itself.
     * Returns an array of [language_slug => post_id].
     */
    public function getTranslations(int $postId): array
    {
        $cacheKey = 'uml_translations_' . $postId;
        $cached = wp_cache_get($cacheKey, self::CACHE_GROUP);
        if (false !== $cached) {
            return $cached;
        }

        $groupId = get_post_meta($postId, self::META_GROUP_ID, true);
        if (empty($groupId)) {
            // Post has no translation group yet, so it only has itself.
            $lang = $this->getPostLanguage($postId);
            if ($lang) {
                $result = [$lang => $postId];
                wp_cache_set($cacheKey, $result, self::CACHE_GROUP);
                return $result;
            }
            return [];
        }

        global $wpdb;
        $postIds = $wpdb->get_col($wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value = %s",
            self::META_GROUP_ID,
            $groupId
        ));

        $translations = [];
        foreach ($postIds as $id) {
            $lang = $this->getPostLanguage((int) $id);
            if ($lang) {
                $translations[$lang] = (int) $id;
            }
        }

        wp_cache_set($cacheKey, $translations, self::CACHE_GROUP);

        return $translations;
    }
}
