<?php

declare(strict_types=1);

namespace UniversityMultilang\Tests\Integration;

use UniversityMultilang\Router\UrlManager;
use UniversityMultilang\Language\Services\LanguageService;
use UniversityMultilang\Language\Repositories\WpTermLanguageRepository;

class TermPermalinkRoutingTest extends IntegrationTestCase
{
    private UrlManager $urlManager;
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

        $this->urlManager = $this->getService(UrlManager::class);
    }

    public function testFilterTermLinkAppendsLanguagePrefixWhenAssigned(): void
    {
        $termId = $this->factory()->category->create(['name' => 'Teknik', 'slug' => 'teknik']);
        $this->languageService->setLanguageForObject($termId, 'term', 'id');

        $term = get_term($termId, 'category');
        $rawLink = get_term_link($term, 'category');

        $filteredLink = $this->urlManager->filterTermLink((string) $rawLink, $term, 'category');

        $this->assertStringContainsString('/id/', $filteredLink);
    }
}
