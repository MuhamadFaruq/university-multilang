<?php

declare(strict_types=1);

namespace UniversityMultilang\Elementor\Widgets;

use Elementor\Widget_Base;

class LanguageSwitcherWidget extends Widget_Base
{
    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function get_name(): string
    {
        return 'uml_language_switcher';
    }

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function get_title(): string
    {
        return 'Language Switcher';
    }

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function get_icon(): string
    {
        return 'eicon-globe';
    }

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function get_categories(): array
    {
        return ['general'];
    }

    protected function render(): void
    {
        echo do_shortcode('[uml_language_switcher]');
    }
}
