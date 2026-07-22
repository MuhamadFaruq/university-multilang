<?php

declare(strict_types=1);

namespace UniversityMultilang\Tests\Integration;

use UniversityMultilang\Settings\Services\SettingsService;
use UniversityMultilang\Settings\SettingsController;
use UniversityMultilang\Settings\Menus\SettingsMenu;

class SettingsServiceIntegrationTest extends IntegrationTestCase
{
    private SettingsService $settingsService;

    public function setUp(): void
    {
        parent::setUp();
        $this->settingsService = $this->getService(SettingsService::class);
    }

    public function testSettingsServiceGetsAndSetsOptionsCorrectly(): void
    {
        $this->settingsService->setDefaultLanguage('id');
        $this->assertEquals('id', $this->settingsService->getDefaultLanguage());

        $this->settingsService->setHideDefaultLanguage(true);
        $this->assertTrue($this->settingsService->isHideDefaultLanguageEnabled());

        $this->settingsService->setUrlStructure('query');
        $this->assertEquals('query', $this->settingsService->getUrlStructure());

        $this->settingsService->setBrowserDetection(true);
        $this->assertTrue($this->settingsService->isBrowserDetectionEnabled());

        $this->settingsService->setGeoRedirect(true);
        $this->assertTrue($this->settingsService->isGeoRedirectEnabled());

        $this->settingsService->setHreflangEnabled(false);
        $this->assertFalse($this->settingsService->isHreflangEnabled());

        $this->settingsService->setCanonicalEnabled(false);
        $this->assertFalse($this->settingsService->isCanonicalEnabled());

        $this->settingsService->setAutoDuplicateDraftsEnabled(false);
        $this->assertFalse($this->settingsService->isAutoDuplicateDraftsEnabled());
    }

    public function testSettingsMenuRendersHtmlForm(): void
    {
        $menu = $this->getService(SettingsMenu::class);
        $this->assertEquals('uml-settings', $menu->getSlug());

        ob_start();
        $menu->render();
        $output = ob_get_clean();

        $this->assertStringContainsString('University Multilang Settings', $output);
        $this->assertStringContainsString('uml_default_language', $output);
        $this->assertStringContainsString('uml_hide_default_language', $output);
        $this->assertStringContainsString('uml_url_structure', $output);
    }

    public function testSettingsControllerSavesSettings(): void
    {
        $controller = $this->getService(SettingsController::class);

        $userId = $this->factory()->user->create(['role' => 'administrator']);
        $this->assertIsInt($userId);
        wp_set_current_user((int) $userId);

        $_POST = [
            'uml_default_language' => 'es',
            'uml_hide_default_language' => '1',
            'uml_url_structure' => 'directory',
            'uml_browser_detection' => '1',
            'uml_geo_redirect' => '0',
            'uml_hreflang_enabled' => '1',
            'uml_canonical_enabled' => '1',
            'uml_auto_duplicate_drafts' => '1',
            'uml_settings_nonce' => wp_create_nonce('uml_save_settings_nonce'),
        ];

        try {
            $controller->handleSaveSettings();
        } catch (\WPDieException $e) {
            // Expected redirect/die in WP unit test runner
        }

        $this->assertEquals('es', $this->settingsService->getDefaultLanguage());
        $this->assertTrue($this->settingsService->isHideDefaultLanguageEnabled());
        $this->assertTrue($this->settingsService->isBrowserDetectionEnabled());
    }
}
