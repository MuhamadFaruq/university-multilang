<?php

declare(strict_types=1);

namespace UniversityMultilang\Tests\Integration;

use UniversityMultilang\Router\Services\RoutingContextService;
use UniversityMultilang\Language\Services\LanguageService;
use UniversityMultilang\Language\Repositories\WpTermLanguageRepository;

class RoutingContextServiceTest extends IntegrationTestCase
{
    private RoutingContextService $contextService;
    private LanguageService $languageService;

    public function setUp(): void
    {
        parent::setUp();

        $langRepo = new WpTermLanguageRepository();
        $this->languageService = new LanguageService($langRepo);

        if (!taxonomy_exists(WpTermLanguageRepository::TAXONOMY)) {
            $provider = new \UniversityMultilang\Language\LanguageServiceProvider(
                $this->app->getContainer(),
                $this->getService(\UniversityMultilang\Core\HookManager::class)
            );
            $provider->registerTaxonomy();
        }

        if (!$this->languageService->getLanguageBySlug('en')) {
            $this->languageService->addLanguage('English', 'en', 'en_US');
        }
        if (!$this->languageService->getLanguageBySlug('id')) {
            $this->languageService->addLanguage('Indonesian', 'id', 'id_ID');
        }

        update_option('uml_default_language', 'en');

        $this->contextService = new RoutingContextService($this->languageService);
    }

    public function testRoutingContextServiceReturnsDefaultLanguageWhenNoLanguageSet(): void
    {
        $this->assertEquals('en', $this->contextService->getCurrentLanguage());
        $this->assertEquals('en', $this->contextService->getDefaultLanguage());
    }

    public function testRoutingContextServiceReturnsActiveLanguageWhenSet(): void
    {
        $this->contextService->setCurrentLanguage('id');
        $this->assertEquals('id', $this->contextService->getCurrentLanguage());
    }
}
