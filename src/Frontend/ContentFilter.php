<?php

declare(strict_types=1);

namespace UniversityMultilang\Frontend;

use UniversityMultilang\Router\RequestProcessor;
use UniversityMultilang\Translation\TranslationManager;
use UniversityMultilang\Language\LanguageManager;

class ContentFilter
{
    private RequestProcessor $requestProcessor;
    private TranslationManager $translationManager;

    public function __construct(RequestProcessor $requestProcessor, TranslationManager $translationManager)
    {
        $this->requestProcessor = $requestProcessor;
        $this->translationManager = $translationManager;
    }

    /**
     * Filters options like 'page_on_front' and 'page_for_posts' to 
     * return the translated page ID for the current language.
     */
    public function filterStaticPageOption(mixed $value): mixed
    {
        if (is_admin()) {
            return $value;
        }

        $currentLang = $this->requestProcessor->getCurrentLanguage();
        if (empty($currentLang)) {
            return $value;
        }

        $pageId = (int) $value;
        if ($pageId > 0) {
            $translations = $this->translationManager->getTranslations($pageId);
            if (isset($translations[$currentLang])) {
                $translatedId = (int) $translations[$currentLang];
                if (get_post_status($translatedId) === 'publish') {
                    return (string) $translatedId;
                }
            }
        }

        return $value;
    }

    /**
     * Filters main query to only show posts in the current language.
     */
    public function filterPreGetPosts(\WP_Query $query): void
    {
        // Only modify frontend main queries
        if (is_admin() || !$query->is_main_query()) {
            return;
        }

        // Don't interfere with single post lookups by slug (post_name)
        // because WP already found the exact post.
        if ($query->is_singular() && !empty($query->get('name'))) {
            return;
        }

        $currentLang = $this->requestProcessor->getCurrentLanguage();
        if (empty($currentLang)) {
            return;
        }

        // Force query to filter by language taxonomy
        $taxQuery = $query->get('tax_query');
        if (!is_array($taxQuery)) {
            $taxQuery = [];
        }

        $taxQuery[] = [
            'taxonomy' => LanguageManager::TAXONOMY,
            'field'    => 'slug',
            'terms'    => $currentLang,
        ];

        $query->set('tax_query', $taxQuery);
    }
}
