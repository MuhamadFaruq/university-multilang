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
        
        // If there's no language prefix in the URL, fallback to the default language (first registered language)
        $isDefaultLanguage = false;
        if (empty($currentLang)) {
            $languages = $this->languageManager->getLanguages();
            
            if (!empty($languages)) {
                $currentLang = $languages[0]->slug;
                $isDefaultLanguage = true;
            } else {
                return; // No languages registered at all, don't filter.
            }
        }
        
        $taxQuery = $query->get('tax_query') ?: [];
        
        if ($isDefaultLanguage) {
            // For the default language view (e.g. root URL /), 
            // show posts explicitly tagged with the default language OR posts that have NO language (legacy posts).
            $taxQuery[] = [
                'relation' => 'OR',
                [
                    'taxonomy' => LanguageManager::TAXONOMY,
                    'field'    => 'slug',
                    'terms'    => $currentLang,
                ],
                [
                    'taxonomy' => LanguageManager::TAXONOMY,
                    'operator' => 'NOT EXISTS',
                ]
            ];
        } else {
            // For secondary languages (e.g. /en/), STRICTLY show only posts tagged with that exact language.
            $taxQuery[] = [
                'taxonomy' => LanguageManager::TAXONOMY,
                'field'    => 'slug',
                'terms'    => $currentLang,
            ];
        }

        $query->set('tax_query', $taxQuery);
    }
}
