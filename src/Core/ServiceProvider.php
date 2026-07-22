<?php

declare(strict_types=1);

namespace UniversityMultilang\Core;

abstract class ServiceProvider
{
    protected Container $container;
    protected HookManager $hooks;

    public function __construct(Container $container, HookManager $hooks)
    {
        $this->container = $container;
        $this->hooks = $hooks;
    }

    abstract public function register(): void;

    public function boot(): void
    {
        // Default empty implementation
    }
}
