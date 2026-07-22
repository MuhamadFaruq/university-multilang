<?php

declare(strict_types=1);

namespace UniversityMultilang\Tests\Integration;

use UniversityMultilang\Setup\Activator;
use UniversityMultilang\Setup\Deactivator;

class SetupTest extends IntegrationTestCase
{
    public function testActivatorAndDeactivatorExistAndExecute(): void
    {
        $this->assertTrue(class_exists(Activator::class));
        $this->assertTrue(class_exists(Deactivator::class));

        Activator::activate();
        $this->assertNotEmpty(get_option('uml_plugin_installed'));

        Deactivator::deactivate();
        $this->assertTrue(true);
    }
}
