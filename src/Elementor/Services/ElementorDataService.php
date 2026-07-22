<?php

declare(strict_types=1);

namespace UniversityMultilang\Elementor\Services;

class ElementorDataService
{
    private ElementorJsonWalker $jsonWalker;

    public function __construct(ElementorJsonWalker $jsonWalker)
    {
        $this->jsonWalker = $jsonWalker;
    }

    public function isElementorPost(int $postId): bool
    {
        $editMode = get_post_meta($postId, '_elementor_edit_mode', true);
        return $editMode === 'builder';
    }

    public function duplicateElementorData(int $sourcePostId, int $targetPostId, string $sourceLang, string $targetLang): void
    {
        if (!$this->isElementorPost($sourcePostId)) {
            return;
        }

        // Copy scalar metadata
        $editMode = get_post_meta($sourcePostId, '_elementor_edit_mode', true);
        update_post_meta($targetPostId, '_elementor_edit_mode', $editMode);

        $templateType = get_post_meta($sourcePostId, '_elementor_template_type', true);
        if (!empty($templateType)) {
            update_post_meta($targetPostId, '_elementor_template_type', $templateType);
        }

        $pageSettings = get_post_meta($sourcePostId, '_elementor_page_settings', true);
        if (!empty($pageSettings)) {
            update_post_meta($targetPostId, '_elementor_page_settings', $pageSettings);
        }

        $controlsUsage = get_post_meta($sourcePostId, '_elementor_controls_usage', true);
        if (!empty($controlsUsage)) {
            update_post_meta($targetPostId, '_elementor_controls_usage', $controlsUsage);
        }

        // Process and translate _elementor_data JSON tree
        $rawJson = get_post_meta($sourcePostId, '_elementor_data', true);
        if (!empty($rawJson) && is_string($rawJson)) {
            $data = json_decode($rawJson, true);
            if (is_array($data)) {
                $translatedData = $this->jsonWalker->walkAndTranslate($data, $sourceLang, $targetLang);
                $encodedJson = json_encode($translatedData);
                if ($encodedJson !== false) {
                    update_post_meta($targetPostId, '_elementor_data', wp_slash($encodedJson));
                }
            }
        }
    }
}
