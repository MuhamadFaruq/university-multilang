<?php

declare(strict_types=1);

namespace UniversityMultilang\Language\Menus;

use UniversityMultilang\Admin\Contracts\MenuInterface;
use UniversityMultilang\Language\LanguageController;

class LanguageMenu implements MenuInterface
{
    private LanguageController $controller;

    public function __construct(LanguageController $controller)
    {
        $this->controller = $controller;
    }

    public function getSlug(): string
    {
        return 'university-multilang-languages';
    }

    public function getPageTitle(): string
    {
        return 'Languages';
    }

    public function getMenuTitle(): string
    {
        return 'Languages';
    }

    public function getCapability(): string
    {
        return 'manage_options';
    }

    public function render(): void
    {
        $this->controller->renderPage();
    }

    public function getParentSlug(): ?string
    {
        // This makes it a sub-menu under the main plugin menu
        return 'university-multilang';
    }

    public function getIcon(): string
    {
        return '';
    }

    public function getPosition(): ?int
    {
        return 1;
    }
}
