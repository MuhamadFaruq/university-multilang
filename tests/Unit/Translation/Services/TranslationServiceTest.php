<?php

declare(strict_types=1);

namespace UniversityMultilang\Tests\Unit\Translation\Services;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use UniversityMultilang\Translation\Services\TranslationService;
use UniversityMultilang\Translation\Contracts\TranslationRepositoryInterface;
use UniversityMultilang\Language\Services\LanguageService;
use UniversityMultilang\Translation\Domain\TranslationGroupEntity;

class TranslationServiceTest extends TestCase
{
    /** @var TranslationRepositoryInterface&MockObject */
    private $repository;

    /** @var LanguageService&MockObject */
    private $languageService;

    private TranslationService $service;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(TranslationRepositoryInterface::class);
        $this->languageService = $this->createMock(LanguageService::class);
        $this->service = new TranslationService($this->repository, $this->languageService);
    }

    public function testGetTranslationsReturnsEmptyArrayIfNoGroupFound(): void
    {
        $this->repository->expects($this->once())
            ->method('getGroupByObjectId')
            ->with(1, 'post')
            ->willReturn(null);

        $this->assertSame([], $this->service->getTranslations(1, 'post'));
    }

    public function testGetTranslationsReturnsTranslationsArray(): void
    {
        $group = new TranslationGroupEntity('group-123', 'post');
        $group->addTranslation('en', 10);
        
        $this->repository->expects($this->once())
            ->method('getGroupByObjectId')
            ->with(1, 'post')
            ->willReturn($group);

        $this->assertSame(['en' => 10], $this->service->getTranslations(1, 'post'));
    }

    public function testLinkTranslationsFailsIfIdsAreIdentical(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Source ID and Target ID cannot be the same.');

        $this->service->linkTranslations(1, 1, 'id', 'post');
    }

    public function testLinkTranslationsFailsIfTargetLanguageInvalid(): void
    {
        $this->languageService->expects($this->once())
            ->method('getLanguageBySlug')
            ->with('invalid-slug')
            ->willReturn(null);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid language slug provided: 'invalid-slug'.");

        $this->service->linkTranslations(1, 2, 'invalid-slug', 'post');
    }
}
