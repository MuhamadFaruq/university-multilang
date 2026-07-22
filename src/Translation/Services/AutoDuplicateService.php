<?php

declare(strict_types=1);

namespace UniversityMultilang\Translation\Services;

use UniversityMultilang\Language\Services\LanguageService;
use UniversityMultilang\Translation\Contracts\ContentTranslatorInterface;
use UniversityMultilang\Translation\Contracts\PostRepositoryInterface;
use UniversityMultilang\Translation\Factories\TranslationProviderFactory;
use UniversityMultilang\Elementor\Services\ElementorDataService;

class AutoDuplicateService
{
    public function __construct(
        private TranslationService $translationService,
        private LanguageService $languageService,
        private TranslationProviderFactory $providerFactory,
        private PostRepositoryInterface $postRepository,
        private ?ElementorDataService $elementorDataService = null
    ) {
    }

    private function getTranslator(): ContentTranslatorInterface
    {
        return $this->providerFactory->create();
    }

    /**
     * Automatically duplicate published posts to other languages as drafts.
     *
     * @param int $postId
     * @param \WP_Post $post
     * @return void
     */
    public function duplicatePost(int $postId, \WP_Post $post): void
    {
        // Only duplicate if it's published
        if ($post->post_status !== 'publish') {
            return;
        }

        // We only auto-duplicate standard posts and pages for now
        if (!in_array($post->post_type, ['post', 'page'], true)) {
            return;
        }

        $sourceLang = $this->languageService->getLanguageSlugForObject($postId, 'post');
        if (!$sourceLang) {
            return; // If post has no language assigned, do not auto-duplicate
        }

        $allLangs = $this->languageService->getAllLanguages();
        $translations = $this->translationService->getTranslations($postId, 'post');

        foreach ($allLangs as $lang) {
            $langSlug = $lang->getSlug();

            // Skip the source language — no need to duplicate itself
            if ($langSlug === $sourceLang) {
                continue;
            }

            // If the post doesn't exist in this language yet, duplicate it as draft
            if (!isset($translations[$langSlug])) {
                $this->createDuplicate($postId, $post, $sourceLang, $langSlug);
            }
        }
    }

    private function createDuplicate(int $sourcePostId, \WP_Post $post, string $sourceLangSlug, string $targetLangSlug): void
    {
        // Get fresh translator from current settings
        $translator = $this->getTranslator();

        // Perform translation
        $translatedTitle = $translator->translate($post->post_title, $sourceLangSlug, $targetLangSlug);
        $translatedContent = $translator->translate($post->post_content, $sourceLangSlug, $targetLangSlug);

        // If it fails, fallback to [LANG] prefix
        if ($translatedTitle === $post->post_title) {
            $translatedTitle = $post->post_title . ' [' . strtoupper($targetLangSlug) . ']';
        }

        $newPostData = [
            'post_title'   => $translatedTitle,
            'post_content' => $translatedContent,
            'post_status'  => 'draft', // SEO SAFETY: Keep it draft
            'post_type'    => $post->post_type,
            'post_author'  => $post->post_author,
        ];

        try {
            $newPostId = $this->postRepository->insertPost($newPostData);

            // Assign Language
            $this->languageService->setLanguageForObject($newPostId, 'post', $targetLangSlug);

            // Link Translation Group
            $this->translationService->linkTranslations($sourcePostId, $newPostId, $targetLangSlug, 'post');

            // Map Taxonomies (Categories, Tags)
            $this->duplicateTaxonomies($sourcePostId, $newPostId, $post->post_type, $targetLangSlug);

            // Copy all Post Meta
            $this->duplicatePostMeta($sourcePostId, $newPostId);

            // Copy & Translate Elementor Data if applicable
            if ($this->elementorDataService !== null) {
                $this->elementorDataService->duplicateElementorData($sourcePostId, $newPostId, $sourceLangSlug, $targetLangSlug);
            }
        } catch (\Exception $e) {
            // Ignore duplication error for a single language to let others continue
        }
    }

    private function duplicateTaxonomies(int $sourcePostId, int $newPostId, string $postType, string $targetLangSlug): void
    {
        $taxonomies = $this->postRepository->getPostTaxonomies($postType);
        foreach ($taxonomies as $taxonomy) {
            if ($taxonomy === 'uml_language') {
                continue;
            }

            $terms = $this->postRepository->getObjectTerms($sourcePostId, $taxonomy);
            if (!empty($terms)) {
                $mappedTermIds = [];
                foreach ($terms as $term) {
                    // Find translation of this term
                    $termTranslations = $this->translationService->getTranslations((int) $term->term_id, 'term');
                    if (isset($termTranslations[$targetLangSlug])) {
                        $mappedTermIds[] = (int) $termTranslations[$targetLangSlug];
                    }
                }

                if (!empty($mappedTermIds)) {
                    $this->postRepository->setObjectTerms($newPostId, $mappedTermIds, $taxonomy);
                }
            }
        }
    }

    private function duplicatePostMeta(int $sourcePostId, int $newPostId): void
    {
        $allMeta = $this->postRepository->getPostMeta($sourcePostId);
        if (empty($allMeta)) {
            return;
        }

        $ignoredKeys = [
            '_uml_translation_group_id',
            '_edit_lock',
            '_edit_last'
        ];

        foreach ($allMeta as $metaKey => $metaValues) {
            if (in_array($metaKey, $ignoredKeys, true)) {
                continue;
            }

            // Delete default generated meta if any, then copy
            $this->postRepository->deletePostMeta($newPostId, $metaKey);
            foreach ($metaValues as $metaValue) {
                $value = maybe_unserialize($metaValue);
                $this->postRepository->addPostMeta($newPostId, $metaKey, $value);
            }
        }
    }
}
