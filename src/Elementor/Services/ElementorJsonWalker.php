<?php

declare(strict_types=1);

namespace UniversityMultilang\Elementor\Services;

use UniversityMultilang\Translation\Contracts\ContentTranslatorInterface;

class ElementorJsonWalker
{
    private ContentTranslatorInterface $translator;

    /**
     * Translatable control keys inside Elementor widget settings.
     */
    private array $translatableKeys = [
        'title',
        'editor',
        'text',
        'caption',
        'description',
        'sub_title',
        'heading_title',
        'button_text',
    ];

    public function __construct(ContentTranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Recursively walks an Elementor JSON array tree and translates translatable text fields.
     *
     * @param array $elements
     * @param string $sourceLang
     * @param string $targetLang
     * @return array
     */
    public function walkAndTranslate(array $elements, string $sourceLang, string $targetLang): array
    {
        foreach ($elements as $key => $element) {
            if (!is_array($element)) {
                continue;
            }

            // Translate widget settings
            if (isset($element['settings']) && is_array($element['settings'])) {
                foreach ($element['settings'] as $settingKey => $settingValue) {
                    if (in_array($settingKey, $this->translatableKeys, true) && is_string($settingValue) && !empty(trim($settingValue))) {
                        $elements[$key]['settings'][$settingKey] = $this->translator->translate($settingValue, $sourceLang, $targetLang);
                    }
                }
            }

            // Recurse down nested elements (sections, containers, columns)
            if (isset($element['elements']) && is_array($element['elements'])) {
                $elements[$key]['elements'] = $this->walkAndTranslate($element['elements'], $sourceLang, $targetLang);
            }
        }

        return $elements;
    }
}
