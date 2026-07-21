<?php

declare(strict_types=1);

namespace UniversityMultilang\Frontend;

use UniversityMultilang\Language\LanguageManager;

class GeoRedirect
{
    private LanguageManager $languageManager;

    public function __construct(LanguageManager $languageManager)
    {
        $this->languageManager = $languageManager;
    }

    public function enqueueScripts(): void
    {
        // We only need to run this on the frontend, not in the admin area
        if (is_admin()) {
            return;
        }

        wp_enqueue_script(
            'uml-geo-redirect-js',
            plugin_dir_url(__FILE__) . '../../assets/js/geo-redirect.js',
            [],
            '1.0.0',
            true
        );

        $languages = $this->languageManager->getLanguages();
        $mapping = [];

        foreach ($languages as $lang) {
            $termId = (int) $lang->term_id;
            $locale = $this->languageManager->getLocale($termId);
            
            // Extract the country code from the locale (e.g., id_ID -> ID)
            if (!empty($locale) && strpos($locale, '_') !== false) {
                $parts = explode('_', $locale);
                $countryCode = strtoupper(end($parts));
                
                // Construct the root URL for this language
                $parsedUrl = parse_url(home_url('/'));
                if ($parsedUrl && isset($parsedUrl['host'])) {
                    $scheme = isset($parsedUrl['scheme']) ? $parsedUrl['scheme'] . '://' : '';
                    $langUrl = $scheme . $parsedUrl['host'] . '/' . $lang->slug . '/';
                    
                    $mapping[$countryCode] = [
                        'slug' => $lang->slug,
                        'url'  => $langUrl,
                    ];
                }
            }
        }

        // Pass the mapping and the current root URL to JavaScript
        wp_localize_script('uml-geo-redirect-js', 'umlGeoData', [
            'mapping' => $mapping,
            'rootUrl' => home_url('/'),
        ]);
    }
}
