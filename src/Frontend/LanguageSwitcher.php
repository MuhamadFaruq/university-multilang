<?php

declare(strict_types=1);

namespace UniversityMultilang\Frontend;

use UniversityMultilang\Language\LanguageManager;
use UniversityMultilang\Translation\TranslationManager;

class LanguageSwitcher
{
    private LanguageManager $languageManager;
    private TranslationManager $translationManager;

    public function __construct(LanguageManager $languageManager, TranslationManager $translationManager)
    {
        $this->languageManager = $languageManager;
        $this->translationManager = $translationManager;
    }

    /**
     * Render the language switcher HTML.
     */
    public function renderSwitcher(array $attributes = []): string
    {
        $languages = $this->languageManager->getLanguages();
        if (empty($languages)) {
            return '';
        }

        $currentPostId = get_queried_object_id();
        $translations = [];

        // If we are on a single post/page, try to find translations
        if (is_singular() && $currentPostId) {
            $translations = $this->translationManager->getTranslations($currentPostId);
        }

        $html = '<ul class="uml-language-switcher" style="list-style:none; padding:0; margin:0; display:flex; gap:10px;">';

        foreach ($languages as $lang) {
            $url = home_url('/'); // Default to home

            // If a specific translation exists for this language, use it.
            if (isset($translations[$lang->slug])) {
                $translatedPostId = $translations[$lang->slug];
                $url = get_permalink($translatedPostId);
            } else {
                // We fallback to home url but we must manually append language prefix
                // because home_url filter might only prepend the *current* viewing language.
                // We want the URL to explicitly point to the target language home.
                $parsedUrl = parse_url(home_url('/'));
                if ($parsedUrl && isset($parsedUrl['host'])) {
                    $scheme = isset($parsedUrl['scheme']) ? $parsedUrl['scheme'] . '://' : '';
                    $url = $scheme . $parsedUrl['host'] . '/' . $lang->slug . '/';
                }
            }

            $html .= '<li class="uml-lang-item uml-lang-' . esc_attr($lang->slug) . '">';
            $html .= '<a href="' . esc_url($url) . '">' . esc_html($lang->name) . '</a>';
            $html .= '</li>';
        }

        $html .= '</ul>';

        return $html;
    }

    /**
     * Shortcode callback.
     */
    public function shortcodeCallback($atts): string
    {
        return $this->renderSwitcher((array) $atts);
    }
}
