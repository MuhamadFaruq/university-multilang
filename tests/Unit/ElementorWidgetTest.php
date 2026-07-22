<?php

declare(strict_types=1);

namespace UniversityMultilang\Tests\Unit;

use PHPUnit\Framework\TestCase;

if (!class_exists('Elementor\Widget_Base')) {
    abstract class ElementorWidgetStub
    {
        public function get_name(): string
        {
            return '';
        }
        public function get_title(): string
        {
            return '';
        }
        public function get_icon(): string
        {
            return '';
        }
        public function get_categories(): array
        {
            return [];
        }
    }
    class_alias(ElementorWidgetStub::class, 'Elementor\Widget_Base');
}

use UniversityMultilang\Elementor\Widgets\LanguageSwitcherWidget;

class ElementorWidgetTest extends TestCase
{
    public function testElementorWidgetMetadataAndMethods(): void
    {
        $widget = new LanguageSwitcherWidget();
        $this->assertEquals('uml_language_switcher', $widget->get_name());
        $this->assertEquals('Language Switcher', $widget->get_title());
        $this->assertEquals('eicon-globe', $widget->get_icon());
        $this->assertContains('general', $widget->get_categories());
    }
}
