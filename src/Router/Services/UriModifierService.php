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

        $prefix = '/' . $languageSlug;
        if (strpos($rawUri, $prefix) === 0) {
            $newUri = substr($rawUri, strlen($prefix));

            // Ensure it doesn't become empty (e.g. /en -> /)
            if (empty($newUri) || $newUri === '?') {
                $newUri = '/' . $newUri;
            }

            return $newUri;
        }

        return $rawUri;
    }
}
