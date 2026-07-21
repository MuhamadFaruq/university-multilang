<?php

declare(strict_types=1);

namespace UniversityMultilang\Translation;

use UniversityMultilang\Language\LanguageManager;

class TermTranslationManager
{
    public const META_GROUP_ID = '_uml_translation_group_id';
    public const CACHE_GROUP = 'uml_term_translation_cache';

    /**
     * Set the language of a term.
     * We store it as term meta since terms don't naturally support taxonomies like posts do.
     */
    public function setTermLanguage(int $termId, string $languageSlug): void
    {
        update_term_meta($termId, '_uml_term_language', $languageSlug);
    }

    /**
     * Get the language slug of a term.
     */
    public function getTermLanguage(int $termId): ?string
    {
        $lang = get_term_meta($termId, '_uml_term_language', true);
        return !empty($lang) ? $lang : null;
    }

    /**
     * Get or create a translation group ID for a term.
     */
    public function getTranslationGroupId(int $termId): string
    {
        $groupId = get_term_meta($termId, self::META_GROUP_ID, true);
        if (empty($groupId)) {
            $groupId = wp_generate_uuid4();
            update_term_meta($termId, self::META_GROUP_ID, $groupId);
        }
        return $groupId;
    }

    /**
     * Link two terms as translations of each other.
     * 
     * @param int $sourceTermId The existing term ID.
     * @param int $translatedTermId The new term ID.
     */
    public function linkTranslations(int $sourceTermId, int $translatedTermId): void
    {
        $groupId = $this->getTranslationGroupId($sourceTermId);
        update_term_meta($translatedTermId, self::META_GROUP_ID, $groupId);
        
        // Invalidate cache
        global $wpdb;
        $termIds = $wpdb->get_col($wpdb->prepare(
            "SELECT term_id FROM {$wpdb->termmeta} WHERE meta_key = %s AND meta_value = %s",
            self::META_GROUP_ID,
            $groupId
        ));
        
        foreach ($termIds as $id) {
            wp_cache_delete('uml_term_translations_' . $id, self::CACHE_GROUP);
        }
    }

    /**
     * Get all translations of a given term, including itself.
     * Returns an array of [language_slug => term_id].
     */
    public function getTranslations(int $termId): array
    {
        $cacheKey = 'uml_term_translations_' . $termId;
        $cached = wp_cache_get($cacheKey, self::CACHE_GROUP);
        if (false !== $cached) {
            return $cached;
        }

        $groupId = get_term_meta($termId, self::META_GROUP_ID, true);
        if (empty($groupId)) {
            $lang = $this->getTermLanguage($termId);
            if ($lang) {
                $result = [$lang => $termId];
                wp_cache_set($cacheKey, $result, self::CACHE_GROUP);
                return $result;
            }
            return [];
        }

        global $wpdb;
        $termIds = $wpdb->get_col($wpdb->prepare(
            "SELECT term_id FROM {$wpdb->termmeta} WHERE meta_key = %s AND meta_value = %s",
            self::META_GROUP_ID,
            $groupId
        ));

        $translations = [];
        foreach ($termIds as $id) {
            $lang = $this->getTermLanguage((int) $id);
            if ($lang) {
                $translations[$lang] = (int) $id;
            }
        }

        wp_cache_set($cacheKey, $translations, self::CACHE_GROUP);

        return $translations;
    }
}
