<?php

declare(strict_types=1);

namespace UniversityMultilang\Language;

class LanguageManager
{
    public const TAXONOMY = 'language';

    public const CACHE_GROUP = 'uml_language_cache';
    public const CACHE_KEY_ALL_LANGS = 'uml_all_languages';

    /**
     * Get all registered languages.
     * 
     * @return \WP_Term[]
     */
    public function getLanguages(): array
    {
        $languages = wp_cache_get(self::CACHE_KEY_ALL_LANGS, self::CACHE_GROUP);
        if (false === $languages) {
            $languages = get_terms([
                'taxonomy'   => self::TAXONOMY,
                'hide_empty' => false,
                'orderby'    => 'term_id',
                'order'      => 'ASC',
            ]);
            
            if (is_wp_error($languages)) {
                $languages = [];
            }
            wp_cache_set(self::CACHE_KEY_ALL_LANGS, $languages, self::CACHE_GROUP);
        }

        return $languages;
    }

    /**
     * Register a new language.
     * 
     * @param string $name The language name (e.g., 'Indonesian')
     * @param string $slug The language slug (e.g., 'id')
     * @param string $locale The locale string (e.g., 'id_ID')
     * @return array|\WP_Error
     */
    public function addLanguage(string $name, string $slug, string $locale = '')
    {
        $result = wp_insert_term($name, self::TAXONOMY, [
            'slug' => $slug,
        ]);

        if (!is_wp_error($result)) {
            if (!empty($locale)) {
                $termId = (int) $result['term_id'];
                update_term_meta($termId, 'locale', $locale);
            }
            
            // Invalidate cache
            wp_cache_delete(self::CACHE_KEY_ALL_LANGS, self::CACHE_GROUP);
        }

        return $result;
    }

    /**
     * Get the locale of a language term.
     */
    public function getLocale(int $termId): string
    {
        return (string) get_term_meta($termId, 'locale', true);
    }
}
