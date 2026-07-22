<?php

declare(strict_types=1);

namespace UniversityMultilang\Tests\Integration;

use UniversityMultilang\Language\LanguageServiceProvider;
use UniversityMultilang\Translation\TranslationServiceProvider;
use UniversityMultilang\Frontend\FrontendServiceProvider;
use UniversityMultilang\Router\RouterServiceProvider;
use UniversityMultilang\Navigation\NavigationServiceProvider;
use UniversityMultilang\Seo\SeoServiceProvider;
use UniversityMultilang\Admin\AdminServiceProvider;

class ServiceProviderTest extends IntegrationTestCase
{
    public function testLanguageServiceProviderIsRegistered(): void
    {
        $provider = new LanguageServiceProvider($this->app->getContainer(), $this->getService(\UniversityMultilang\Core\HookManager::class));
        $this->assertInstanceOf(LanguageServiceProvider::class, $provider);
    }

    public function testTranslationServiceProviderIsRegistered(): void
    {
        $provider = new TranslationServiceProvider($this->app->getContainer(), $this->getService(\UniversityMultilang\Core\HookManager::class));
        $this->assertInstanceOf(TranslationServiceProvider::class, $provider);
    }

    public function testFrontendServiceProviderIsRegistered(): void
    {
        $provider = new FrontendServiceProvider($this->app->getContainer(), $this->getService(\UniversityMultilang\Core\HookManager::class));
        $this->assertInstanceOf(FrontendServiceProvider::class, $provider);
    }

    public function testRouterServiceProviderIsRegistered(): void
    {
        $provider = new RouterServiceProvider($this->app->getContainer(), $this->getService(\UniversityMultilang\Core\HookManager::class));
        $this->assertInstanceOf(RouterServiceProvider::class, $provider);
    }

    public function testNavigationServiceProviderIsRegistered(): void
    {
        $provider = new NavigationServiceProvider($this->app->getContainer(), $this->getService(\UniversityMultilang\Core\HookManager::class));
        $this->assertInstanceOf(NavigationServiceProvider::class, $provider);
    }

    public function testSeoServiceProviderIsRegistered(): void
    {
        $provider = new SeoServiceProvider($this->app->getContainer(), $this->getService(\UniversityMultilang\Core\HookManager::class));
        $this->assertInstanceOf(SeoServiceProvider::class, $provider);
    }

    public function testAdminServiceProviderIsRegistered(): void
    {
        $provider = new AdminServiceProvider($this->app->getContainer(), $this->getService(\UniversityMultilang\Core\HookManager::class));
        $this->assertInstanceOf(AdminServiceProvider::class, $provider);
    }
}
