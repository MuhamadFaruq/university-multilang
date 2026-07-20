<?php

declare(strict_types=1);

namespace UniversityMultilang\Admin\Menus;

use UniversityMultilang\Admin\Contracts\MenuInterface;
use UniversityMultilang\Admin\AdminController;

class MainMenu implements MenuInterface
{
    private AdminController $controller;

    public function __construct(AdminController $controller)
    {
        $this->controller = $controller;
    }

    public function getSlug(): string
    {
        return 'university-multilang';
    }

    public function getPageTitle(): string
    {
        return 'University Multilang';
    }

    public function getMenuTitle(): string
    {
        return 'University Multilang';
    }

    public function getCapability(): string
    {
        return 'manage_options';
    }

    public function render(): void
    {
        $this->controller->renderDashboard();
    }

    public function getParentSlug(): ?string
    {
        return null; // Top-level menu
    }

    public function getIcon(): string
    {
        return 'dashicons-translation';
    }

    public function getPosition(): ?int
    {
        return null; // Default position
    }
}
