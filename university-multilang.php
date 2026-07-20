<?php
declare(strict_types=1);

use UniversityMultilang\Setup\Activator;
use UniversityMultilang\Core\Application;

/**
 * Plugin Name: University Multilang
 * Plugin URI: https://github.com/faruq/university-multilang
 * Description: Modern multilingual plugin for WordPress.
 * Version: 1.0.0
 * Requires at least: 6.8
 * Requires PHP: 8.2
 * Author: Faruq
 * License: GPL-2.0-or-later
 * Text Domain: university-multilang
 */


if (! defined('ABSPATH')) {
    exit;
}

define('UML_PLUGIN_FILE', __FILE__);
define('UML_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('UML_PLUGIN_URL', plugin_dir_url(__FILE__));
define('UML_PLUGIN_VERSION', '1.0.0');

require_once UML_PLUGIN_PATH . 'vendor/autoload.php';

register_activation_hook(
    UML_PLUGIN_FILE,
    [Activator::class, 'activate']
);

$app = new Application();
$app->boot();