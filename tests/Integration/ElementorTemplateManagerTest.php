<?php

declare(strict_types=1);

namespace UniversityMultilang\Tests\Integration;

use UniversityMultilang\Elementor\Services\ElementorTemplateManager;
use UniversityMultilang\Language\Services\LanguageService;

class ElementorTemplateManagerTest extends IntegrationTestCase
{
    private ElementorTemplateManager $templateManager;

    public function setUp(): void
    {
        parent::setUp();
        $this->templateManager = $this->getService(ElementorTemplateManager::class);
    }

    public function testFilterLocationTemplatesReturnsTranslatedTemplateIfAvailable(): void
    {
        $originalHeaderId = $this->factory()->post->create(['post_type' => 'elementor_library', 'post_title' => 'English Header']);
        $translatedHeaderId = $this->factory()->post->create(['post_type' => 'elementor_library', 'post_title' => 'Indonesian Header']);

        /** @var LanguageService $langService */
        $langService = $this->getService(LanguageService::class);
        $langService->setLanguageForObject($originalHeaderId, 'post', 'en');
        $langService->setLanguageForObject($translatedHeaderId, 'post', 'id');

        /** @var \UniversityMultilang\Translation\Services\TranslationService $transService */
        $transService = $this->getService(\UniversityMultilang\Translation\Services\TranslationService::class);
        $transService->linkTranslations($originalHeaderId, $translatedHeaderId, 'id', 'post');

        $templates = [$originalHeaderId => ['id' => $originalHeaderId]];
        $filtered = $this->templateManager->filterLocationTemplates($templates, 'id');

        $this->assertArrayHasKey($translatedHeaderId, $filtered);
    }
}
