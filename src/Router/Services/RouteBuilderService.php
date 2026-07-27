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

        // Determine base path (e.g., subdirectory like /staging)
        $basePath = '';
        if (function_exists('home_url') || function_exists(__NAMESPACE__ . '\\home_url')) {
            $homePath = parse_url(home_url('/'), PHP_URL_PATH);
            $basePath = rtrim((string) $homePath, '/');
        }

        $relPath = $path;
        if (!empty($basePath) && strpos($path, $basePath) === 0) {
            $relPath = substr($path, strlen($basePath));
            if (empty($relPath) || $relPath[0] !== '/') {
                $relPath = '/' . ltrim($relPath, '/');
            }
        }

        // Prevent double prefixing
        $pathParts = explode('/', ltrim($relPath, '/'));
        if (($pathParts[0] ?? '') === $languageSlug) {
            return $url;
        }

        // Inject language slug after base path
        $newPath = $basePath . '/' . $languageSlug . '/' . ltrim($relPath, '/');

        return $scheme . $host . $port . $newPath . $query . $fragment;
    }
}
