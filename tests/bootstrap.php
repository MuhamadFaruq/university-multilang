<?php

declare(strict_types=1);

/**
 * PHPUnit bootstrap file for Integration Tests.
 */

require_once dirname(__DIR__) . '/vendor/autoload.php';

if (!class_exists('Elementor\Widget_Base')) {
    abstract class ElementorWidgetStub
    {
        public function get_name(): string
        {
            return '';
        }
        public function get_title(): string
        {
            return '';
        }
        public function get_icon(): string
        {
            return '';
        }
        public function get_categories(): array
        {
            return [];
        }
    }
    class_alias(ElementorWidgetStub::class, 'Elementor\Widget_Base');
}

// First, check if wp-phpunit is installed
$wp_tests_dir = dirname(__DIR__) . '/vendor/wp-phpunit/wp-phpunit';
define('WP_TESTS_CONFIG_FILE_PATH', __DIR__ . '/wp-tests-config.php');

if (!file_exists($wp_tests_dir . '/includes/functions.php')) {
    echo "Error: WP Test suite not found. Run composer install.\n";
    exit(1);
}

// Give access to tests_add_filter() function.
require_once $wp_tests_dir . '/includes/functions.php';

// Setup SQLite db drop-in
$sqlite_plugin = dirname(__DIR__) . '/wp-content/wp-sqlite-db/src/db.php';
if (file_exists($sqlite_plugin)) {
    $wp_content_dir = __DIR__ . '/wp-content';
    
    if (!is_dir($wp_content_dir)) {
        @mkdir($wp_content_dir, 0777, true);
    }
    
    // Copy drop-in
    if (!file_exists($wp_content_dir . '/db.php')) {
        @copy($sqlite_plugin, $wp_content_dir . '/db.php');
    }
}

// Load the plugin being tested.
tests_add_filter('muplugins_loaded', function () {
    require dirname(__DIR__) . '/university-multilang.php';
});

// Start up the WP testing environment.
require $wp_tests_dir . '/includes/bootstrap.php';
