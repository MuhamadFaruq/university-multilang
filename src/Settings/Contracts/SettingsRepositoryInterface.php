<?php

declare(strict_types=1);

namespace UniversityMultilang\Settings\Contracts;

interface SettingsRepositoryInterface
{
    /**
     * Get a setting value by key with optional default.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null);

    /**
     * Set/update a setting value by key.
     *
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public function set(string $key, $value): bool;

    /**
     * Delete a setting key.
     *
     * @param string $key
     * @return bool
     */
    public function delete(string $key): bool;
}
