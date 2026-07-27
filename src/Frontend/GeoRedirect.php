<?php

declare(strict_types=1);

namespace UniversityMultilang\Frontend;

use UniversityMultilang\Language\Services\LanguageService;

class GeoRedirect
{
    private LanguageService $languageService;

    public function __construct(LanguageService $languageService)
    {
        $this->languageService = $languageService;
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

        $languages = $this->languageService->getAllLanguages();
        $mapping = [];

        foreach ($languages as $lang) {
            $locale = $lang->getLocale();

            // Extract the country code from the locale (e.g., id_ID -> ID)
            if (!empty($locale) && strpos($locale, '_') !== false) {
                $parts = explode('_', $locale);
                $countryCode = strtoupper(end($parts));

                // Construct the root URL for this language
                $parsedUrl = parse_url(home_url('/'));
                if ($parsedUrl && isset($parsedUrl['host'])) {
                    $scheme = isset($parsedUrl['scheme']) ? $parsedUrl['scheme'] . '://' : '';
                    $host = $parsedUrl['host'];
                    $port = isset($parsedUrl['port']) ? ':' . $parsedUrl['port'] : '';
                    $path = rtrim($parsedUrl['path'] ?? '', '/');
                    $langUrl = $scheme . $host . $port . $path . '/' . $lang->getSlug() . '/';

                    $mapping[$countryCode] = [
                        'slug' => $lang->getSlug(),
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
