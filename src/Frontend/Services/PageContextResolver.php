<?php

declare(strict_types=1);

namespace UniversityMultilang\Frontend\Services;

use UniversityMultilang\Frontend\Contracts\WpContextRepositoryInterface;
use UniversityMultilang\Frontend\DTOs\UrlContext;

class PageContextResolver
{
    private WpContextRepositoryInterface $contextRepository;

    public function __construct(WpContextRepositoryInterface $contextRepository)
    {
        $this->contextRepository = $contextRepository;
    }

    /**
     * Resolves the current WordPress context into a DTO.
     */
    public function resolveCurrentContext(): UrlContext
    {
        return new UrlContext(
            $this->contextRepository->isSingular(),
            $this->contextRepository->isFrontPage(),
            $this->contextRepository->getQueriedObjectId()
        );
    }
}
