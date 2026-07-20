<?php

declare(strict_types=1);

namespace UniversityMultilang\Language;

use UniversityMultilang\Core\ServiceProvider;
use UniversityMultilang\Admin\MenuManager;
use UniversityMultilang\Language\Menus\LanguageMenu;

class LanguageServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind Manager
        $this->container->bind(LanguageManager::class, function () {
            return new LanguageManager();
        });

        // Bind Controller
        $this->container->bind(LanguageController::class, function ($container) {
            return new LanguageController($container->get(LanguageManager::class));
        });

        // Bind Menu
        $this->container->bind(LanguageMenu::class, function ($container) {
            return new LanguageMenu($container->get(LanguageController::class));
        });

        // Register custom taxonomy hook
        $this->hooks->addAction('init', $this, 'registerTaxonomy');
        
        // Hook form processing
        $this->hooks->addAction('admin_init', $this->container->get(LanguageController::class), 'handleFormSubmission');
    }

    public function registerTaxonomy(): void
    {
        $labels = [
            'name'          => 'Languages',
            'singular_name' => 'Language',
            'search_items'  => 'Search Languages',
            'all_items'     => 'All Languages',
            'edit_item'     => 'Edit Language',
            'update_item'   => 'Update Language',
            'add_new_item'  => 'Add New Language',
            'new_item_name' => 'New Language Name',
            'menu_name'     => 'Languages',
        ];

        $args = [
            'labels'            => $labels,
            'public'            => false, // Hidden from standard UI
            'show_ui'           => false, // We use our custom UI
            'show_in_nav_menus' => false,
            'show_admin_column' => true,
            'hierarchical'      => false,
            'query_var'         => true,
            'rewrite'           => false,
        ];

        register_taxonomy(LanguageManager::TAXONOMY, ['post', 'page'], $args);
    }

    public function boot(): void
    {
        /** @var MenuManager $menuManager */
        $menuManager = $this->container->get(MenuManager::class);

        // Add our sub-menu
        $menuManager->addMenu($this->container->get(LanguageMenu::class));
    }
}
