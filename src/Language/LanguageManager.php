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
     * @return array|\WP_Error
     */
    public function addLanguage(string $name, string $slug)
    {
        return wp_insert_term($name, self::TAXONOMY, [
            'slug' => $slug,
        ]);
    }
}
