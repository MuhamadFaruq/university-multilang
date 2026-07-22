<?php

declare(strict_types=1);

namespace UniversityMultilang\Seo;

use UniversityMultilang\Core\ServiceProvider;
use UniversityMultilang\Language\Services\LanguageService;
use UniversityMultilang\Frontend\Services\PageContextResolver;
use UniversityMultilang\Frontend\Services\UrlBuilderService;

class SeoServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind HreflangGenerator
        $this->container->bind(HreflangGenerator::class, function ($container) {
            return new HreflangGenerator(
                $container->get(LanguageService::class),
                $container->get(PageContextResolver::class),
                $container->get(UrlBuilderService::class)
            );
        });

        // Register hook for wp_head
        $this->hooks->addAction('wp_head', $this->container->get(HreflangGenerator::class), 'renderHreflang');
    }
}
