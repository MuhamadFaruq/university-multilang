<?php

declare(strict_types=1);

namespace UniversityMultilang\Tests\Integration;

use UniversityMultilang\Translation\Metabox\LanguageMetabox;
use UniversityMultilang\Language\Services\LanguageService;
use UniversityMultilang\Language\Repositories\WpTermLanguageRepository;

class AdminIntegrationTest extends IntegrationTestCase
{
    private LanguageMetabox $languageMetabox;
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

        $this->languageMetabox = $this->getService(LanguageMetabox::class);
    }

    public function testRegisterMetaboxRegistersWpMetaBox(): void
    {
        global $wp_meta_boxes;

        $this->languageMetabox->registerMetabox();

        $this->assertArrayHasKey('post', $wp_meta_boxes);
        $this->assertArrayHasKey('side', $wp_meta_boxes['post']);
        $this->assertArrayHasKey('high', $wp_meta_boxes['post']['side']);
        $this->assertArrayHasKey('uml_language_metabox', $wp_meta_boxes['post']['side']['high']);
    }

    public function testRenderMetaboxOutputsHtmlOptions(): void
    {
        $postId = $this->createPost(['post_title' => 'Metabox Test Post']);
        $post = get_post($postId);
        $this->assertNotNull($post);

        ob_start();
        $this->languageMetabox->renderMetabox($post);
        $html = ob_get_clean();

        $this->assertStringContainsString('uml_language_metabox_nonce', $html);
        $this->assertStringContainsString('Select Language:', $html);
        $this->assertStringContainsString('English', $html);
    }

    public function testSavePostDataSavesLanguageWhenNonceIsValid(): void
    {
        $postId = $this->createPost(['post_title' => 'Save Metabox Test']);
        
        // Grant edit_post capability to current user in test suite
        $user = self::factory()->user->create_and_get(['role' => 'administrator']);
        wp_set_current_user($user->ID);

        $_POST['uml_language_metabox_nonce'] = wp_create_nonce('uml_save_post_language');
        $_POST['uml_post_language'] = 'en';

        $this->languageMetabox->savePostData($postId);

        $savedSlug = $this->languageService->getLanguageSlugForObject($postId, 'post');
        $this->assertEquals('en', $savedSlug);

        // Cleanup superglobal
        unset($_POST['uml_language_metabox_nonce'], $_POST['uml_post_language']);
    }
}
