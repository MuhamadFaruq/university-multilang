<?php

declare(strict_types=1);

namespace UniversityMultilang\Router;

use UniversityMultilang\Translation\TranslationManager;

class UrlManager
{
    private RequestProcessor $requestProcessor;
    private TranslationManager $translationManager;

    public function __construct(RequestProcessor $requestProcessor, TranslationManager $translationManager)
    {
        $this->requestProcessor = $requestProcessor;
        $this->translationManager = $translationManager;
    }

    /**
     * Add language prefix to a URL.
     */
    private function addLanguagePrefix(string $url, string $languageSlug): string
    {
        if (empty($languageSlug)) {
            return $url;
        }

        $parsedUrl = parse_url($url);
        if (!$parsedUrl || !isset($parsedUrl['host'])) {
            return $url;
        }

        $scheme = isset($parsedUrl['scheme']) ? $parsedUrl['scheme'] . '://' : '';
        $host = $parsedUrl['host'];
        $port = isset($parsedUrl['port']) ? ':' . $parsedUrl['port'] : '';
        $path = $parsedUrl['path'] ?? '/';
        $query = isset($parsedUrl['query']) ? '?' . $parsedUrl['query'] : '';
        $fragment = isset($parsedUrl['fragment']) ? '#' . $parsedUrl['fragment'] : '';

        // Inject language slug at the beginning of the path
        $path = '/' . $languageSlug . $path;

        return $scheme . $host . $port . $path . $query . $fragment;
    }

    public function filterHomeUrl(string $url, string $path, ?string $origScheme, ?int $blogId): string
    {
        $currentLang = $this->requestProcessor->getCurrentLanguage();
        if (!empty($currentLang)) {
            return $this->addLanguagePrefix($url, $currentLang);
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
            $lang = $this->translationManager->getPostLanguage($postId);
            if ($lang) {
                return $this->addLanguagePrefix($permalink, $lang);
            }
        }
        return $permalink;
    }
}
