<?php

declare(strict_types=1);

namespace UniversityMultilang\Router;

use UniversityMultilang\Core\ServiceProvider;
use UniversityMultilang\Language\LanguageManager;
use UniversityMultilang\Translation\TranslationManager;

class RouterServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind RequestProcessor (Singleton)
        $this->container->bind(RequestProcessor::class, function ($container) {
            return new RequestProcessor($container->get(LanguageManager::class));
        });

        // Bind UrlManager
        $this->container->bind(UrlManager::class, function ($container) {
            return new UrlManager(
                $container->get(RequestProcessor::class),
                $container->get(TranslationManager::class)
            );
        });

        // Register Permalink hooks
        $urlManager = $this->container->get(UrlManager::class);
        $this->hooks->addFilter('home_url', $urlManager, 'filterHomeUrl', 10, 4);
        $this->hooks->addFilter('post_link', $urlManager, 'filterPostLink', 10, 3);
        $this->hooks->addFilter('page_link', $urlManager, 'filterPostLink', 10, 3);
        $this->hooks->addFilter('post_type_link', $urlManager, 'filterPostLink', 10, 3);
        
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
        // before we query get_terms() in LanguageManager.
        $this->hooks->addAction('wp_loaded', $this->container->get(RequestProcessor::class), 'interceptRequest');
    }
}
