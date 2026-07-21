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

        $type = $attributes['type'] ?? 'list'; // list or dropdown

        if ($type === 'dropdown') {
            $html = '<select class="uml-language-switcher-dropdown" onchange="if(this.value) window.location.href=this.value;">';
            $html .= '<option value="">-- Language --</option>';
            foreach ($languages as $lang) {
                $url = $this->getLanguageUrl($lang->slug, $translations);
                $html .= '<option value="' . esc_url($url) . '">' . esc_html($lang->name) . '</option>';
            }
            $html .= '</select>';
            return $html;
        }

        $html = '<ul class="uml-language-switcher" style="list-style:none; padding:0; margin:0; display:flex; gap:10px;">';
        foreach ($languages as $lang) {
            $url = $this->getLanguageUrl($lang->slug, $translations);
            $html .= '<li class="uml-lang-item uml-lang-' . esc_attr($lang->slug) . '">';
            $html .= '<a href="' . esc_url($url) . '">' . esc_html($lang->name) . '</a>';
            $html .= '</li>';
        }
        $html .= '</ul>';

        return $html;
    }

    private function getLanguageUrl(string $langSlug, array $translations): string
    {
        $url = home_url('/');
        if (isset($translations[$langSlug])) {
            $translatedPostId = (int) $translations[$langSlug];
            if (get_post_status($translatedPostId) === 'publish') {
                return get_permalink($translatedPostId);
            }
        }
        
        $parsedUrl = parse_url(home_url('/'));
        if ($parsedUrl && isset($parsedUrl['host'])) {
            $scheme = isset($parsedUrl['scheme']) ? $parsedUrl['scheme'] . '://' : '';
            return $scheme . $parsedUrl['host'] . '/' . $langSlug . '/';
        }
        
        return $url;
    }

    /**
     * Shortcode callback.
     */
    public function shortcodeCallback($atts): string
    {
        return $this->renderSwitcher((array) $atts);
    }
}
