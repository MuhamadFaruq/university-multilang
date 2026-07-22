<?php

declare(strict_types=1);

namespace UniversityMultilang\Tests\Unit\Language\Services;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use UniversityMultilang\Language\Services\LanguageService;
use UniversityMultilang\Language\Contracts\LanguageRepositoryInterface;
use UniversityMultilang\Language\Domain\LanguageEntity;

class LanguageServiceTest extends TestCase
{
    /** @var LanguageRepositoryInterface&MockObject */
    private $repository;

    private LanguageService $service;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(LanguageRepositoryInterface::class);
        $this->service = new LanguageService($this->repository);
    }

    public function testGetAllLanguagesReturnsArray(): void
    {
        $languages = [
            new LanguageEntity(1, 'English', 'en', 'en_US'),
            new LanguageEntity(2, 'Indonesian', 'id', 'id_ID')
        ];

        $this->repository->expects($this->once())
            ->method('findAll')
            ->willReturn($languages);

        $result = $this->service->getAllLanguages();
        
        $this->assertSame($languages, $result);
    }

    public function testGetLanguageBySlug(): void
    {
        $language = new LanguageEntity(1, 'English', 'en', 'en_US');

        $this->repository->expects($this->once())
            ->method('findBySlug')
            ->with('en')
            ->willReturn($language);

        $result = $this->service->getLanguageBySlug('en');
        
        $this->assertSame($language, $result);
    }

    public function testAddLanguageFailsIfSlugEmpty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Language slug cannot be empty.');

        $this->service->addLanguage('English', '', 'en_US');
    }

    public function testAddLanguageFailsIfSlugExists(): void
    {
        $existingLanguage = new LanguageEntity(1, 'English', 'en', 'en_US');

        $this->repository->expects($this->once())
            ->method('findBySlug')
            ->with('en')
            ->willReturn($existingLanguage);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Language with slug 'en' already exists.");

        $this->service->addLanguage('English 2', 'en', 'en_GB');
    }

    public function testAddLanguageSucceeds(): void
    {
        $this->repository->expects($this->once())
            ->method('findBySlug')
            ->with('en')
            ->willReturn(null);

        $expectedEntity = new LanguageEntity(0, 'English', 'en', 'en_US');
        $returnedEntity = new LanguageEntity(1, 'English', 'en', 'en_US');

        $this->repository->expects($this->once())
            ->method('save')
            ->with($this->callback(function (LanguageEntity $entity) use ($expectedEntity) {
                return $entity->getName() === $expectedEntity->getName()
                    && $entity->getSlug() === $expectedEntity->getSlug()
                    && $entity->getLocale() === $expectedEntity->getLocale();
            }))
            ->willReturn($returnedEntity);

        $result = $this->service->addLanguage('English', 'en', 'en_US');
        
        $this->assertSame($returnedEntity, $result);
    }

    public function testUpdateLanguageFailsIfIdDoesNotExist(): void
    {
        $this->repository->expects($this->once())
            ->method('findById')
            ->with(99)
            ->willReturn(null);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Language with ID '99' does not exist.");

        $this->service->updateLanguage(99, 'English', 'en', 'en_US');
    }
}
