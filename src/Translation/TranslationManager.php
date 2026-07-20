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
    }

    /**
     * Get all translations of a given post, including itself.
     * Returns an array of [language_slug => post_id].
     */
    public function getTranslations(int $postId): array
    {
        $groupId = get_post_meta($postId, self::META_GROUP_ID, true);
        if (empty($groupId)) {
            // Post has no translation group yet, so it only has itself.
            $lang = $this->getPostLanguage($postId);
            if ($lang) {
                return [$lang => $postId];
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

        return $translations;
    }
}
