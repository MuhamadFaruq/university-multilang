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
            // Space out background translation jobs by 20 seconds to prevent WP-Cron memory exhaustion
            $offset = (int) get_transient('uml_translation_cron_offset');
            wp_schedule_single_event(time() + $offset, 'uml_process_translation_queue_event', [$postId]);
            set_transient('uml_translation_cron_offset', $offset + 20, 60);
        } else {
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
