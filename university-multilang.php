<?php

declare(strict_types=1);

use UniversityMultilang\Setup\Activator;
use UniversityMultilang\Setup\Deactivator;
use UniversityMultilang\Core\Application;

/**
 * Plugin Name: University Multilang
 * Plugin URI: https://github.com/faruq/university-multilang
 * Description: Modern multilingual plugin for WordPress.
 * Version: 1.0.0
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Author: Faruq
 * License: GPL-2.0-or-later
 * Text Domain: university-multilang
 * Domain Path: /languages
 */


if (! defined('ABSPATH')) {
    exit;
}

register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR])) {
        while (ob_get_level()) { ob_end_clean(); }
        
        $errorMsg = "Type: {$error['type']} | Message: {$error['message']} | File: {$error['file']} | Line: {$error['line']}";
        
        if (wp_doing_ajax() || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')) {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'data' => ['message' => 'FATAL ERROR: ' . $errorMsg]
            ]);
        } else {
            echo "<h1>FATAL ERROR INTERCEPTED</h1>";
            echo "<p>Please copy this message and show it to Faruq:</p>";
            echo "<pre style='background:#f4f4f4; padding:15px; border:1px solid #ccc; white-space:pre-wrap;'>";
            echo $errorMsg;
            echo "</pre>";
        }
        exit;
    }
});

define('UML_PLUGIN_FILE', __FILE__);
define('UML_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('UML_PLUGIN_URL', plugin_dir_url(__FILE__));
define('UML_PLUGIN_VERSION', '1.0.0');

try {
    require_once UML_PLUGIN_PATH . 'vendor/autoload.php';

    register_activation_hook(
        UML_PLUGIN_FILE,
        [Activator::class, 'activate']
    );

    register_deactivation_hook(
        UML_PLUGIN_FILE,
        [Deactivator::class, 'deactivate']
    );

    add_action('plugins_loaded', function () {
        load_plugin_textdomain('university-multilang', false, dirname(plugin_basename(UML_PLUGIN_FILE)) . '/languages');
    });

    global $university_multilang_app;
    $university_multilang_app = new Application();
    $university_multilang_app->boot();
} catch (\Throwable $e) {
    $errorMsg = "FATAL ERROR CAUGHT: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine();
    
    // Add admin notice so the user sees it in WP Admin
    add_action('admin_notices', function() use ($errorMsg) {
        echo '<div class="notice notice-error"><p style="font-size: 16px; font-weight: bold; color: red;">' . esc_html($errorMsg) . '</p><p>Mohon copy pesan merah di atas ini dan berikan ke Faruq.</p></div>';
    });
}