<?php

declare(strict_types=1);

namespace UniversityMultilang\Frontend\Services;

use UniversityMultilang\Frontend\Contracts\WpContextRepositoryInterface;
use UniversityMultilang\Frontend\DTOs\UrlContext;
use UniversityMultilang\Translation\Services\TranslationService;

class UrlBuilderService
{
    private WpContextRepositoryInterface $contextRepository;
    private TranslationService $translationService;

    public function __construct(WpContextRepositoryInterface $contextRepository, TranslationService $translationService)
    {
        $this->contextRepository = $contextRepository;
        $this->translationService = $translationService;
    }

    /**
     * Builds the correct URL for a given language based on the current context.
     *
     * @param UrlContext $context
     * @param string $languageSlug
     * @return string
     */
    public function buildLanguageUrl(UrlContext $context, string $languageSlug, bool $fallbackToHome = true): ?string
    {
        $homeUrl = $this->contextRepository->getHomeUrl('/');

        if ($context->isSingular() && $context->getQueriedObjectId() > 0) {
            $translations = $this->translationService->getTranslations($context->getQueriedObjectId(), 'post');

            if (isset($translations[$languageSlug])) {
                $translatedPostId = (int) $translations[$languageSlug];

                // SEO SAFETY: Only output URL for published posts
                if ($this->contextRepository->getPostStatus($translatedPostId) === 'publish') {
                    $permalink = $this->contextRepository->getPermalink($translatedPostId);
                    if (!empty($permalink)) {
                        return $permalink;
                    }
                }
            }

            // If singular and no translation found, and we shouldn't fallback
            if (!$fallbackToHome && !$context->isFrontPage()) {
                return null;
            }
        }

        // Fallback or Front Page logic: Prefix the home URL with the language slug
        $parsedUrl = parse_url($homeUrl);
        if ($parsedUrl && isset($parsedUrl['host'])) {
            $scheme = isset($parsedUrl['scheme']) ? $parsedUrl['scheme'] . '://' : '';
            $host = $parsedUrl['host'];
            $port = isset($parsedUrl['port']) ? ':' . $parsedUrl['port'] : '';
            $path = rtrim($parsedUrl['path'] ?? '', '/');
            return $scheme . $host . $port . $path . '/' . $languageSlug . '/';
        }

        return $homeUrl;
    }
}
