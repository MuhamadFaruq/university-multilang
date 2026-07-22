<?php

declare(strict_types=1);

namespace UniversityMultilang\Translation\Factories;

use UniversityMultilang\Translation\Contracts\ContentTranslatorInterface;
use UniversityMultilang\Translation\Providers\NullTranslator;
use UniversityMultilang\Translation\Providers\GoogleTranslateProvider;
use UniversityMultilang\Translation\Providers\DeepLTranslateProvider;
use UniversityMultilang\Settings\Services\SettingsService;

class TranslationProviderFactory
{
    private SettingsService $settingsService;

    public function __construct(SettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
    }

    public function create(): ContentTranslatorInterface
    {
        $providerType = $this->settingsService->getTranslationProvider();

        switch ($providerType) {
            case 'deepl':
                $apiKey = $this->settingsService->getDeepLApiKey();
                return new DeepLTranslateProvider($apiKey);
            case 'google':
                return new GoogleTranslateProvider();
            case 'null':
            default:
                return new NullTranslator();
        }
    }
}
