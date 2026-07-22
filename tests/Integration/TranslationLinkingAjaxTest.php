<?php

declare(strict_types=1);

namespace UniversityMultilang\Tests\Integration;

use UniversityMultilang\Translation\TranslationController;
use UniversityMultilang\Language\Services\LanguageService;
use UniversityMultilang\Language\Repositories\WpTermLanguageRepository;
use UniversityMultilang\Translation\Services\TranslationService;
use UniversityMultilang\Translation\Repositories\WpMetaTranslationRepository;
use UniversityMultilang\Translation\Services\AutoDuplicateService;
use UniversityMultilang\Translation\MachineTranslator;
use UniversityMultilang\Translation\Repositories\WpPostRepository;

class TranslationLinkingAjaxTest extends IntegrationTestCase
{
    private TranslationController $controller;
    private LanguageService $languageService;
    private TranslationService $translationService;

    public function setUp(): void
    {
        parent::setUp();

        $langRepo = new WpTermLanguageRepository();
        $this->languageService = new LanguageService($langRepo);

        $transRepo = new WpMetaTranslationRepository($langRepo);
        $this->translationService = new TranslationService($transRepo, $this->languageService);

        $autoDup = new AutoDuplicateService(
            $this->translationService,
            $this->languageService,
            $this->getService(\UniversityMultilang\Translation\Factories\TranslationProviderFactory::class),
            new WpPostRepository()
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

        $this->controller = new TranslationController(
            $this->translationService,
            $this->languageService,
            $autoDup
        );
    }

    public function testHandleLinkExistingPostLinksTwoPosts(): void
    {
        $postEn = $this->createPost(['post_title' => 'Standalone EN Post']);
        $postId = $this->createPost(['post_title' => 'Standalone ID Post']);

        $this->languageService->setLanguageForObject($postEn, 'post', 'en');
        $this->languageService->setLanguageForObject($postId, 'post', 'id');

        // Set admin user context first
        $userId = $this->factory()->user->create(['role' => 'administrator']);
        $this->assertIsInt($userId);
        wp_set_current_user((int) $userId);

        $_POST = [
            'from_post_id' => $postEn,
            'target_post_id' => $postId,
            'target_lang' => 'id',
            'nonce' => wp_create_nonce('uml_link_existing_post_nonce'),
        ];

        try {
            $this->controller->handleLinkExistingPost();
        } catch (\WPAjaxDieContinueException $e) {
            // Expected when wp_send_json_success is called in WP PHPUnit suite
        } catch (\WPAjaxDieStopException $e) {
            // Expected when wp_send_json_success dies
        }

        $translations = $this->translationService->getTranslations($postEn, 'post');
        $this->assertEquals($postId, $translations['id'] ?? null);
    }
}
