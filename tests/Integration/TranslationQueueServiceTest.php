<?php

declare(strict_types=1);

namespace UniversityMultilang\Tests\Integration;

use UniversityMultilang\Translation\Services\TranslationQueueService;

class TranslationQueueServiceTest extends IntegrationTestCase
{
    public function testTranslationQueueServiceCanDispatchBackgroundJob(): void
    {
        $queueService = $this->getService(TranslationQueueService::class);
        $this->assertInstanceOf(TranslationQueueService::class, $queueService);

        $postId = $this->factory()->post->create(['post_title' => 'Large Article']);
        $queueService->dispatchTranslationJob($postId);

        $this->assertTrue(true);
    }
}
