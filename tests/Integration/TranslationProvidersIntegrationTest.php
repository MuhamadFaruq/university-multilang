<?php

declare(strict_types=1);

namespace UniversityMultilang\Tests\Integration;

use UniversityMultilang\Translation\Contracts\ContentTranslatorInterface;
use UniversityMultilang\Translation\Providers\NullTranslator;
use UniversityMultilang\Translation\Providers\GoogleTranslateProvider;
use UniversityMultilang\Translation\Providers\DeepLTranslateProvider;
use UniversityMultilang\Settings\Services\SettingsService;

class TranslationProvidersIntegrationTest extends IntegrationTestCase
{
    public function testContainerResolvesTranslatorBasedOnSettings(): void
    {
        $settings = $this->getService(SettingsService::class);

        $factory = $this->getService(\UniversityMultilang\Translation\Factories\TranslationProviderFactory::class);

        // 1. Default or 'null' provider
        $settings->setTranslationProvider('null');
        $translator1 = $factory->create();
        $this->assertInstanceOf(NullTranslator::class, $translator1);

        // 2. 'google' provider
        $settings->setTranslationProvider('google');
        $translator2 = $factory->create();
        $this->assertInstanceOf(GoogleTranslateProvider::class, $translator2);

        // 3. 'deepl' provider
        $settings->setTranslationProvider('deepl');
        $settings->setDeepLApiKey('sample_key:fx');
        $translator3 = $factory->create();
        $this->assertInstanceOf(DeepLTranslateProvider::class, $translator3);
    }
}
