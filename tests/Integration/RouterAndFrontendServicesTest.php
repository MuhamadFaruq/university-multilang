<?php

declare(strict_types=1);

namespace UniversityMultilang\Tests\Integration;

use UniversityMultilang\Router\Services\LanguageDetectorService;
use UniversityMultilang\Router\Services\CanonicalRedirectService;
use UniversityMultilang\Router\DTOs\RequestContext;
use UniversityMultilang\Router\DTOs\RoutingResult;
use UniversityMultilang\Router\Repositories\WpRequestRepository;
use UniversityMultilang\Frontend\Services\UrlBuilderService;
use UniversityMultilang\Frontend\Repositories\WpContextRepository;
use UniversityMultilang\Frontend\DTOs\UrlContext;
use UniversityMultilang\Language\Services\LanguageService;
use UniversityMultilang\Language\Repositories\WpTermLanguageRepository;
use UniversityMultilang\Translation\Services\TranslationService;
use UniversityMultilang\Translation\Repositories\WpMetaTranslationRepository;
use Exception;

class RouterAndFrontendServicesTest extends IntegrationTestCase
{
    private LanguageService $languageService;
    private TranslationService $translationService;
    private LanguageDetectorService $detectorService;
    private CanonicalRedirectService $canonicalRedirectService;
    private UrlBuilderService $urlBuilderService;
    private WpContextRepository $contextRepository;

    public function setUp(): void
    {
        parent::setUp();

        $langRepo = new WpTermLanguageRepository();
        $this->languageService = new LanguageService($langRepo);

        $transRepo = new WpMetaTranslationRepository($langRepo);
        $this->translationService = new TranslationService($transRepo, $this->languageService);

        $this->detectorService = new LanguageDetectorService($this->languageService);

        $requestRepo = new WpRequestRepository();
        $this->canonicalRedirectService = new CanonicalRedirectService($requestRepo);

        $this->contextRepository = new WpContextRepository();
        $this->urlBuilderService = new UrlBuilderService($this->contextRepository, $this->translationService);

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
    }

    public function testLanguageDetectorDetectsLanguageFromUriPath(): void
    {
        $contextEn = new RequestContext('/en/about-us', 'example.org');
        $resultEn = $this->detectorService->detect($contextEn);

        $this->assertEquals('en', $resultEn->getLanguageSlug());

        $contextId = new RequestContext('/id/tentang-kami', 'example.org');
        $resultId = $this->detectorService->detect($contextId);

        $this->assertEquals('id', $resultId->getLanguageSlug());

        $contextNone = new RequestContext('/about-us', 'example.org');
        $resultNone = $this->detectorService->detect($contextNone);

        $this->assertNull($resultNone->getLanguageSlug());
    }

    public function testCanonicalRedirectServiceTriggersRedirectWhenNeeded(): void
    {
        $routingResult = new RoutingResult('en', true, '/en/target-url/');

        $redirectExecuted = false;
        add_filter('wp_redirect', function ($location, $status) use (&$redirectExecuted) {
            $redirectExecuted = true;
            $this->assertEquals('/en/target-url/', $location);
            $this->assertEquals(301, $status);
            throw new Exception('RedirectIntercepted');
        }, 10, 2);

        try {
            $this->canonicalRedirectService->handleRedirectIfNeeded($routingResult);
        } catch (Exception $e) {
            $this->assertEquals('RedirectIntercepted', $e->getMessage());
        }

        $this->assertTrue($redirectExecuted);
    }

    public function testCanonicalRedirectDoesNothingWhenNoRedirectNeeded(): void
    {
        $routingResult = new RoutingResult('en', false, '');

        // Should not throw or redirect
        $this->canonicalRedirectService->handleRedirectIfNeeded($routingResult);
        $this->assertFalse($routingResult->needsRedirect());
    }

    public function testUrlBuilderServiceBuildsPermalinkForSingularPublishedTranslation(): void
    {
        $postEn = $this->createPost(['post_title' => 'EN Post', 'post_status' => 'publish']);
        $postId = $this->createPost(['post_title' => 'ID Post', 'post_status' => 'publish']);

        $this->languageService->setLanguageForObject($postEn, 'post', 'en');
        $this->languageService->setLanguageForObject($postId, 'post', 'id');

        $this->translationService->linkTranslations($postEn, $postId, 'id', 'post');

        $urlContext = new UrlContext(true, false, $postEn);

        $urlId = $this->urlBuilderService->buildLanguageUrl($urlContext, 'id');
        $this->assertNotEmpty($urlId);
        $this->assertStringContainsString((string) $postId, $urlId);
    }

    public function testUrlBuilderServiceFallsBackToHomeWhenDraftTranslation(): void
    {
        $postEn = $this->createPost(['post_title' => 'EN Post Published', 'post_status' => 'publish']);
        $postId = $this->createPost(['post_title' => 'ID Post Draft', 'post_status' => 'draft']);

        $this->languageService->setLanguageForObject($postEn, 'post', 'en');
        $this->languageService->setLanguageForObject($postId, 'post', 'id');

        $this->translationService->linkTranslations($postEn, $postId, 'id', 'post');

        $urlContext = new UrlContext(true, false, $postEn);

        // SEO Safety: Draft target should fall back to home URL with slug prefix
        $urlId = $this->urlBuilderService->buildLanguageUrl($urlContext, 'id', true);
        $this->assertStringEndsWith('/id/', $urlId);

        // If fallbackToHome is false, it returns null
        $urlIdNoFallback = $this->urlBuilderService->buildLanguageUrl($urlContext, 'id', false);
        $this->assertNull($urlIdNoFallback);
    }
}
