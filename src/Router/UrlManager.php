<?php

declare(strict_types=1);

namespace UniversityMultilang\Router;

use UniversityMultilang\Language\Services\LanguageService;
use UniversityMultilang\Router\Services\RouteBuilderService;

class UrlManager
{
    private RequestProcessor $requestProcessor;
    private LanguageService $languageService;
    private RouteBuilderService $routeBuilder;

    public function __construct(
        RequestProcessor $requestProcessor,
        LanguageService $languageService,
        RouteBuilderService $routeBuilder
    ) {
        $this->requestProcessor = $requestProcessor;
        $this->languageService = $languageService;
        $this->routeBuilder = $routeBuilder;
    }

    public function filterHomeUrl(string $url, string $path, ?string $origScheme, ?int $blogId): string
    {
        $currentLang = $this->requestProcessor->getCurrentLanguage();
        if (!empty($currentLang)) {
            return $this->routeBuilder->addLanguagePrefix($url, $currentLang);
        }
        return $url;
    }

    /**
     * Filter post or page link.
     *
     * @param string $permalink
     * @param \WP_Post|int $post
     * @param bool $leavenameOrSample
     */
    public function filterPostLink(string $permalink, $post, bool $leavenameOrSample = false): string
    {
        $postId = 0;
        if ($post instanceof \WP_Post) {
            $postId = $post->ID;
        } elseif (is_numeric($post)) {
            $postId = (int) $post;
        }

        if ($postId > 0) {
            $lang = $this->languageService->getLanguageSlugForObject($postId, 'post');
            if (!empty($lang)) {
                return $this->routeBuilder->addLanguagePrefix($permalink, $lang);
            }
        }
        return $permalink;
    }

    /**
     * Filter term link.
     *
     * @param string $termlink
     * @param \WP_Term|object|int $term
     * @param string $taxonomy
     */
    public function filterTermLink(string $termlink, $term, string $taxonomy = ''): string
    {
        $termId = 0;
        if ($term instanceof \WP_Term) {
            $termId = (int) $term->term_id;
        } elseif (is_object($term) && isset($term->term_id)) {
            $termId = (int) $term->term_id;
        } elseif (is_numeric($term)) {
            $termId = (int) $term;
        }

        if ($termId > 0) {
            $lang = $this->languageService->getLanguageSlugForObject($termId, 'term');
            if (!empty($lang)) {
                return $this->routeBuilder->addLanguagePrefix($termlink, $lang);
            }
        }

        $currentLang = $this->requestProcessor->getCurrentLanguage();
        if (!empty($currentLang)) {
            return $this->routeBuilder->addLanguagePrefix($termlink, $currentLang);
        }

        return $termlink;
    }

    /**
     * Filter post type archive link.
     *
     * @param string $link
     * @param string $postType
     */
    public function filterPostTypeArchiveLink(string $link, string $postType): string
    {
        $currentLang = $this->requestProcessor->getCurrentLanguage();
        if (!empty($currentLang)) {
            return $this->routeBuilder->addLanguagePrefix($link, $currentLang);
        }
        return $link;
    }
}
