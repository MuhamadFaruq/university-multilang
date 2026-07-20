<?php

declare(strict_types=1);

namespace UniversityMultilang\Admin;

use UniversityMultilang\Core\ServiceProvider;

class AdminServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // 1. Bind controller to container
        $this->container->bind(AdminController::class, function () {
            return new AdminController();
        });

        // 2. Register hook via HookManager
        $this->hooks->addAction('admin_menu', $this, 'registerMenu');
    }

    public function registerMenu(): void
    {
        add_menu_page(
            'University Multilang',
            'University Multilang',
            'manage_options',
            'university-multilang',
            [$this->container->get(AdminController::class), 'renderDashboard'],
            'dashicons-translation'
        );
    }
}