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
    }

    public function boot(): void
    {
        /** @var MenuManager $menuManager */
        $menuManager = $this->container->get(MenuManager::class);

        // Add our Main Menu
        $menuManager->addMenu($this->container->get(MainMenu::class));

        // Let MenuManager register all its menus into HookManager
        $menuManager->registerToWordPress($this->hooks);
    }
}