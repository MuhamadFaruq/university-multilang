<?php

/**
 * Uninstall Handler for University Multilang.
 * Triggered when plugin is deleted from WordPress Admin.
 *
 * @package UniversityMultilang
 */

declare(strict_types=1);

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// List of all options created by University Multilang
$optionsToDelete = [
    'uml_plugin_installed',
    'uml_default_language',
    'uml_hide_default_language',
    'uml_url_structure',
    'uml_browser_detection',
    'uml_geo_redirect',
    'uml_hreflang_enabled',
    'uml_canonical_enabled',
    'uml_auto_duplicate_drafts',
    'uml_translation_provider',
    'uml_deepl_api_key',
    'uml_nav_menu_mappings',
];

foreach ($optionsToDelete as $optionName) {
    delete_option($optionName);
}
