<?php

declare(strict_types=1);

namespace UniversityMultilang\Router;

use UniversityMultilang\Core\ServiceProvider;
use UniversityMultilang\Language\Services\LanguageService;
use UniversityMultilang\Router\Contracts\WpRequestRepositoryInterface;
use UniversityMultilang\Router\Repositories\WpRequestRepository;
use UniversityMultilang\Router\Services\LanguageDetectorService;
use UniversityMultilang\Router\Services\UriModifierService;
use UniversityMultilang\Router\Services\CanonicalRedirectService;
use UniversityMultilang\Router\Services\RouteBuilderService;

class RouterServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind Repository
        $this->container->bind(WpRequestRepositoryInterface::class, function () {
            return new WpRequestRepository();
        });

        // Bind Services
        $this->container->bind(LanguageDetectorService::class, function ($container) {
            return new LanguageDetectorService($container->get(LanguageService::class));
        });

        $this->container->bind(UriModifierService::class, function () {
            return new UriModifierService();
        });

        $this->container->bind(CanonicalRedirectService::class, function ($container) {
            return new CanonicalRedirectService($container->get(WpRequestRepositoryInterface::class));
        });

        $this->container->bind(RouteBuilderService::class, function () {
            return new RouteBuilderService();
        });

        $this->container->bind(\UniversityMultilang\Router\Services\RoutingGuardService::class, function () {
            return new \UniversityMultilang\Router\Services\RoutingGuardService();
        });

        $this->container->bind(\UniversityMultilang\Router\Services\RoutingContextService::class, function ($container) {
            return new \UniversityMultilang\Router\Services\RoutingContextService(
                $container->get(LanguageService::class),
                $container->get(\UniversityMultilang\Settings\Services\SettingsService::class)
            );
        });

        // Bind RequestProcessor (Singleton)
        $this->container->bind(RequestProcessor::class, function ($container) {
            return new RequestProcessor(
                $container->get(WpRequestRepositoryInterface::class),
                $container->get(LanguageDetectorService::class),
                $container->get(UriModifierService::class),
                $container->get(CanonicalRedirectService::class),
                $container->get(\UniversityMultilang\Router\Services\RoutingGuardService::class),
                $container->get(\UniversityMultilang\Router\Services\RoutingContextService::class)
            );
        });

        // Bind UrlManager
        $this->container->bind(UrlManager::class, function ($container) {
            return new UrlManager(
                $container->get(RequestProcessor::class),
                $container->get(LanguageService::class),
                $container->get(RouteBuilderService::class)
            );
        });

        // Register Permalink hooks
        $urlManager = $this->container->get(UrlManager::class);
        $this->hooks->addFilter('home_url', $urlManager, 'filterHomeUrl', 10, 4);
        $this->hooks->addFilter('post_link', $urlManager, 'filterPostLink', 10, 3);
        $this->hooks->addFilter('page_link', $urlManager, 'filterPostLink', 10, 3);
        $this->hooks->addFilter('post_type_link', $urlManager, 'filterPostLink', 10, 3);
        $this->hooks->addFilter('term_link', $urlManager, 'filterTermLink', 10, 3);
        $this->hooks->addFilter('category_link', $urlManager, 'filterTermLink', 10, 3);
        $this->hooks->addFilter('tag_link', $urlManager, 'filterTermLink', 10, 3);
        $this->hooks->addFilter('post_type_archive_link', $urlManager, 'filterPostTypeArchiveLink', 10, 2);

        // Prevent redirect_canonical from fighting our language URLs
        $this->hooks->addFilter('redirect_canonical', $this, 'disableCanonicalRedirectForLanguages', 10, 2);
    }

    public function disableCanonicalRedirectForLanguages($redirectUrl, $requestedUrl)
    {
        /** @var RequestProcessor $requestProcessor */
        $requestProcessor = $this->container->get(RequestProcessor::class);
        if (!empty($requestProcessor->getCurrentLanguage())) {
            return false;
        }
        return $redirectUrl;
    }

    public function boot(): void
    {
        // Execute request interception on 'wp_loaded' so taxonomies are registered
        // before we query get_terms() in LanguageService.
        $this->hooks->addAction('wp_loaded', $this->container->get(RequestProcessor::class), 'interceptRequest');
    }
}
