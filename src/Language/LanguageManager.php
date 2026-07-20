<?php

declare(strict_types=1);

namespace UniversityMultilang\Language;

class LanguageManager
{
    public const TAXONOMY = 'language';

    /**
     * Get all registered languages.
     * 
     * @return \WP_Term[]
     */
    public function getLanguages(): array
    {
        $terms = get_terms([
            'taxonomy'   => self::TAXONOMY,
            'hide_empty' => false,
        ]);

        return is_array($terms) ? $terms : [];
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

        if (!is_wp_error($result) && !empty($locale)) {
            $termId = (int) $result['term_id'];
            update_term_meta($termId, 'locale', $locale);
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
