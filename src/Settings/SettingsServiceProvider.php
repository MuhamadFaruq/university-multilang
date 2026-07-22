<?php

declare(strict_types=1);

namespace UniversityMultilang\Settings;

use UniversityMultilang\Core\ServiceProvider;
use UniversityMultilang\Admin\MenuManager;
use UniversityMultilang\Settings\Contracts\SettingsRepositoryInterface;
use UniversityMultilang\Settings\Repositories\WpSettingsRepository;
use UniversityMultilang\Settings\Services\SettingsService;
use UniversityMultilang\Settings\Menus\SettingsMenu;
use UniversityMultilang\Language\Services\LanguageService;

class SettingsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind Repository
        $this->container->bind(SettingsRepositoryInterface::class, function () {
            return new WpSettingsRepository();
        });

        // Bind Service
        $this->container->bind(SettingsService::class, function ($container) {
            return new SettingsService(
                $container->get(SettingsRepositoryInterface::class)
            );
        });

        // Bind Menu
        $this->container->bind(SettingsMenu::class, function ($container) {
            return new SettingsMenu(
                $container->get(SettingsService::class),
                $container->get(LanguageService::class)
            );
        });

        // Bind Controller
        $this->container->bind(SettingsController::class, function ($container) {
            return new SettingsController(
                $container->get(SettingsService::class)
            );
        });

        // Register form submit action & AJAX test connection
        $this->hooks->addAction('admin_post_uml_save_settings', $this->container->get(SettingsController::class), 'handleSaveSettings');
        $this->hooks->addAction('wp_ajax_uml_test_translation_connection', $this->container->get(SettingsController::class), 'handleTestTranslationConnection');
    }

    public function boot(): void
    {
        /** @var MenuManager $menuManager */
        $menuManager = $this->container->get(MenuManager::class);
        $menuManager->addMenu($this->container->get(SettingsMenu::class));
    }
}
