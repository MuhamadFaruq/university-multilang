<?php

declare(strict_types=1);

namespace UniversityMultilang\Admin\Contracts;

interface MenuInterface
{
    /**
     * Get the menu slug.
     */
    public function getSlug(): string;

    /**
     * Get the page title.
     */
    public function getPageTitle(): string;

    /**
     * Get the menu title.
     */
    public function getMenuTitle(): string;

    /**
     * Get the capability required for this menu to be displayed to the user.
     */
    public function getCapability(): string;

    /**
     * Render the menu page.
     */
    public function render(): void;

    /**
     * Get the parent slug if this is a submenu.
     * Return null if it's a top-level menu.
     */
    public function getParentSlug(): ?string;

    /**
     * Get the icon URL or dashicon class (only for top-level menus).
     */
    public function getIcon(): string;

    /**
     * Get the position in the menu order.
     */
    public function getPosition(): ?int;
}
