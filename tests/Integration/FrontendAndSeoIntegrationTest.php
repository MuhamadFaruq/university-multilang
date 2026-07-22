<?php

declare(strict_types=1);

namespace UniversityMultilang\Tests\Integration;

use UniversityMultilang\Seo\HreflangGenerator;
use UniversityMultilang\Frontend\QueryFilter;
use UniversityMultilang\Frontend\LanguageSwitcher;
use UniversityMultilang\Language\Services\LanguageService;
use UniversityMultilang\Language\Repositories\WpTermLanguageRepository;
use UniversityMultilang\Router\RequestProcessor;
use WP_Query;

class FrontendAndSeoIntegrationTest extends IntegrationTestCase
{
    private LanguageService $languageService;
    private HreflangGenerator $hreflangGenerator;
    private QueryFilter $queryFilter;
    private LanguageSwitcher $languageSwitcher;

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

        $this->hreflangGenerator = $this->getService(HreflangGenerator::class);
        $this->queryFilter = $this->getService(QueryFilter::class);
        $this->languageSwitcher = $this->getService(LanguageSwitcher::class);
    }

    public function testRenderHreflangOutputsHtmlTags(): void
    {
        ob_start();
        $this->hreflangGenerator->renderHreflang();
        $output = ob_get_clean();

        $this->assertStringContainsString('hreflang="en-US"', $output);
        $this->assertStringContainsString('hreflang="id-ID"', $output);
        $this->assertStringContainsString('hreflang="x-default"', $output);
    }

    public function testQueryFilterModifiesMainQueryTaxonomy(): void
    {
        $processor = $this->getService(RequestProcessor::class);
        $requestRepo = $this->getService(\UniversityMultilang\Router\Contracts\WpRequestRepositoryInterface::class);

        $requestRepo->setRequestUri('/id/berita/');
        $processor->interceptRequest();

        $wpQuery = new WP_Query();
        $wpQuery->is_main_query = true;

        $this->queryFilter->filterMainQuery($wpQuery);

        $taxQuery = $wpQuery->get('tax_query');
        $this->assertIsArray($taxQuery);
        $this->assertEquals('language', $taxQuery[0]['taxonomy']);
        $this->assertEquals('id', $taxQuery[0]['terms']);
    }

    public function testLanguageSwitcherRenderOutputsDropdownHtml(): void
    {
        $html = $this->languageSwitcher->renderSwitcher(['type' => 'dropdown']);

        $this->assertStringContainsString('uml-custom-dropdown', $html);
        $this->assertStringContainsString('English', $html);
        $this->assertStringContainsString('Indonesian', $html);
    }
}
