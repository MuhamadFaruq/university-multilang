<?php

declare(strict_types=1);

namespace UniversityMultilang\Router;

use UniversityMultilang\Router\Contracts\WpRequestRepositoryInterface;
use UniversityMultilang\Router\Services\LanguageDetectorService;
use UniversityMultilang\Router\Services\UriModifierService;
use UniversityMultilang\Router\Services\CanonicalRedirectService;
use UniversityMultilang\Router\Services\RoutingGuardService;
use UniversityMultilang\Router\Services\RoutingContextService;
use UniversityMultilang\Router\DTOs\RequestContext;

class RequestProcessor
{
    private WpRequestRepositoryInterface $requestRepository;
    private LanguageDetectorService $detectorService;
    private UriModifierService $modifierService;
    private CanonicalRedirectService $redirectService;
    private RoutingGuardService $guardService;
    private ?RoutingContextService $contextService;

    private string $currentLanguage = '';

    public function __construct(
        WpRequestRepositoryInterface $requestRepository,
        LanguageDetectorService $detectorService,
        UriModifierService $modifierService,
        CanonicalRedirectService $redirectService,
        ?RoutingGuardService $guardService = null,
        ?RoutingContextService $contextService = null
    ) {
        $this->requestRepository = $requestRepository;
        $this->detectorService = $detectorService;
        $this->modifierService = $modifierService;
        $this->redirectService = $redirectService;
        $this->guardService = $guardService ?? new RoutingGuardService();
        $this->contextService = $contextService;
    }

    /**
     * Hooked early to modify $_SERVER['REQUEST_URI'] so WP routing works natively.
     */
    public function interceptRequest(): void
    {
        if ($this->requestRepository->isAdmin()) {
            return;
        }

        $rawUri = $this->requestRepository->getRequestUri();

        // Check if URI should bypass routing
        if ($this->guardService->shouldBypass($rawUri)) {
            $this->currentLanguage = '';
            if ($this->contextService !== null) {
                $this->contextService->setCurrentLanguage('');
            }
            return;
        }

        $context = new RequestContext($rawUri);

        // Detect Language
        $routingResult = $this->detectorService->detect($context);

        $detectedLang = $routingResult->getLanguageSlug();
        if (!empty($detectedLang)) {
            $this->currentLanguage = $detectedLang;
            if ($this->contextService !== null) {
                $this->contextService->setCurrentLanguage($detectedLang);
            }
        }

        // Handle Redirects (if any)
        $this->redirectService->handleRedirectIfNeeded($routingResult);

        // Modify URI for WordPress Core
        $modifiedUri = $this->modifierService->modifyUri($context, $routingResult);

        if ($modifiedUri !== $rawUri) {
            $this->requestRepository->setRequestUri($modifiedUri);
        }
    }

    public function getCurrentLanguage(): string
    {
        return $this->currentLanguage;
    }
}
