<?php

declare(strict_types=1);

namespace UniversityMultilang\Tests\Integration;

use UniversityMultilang\Core\Application;
use UniversityMultilang\Language\Services\LanguageService;
use UniversityMultilang\Translation\Services\TranslationService;
use UniversityMultilang\Translation\Services\AutoDuplicateService;
use UniversityMultilang\Frontend\Services\UrlBuilderService;
use UniversityMultilang\Router\Services\RouteBuilderService;
use UniversityMultilang\Router\Services\LanguageDetectorService;
use UniversityMultilang\Router\Services\UriModifierService;
use UniversityMultilang\Router\Services\CanonicalRedirectService;

class ContainerTest extends IntegrationTestCase
{
    public function testApplicationIsResolved(): void
    {
        $this->assertInstanceOf(Application::class, $this->app);
    }

    public function testLanguageServiceIsResolved(): void
    {
        $service = $this->getService(LanguageService::class);
        $this->assertInstanceOf(LanguageService::class, $service);
    }

    public function testTranslationServiceIsResolved(): void
    {
        $service = $this->getService(TranslationService::class);
        $this->assertInstanceOf(TranslationService::class, $service);
    }

    public function testAutoDuplicateServiceIsResolved(): void
    {
        $service = $this->getService(AutoDuplicateService::class);
        $this->assertInstanceOf(AutoDuplicateService::class, $service);
    }

    public function testUrlBuilderServiceIsResolved(): void
    {
        $service = $this->getService(UrlBuilderService::class);
        $this->assertInstanceOf(UrlBuilderService::class, $service);
    }

    public function testRouteBuilderServiceIsResolved(): void
    {
        $service = $this->getService(RouteBuilderService::class);
        $this->assertInstanceOf(RouteBuilderService::class, $service);
    }

    public function testLanguageDetectorServiceIsResolved(): void
    {
        $service = $this->getService(LanguageDetectorService::class);
        $this->assertInstanceOf(LanguageDetectorService::class, $service);
    }

    public function testUriModifierServiceIsResolved(): void
    {
        $service = $this->getService(UriModifierService::class);
        $this->assertInstanceOf(UriModifierService::class, $service);
    }

    public function testCanonicalRedirectServiceIsResolved(): void
    {
        $service = $this->getService(CanonicalRedirectService::class);
        $this->assertInstanceOf(CanonicalRedirectService::class, $service);
    }
}
