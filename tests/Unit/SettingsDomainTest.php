<?php

declare(strict_types=1);

namespace UniversityMultilang\Tests\Unit;

use PHPUnit\Framework\TestCase;
use UniversityMultilang\Settings\Contracts\SettingsRepositoryInterface;
use UniversityMultilang\Settings\Services\SettingsService;

class SettingsDomainTest extends TestCase
{
    public function testSettingsInterfacesAndClassesExist(): void
    {
        $this->assertTrue(interface_exists(SettingsRepositoryInterface::class));
        $this->assertTrue(class_exists(SettingsService::class));
    }
}
