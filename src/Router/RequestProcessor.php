<?php

declare(strict_types=1);

namespace UniversityMultilang\Router;

use UniversityMultilang\Language\LanguageManager;

class RequestProcessor
{
    private LanguageManager $languageManager;
    private string $currentLanguage = '';

    public function __construct(LanguageManager $languageManager)
    {
        $this->languageManager = $languageManager;
    }

    /**
     * Hooked early to modify $_SERVER['REQUEST_URI'] so WP routing works natively.
     */
    public function interceptRequest(): void
    {
        if (is_admin()) {
            return;
        }

        $requestUri = $_SERVER['REQUEST_URI'] ?? '';
        $path = parse_url($requestUri, PHP_URL_PATH) ?: '';
        $path = ltrim($path, '/');

        // Extract potential language slug
        $parts = explode('/', $path);
        $potentialSlug = $parts[0] ?? '';

        if (!empty($potentialSlug)) {
            $languages = $this->languageManager->getLanguages();
            $languageSlugs = array_map(function ($lang) {
                return $lang->slug;
            }, $languages);

            if (in_array($potentialSlug, $languageSlugs, true)) {
                $this->currentLanguage = $potentialSlug;

                // Remove the language prefix from REQUEST_URI so WP doesn't see it
                // e.g. /en/about-us/ -> /about-us/
                $prefix = '/' . $potentialSlug;
                if (strpos($requestUri, $prefix) === 0) {
                    $newUri = substr($requestUri, strlen($prefix));
                    if (empty($newUri)) {
                        $newUri = '/';
                    }
                    $_SERVER['REQUEST_URI'] = $newUri;
                }
            }
        }
    }

    public function getCurrentLanguage(): string
    {
        return $this->currentLanguage;
    }
}
