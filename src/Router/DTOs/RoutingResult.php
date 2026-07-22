<?php

declare(strict_types=1);

namespace UniversityMultilang\Router\DTOs;

class RoutingResult
{
    private ?string $languageSlug;
    private bool $needsRedirect;
    private string $redirectUrl;

    public function __construct(?string $languageSlug, bool $needsRedirect = false, string $redirectUrl = '')
    {
        $this->languageSlug = $languageSlug;
        $this->needsRedirect = $needsRedirect;
        $this->redirectUrl = $redirectUrl;
    }

    public function getLanguageSlug(): ?string
    {
        return $this->languageSlug;
    }

    public function needsRedirect(): bool
    {
        return $this->needsRedirect;
    }

    public function getRedirectUrl(): string
    {
        return $this->redirectUrl;
    }
}
