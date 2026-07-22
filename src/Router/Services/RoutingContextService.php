<?php

declare(strict_types=1);

namespace UniversityMultilang\Router\Services;

use UniversityMultilang\Language\Services\LanguageService;
use UniversityMultilang\Settings\Services\SettingsService;

class RoutingContextService
{
    private LanguageService $languageService;
    private ?SettingsService $settingsService;
    private string $currentLanguage = '';

    public function __construct(LanguageService $languageService, ?SettingsService $settingsService = null)
    {
        $this->languageService = $languageService;
        $this->settingsService = $settingsService;
    }

    public function setCurrentLanguage(string $langSlug): void
    {
        $this->currentLanguage = $langSlug;
    }

    public function getCurrentLanguage(): string
    {
        if (!empty($this->currentLanguage)) {
            return $this->currentLanguage;
        }

        return $this->getDefaultLanguage();
    }

    public function getDefaultLanguage(): string
    {
        if ($this->settingsService !== null) {
            $defaultLang = $this->settingsService->getDefaultLanguage();
            if (!empty($defaultLang)) {
                return $defaultLang;
            }
        }

        $defaultOption = get_option('uml_default_language');
        if (!empty($defaultOption) && is_string($defaultOption)) {
            return $defaultOption;
        }

        $languages = $this->languageService->getAllLanguages();
        if (!empty($languages)) {
            return reset($languages)->getSlug();
        }

        return '';
    }
}
