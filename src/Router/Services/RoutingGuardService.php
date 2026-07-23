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
        if (strpos($path, '/wp-json') === 0) {
            return true;
        }

        // 2. Admin & System endpoints Guard
        if (strpos($path, '/wp-admin') === 0 || strpos($path, 'wp-login.php') !== false || strpos($path, 'wp-cron.php') !== false) {
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
