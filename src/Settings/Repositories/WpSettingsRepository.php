<?php

declare(strict_types=1);

namespace UniversityMultilang\Settings\Repositories;

use UniversityMultilang\Settings\Contracts\SettingsRepositoryInterface;

class WpSettingsRepository implements SettingsRepositoryInterface
{
    private const OPTION_PREFIX = 'uml_';

    public function get(string $key, $default = null)
    {
        $optionName = self::OPTION_PREFIX . $key;
        return get_option($optionName, $default);
    }

    public function set(string $key, $value): bool
    {
        $optionName = self::OPTION_PREFIX . $key;
        return update_option($optionName, $value);
    }

    public function delete(string $key): bool
    {
        $optionName = self::OPTION_PREFIX . $key;
        return delete_option($optionName);
    }
}
