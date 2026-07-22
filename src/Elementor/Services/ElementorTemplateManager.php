<?php

declare(strict_types=1);

namespace UniversityMultilang\Elementor\Services;

use UniversityMultilang\Translation\Services\TranslationService;

class ElementorTemplateManager
{
    private TranslationService $translationService;

    public function __construct(TranslationService $translationService)
    {
        $this->translationService = $translationService;
    }

    /**
     * Filter location templates (Header, Footer, Single, Archive) to serve active language template.
     *
     * @param array $templates
     * @param string $currentLanguage
     * @return array
     */
    public function filterLocationTemplates(array $templates, string $currentLanguage): array
    {
        if (empty($templates) || empty($currentLanguage)) {
            return $templates;
        }

        $filtered = [];
        foreach ($templates as $templateId => $templateData) {
            $translations = $this->translationService->getTranslations((int) $templateId, 'post');
            if (isset($translations[$currentLanguage])) {
                $translatedId = (int) $translations[$currentLanguage];
                $filtered[$translatedId] = is_array($templateData) ? array_merge($templateData, ['id' => $translatedId]) : $translatedId;
            } else {
                $filtered[$templateId] = $templateData;
            }
        }

        return $filtered;
    }
}
