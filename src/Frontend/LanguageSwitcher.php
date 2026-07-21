<?php

declare(strict_types=1);

namespace UniversityMultilang\Frontend;

use UniversityMultilang\Language\LanguageManager;
use UniversityMultilang\Translation\TranslationManager;
use UniversityMultilang\Router\RequestProcessor;

class LanguageSwitcher
{
    private LanguageManager $languageManager;
    private TranslationManager $translationManager;
    private RequestProcessor $requestProcessor;

    public function __construct(LanguageManager $languageManager, TranslationManager $translationManager, RequestProcessor $requestProcessor)
    {
        $this->languageManager = $languageManager;
        $this->translationManager = $translationManager;
        $this->requestProcessor = $requestProcessor;
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
            return $this->renderCustomDropdown($languages, $translations);
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

    private function renderCustomDropdown(array $languages, array $translations): string
    {
        $currentLangSlug = $this->requestProcessor->getCurrentLanguage();
        if (empty($currentLangSlug)) {
            $defaultLang = get_option('uml_default_language');
            $currentLangSlug = !empty($defaultLang) ? $defaultLang : $languages[0]->slug;
        }

        $currentLangName = 'Language';
        foreach ($languages as $lang) {
            if ($lang->slug === $currentLangSlug) {
                $currentLangName = strtoupper(substr($lang->slug, 0, 2));
                break;
            }
        }

        ob_start();
        ?>
        <style>
            .uml-custom-dropdown {
                position: relative;
                display: inline-block;
                font-family: inherit;
            }
            .uml-dropdown-toggle {
                background: transparent;
                border: 1px solid rgba(0,0,0,0.1);
                padding: 6px 12px;
                border-radius: 20px;
                cursor: pointer;
                display: flex;
                align-items: center;
                gap: 6px;
                font-size: 14px;
                font-weight: 500;
                color: inherit;
                transition: all 0.2s ease;
            }
            .uml-dropdown-toggle:hover {
                background: rgba(0,0,0,0.05);
            }
            .uml-dropdown-menu {
                position: absolute;
                top: calc(100% + 8px);
                right: 0;
                background: #ffffff;
                min-width: 150px;
                border-radius: 12px;
                box-shadow: 0 10px 25px rgba(0,0,0,0.1);
                opacity: 0;
                visibility: hidden;
                transform: translateY(-10px);
                transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
                z-index: 9999;
                padding: 8px;
                list-style: none !important;
                margin: 0;
            }
            .uml-custom-dropdown:hover .uml-dropdown-menu {
                opacity: 1;
                visibility: visible;
                transform: translateY(0);
            }
            .uml-dropdown-item {
                display: block;
                padding: 8px 12px;
                color: #333;
                text-decoration: none !important;
                border-radius: 6px;
                font-size: 14px;
                transition: background 0.2s;
            }
            .uml-dropdown-item:hover {
                background: #f0f0f0;
                color: #000;
            }
            .uml-dropdown-item.active {
                background: #f0f4f8;
                color: #0056b3;
                font-weight: 600;
            }
            .uml-globe-icon {
                width: 16px;
                height: 16px;
                fill: currentColor;
            }
        </style>
        <div class="uml-custom-dropdown">
            <div class="uml-dropdown-toggle">
                <svg class="uml-globe-icon" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/></svg>
                <span><?php echo esc_html($currentLangName); ?></span>
            </div>
            <ul class="uml-dropdown-menu">
                <?php foreach ($languages as $lang): ?>
                    <?php 
                        $url = $this->getLanguageUrl($lang->slug, $translations); 
                        $isActive = ($lang->slug === $currentLangSlug);
                    ?>
                    <li>
                        <a href="<?php echo esc_url($url); ?>" class="uml-dropdown-item <?php echo $isActive ? 'active' : ''; ?>">
                            <?php echo esc_html($lang->name); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php
        return ob_get_clean();
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
