<?php

declare(strict_types=1);

namespace UniversityMultilang\Tests\Unit;

use PHPUnit\Framework\TestCase;
use UniversityMultilang\Translation\Contracts\ContentTranslatorInterface;
use UniversityMultilang\Translation\Providers\NullTranslator;
use UniversityMultilang\Translation\Providers\DeepLTranslateProvider;
use UniversityMultilang\Translation\Providers\GoogleTranslateProvider;
use UniversityMultilang\Translation\Factories\TranslationProviderFactory;
use UniversityMultilang\Settings\Services\SettingsService;
use UniversityMultilang\Settings\Contracts\SettingsRepositoryInterface;

class TranslationProvidersTest extends TestCase
{
    public function testNullTranslatorReturnsOriginalText(): void
    {
        $translator = new NullTranslator();
        $this->assertInstanceOf(ContentTranslatorInterface::class, $translator);

        $text = 'Hello World';
        $translated = $translator->translate($text, 'en', 'id');
        $this->assertEquals($text, $translated);
    }

    public function testFactoryResolvesNullTranslatorByDefault(): void
    {
        $repoMock = $this->createMock(SettingsRepositoryInterface::class);
        $repoMock->method('get')->willReturn('null');

        $settings = new SettingsService($repoMock);
        $factory = new TranslationProviderFactory($settings);

        $provider = $factory->create();
        $this->assertInstanceOf(NullTranslator::class, $provider);
    }

    public function testFactoryResolvesDeepLProviderWhenConfigured(): void
    {
        $repoMock = $this->createMock(SettingsRepositoryInterface::class);
        $repoMock->method('get')->willReturnCallback(function ($key, $default) {
            if ($key === 'translation_provider') return 'deepl';
            if ($key === 'deepl_api_key') return 'dummy_key:fx';
            return $default;
        });

        $settings = new SettingsService($repoMock);
        $factory = new TranslationProviderFactory($settings);

        $provider = $factory->create();
        $this->assertInstanceOf(DeepLTranslateProvider::class, $provider);
    }
}
