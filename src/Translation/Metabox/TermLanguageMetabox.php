<?php

declare(strict_types=1);

namespace UniversityMultilang\Translation\Metabox;

use UniversityMultilang\Language\Services\LanguageService;
use UniversityMultilang\Translation\Services\TranslationService;

class TermLanguageMetabox
{
    private LanguageService $languageService;
    private TranslationService $translationService;

    public function __construct(LanguageService $languageService, TranslationService $translationService)
    {
        $this->languageService = $languageService;
        $this->translationService = $translationService;
    }

    public function registerHooks(): void
    {
        $taxonomies = get_taxonomies(['public' => true], 'names');

        foreach ($taxonomies as $taxonomy) {
            // Exclude our own taxonomy
            if ($taxonomy === 'uml_language') {
                continue;
            }

            // Add form fields
            add_action("{$taxonomy}_add_form_fields", [$this, 'renderAddFormFields']);
            add_action("{$taxonomy}_edit_form_fields", [$this, 'renderEditFormFields']);

            // Save actions
            add_action("created_{$taxonomy}", [$this, 'saveTermLanguage']);
            add_action("edited_{$taxonomy}", [$this, 'saveTermLanguage']);
        }
    }

    public function renderAddFormFields(): void
    {
        $languages = $this->languageService->getAllLanguages();
        if (empty($languages)) {
            return;
        }

        echo '<div class="form-field term-language-wrap">';
        echo '<label for="uml_term_language">Language</label>';
        echo '<select name="uml_term_language" id="uml_term_language">';
        echo '<option value="">-- Select Language --</option>';
        foreach ($languages as $lang) {
            echo '<option value="' . esc_attr($lang->getSlug()) . '">' . esc_html($lang->getName()) . '</option>';
        }
        echo '</select>';
        echo '<p>Select the language for this term.</p>';
        echo '</div>';
    }

    public function renderEditFormFields(\WP_Term $term): void
    {
        $languages = $this->languageService->getAllLanguages();
        if (empty($languages)) {
            return;
        }

        $currentLang = $this->languageService->getLanguageSlugForObject((int) $term->term_id, 'term');

        echo '<tr class="form-field term-language-wrap">';
        echo '<th scope="row"><label for="uml_term_language">Language</label></th>';
        echo '<td>';
        echo '<select name="uml_term_language" id="uml_term_language">';
        echo '<option value="">-- Select Language --</option>';
        foreach ($languages as $lang) {
            $selected = selected($currentLang, $lang->getSlug(), false);
            echo '<option value="' . esc_attr($lang->getSlug()) . '" ' . $selected . '>' . esc_html($lang->getName()) . '</option>';
        }
        echo '</select>';
        echo '<p class="description">Select the language for this term.</p>';
        echo '</td>';
        echo '</tr>';
    }

    public function saveTermLanguage(int $termId): void
    {
        // Check for nonce or permission if needed, but since it's admin term save:
        if (!current_user_can('manage_categories')) {
            return;
        }

        if (isset($_POST['uml_term_language'])) {
            $langSlug = sanitize_text_field($_POST['uml_term_language']);
            if (!empty($langSlug)) {
                try {
                    $this->languageService->setLanguageForObject($termId, 'term', $langSlug);
                } catch (\Exception $e) {
                    // Ignore or log error
                }
            }
        }
    }
}
