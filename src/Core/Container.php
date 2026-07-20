<?php

declare(strict_types=1);

namespace UniversityMultilang\Core;

use Psr\Container\ContainerInterface;
use Exception;

class Container implements ContainerInterface
{
    /**
     * @var array<string, callable>
     */
    private array $bindings = [];

    /**
     * @var array<string, mixed>
     */
    private array $instances = [];

    public function get(string $id)
    {
        if ($this->hasInstance($id)) {
            return $this->instances[$id];
        }

        if (!$this->has($id)) {
            throw new Exception("Dependency {$id} not found in container.");
        }

        $this->instances[$id] = $this->bindings[$id]($this);

        return $this->instances[$id];
    }

    public function has(string $id): bool
    {
        return isset($this->bindings[$id]) || isset($this->instances[$id]);
    }

    public function hasInstance(string $id): bool
    {
        return isset($this->instances[$id]);
    }

    public function bind(string $id, callable $resolver): void
    {
        $this->bindings[$id] = $resolver;
    }

    public function instance(string $id, $instance): void
    {
        $this->instances[$id] = $instance;
    }
}
