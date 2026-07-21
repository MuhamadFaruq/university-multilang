<?php

declare(strict_types=1);

namespace UniversityMultilang\Navigation;

class NavigationController
{
    private NavigationManager $navigationManager;

    public function __construct(NavigationManager $navigationManager)
    {
        $this->navigationManager = $navigationManager;
    }

    public function handleFormSubmission(): void
    {
        if (isset($_POST['uml_save_menu_sync']) && isset($_POST['uml_menu_sync_nonce'])) {
            if (!wp_verify_nonce($_POST['uml_menu_sync_nonce'], 'uml_save_menu_sync_action')) {
                wp_die('Security check failed.');
            }

            if (!current_user_can('manage_options')) {
                wp_die('Unauthorized access.');
            }

            $mappings = [];
            if (isset($_POST['uml_menu_mapping']) && is_array($_POST['uml_menu_mapping'])) {
                foreach ($_POST['uml_menu_mapping'] as $location => $langs) {
                    if (is_array($langs)) {
                        foreach ($langs as $langSlug => $menuId) {
                            $mappings[sanitize_text_field($location)][sanitize_title($langSlug)] = (int) $menuId;
                        }
                    }
                }
            }

            $this->navigationManager->saveMappings($mappings);

            wp_redirect(add_query_arg(['page' => 'uml-menu-sync', 'updated' => 'true'], admin_url('admin.php')));
            exit;
        }
    }
}
