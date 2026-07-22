<?php

declare(strict_types=1);

namespace UniversityMultilang\Tests\Integration;

use UniversityMultilang\Translation\Services\AutoDuplicateService;
use UniversityMultilang\Translation\Services\TranslationService;
use UniversityMultilang\Translation\Repositories\WpMetaTranslationRepository;
use UniversityMultilang\Translation\Repositories\WpPostRepository;
use UniversityMultilang\Translation\MachineTranslator;
use UniversityMultilang\Language\Services\LanguageService;
use UniversityMultilang\Language\Repositories\WpTermLanguageRepository;

class AutoDuplicateServiceTest extends IntegrationTestCase
{
    private AutoDuplicateService $autoDuplicateService;
    private TranslationService $translationService;
    private LanguageService $languageService;
    private WpPostRepository $postRepository;

    public function setUp(): void
    {
        parent::setUp();

        $langRepo = new WpTermLanguageRepository();
        $this->languageService = new LanguageService($langRepo);

        $transRepo = new WpMetaTranslationRepository($langRepo);
        $this->translationService = new TranslationService($transRepo, $this->languageService);

        $this->postRepository = new WpPostRepository();
        $providerFactory = $this->getService(\UniversityMultilang\Translation\Factories\TranslationProviderFactory::class);

        $this->autoDuplicateService = new AutoDuplicateService(
            $this->translationService,
            $this->languageService,
            $providerFactory,
            $this->postRepository
        );

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

    public function testDuplicatePublishedPostCreatesDraftForMissingLanguages(): void
    {
        $postId = $this->createPost([
            'post_title'   => 'Original Research Paper',
            'post_content' => 'Sample content for research paper.',
            'post_status'  => 'publish',
            'post_type'    => 'post',
        ]);

        $this->languageService->setLanguageForObject($postId, 'post', 'en');

        $wpPost = get_post($postId);
        $this->assertNotNull($wpPost);

        // Run auto duplicate
        $this->autoDuplicateService->duplicatePost($postId, $wpPost);

        $translations = $this->translationService->getTranslations($postId, 'post');

        $this->assertCount(2, $translations);
        $this->assertArrayHasKey('en', $translations);
        $this->assertArrayHasKey('id', $translations);

        $duplicatedPostId = $translations['id'];
        $duplicatedPost = get_post($duplicatedPostId);

        $this->assertNotNull($duplicatedPost);
        $this->assertEquals('draft', $duplicatedPost->post_status); // SEO Safety
        $this->assertNotEmpty($duplicatedPost->post_title);
    }

    public function testNonPublishedPostIsNotDuplicated(): void
    {
        $postId = $this->createPost([
            'post_title'  => 'Draft Paper',
            'post_status' => 'draft',
            'post_type'   => 'post',
        ]);

        $this->languageService->setLanguageForObject($postId, 'post', 'en');

        $wpPost = get_post($postId);
        $this->autoDuplicateService->duplicatePost($postId, $wpPost);

        $translations = $this->translationService->getTranslations($postId, 'post');
        $this->assertEmpty($translations);
    }

    public function testPostWithoutAssignedLanguageIsNotDuplicated(): void
    {
        $postId = $this->createPost([
            'post_title'  => 'No Language Post',
            'post_status' => 'publish',
            'post_type'   => 'post',
        ]);

        $wpPost = get_post($postId);
        $this->autoDuplicateService->duplicatePost($postId, $wpPost);

        $translations = $this->translationService->getTranslations($postId, 'post');
        $this->assertEmpty($translations);
    }
}
