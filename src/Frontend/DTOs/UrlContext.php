<?php

declare(strict_types=1);

namespace UniversityMultilang\Frontend\DTOs;

class UrlContext
{
    private bool $isSingular;
    private bool $isFrontPage;
    private int $queriedObjectId;

    public function __construct(bool $isSingular, bool $isFrontPage, int $queriedObjectId)
    {
        $this->isSingular = $isSingular;
        $this->isFrontPage = $isFrontPage;
        $this->queriedObjectId = $queriedObjectId;
    }

    public function isSingular(): bool
    {
        return $this->isSingular;
    }

    public function isFrontPage(): bool
    {
        return $this->isFrontPage;
    }

    public function getQueriedObjectId(): int
    {
        return $this->queriedObjectId;
    }
}
