<?php

declare(strict_types=1);

namespace UniversityMultilang\Elementor;

use UniversityMultilang\Core\ServiceProvider;
use UniversityMultilang\Elementor\Widgets\LanguageSwitcherWidget;
use UniversityMultilang\Elementor\Services\ElementorJsonWalker;
use UniversityMultilang\Elementor\Services\ElementorDataService;
use UniversityMultilang\Translation\Contracts\ContentTranslatorInterface;

class ElementorServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind ElementorJsonWalker
        $this->container->bind(ElementorJsonWalker::class, function ($container) {
            return new ElementorJsonWalker(
                $container->get(ContentTranslatorInterface::class)
            );
        });

        // Bind ElementorDataService
        $this->container->bind(ElementorDataService::class, function ($container) {
            return new ElementorDataService(
                $container->get(ElementorJsonWalker::class)
            );
        });

        // Bind ElementorTemplateManager
        $this->container->bind(\UniversityMultilang\Elementor\Services\ElementorTemplateManager::class, function ($container) {
            return new \UniversityMultilang\Elementor\Services\ElementorTemplateManager(
                $container->get(\UniversityMultilang\Translation\Services\TranslationService::class)
            );
        });

        // Check if Elementor is installed and active
        if (did_action('elementor/loaded')) {
            $this->hooks->addAction('elementor/widgets/register', $this, 'registerWidgets');
        }
    }

    public function registerWidgets($widgets_manager): void
    {
        $widgets_manager->register(new LanguageSwitcherWidget());
    }
}
