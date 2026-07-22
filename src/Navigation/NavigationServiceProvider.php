<?php

declare(strict_types=1);

namespace UniversityMultilang\Navigation;

use UniversityMultilang\Core\ServiceProvider;
use UniversityMultilang\Admin\MenuManager;
use UniversityMultilang\Navigation\Menus\MenuSyncMenu;
use UniversityMultilang\Router\RequestProcessor;
use UniversityMultilang\Language\Services\LanguageService;

class NavigationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind NavigationManager
        $this->container->bind(NavigationManager::class, function ($container) {
            return new NavigationManager($container->get(RequestProcessor::class));
        });

        // Bind Controller
        $this->container->bind(NavigationController::class, function ($container) {
            return new NavigationController($container->get(NavigationManager::class));
        });

        // Bind MenuSyncMenu
        $this->container->bind(MenuSyncMenu::class, function ($container) {
            return new MenuSyncMenu(
                $container->get(NavigationManager::class),
                $container->get(LanguageService::class)
            );
        });

        // Register hooks
        $navigationManager = $this->container->get(NavigationManager::class);

        // Filter theme_mod_nav_menu_locations
        $this->hooks->addFilter('theme_mod_nav_menu_locations', $navigationManager, 'filterNavMenuLocations');

        // Form submission
        $this->hooks->addAction('admin_init', $this->container->get(NavigationController::class), 'handleFormSubmission');
    }

    public function boot(): void
    {
        /** @var MenuManager $menuManager */
        $menuManager = $this->container->get(MenuManager::class);

        // Add our sub-menu
        $menuManager->addMenu($this->container->get(MenuSyncMenu::class));
    }
}
