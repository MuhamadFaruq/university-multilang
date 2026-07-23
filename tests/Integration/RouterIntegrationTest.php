<?php

declare(strict_types=1);

namespace UniversityMultilang\Tests\Integration;

use UniversityMultilang\Router\UrlManager;
use UniversityMultilang\Router\RequestProcessor;
use UniversityMultilang\Language\Services\LanguageService;
use UniversityMultilang\Language\Repositories\WpTermLanguageRepository;
use UniversityMultilang\Router\Repositories\WpRequestRepository;

class RouterIntegrationTest extends IntegrationTestCase
{
    private UrlManager $urlManager;
    private RequestProcessor $requestProcessor;
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

        $this->requestProcessor = $this->getService(RequestProcessor::class);
        $this->urlManager = $this->getService(UrlManager::class);
    }

    public function testFilterPostLinkAppendsLanguagePrefixWhenAssigned(): void
    {
        $postId = $this->createPost(['post_title' => 'Indonesian Post']);
        $this->languageService->setLanguageForObject($postId, 'post', 'id');

        $originalPermalink = 'http://example.org/indonesian-post/';
        $filteredPermalink = $this->urlManager->filterPostLink($originalPermalink, $postId);

        $this->assertStringContainsString('/id/', $filteredPermalink);
    }

    public function testFilterPostLinkReturnsOriginalWhenNoLanguage(): void
    {
        $postId = $this->createPost(['post_title' => 'No Language Post']);
        
        // Remove any auto-assigned language term from publish save
        wp_delete_object_term_relationships($postId, \UniversityMultilang\Language\Repositories\WpTermLanguageRepository::TAXONOMY);

        $originalPermalink = 'http://example.org/no-language-post/';
        $filteredPermalink = $this->urlManager->filterPostLink($originalPermalink, $postId);

        $this->assertEquals($originalPermalink, $filteredPermalink);
    }

    public function testInterceptRequestDetectsLanguageAndModifiesRequestUri(): void
    {
        $requestRepo = $this->getService(\UniversityMultilang\Router\Contracts\WpRequestRepositoryInterface::class);
        $requestRepo->setRequestUri('/id/fakultas/teknik/');

        $this->requestProcessor->interceptRequest();

        $this->assertEquals('id', $this->requestProcessor->getCurrentLanguage());
        $this->assertEquals('/fakultas/teknik/', $requestRepo->getRequestUri());
    }
}
