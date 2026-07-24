<?php

declare(strict_types=1);

namespace UniversityMultilang\Frontend;

use UniversityMultilang\Router\RequestProcessor;
use UniversityMultilang\Language\Services\LanguageService;
use UniversityMultilang\Language\Repositories\WpTermLanguageRepository;

class QueryFilter
{
    private RequestProcessor $requestProcessor;
    private LanguageService $languageService;

    public function __construct(RequestProcessor $requestProcessor, LanguageService $languageService)
    {
        $this->requestProcessor = $requestProcessor;
        $this->languageService = $languageService;
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

        // Also skip menu queries, media queries, block theme post types, and builder templates
        $postType = $query->get('post_type');
        $excludedPostTypes = [
            'nav_menu_item', 'attachment', 'wp_navigation', 'wp_template', 'wp_template_part', 
            'wp_global_styles', 'wp_block', 'elementor_library', 'kadence_element', 
            'et_pb_layout', 'fl-builder-template'
        ];
        
        if (is_array($postType)) {
            foreach ($excludedPostTypes as $excluded) {
                if (in_array($excluded, $postType, true)) {
                    return;
                }
            }
        } else {
            if (in_array($postType, $excludedPostTypes, true)) {
                return;
            }
        }

        // Avoid infinite loops
        if ($query->get('lang_filter_applied')) {
            return;
        }
        
        $currentLang = $this->requestProcessor->getCurrentLanguage();

        // If there's no language prefix in the URL, use the default language
        if (empty($currentLang)) {
            $defaultLang = get_option('uml_default_language');
            if (!empty($defaultLang)) {
                $currentLang = $defaultLang;
            } else {
                $languages = $this->languageService->getAllLanguages();
                if (!empty($languages)) {
                    $currentLang = reset($languages)->getSlug();
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
                        'taxonomy' => WpTermLanguageRepository::TAXONOMY,
                        'field'    => 'slug',
                        'terms'    => $currentLang,
                    ]
                ];
            } else {
                $taxQuery = [
                    [
                        'taxonomy' => WpTermLanguageRepository::TAXONOMY,
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
