<?php

declare(strict_types=1);

namespace UniversityMultilang\Router\Services;

use UniversityMultilang\Language\Services\LanguageService;
use UniversityMultilang\Router\DTOs\RequestContext;
use UniversityMultilang\Router\DTOs\RoutingResult;

class LanguageDetectorService
{
    private LanguageService $languageService;

    public function __construct(LanguageService $languageService)
    {
        $this->languageService = $languageService;
    }

    /**
     * Detect the language from the request context.
     * Currently only path-based detection is supported.
     */
    public function detect(RequestContext $context): RoutingResult
    {
        $path = $context->getPath();
        $parts = explode('/', $path);
        $potentialSlug = $parts[0] ?? '';

        if (!empty($potentialSlug)) {
            $languages = $this->languageService->getAllLanguages();
            foreach ($languages as $lang) {
                if ($lang->getSlug() === $potentialSlug) {
                    return new RoutingResult($potentialSlug);
                }
            }
        }

        return new RoutingResult(null);
    }
}
