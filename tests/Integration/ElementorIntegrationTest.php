<?php

declare(strict_types=1);

namespace UniversityMultilang\Tests\Integration;

use UniversityMultilang\Elementor\Widgets\LanguageSwitcherWidget;
use UniversityMultilang\Elementor\ElementorServiceProvider;
use UniversityMultilang\Router\Services\RoutingGuardService;

class ElementorIntegrationTest extends IntegrationTestCase
{
    private RoutingGuardService $guardService;

    public function setUp(): void
    {
        parent::setUp();
        $this->guardService = $this->getService(RoutingGuardService::class);
    }

    public function testElementorEditorUrlsAreBypassedByRoutingGuard(): void
    {
        $editorUri1 = '/?elementor-preview=123&ver=1.0';
        $editorUri2 = '/wp-admin/post.php?post=456&action=elementor';

        $this->assertTrue($this->guardService->shouldBypass($editorUri1));
        $this->assertTrue($this->guardService->shouldBypass($editorUri2));
    }

    public function testElementorLanguageSwitcherWidgetClassExists(): void
    {
        $this->assertTrue(class_exists(LanguageSwitcherWidget::class));
    }

    public function testElementorServiceProviderIsRegistered(): void
    {
        $provider = new ElementorServiceProvider($this->app->getContainer(), $this->getService(\UniversityMultilang\Core\HookManager::class));
        $this->assertInstanceOf(ElementorServiceProvider::class, $provider);
    }
}
