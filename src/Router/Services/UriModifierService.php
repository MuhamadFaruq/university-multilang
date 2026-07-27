<?php

declare(strict_types=1);

namespace UniversityMultilang\Router\Services;

use UniversityMultilang\Router\DTOs\RequestContext;
use UniversityMultilang\Router\DTOs\RoutingResult;

class UriModifierService
{
    /**
     * Removes the language slug from the URI so WordPress can route it natively.
     */
    public function modifyUri(RequestContext $context, RoutingResult $routingResult): string
    {
        $languageSlug = $routingResult->getLanguageSlug();
        $rawUri = $context->getRawUri();

        if (empty($languageSlug)) {
            return $rawUri;
        }

        $basePath = '';
        if (function_exists('home_url') || function_exists(__NAMESPACE__ . '\\home_url')) {
            $homePath = parse_url(home_url('/'), PHP_URL_PATH);
            $basePath = rtrim((string) $homePath, '/');
        }

        $targetPrefix = $basePath . '/' . $languageSlug;
        if (strpos($rawUri, $targetPrefix) === 0) {
            $nextChar = $rawUri[strlen($targetPrefix)] ?? '';
            if ($nextChar === '' || $nextChar === '/' || $nextChar === '?' || $nextChar === '#') {
                $remainder = substr($rawUri, strlen($targetPrefix));

                if (empty($remainder) || ($remainder[0] !== '/' && $remainder[0] !== '?')) {
                    $remainder = '/' . $remainder;
                } elseif ($remainder[0] === '?' && !empty($basePath)) {
                    $remainder = '/' . $remainder;
                }

                return $basePath . $remainder;
            }
        }

        return $rawUri;
    }
}
