<?php

declare(strict_types=1);

namespace UniversityMultilang\Admin;

use UniversityMultilang\Admin\Contracts\MenuInterface;
use UniversityMultilang\Core\HookManager;

class MenuManager
{
    /**
     * @var MenuInterface[]
     */
    private array $menus = [];

    /**
     * Register a menu to the manager.
     */
    public function addMenu(MenuInterface $menu): void
    {
        $this->menus[] = $menu;
    }

    /**
     * Hook into WordPress to register all added menus.
     */
    public function registerToWordPress(HookManager $hookManager): void
    {
        $hookManager->addAction('admin_menu', $this, 'registerMenus');
    }

    /**
     * The callback that runs during the 'admin_menu' action.
     */
    public function registerMenus(): void
    {
        foreach ($this->menus as $menu) {
            $parentSlug = $menu->getParentSlug();

            if ($parentSlug === null) {
                add_menu_page(
                    $menu->getPageTitle(),
                    $menu->getMenuTitle(),
                    $menu->getCapability(),
                    $menu->getSlug(),
                    [$menu, 'render'],
                    $menu->getIcon(),
                    $menu->getPosition()
                );
            } else {
                add_submenu_page(
                    $parentSlug,
                    $menu->getPageTitle(),
                    $menu->getMenuTitle(),
                    $menu->getCapability(),
                    $menu->getSlug(),
                    [$menu, 'render'],
                    $menu->getPosition()
                );
            }
        }
    }
}
