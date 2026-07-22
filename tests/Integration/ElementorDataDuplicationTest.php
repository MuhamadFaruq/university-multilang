<?php

declare(strict_types=1);

namespace UniversityMultilang\Tests\Integration;

use UniversityMultilang\Elementor\Services\ElementorDataService;

class ElementorDataDuplicationTest extends IntegrationTestCase
{
    private ElementorDataService $dataService;

    public function setUp(): void
    {
        parent::setUp();
        $this->dataService = $this->getService(ElementorDataService::class);
    }

    public function testDuplicateElementorDataCopiesEditModeAndData(): void
    {
        $sourceId = $this->factory()->post->create(['post_title' => 'Elementor Page']);
        $targetId = $this->factory()->post->create(['post_title' => 'Draft Translation']);

        update_post_meta($sourceId, '_elementor_edit_mode', 'builder');
        update_post_meta($sourceId, '_elementor_template_type', 'wp-page');

        $sampleJson = json_encode([
            [
                'id' => 's1',
                'elType' => 'section',
                'elements' => [
                    [
                        'id' => 'c1',
                        'elType' => 'column',
                        'elements' => [
                            [
                                'id' => 'w1',
                                'elType' => 'widget',
                                'widgetType' => 'heading',
                                'settings' => ['title' => 'Header Text'],
                            ]
                        ]
                    ]
                ]
            ]
        ]);
        update_post_meta($sourceId, '_elementor_data', wp_slash($sampleJson));

        $this->assertTrue($this->dataService->isElementorPost($sourceId));

        $this->dataService->duplicateElementorData($sourceId, $targetId, 'en', 'id');

        $this->assertEquals('builder', get_post_meta($targetId, '_elementor_edit_mode', true));
        $this->assertEquals('wp-page', get_post_meta($targetId, '_elementor_template_type', true));
        $this->assertNotEmpty(get_post_meta($targetId, '_elementor_data', true));
    }
}
