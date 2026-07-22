<?php

declare(strict_types=1);

namespace UniversityMultilang\Router\Services;

class RoutingGuardService
{
    /**
     * Determines whether the given request URI should bypass language routing interception.
     *
     * @param string $requestUri
     * @return bool True if the URI should be bypassed, false otherwise.
     */
    public function shouldBypass(string $requestUri): bool
    {
        if (empty($requestUri)) {
            return false;
        }

        $parsed = parse_url($requestUri);
        $path = strtolower($parsed['path'] ?? '');
        $query = strtolower($parsed['query'] ?? '');

        // 1. REST API Guard
        if (str_starts_with($path, '/wp-json')) {
            return true;
        }

        // 2. Admin & System endpoints Guard
        if (str_starts_with($path, '/wp-admin') || str_contains($path, 'wp-login.php') || str_contains($path, 'wp-cron.php')) {
            return true;
        }

        // 3. Static/SEO files Guard
        if (in_array($path, ['/sitemap.xml', '/robots.txt', '/favicon.ico'], true)) {
            return true;
        }

        // 4. Preview & Elementor Editor Guard
        if (!empty($query)) {
            parse_str($query, $queryParams);
            if (isset($queryParams['preview']) || isset($queryParams['elementor-preview']) || isset($queryParams['action']) && $queryParams['action'] === 'elementor') {
                return true;
            }
        }

        return false;
    }
}
