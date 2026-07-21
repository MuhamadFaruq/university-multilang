<?php

declare(strict_types=1);

namespace UniversityMultilang\Frontend;

use UniversityMultilang\Router\RequestProcessor;
use UniversityMultilang\Language\LanguageManager;

class QueryFilter
{
    private RequestProcessor $requestProcessor;
    private LanguageManager $languageManager;

    public function __construct(RequestProcessor $requestProcessor, LanguageManager $languageManager)
    {
        $this->requestProcessor = $requestProcessor;
        $this->languageManager = $languageManager;
    }

    /**
     * Hooked to 'pre_get_posts'.
     * Filters the main query to only include posts of the current language.
     */
    public function filterMainQuery(\WP_Query $query): void
    {
        // Don't interfere with the admin panel or non-main queries
        if (is_admin() || !$query->is_main_query()) {
            return;
        }

        // Only filter archive-like views (home, category, tag, search, date, etc)
        // We don't need to filter singular queries because the URL routing handles finding the exact post.
        if ($query->is_singular()) {
            return;
        }

        $currentLang = $this->requestProcessor->getCurrentLanguage();
        
        // If there's no language prefix in the URL, use the default language.
        if (empty($currentLang)) {
            $currentLang = $this->languageManager->getDefaultLanguageSlug();
        }
        
        if (!empty($currentLang)) {
            $taxQuery = $query->get('tax_query') ?: [];
            
            $taxQuery[] = [
                'taxonomy' => LanguageManager::TAXONOMY,
                'field'    => 'slug',
                'terms'    => $currentLang,
            ];

            $query->set('tax_query', $taxQuery);
        }
    }
}
