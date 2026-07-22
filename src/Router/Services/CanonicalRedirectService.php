<?php

declare(strict_types=1);

namespace UniversityMultilang\Router\Services;

use UniversityMultilang\Router\DTOs\RequestContext;
use UniversityMultilang\Router\DTOs\RoutingResult;
use UniversityMultilang\Router\Contracts\WpRequestRepositoryInterface;

class CanonicalRedirectService
{
    private WpRequestRepositoryInterface $requestRepository;

    public function __construct(WpRequestRepositoryInterface $requestRepository)
    {
        $this->requestRepository = $requestRepository;
    }

    /**
     * Perform redirect if the routing result requires it.
     */
    public function handleRedirectIfNeeded(RoutingResult $result): void
    {
        if ($result->needsRedirect() && !empty($result->getRedirectUrl())) {
            $this->requestRepository->redirect($result->getRedirectUrl(), 301);
        }
    }
}
