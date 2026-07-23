<?php

declare(strict_types=1);

namespace UniversityMultilang\Settings\Services;

use UniversityMultilang\Settings\Contracts\SettingsRepositoryInterface;

class SettingsService
{
    private SettingsRepositoryInterface $repository;

    public function __construct(SettingsRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getDefaultLanguage(): string
    {
        $value = $this->repository->get('default_language', '');
        return is_string($value) ? $value : '';
    }

    public function setDefaultLanguage(string $langSlug): bool
    {
        return $this->repository->set('default_language', sanitize_title($langSlug));
    }

    public function isHideDefaultLanguageEnabled(): bool
    {
        return (bool) $this->repository->get('hide_default_language', false);
    }

    public function setHideDefaultLanguage(bool $enabled): bool
    {
        return $this->repository->set('hide_default_language', $enabled);
    }

    public function getUrlStructure(): string
    {
        $value = $this->repository->get('url_structure', 'directory');
        return is_string($value) ? $value : 'directory';
    }

    public function setUrlStructure(string $structure): bool
    {
        $allowed = ['directory', 'query', 'subdomain'];
        $structure = in_array($structure, $allowed, true) ? $structure : 'directory';
        return $this->repository->set('url_structure', $structure);
    }

    public function isBrowserDetectionEnabled(): bool
    {
        return (bool) $this->repository->get('browser_detection', false);
    }

    public function setBrowserDetection(bool $enabled): bool
    {
        return $this->repository->set('browser_detection', $enabled);
    }

    public function isGeoRedirectEnabled(): bool
    {
        return (bool) $this->repository->get('geo_redirect', false);
    }

    public function setGeoRedirect(bool $enabled): bool
    {
        return $this->repository->set('geo_redirect', $enabled);
    }

    public function isHreflangEnabled(): bool
    {
        return (bool) $this->repository->get('hreflang_enabled', true);
    }

    public function setHreflangEnabled(bool $enabled): bool
    {
        return $this->repository->set('hreflang_enabled', $enabled);
    }

    public function isCanonicalEnabled(): bool
    {
        return (bool) $this->repository->get('canonical_enabled', true);
    }

    public function setCanonicalEnabled(bool $enabled): bool
    {
        return $this->repository->set('canonical_enabled', $enabled);
    }

    public function isAutoDuplicateDraftsEnabled(): bool
    {
        return (bool) $this->repository->get('auto_duplicate_drafts', true);
    }

    public function setAutoDuplicateDraftsEnabled(bool $enabled): bool
    {
        return $this->repository->set('auto_duplicate_drafts', $enabled);
    }

    public function getTranslationProvider(): string
    {
        $value = $this->repository->get('translation_provider', 'google');
        return is_string($value) ? $value : 'google';
    }

    public function setTranslationProvider(string $provider): bool
    {
        $allowed = ['null', 'google', 'deepl'];
        $provider = in_array($provider, $allowed, true) ? $provider : 'null';
        return $this->repository->set('translation_provider', $provider);
    }

    public function getDeepLApiKey(): string
    {
        $value = $this->repository->get('deepl_api_key', '');
        return is_string($value) ? $value : '';
    }

    public function setDeepLApiKey(string $apiKey): bool
    {
        return $this->repository->set('deepl_api_key', sanitize_text_field($apiKey));
    }
}
