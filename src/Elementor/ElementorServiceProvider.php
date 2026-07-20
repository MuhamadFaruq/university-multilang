<?php

declare(strict_types=1);

namespace UniversityMultilang\Elementor;

use UniversityMultilang\Core\ServiceProvider;
use UniversityMultilang\Elementor\Widgets\LanguageSwitcherWidget;

class ElementorServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Check if Elementor is installed and active
        if (!did_action('elementor/loaded')) {
            return;
        }

        // Register the widget
        $this->hooks->addAction('elementor/widgets/register', $this, 'registerWidgets');
    }

    public function registerWidgets($widgets_manager): void
    {
        $widgets_manager->register(new LanguageSwitcherWidget());
    }
}
