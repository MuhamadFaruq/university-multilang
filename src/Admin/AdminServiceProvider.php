<?php

declare(strict_types=1);

namespace UniversityMultilang\Admin;

use UniversityMultilang\Core\ServiceProvider;
use UniversityMultilang\Admin\Menus\MainMenu;

class AdminServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // 1. Bind Controller to Container
        $this->container->bind(AdminController::class, function () {
            return new AdminController();
        });

        // 2. Bind MenuManager to Container (Singleton-like)
        $this->container->bind(MenuManager::class, function () {
            return new MenuManager();
        });

        // 3. Bind MainMenu to Container
        $this->container->bind(MainMenu::class, function ($container) {
            return new MainMenu($container->get(AdminController::class));
        });

        // Bind BulkSync components
        $this->container->bind(\UniversityMultilang\Admin\BulkSyncController::class, function ($container) {
            return new \UniversityMultilang\Admin\BulkSyncController(
                $container->get(\UniversityMultilang\Translation\TranslationController::class),
                $container->get(\UniversityMultilang\Language\Services\LanguageService::class)
            );
        });

        $this->container->bind(\UniversityMultilang\Admin\Menus\BulkSyncMenu::class, function () {
            return new \UniversityMultilang\Admin\Menus\BulkSyncMenu();
        });
    }

    public function boot(): void
    {
        /** @var MenuManager $menuManager */
        $menuManager = $this->container->get(MenuManager::class);

        // Add our Menus
        $menuManager->addMenu($this->container->get(MainMenu::class));
        $menuManager->addMenu($this->container->get(\UniversityMultilang\Admin\Menus\BulkSyncMenu::class));

        // Let MenuManager register all its menus into HookManager
        $menuManager->registerToWordPress($this->hooks);

        // Register BulkSync AJAX hooks
        $bulkSyncController = $this->container->get(\UniversityMultilang\Admin\BulkSyncController::class);
        $this->hooks->addAction('wp_ajax_uml_bulk_sync_init', $bulkSyncController, 'handleInitAjax');
        $this->hooks->addAction('wp_ajax_uml_bulk_sync_process', $bulkSyncController, 'handleProcessAjax');
    }
}
