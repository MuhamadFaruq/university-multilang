<?php

declare(strict_types=1);

namespace UniversityMultilang\Elementor\Widgets;

use Elementor\Widget_Base;

class LanguageSwitcherWidget extends Widget_Base
{
    public function get_name(): string
    {
        return 'uml_language_switcher_widget';
    }

    public function get_title(): string
    {
        return 'UML Language Switcher';
    }

    public function get_icon(): string
    {
        return 'eicon-globe';
    }

    public function get_categories(): array
    {
        return ['general'];
    }

    protected function render(): void
    {
        echo do_shortcode('[uml_language_switcher]');
    }
}
