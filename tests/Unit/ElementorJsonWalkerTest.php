<?php

declare(strict_types=1);

namespace UniversityMultilang\Tests\Unit;

use PHPUnit\Framework\TestCase;
use UniversityMultilang\Elementor\Services\ElementorJsonWalker;
use UniversityMultilang\Translation\Providers\NullTranslator;

class ElementorJsonWalkerTest extends TestCase
{
    public function testWalkerTraversesAndTranslatesElementorJsonTree(): void
    {
        $translator = new NullTranslator();
        $walker = new ElementorJsonWalker($translator);

        $sampleElementorData = [
            [
                'id' => 'sec1',
                'elType' => 'section',
                'elements' => [
                    [
                        'id' => 'col1',
                        'elType' => 'column',
                        'elements' => [
                            [
                                'id' => 'widget1',
                                'elType' => 'widget',
                                'widgetType' => 'heading',
                                'settings' => [
                                    'title' => 'Welcome to University',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $translatedData = $walker->walkAndTranslate($sampleElementorData, 'en', 'id');

        $this->assertIsArray($translatedData);
        $this->assertEquals('Welcome to University', $translatedData[0]['elements'][0]['elements'][0]['settings']['title']);
    }
}
