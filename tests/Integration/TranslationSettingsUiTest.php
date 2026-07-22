<?php

declare(strict_types=1);

namespace UniversityMultilang\Tests\Integration;

use UniversityMultilang\Settings\Services\SettingsService;
use UniversityMultilang\Settings\SettingsController;
use UniversityMultilang\Settings\Menus\SettingsMenu;

class TranslationSettingsUiTest extends IntegrationTestCase
{
    private SettingsService $settingsService;

    public function setUp(): void
    {
        parent::setUp();
        $this->settingsService = $this->getService(SettingsService::class);
    }

    public function testSettingsMenuRendersTranslationProviderFields(): void
    {
        $menu = $this->getService(SettingsMenu::class);

        ob_start();
        $menu->render();
        $output = ob_get_clean();

        $this->assertStringContainsString('uml_translation_provider', $output);
        $this->assertStringContainsString('uml_deepl_api_key', $output);
    }

    public function testSettingsControllerSavesTranslationProviderAndApiKey(): void
    {
        $controller = $this->getService(SettingsController::class);

        $userId = $this->factory()->user->create(['role' => 'administrator']);
        $this->assertIsInt($userId);
        wp_set_current_user((int) $userId);

        $_POST = [
            'uml_translation_provider' => 'deepl',
            'uml_deepl_api_key'        => 'my_secret_key:fx',
            'uml_settings_nonce'       => wp_create_nonce('uml_save_settings_nonce'),
        ];

        try {
            $controller->handleSaveSettings();
        } catch (\WPDieException $e) {
            // Expected redirect/die in WP unit test runner
        }

        $this->assertEquals('deepl', $this->settingsService->getTranslationProvider());
        $this->assertEquals('my_secret_key:fx', $this->settingsService->getDeepLApiKey());
    }
}
