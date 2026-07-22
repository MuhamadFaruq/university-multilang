<?php

declare(strict_types=1);

namespace UniversityMultilang\Tests\Integration;

use WP_UnitTestCase;
use UniversityMultilang\Core\Application;

class IntegrationTestCase extends WP_UnitTestCase
{
    protected Application $app;

    public function setUp(): void
    {
        parent::setUp();
        
        // Ensure our plugin app is booted
        global $university_multilang_app;
        
        if (!isset($university_multilang_app)) {
            $this->fail('Plugin application was not initialized.');
        }
        
        $this->app = $university_multilang_app;
    }
    
    /**
     * Resolves a dependency from the plugin's DI container.
     */
    protected function getService(string $id)
    {
        return $this->app->getContainer()->get($id);
    }

    // --- PHASE 5: PLUGIN LIFECYCLE INFRASTRUCTURE ---

    protected function simulatePluginActivation(): void
    {
        do_action('activate_' . plugin_basename(UML_PLUGIN_FILE));
    }

    protected function simulatePluginDeactivation(): void
    {
        do_action('deactivate_' . plugin_basename(UML_PLUGIN_FILE));
    }

    // --- PHASE 6: HOOK INFRASTRUCTURE ---

    protected function assertHasAction(string $tag, $function_to_check = false): void
    {
        $this->assertNotFalse(has_action($tag, $function_to_check), "Expected action '{$tag}' to be registered.");
    }

    protected function assertHasFilter(string $tag, $function_to_check = false): void
    {
        $this->assertNotFalse(has_filter($tag, $function_to_check), "Expected filter '{$tag}' to be registered.");
    }

    protected function assertDidAction(string $tag): void
    {
        $this->assertGreaterThan(0, did_action($tag), "Expected action '{$tag}' to have been fired.");
    }

    protected function assertDoingAction(string $tag = null): void
    {
        $this->assertTrue(doing_action($tag), "Expected to be doing action '{$tag}'.");
    }

    // --- PHASE 7: TESTING UTILITIES & FACTORY HELPERS ---

    protected function createLanguage(string $name, string $slug, string $locale = ''): \UniversityMultilang\Language\Domain\LanguageEntity
    {
        /** @var \UniversityMultilang\Language\Services\LanguageService $service */
        $service = $this->getService(\UniversityMultilang\Language\Services\LanguageService::class);
        return $service->addLanguage($name, $slug, $locale);
    }

    protected function createPost(array $args = []): int
    {
        return self::factory()->post->create($args);
    }

    protected function createTerm(string $name, string $taxonomy, array $args = []): int
    {
        return self::factory()->term->create(array_merge([
            'name'     => $name,
            'taxonomy' => $taxonomy
        ], $args));
    }

    protected function generateUuid(): string
    {
        return wp_generate_uuid4();
    }
}
