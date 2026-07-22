<?php

declare(strict_types=1);

namespace UniversityMultilang\Seo;

use UniversityMultilang\Language\Services\LanguageService;
use UniversityMultilang\Frontend\Services\PageContextResolver;
use UniversityMultilang\Frontend\Services\UrlBuilderService;

class HreflangGenerator
{
    private LanguageService $languageService;
    private PageContextResolver $contextResolver;
    private UrlBuilderService $urlBuilder;

    public function __construct(
        LanguageService $languageService,
        PageContextResolver $contextResolver,
        UrlBuilderService $urlBuilder
    ) {
        $this->languageService = $languageService;
        $this->contextResolver = $contextResolver;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Hooked to wp_head to output hreflang tags.
     */
    public function renderHreflang(): void
    {
        $languages = $this->languageService->getAllLanguages();
        if (empty($languages)) {
            return;
        }

        $urlContext = $this->contextResolver->resolveCurrentContext();
        $urls = [];

        foreach ($languages as $lang) {
            $url = $this->urlBuilder->buildLanguageUrl($urlContext, $lang->getSlug(), false);
            if ($url !== null) {
                $urls[$lang->getSlug()] = $url;
            }
        }

        if (!empty($urls)) {
            echo "\n<!-- University Multilang SEO Hreflang -->\n";

            $xDefaultSlug = null;

            foreach ($urls as $slug => $url) {
                $langEntity = $this->languageService->getLanguageBySlug($slug);
                $locale = $langEntity ? $langEntity->getLocale() : '';
                $hreflang = !empty($locale) ? str_replace('_', '-', $locale) : $slug;

                echo sprintf('<link rel="alternate" hreflang="%s" href="%s" />' . "\n", esc_attr($hreflang), esc_url($url));

                if ($xDefaultSlug === null) {
                    $xDefaultSlug = $slug;
                }
            }

            if ($xDefaultSlug !== null && isset($urls[$xDefaultSlug])) {
                echo sprintf('<link rel="alternate" hreflang="x-default" href="%s" />' . "\n", esc_url($urls[$xDefaultSlug]));
            }

            echo "<!-- End University Multilang SEO Hreflang -->\n";
        }
    }
}
