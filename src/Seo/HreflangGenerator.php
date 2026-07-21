<?php

declare(strict_types=1);

namespace UniversityMultilang\Seo;

use UniversityMultilang\Language\LanguageManager;
use UniversityMultilang\Translation\TranslationManager;

class HreflangGenerator
{
    private LanguageManager $languageManager;
    private TranslationManager $translationManager;

    public function __construct(LanguageManager $languageManager, TranslationManager $translationManager)
    {
        $this->languageManager = $languageManager;
        $this->translationManager = $translationManager;
    }

    /**
     * Hooked to wp_head to output hreflang tags.
     */
    public function renderHreflang(): void
    {
        $languages = $this->languageManager->getLanguages();
        if (empty($languages)) {
            return;
        }

        $urls = [];

        // Build URLs based on context (Singular Post/Page vs Home/Archive)
        if (is_singular()) {
            $postId = get_queried_object_id();
            if ($postId) {
                $translations = $this->translationManager->getTranslations($postId);
                foreach ($languages as $lang) {
                    if (isset($translations[$lang->slug])) {
                        $translatedPostId = (int) $translations[$lang->slug];
                        // SEO SAFETY: Only output hreflang for published posts
                        if (get_post_status($translatedPostId) === 'publish') {
                            $urls[$lang->slug] = get_permalink($translatedPostId);
                        }
                    }
                }
            }
        } elseif (is_front_page() || is_home()) {
            // For homepage, output hreflang for all languages
            foreach ($languages as $lang) {
                // Ensure correct home URL with language prefix
                $parsedUrl = parse_url(home_url('/'));
                if ($parsedUrl && isset($parsedUrl['host'])) {
                    $scheme = isset($parsedUrl['scheme']) ? $parsedUrl['scheme'] . '://' : '';
                    $urls[$lang->slug] = $scheme . $parsedUrl['host'] . '/' . $lang->slug . '/';
                }
            }
        }

        // Output hreflang tags if we have translation URLs
        if (!empty($urls)) {
            echo "\n<!-- University Multilang SEO Hreflang -->\n";
            
            $xDefaultSlug = null;
            
            foreach ($urls as $slug => $url) {
                // Get locale for formatting (e.g. id_ID -> id-ID)
                $term = get_term_by('slug', $slug, LanguageManager::TAXONOMY);
                if ($term) {
                    $locale = $this->languageManager->getLocale((int) $term->term_id);
                    $hreflang = !empty($locale) ? str_replace('_', '-', $locale) : $slug;
                    
                    echo sprintf('<link rel="alternate" hreflang="%s" href="%s" />' . "\n", esc_attr($hreflang), esc_url($url));
                    
                    // Assign the first one as x-default for now (or fallback)
                    if ($xDefaultSlug === null) {
                        $xDefaultSlug = $slug;
                    }
                }
            }

            // Output x-default
            if ($xDefaultSlug !== null && isset($urls[$xDefaultSlug])) {
                echo sprintf('<link rel="alternate" hreflang="x-default" href="%s" />' . "\n", esc_url($urls[$xDefaultSlug]));
            }
            
            echo "<!-- End University Multilang SEO Hreflang -->\n";
        }
    }
}
