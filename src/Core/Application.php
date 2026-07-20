<?php

declare(strict_types=1);

namespace UniversityMultilang\Core;

use UniversityMultilang\Admin\AdminServiceProvider;

class Application
{
    private Container $container;
    private HookManager $hooks;
    
    /**
     * @var ServiceProvider[]
     */
    private array $providers = [];

    public function __construct()
    {
        $this->container = new Container();
        $this->hooks = new HookManager();
        
        $this->container->instance(Container::class, $this->container);
        $this->container->instance(HookManager::class, $this->hooks);
        $this->container->instance(Application::class, $this);

        $this->registerProviders();
    }

    private function registerProviders(): void
    {
        // Define all service providers here
        $providerClasses = [
            \UniversityMultilang\Admin\AdminServiceProvider::class,
            \UniversityMultilang\Language\LanguageServiceProvider::class,
            \UniversityMultilang\Translation\TranslationServiceProvider::class,
        ];

        foreach ($providerClasses as $class) {
            $this->providers[] = new $class($this->container, $this->hooks);
        }
    }

    public function boot(): void
    {
        // 1. Register bindings & hooks
        foreach ($this->providers as $provider) {
            $provider->register();
        }

        // 2. Boot any final logic after all registrations
        foreach ($this->providers as $provider) {
            $provider->boot();
        }

        // 3. Register all collected hooks to WordPress
        $this->hooks->registerHooks();
    }
}