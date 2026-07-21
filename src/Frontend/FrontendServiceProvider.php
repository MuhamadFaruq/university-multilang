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

        // Bind ContentFilter
        $this->container->bind(ContentFilter::class, function ($container) {
            return new ContentFilter(
                $container->get(\UniversityMultilang\Router\RequestProcessor::class),
                $container->get(TranslationManager::class)
            );
        });

        // Register Shortcode
        add_shortcode('uml_language_switcher', [$this->container->get(LanguageSwitcher::class), 'shortcodeCallback']);

        // Register Content Filter Hooks
        $contentFilter = $this->container->get(ContentFilter::class);
        $this->hooks->addFilter('option_page_on_front', $contentFilter, 'filterStaticPageOption');
        $this->hooks->addFilter('option_page_for_posts', $contentFilter, 'filterStaticPageOption');
        $this->hooks->addAction('pre_get_posts', $contentFilter, 'filterPreGetPosts');
    }
}
