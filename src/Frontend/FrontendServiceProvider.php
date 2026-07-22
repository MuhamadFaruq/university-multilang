<?php

declare(strict_types=1);

namespace UniversityMultilang\Frontend;

use UniversityMultilang\Core\ServiceProvider;
use UniversityMultilang\Language\Services\LanguageService;
use UniversityMultilang\Translation\Services\TranslationService;
use UniversityMultilang\Frontend\Contracts\WpContextRepositoryInterface;
use UniversityMultilang\Frontend\Repositories\WpContextRepository;
use UniversityMultilang\Frontend\Services\PageContextResolver;
use UniversityMultilang\Frontend\Services\UrlBuilderService;

class FrontendServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind Context Repository
        $this->container->bind(WpContextRepositoryInterface::class, function () {
            return new WpContextRepository();
        });

        // Bind Context Resolver
        $this->container->bind(PageContextResolver::class, function ($container) {
            return new PageContextResolver($container->get(WpContextRepositoryInterface::class));
        });

        // Bind UrlBuilderService
        $this->container->bind(UrlBuilderService::class, function ($container) {
            return new UrlBuilderService(
                $container->get(WpContextRepositoryInterface::class),
                $container->get(TranslationService::class)
            );
        });

        // Bind LanguageSwitcher
        $this->container->bind(LanguageSwitcher::class, function ($container) {
            return new LanguageSwitcher(
                $container->get(LanguageService::class),
                $container->get(PageContextResolver::class),
                $container->get(UrlBuilderService::class),
                $container->get(\UniversityMultilang\Router\RequestProcessor::class)
            );
        });

        // Bind QueryFilter
        $this->container->bind(QueryFilter::class, function ($container) {
            return new QueryFilter(
                $container->get(\UniversityMultilang\Router\RequestProcessor::class),
                $container->get(LanguageService::class)
            );
        });

        // Bind GeoRedirect
        $this->container->bind(GeoRedirect::class, function ($container) {
            return new GeoRedirect(
                $container->get(LanguageService::class)
            );
        });

        // Load global helper functions
        require_once __DIR__ . '/../functions.php';

        // Register Shortcode
        add_shortcode('uml_language_switcher', [$this->container->get(LanguageSwitcher::class), 'shortcodeCallback']);

        // Register Widget
        $this->hooks->addAction('widgets_init', $this, 'registerWidget');

        // Register Query Filter
        $this->hooks->addAction('pre_get_posts', $this->container->get(QueryFilter::class), 'filterMainQuery');

        // Register GeoRedirect Scripts
        $this->hooks->addAction('wp_enqueue_scripts', $this->container->get(GeoRedirect::class), 'enqueueScripts');
    }

    public function registerWidget(): void
    {
        register_widget(\UniversityMultilang\Frontend\Widgets\LanguageSwitcherWidget::class);
    }
}
