<?php

declare(strict_types=1);

namespace UniversityMultilang\Tests\Integration;

use UniversityMultilang\Translation\Services\TranslationService;
use UniversityMultilang\Translation\Repositories\WpMetaTranslationRepository;
use UniversityMultilang\Language\Services\LanguageService;
use UniversityMultilang\Language\Repositories\WpTermLanguageRepository;
use InvalidArgumentException;
use DomainException;

class TranslationServiceTest extends IntegrationTestCase
{
    private TranslationService $translationService;
    private LanguageService $languageService;

    public function setUp(): void
    {
        parent::setUp();
        
        $langRepo = new WpTermLanguageRepository();
        $this->languageService = new LanguageService($langRepo);

        $transRepo = new WpMetaTranslationRepository($langRepo);
        $this->translationService = new TranslationService($transRepo, $this->languageService);

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
        if (!$this->languageService->getLanguageBySlug('fr')) {
            $this->languageService->addLanguage('French', 'fr', 'fr_FR');
        }
    }

    public function testCanLinkTranslationsSuccessfully(): void
    {
        $postEn = $this->createPost(['post_title' => 'EN Title']);
        $postId = $this->createPost(['post_title' => 'ID Title']);

        $this->languageService->setLanguageForObject($postEn, 'post', 'en');
        $this->languageService->setLanguageForObject($postId, 'post', 'id');

        $this->translationService->linkTranslations($postEn, $postId, 'id', 'post');

        $translations = $this->translationService->getTranslations($postEn, 'post');

        $this->assertCount(2, $translations);
        $this->assertEquals($postEn, $translations['en']);
        $this->assertEquals($postId, $translations['id']);
    }

    public function testLinkTranslationsWithSameSourceAndTargetThrowsInvalidArgumentException(): void
    {
        $post = $this->createPost(['post_title' => 'Self Post']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Source ID and Target ID cannot be the same.");

        $this->translationService->linkTranslations($post, $post, 'en', 'post');
    }

    public function testLinkTranslationsWithInvalidLanguageSlugThrowsInvalidArgumentException(): void
    {
        $postEn = $this->createPost(['post_title' => 'EN']);
        $postId = $this->createPost(['post_title' => 'ID']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid language slug provided: 'invalid_slug'.");

        $this->translationService->linkTranslations($postEn, $postId, 'invalid_slug', 'post');
    }

    public function testLinkTranslationsWithoutSourceLanguageThrowsDomainException(): void
    {
        $postEnWithoutLang = $this->createPost(['post_title' => 'No Lang']);
        $postId = $this->createPost(['post_title' => 'Has Lang']);

        $this->languageService->setLanguageForObject($postId, 'post', 'id');

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("Source object ID {$postEnWithoutLang} must have an assigned language before it can be translated.");

        $this->translationService->linkTranslations($postEnWithoutLang, $postId, 'id', 'post');
    }

    public function testLinkTargetAlreadyInAnotherGroupThrowsDomainException(): void
    {
        $postEn1 = $this->createPost(['post_title' => 'EN 1']);
        $postId1 = $this->createPost(['post_title' => 'ID 1']);

        $postEn2 = $this->createPost(['post_title' => 'EN 2']);
        $postId2 = $this->createPost(['post_title' => 'ID 2']);

        $this->languageService->setLanguageForObject($postEn1, 'post', 'en');
        $this->languageService->setLanguageForObject($postId1, 'post', 'id');

        $this->languageService->setLanguageForObject($postEn2, 'post', 'en');
        $this->languageService->setLanguageForObject($postId2, 'post', 'id');

        // Link group 1
        $this->translationService->linkTranslations($postEn1, $postId1, 'id', 'post');

        // Try linking postEn2 to postId1 (which is already in group 1)
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("Target object ID {$postId1} is already part of another translation group.");

        $this->translationService->linkTranslations($postEn2, $postId1, 'id', 'post');
    }

    public function testCanUnlinkTranslation(): void
    {
        $postEn = $this->createPost(['post_title' => 'EN Unlink']);
        $postId = $this->createPost(['post_title' => 'ID Unlink']);

        $this->languageService->setLanguageForObject($postEn, 'post', 'en');
        $this->languageService->setLanguageForObject($postId, 'post', 'id');

        $this->translationService->linkTranslations($postEn, $postId, 'id', 'post');

        // Unlink postEn
        $this->translationService->unlinkTranslation($postEn, 'post');

        $translationsEn = $this->translationService->getTranslations($postEn, 'post');
        $this->assertEmpty($translationsEn);

        // postId should still remain in group
        $translationsId = $this->translationService->getTranslations($postId, 'post');
        $this->assertCount(1, $translationsId);
        $this->assertEquals($postId, $translationsId['id']);
    }

    public function testUnlinkTranslationWithInvalidIdThrowsInvalidArgumentException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->translationService->unlinkTranslation(0, 'post');
    }
}
