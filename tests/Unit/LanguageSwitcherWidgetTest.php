<?php

declare(strict_types=1);

namespace {
    if (!class_exists('WP_Widget')) {
        class WP_Widget {}
    }
}

namespace UniversityMultilang\Tests\Unit {
    use PHPUnit\Framework\TestCase;
    use UniversityMultilang\Frontend\Widgets\LanguageSwitcherWidget;

    class LanguageSwitcherWidgetTest extends TestCase
    {
        public function testWidgetClassExistsAndExtendsWpWidget(): void
        {
            $this->assertTrue(class_exists(LanguageSwitcherWidget::class));
            $this->assertTrue(is_subclass_of(LanguageSwitcherWidget::class, 'WP_Widget'));
        }
    }
}
