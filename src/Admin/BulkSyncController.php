<?php

declare(strict_types=1);

namespace UniversityMultilang\Admin;

use UniversityMultilang\Translation\TranslationController;
use UniversityMultilang\Language\Services\LanguageService;

class BulkSyncController
{
    private TranslationController $translationController;
    private LanguageService $languageService;

    public function __construct(
        TranslationController $translationController,
        LanguageService $languageService
    ) {
        $this->translationController = $translationController;
        $this->languageService = $languageService;
    }

    public function handleInitAjax(): void
    {
        check_ajax_referer('uml_bulk_sync_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        // Count how many posts/pages we have to process using native fast counting
        $total = 0;
        $postCount = wp_count_posts('post');
        if (isset($postCount->publish)) {
            $total += (int) $postCount->publish;
        }

        $pageCount = wp_count_posts('page');
        if (isset($pageCount->publish)) {
            $total += (int) $pageCount->publish;
        }

        wp_send_json_success(['total' => $total]);
    }

    public function handleProcessAjax(): void
    {
        check_ajax_referer('uml_bulk_sync_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        $offset = isset($_POST['offset']) ? (int)$_POST['offset'] : 0;
        $limit = isset($_POST['limit']) ? (int)$_POST['limit'] : 5;

        $query = new \WP_Query([
            'post_type' => ['post', 'page'],
            'post_status' => 'publish',
            'posts_per_page' => $limit,
            'offset' => $offset,
            'orderby' => 'ID',
            'order' => 'ASC'
        ]);

        $defaultLang = get_option('uml_default_language');

        $processed = 0;
        foreach ($query->posts as $post) {
            // Check if post already has a language
            $currentLang = $this->languageService->getLanguageSlugForObject($post->ID, 'post');

            // If no language, assign the default language
            if (empty($currentLang) && !empty($defaultLang)) {
                try {
                    $this->languageService->setLanguageForObject($post->ID, 'post', $defaultLang);
                } catch (\Exception $e) {
                    // Ignore
                }
            }

            // Trigger auto-duplicate programmatically (this also checks if translations already exist)
            $this->translationController->autoDuplicateTranslations($post->ID, $post, true);

            $processed++;
        }

        wp_send_json_success(['processed' => $processed]);
    }
}
