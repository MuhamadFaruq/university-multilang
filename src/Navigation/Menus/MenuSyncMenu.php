<?php

declare(strict_types=1);

namespace UniversityMultilang\Navigation\Menus;

use UniversityMultilang\Admin\Contracts\MenuInterface;
use UniversityMultilang\Navigation\NavigationManager;
use UniversityMultilang\Language\Services\LanguageService;

class MenuSyncMenu implements MenuInterface
{
    private NavigationManager $navigationManager;
    private LanguageService $languageService;

    public function __construct(NavigationManager $navigationManager, LanguageService $languageService)
    {
        $this->navigationManager = $navigationManager;
        $this->languageService = $languageService;
    }

    public function getParentSlug(): string
    {
        return 'university-multilang';
    }

    public function getPageTitle(): string
    {
        return 'Menu Sync';
    }

    public function getMenuTitle(): string
    {
        return 'Menu Sync';
    }

    public function getCapability(): string
    {
        return 'manage_options';
    }

    public function getSlug(): string
    {
        return 'uml-menu-sync';
    }

    public function getIcon(): string
    {
        return '';
    }

    public function getPosition(): ?int
    {
        return null;
    }

    public function render(): void
    {
        $mappings = $this->navigationManager->getMappings();
        $languages = $this->languageService->getAllLanguages();

        // Get registered theme locations
        global $_wp_registered_nav_menus;
        $locations = $_wp_registered_nav_menus ?? [];

        // Get all created menus
        $allMenus = wp_get_nav_menus();

        echo '<div class="wrap">';
        echo '<h1>Menu Synchronization</h1>';
        echo '<p>Map your navigation menus to different languages. When a visitor switches languages, the menu will automatically swap to the mapped menu.</p>';

        if (isset($_GET['updated']) && $_GET['updated'] === 'true') {
            echo '<div class="notice notice-success is-dismissible"><p>Menu mappings saved successfully.</p></div>';
        }

        if (empty($locations)) {
            echo '<div class="notice notice-warning"><p>Your current theme does not have any registered menu locations.</p></div>';
            echo '</div>';
            return;
        }

        if (empty($languages)) {
            echo '<div class="notice notice-warning"><p>No languages registered yet. Please add languages first.</p></div>';
            echo '</div>';
            return;
        }

        echo '<form method="post" action="' . esc_url(admin_url('admin.php?page=uml-menu-sync')) . '">';
        wp_nonce_field('uml_save_menu_sync_action', 'uml_menu_sync_nonce');

        echo '<table class="form-table" role="presentation">';
        echo '<tbody>';

        foreach ($locations as $locationSlug => $locationName) {
            echo '<tr>';
            echo '<th scope="row"><strong>' . esc_html($locationName) . '</strong> <br><small>(' . esc_html($locationSlug) . ')</small></th>';
            echo '<td>';

            foreach ($languages as $lang) {
                $currentMapping = $mappings[$locationSlug][$lang->getSlug()] ?? '';

                echo '<div style="margin-bottom: 10px;">';
                echo '<label style="display:inline-block; width: 100px;">' . esc_html($lang->getName()) . ':</label>';
                echo '<select name="uml_menu_mapping[' . esc_attr($locationSlug) . '][' . esc_attr($lang->getSlug()) . ']">';
                echo '<option value="">-- Do not translate --</option>';

                foreach ($allMenus as $menu) {
                    $selected = selected($currentMapping, $menu->term_id, false);
                    echo '<option value="' . esc_attr((string)$menu->term_id) . '" ' . $selected . '>' . esc_html($menu->name) . '</option>';
                }

                echo '</select>';
                echo '</div>';
            }

            echo '</td>';
            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';

        echo '<p class="submit">';
        echo '<input type="submit" name="uml_save_menu_sync" id="submit" class="button button-primary" value="Save Mappings">';
        echo '</p>';

        echo '</form>';
        echo '</div>';
    }
}
