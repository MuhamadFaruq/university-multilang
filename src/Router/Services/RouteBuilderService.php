<?php

declare(strict_types=1);

namespace UniversityMultilang\Router\Services;

class RouteBuilderService
{
    /**
     * Injects the language slug into an outbound URL.
     */
    public function addLanguagePrefix(string $url, string $languageSlug): string
    {
        if (empty($languageSlug)) {
            return $url;
        }

        $parsedUrl = parse_url($url);
        if (!$parsedUrl || !isset($parsedUrl['host'])) {
            return $url;
        }

        $scheme = isset($parsedUrl['scheme']) ? $parsedUrl['scheme'] . '://' : '';
        $host = $parsedUrl['host'];
        $port = isset($parsedUrl['port']) ? ':' . $parsedUrl['port'] : '';
        $path = $parsedUrl['path'] ?? '/';
        $query = isset($parsedUrl['query']) ? '?' . $parsedUrl['query'] : '';
        $fragment = isset($parsedUrl['fragment']) ? '#' . $parsedUrl['fragment'] : '';

        // Prevent double prefixing
        $pathParts = explode('/', ltrim($path, '/'));
        if (($pathParts[0] ?? '') === $languageSlug) {
            return $url;
        }

        // Inject language slug at the beginning of the path
        $path = '/' . $languageSlug . $path;

        return $scheme . $host . $port . $path . $query . $fragment;
    }
}
