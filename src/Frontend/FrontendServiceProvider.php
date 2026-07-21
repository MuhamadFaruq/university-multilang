<?php

declare(strict_types=1);

namespace UniversityMultilang\Frontend;

use UniversityMultilang\Core\ServiceProvider;
use UniversityMultilang\Language\LanguageManager;
use UniversityMultilang\Translation\TranslationManager;

class FrontendServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind LanguageSwitcher
        $this->container->bind(LanguageSwitcher::class, function ($container) {
            return new LanguageSwitcher(
                $container->get(LanguageManager::class),
                $container->get(TranslationManager::class)
            );
        });

        // Register Shortcode
        add_shortcode('uml_language_switcher', [$this->container->get(LanguageSwitcher::class), 'shortcodeCallback']);
    }
}
