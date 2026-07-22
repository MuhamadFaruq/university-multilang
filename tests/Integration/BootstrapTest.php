<?php

declare(strict_types=1);

namespace UniversityMultilang\Tests\Integration;

use WP_UnitTestCase;

class BootstrapTest extends IntegrationTestCase
{
    public function testWordPressIsLoaded(): void
    {
        $this->assertTrue(function_exists('do_action'));
    }

    public function testPluginIsLoaded(): void
    {
        $this->assertTrue(class_exists(\UniversityMultilang\Core\Plugin::class));
    }

    public function testContainerIsAvailable(): void
    {
        $this->assertInstanceOf(\UniversityMultilang\Core\Container::class, $this->app->getContainer());
    }
}
