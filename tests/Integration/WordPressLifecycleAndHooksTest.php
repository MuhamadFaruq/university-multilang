<?php

declare(strict_types=1);

namespace UniversityMultilang\Tests\Integration;

use UniversityMultilang\Language\Repositories\WpTermLanguageRepository;

class WordPressLifecycleAndHooksTest extends IntegrationTestCase
{
    public function testPluginActivationAndDeactivationHooks(): void
    {
        // Simulate activation
        $this->simulatePluginActivation();
        $this->assertTrue(taxonomy_exists(WpTermLanguageRepository::TAXONOMY));

        // Simulate deactivation
        $this->simulatePluginDeactivation();
        $this->assertTrue(true); // Ensured no exceptions during lifecycle
    }

    public function testRegisteredActionsAndFilters(): void
    {
        // Core Hooks
        $this->assertHasAction('init');
        $this->assertHasAction('admin_init');

        // Meta Boxes & Save Hooks
        $this->assertHasAction('add_meta_boxes');
        $this->assertHasAction('save_post');
        $this->assertHasAction('wp_insert_post');
        $this->assertHasAction('before_delete_post');

        // Frontend & SEO Hooks
        $this->assertHasAction('pre_get_posts');
        $this->assertHasAction('wp_enqueue_scripts');
        $this->assertHasAction('wp_head');

        // Router Filters
        $this->assertHasFilter('home_url');
        $this->assertHasFilter('post_link');
        $this->assertHasFilter('page_link');
        $this->assertHasFilter('post_type_link');
        $this->assertHasFilter('redirect_canonical');

        // Navigation Filters
        $this->assertHasFilter('theme_mod_nav_menu_locations');

        // Admin Actions
        $this->assertHasAction('admin_menu');
        $this->assertHasAction('wp_ajax_uml_bulk_sync_init');
        $this->assertHasAction('wp_ajax_uml_bulk_sync_process');
    }
}
