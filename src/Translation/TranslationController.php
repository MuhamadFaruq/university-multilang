<?php

declare(strict_types=1);

namespace UniversityMultilang\Translation;

use UniversityMultilang\Language\Services\LanguageService;
use UniversityMultilang\Translation\Services\TranslationService;
use UniversityMultilang\Translation\Services\TranslationQueueService;

class TranslationController
{
    private TranslationService $translationService;
    private LanguageService $languageService;
    private TranslationQueueService $translationQueueService;

    public function __construct(
        TranslationService $translationService,
        LanguageService $languageService,
        TranslationQueueService $translationQueueService
    ) {
        $this->translationService = $translationService;
        $this->languageService = $languageService;
        $this->translationQueueService = $translationQueueService;
    }

    /**
     * Hooked to 'wp_insert_post'.
     * Runs when a new post (including auto-draft) is created in the database.
     */
    public function linkNewTranslation(int $postId, \WP_Post $post, bool $update): void
    {
        // Only run for new posts (not updates)
        if ($update) {
            return;
        }

        // We only care if the request comes from our "Add Translation" link
        if (!isset($_GET['from_post']) || !isset($_GET['new_lang'])) {
            return;
        }

        $fromPostId = (int) $_GET['from_post'];
        $newLang = sanitize_title($_GET['new_lang']);

        // Set the language for the new post
        $this->languageService->setLanguageForObject($postId, 'post', $newLang);

        // Link the translation
        $this->translationService->linkTranslations($fromPostId, $postId, $newLang, 'post');
    }

    private static bool $isAutoDuplicating = false;

    /**
     * Hooked to 'save_post'.
     * Automatically duplicate published posts to other languages as drafts.
     */
    public function autoDuplicateTranslations(int $postId, \WP_Post $post, bool $update): void
    {
        // Ignore autosaves
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Limit auto-duplication during bulk edits to max 5 items to prevent server crash
        if (isset($_REQUEST['bulk_edit'])) {
            $postIds = $_REQUEST['post'] ?? [];
            if (is_array($postIds) && count($postIds) > 5) {
                return;
            }
        }

        // Prevent infinite loops if we are programmatically inserting a post
        if (self::$isAutoDuplicating) {
            return;
        }

        // We only auto-duplicate published posts
        if ($post->post_status !== 'publish') {
            return;
        }

        // We only auto-duplicate standard posts and pages for now
        if (!in_array($post->post_type, ['post', 'page'], true)) {
            return;
        }

        self::$isAutoDuplicating = true;

        try {
            $sourceLang = $this->languageService->getLanguageSlugForObject($postId, 'post');

            // Fallback: If post has no language (e.g., saved by Elementor without nonce), assign default language
            if (!$sourceLang) {
                $allLangs = $this->languageService->getAllLanguages();
                if (!empty($allLangs)) {
                    $sourceLang = $allLangs[0]->getSlug();
                    $this->languageService->setLanguageForObject($postId, 'post', $sourceLang);
                }
            }

            // Dispatch background job for translation
            if ($sourceLang) {
                $this->translationQueueService->dispatchTranslationJob($postId);
            }
        } catch (\Exception $e) {
            // Ignore duplication error
        }

        self::$isAutoDuplicating = false;
    }

    /**
     * Hooked to 'before_delete_post'.
     * Clears translation group cache when a post is permanently deleted.
     */
    public function handlePostDeletion(int $postId): void
    {
        // Don't run on autosaves or revisions
        if (wp_is_post_revision($postId) || wp_is_post_autosave($postId)) {
            return;
        }

        try {
            $group = $this->translationService->getTranslations($postId, 'post');
            if (!empty($group)) {
                $this->translationService->unlinkTranslation($postId, 'post');

                // The cache clearing logic from legacy was here,
                // but our repository unlinkTranslation already deletes the meta.
                // If legacy cache is still around, clear it manually for safety:
                wp_cache_delete('uml_translations_' . $postId, 'uml_translation_cache');
                foreach ($group as $lang => $id) {
                    wp_cache_delete('uml_translations_' . $id, 'uml_translation_cache');
                }
            }
        } catch (\Exception $e) {
            // Ignore if translation unlink fails during delete
        }
    }

    /**
     * Hooked to 'wp_ajax_uml_link_existing_post'.
     * Links two existing posts into a translation group via AJAX.
     */
    public function handleLinkExistingPost(): void
    {
        $this->cleanOutputBuffer();

        $nonce = $_POST['nonce'] ?? '';
        if (!wp_verify_nonce((string) $nonce, 'uml_link_existing_post_nonce')) {
            wp_send_json_error(['message' => 'Invalid nonce'], 403);
        }

        $fromPostId = isset($_POST['from_post_id']) ? (int) $_POST['from_post_id'] : 0;
        $targetPostId = isset($_POST['target_post_id']) ? (int) $_POST['target_post_id'] : 0;
        $targetLang = isset($_POST['target_lang']) ? sanitize_title((string) $_POST['target_lang']) : '';

        if (!$fromPostId || !$targetPostId || empty($targetLang)) {
            wp_send_json_error(['message' => 'Missing required parameter'], 400);
        }

        if (!current_user_can('edit_post', $fromPostId) || !current_user_can('edit_post', $targetPostId)) {
            wp_send_json_error(['message' => 'Insufficient permission'], 403);
        }

        try {
            $this->languageService->setLanguageForObject($targetPostId, 'post', $targetLang);
            $this->translationService->linkTranslations($fromPostId, $targetPostId, $targetLang, 'post');
            wp_send_json_success(['message' => 'Translation linked successfully']);
        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Clean any stale output from other plugins (e.g. W3 Total Cache error notices)
     * that would corrupt our JSON response.
     */
    private function cleanOutputBuffer(): void
    {
        while (ob_get_level()) {
            ob_end_clean();
        }
        ob_start();
    }
}
