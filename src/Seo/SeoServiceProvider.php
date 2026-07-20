<?php

declare(strict_types=1);

namespace UniversityMultilang\Seo;

use UniversityMultilang\Core\ServiceProvider;
use UniversityMultilang\Language\LanguageManager;
use UniversityMultilang\Translation\TranslationManager;

class SeoServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind HreflangGenerator
        $this->container->bind(HreflangGenerator::class, function ($container) {
            return new HreflangGenerator(
                $container->get(LanguageManager::class),
                $container->get(TranslationManager::class)
            );
        });

        // Register hook for wp_head
        $this->hooks->addAction('wp_head', $this->container->get(HreflangGenerator::class), 'renderHreflang');
    }
}
