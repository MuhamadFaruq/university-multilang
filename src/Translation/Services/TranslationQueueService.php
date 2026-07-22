<?php

declare(strict_types=1);

namespace UniversityMultilang\Translation\Services;

class TranslationQueueService
{
    private AutoDuplicateService $autoDuplicateService;

    public function __construct(AutoDuplicateService $autoDuplicateService)
    {
        $this->autoDuplicateService = $autoDuplicateService;
    }

    public function dispatchTranslationJob(int $postId): void
    {
        if (function_exists('wp_schedule_single_event')) {
            // Schedule via WP Cron to process asynchronously in background
            wp_schedule_single_event(time(), 'uml_process_translation_queue_event', [$postId]);
        } else {
            // Synchronous fallback
            $this->processPostTranslation($postId);
        }
    }

    public function processPostTranslation(int $postId): void
    {
        $post = get_post($postId);
        if ($post instanceof \WP_Post) {
            $this->autoDuplicateService->duplicatePost($postId, $post);
        }
    }
}
