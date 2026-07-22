<?php

declare(strict_types=1);

namespace UniversityMultilang\Tests\Integration;

use UniversityMultilang\Translation\Metabox\LanguageMetabox;
use UniversityMultilang\Language\Services\LanguageService;
use UniversityMultilang\Language\Repositories\WpTermLanguageRepository;
use UniversityMultilang\Translation\Services\TranslationService;
use UniversityMultilang\Translation\Repositories\WpMetaTranslationRepository;

class EnhancedLanguageMetaboxTest extends IntegrationTestCase
{
    private LanguageMetabox $metabox;
    private LanguageService $languageService;
    private TranslationService $translationService;

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

        $this->metabox = new LanguageMetabox($this->languageService, $this->translationService);
    }

    public function testRenderMetaboxDisplaysStatusBadgeAndUnlinkOption(): void
    {
        $postEn = $this->createPost(['post_title' => 'EN Post Metabox Test', 'post_status' => 'publish']);
        $postId = $this->createPost(['post_title' => 'ID Post Metabox Test', 'post_status' => 'draft']);

        $this->languageService->setLanguageForObject($postEn, 'post', 'en');
        $this->languageService->setLanguageForObject($postId, 'post', 'id');

        $this->translationService->linkTranslations($postEn, $postId, 'id', 'post');

        $postObj = get_post($postEn);
        ob_start();
        $this->metabox->renderMetabox($postObj);
        $output = ob_get_clean();

        // Check for status badge (draft)
        $this->assertStringContainsString('Draft', $output);
        // Check for Unlink option/button
        $this->assertStringContainsString('uml_unlink_lang', $output);
    }
}
