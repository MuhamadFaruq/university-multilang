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
        // Don't interfere with the admin panel
        if (is_admin()) {
            return;
        }

        // We don't need to filter singular queries because the URL routing handles finding the exact post.
        // However, we DO want to filter custom loops, widgets, and archive views on the frontend.
        if ($query->is_main_query() && $query->is_singular()) {
            return;
        }
        
        // Also skip menu queries and media queries
        $postType = $query->get('post_type');
        if ($postType === 'nav_menu_item' || $postType === 'attachment') {
            return;
        }
        
        // error_log('QueryFilter running!');

        $currentLang = $this->requestProcessor->getCurrentLanguage();
        
        // If there's no language prefix in the URL, use the default language
        if (empty($currentLang)) {
            $defaultLang = get_option('uml_default_language');
            if (!empty($defaultLang)) {
                $currentLang = $defaultLang;
            } else {
                $languages = $this->languageManager->getLanguages();
                if (!empty($languages)) {
                    $currentLang = reset($languages)->slug;
                }
            }
        }
        
        if (!empty($currentLang)) {
            $taxQuery = $query->get('tax_query') ?: [];
            
            // To prevent our language filter from being swallowed by an existing 'OR' relation,
            // we wrap the existing tax_query and enforce an 'AND' relation with our language constraint.
            if (!empty($taxQuery)) {
                $taxQuery = [
                    'relation' => 'AND',
                    $taxQuery,
                    [
                        'taxonomy' => LanguageManager::TAXONOMY,
                        'field'    => 'slug',
                        'terms'    => $currentLang,
                    ]
                ];
            } else {
                $taxQuery = [
                    [
                        'taxonomy' => LanguageManager::TAXONOMY,
                        'field'    => 'slug',
                        'terms'    => $currentLang,
                    ]
                ];
            }

            // Prevent tax_query duplication if we run multiple times on the same query
            $query->set('tax_query', $taxQuery);
        }
    }
}
