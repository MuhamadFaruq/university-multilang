<?php

declare(strict_types=1);

namespace UniversityMultilang\Tests\Unit\Translation\Domain;

use PHPUnit\Framework\TestCase;
use UniversityMultilang\Translation\Domain\TranslationGroupEntity;

class TranslationGroupEntityTest extends TestCase
{
    public function testInitialization(): void
    {
        $entity = new TranslationGroupEntity('group-123', 'post');

        $this->assertSame('group-123', $entity->getGroupId());
        $this->assertSame('post', $entity->getType());
        $this->assertIsArray($entity->getTranslations());
        $this->assertEmpty($entity->getTranslations());
    }

    public function testAddTranslationAndGetTranslations(): void
    {
        $entity = new TranslationGroupEntity('group-123', 'post');

        $entity->addTranslation('en', 10);
        $entity->addTranslation('id', 20);

        $translations = $entity->getTranslations();
        
        $this->assertCount(2, $translations);
        $this->assertArrayHasKey('en', $translations);
        $this->assertSame(10, $translations['en']);
        $this->assertArrayHasKey('id', $translations);
        $this->assertSame(20, $translations['id']);
    }

    public function testAddTranslationThrowsExceptionIfLanguageExists(): void
    {
        $entity = new TranslationGroupEntity('group-123', 'post');

        $entity->addTranslation('en', 10);
        
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage("A translation for language 'en' already exists in this group.");
        
        $entity->addTranslation('en', 15);
    }

    public function testRemoveTranslation(): void
    {
        $entity = new TranslationGroupEntity('group-123', 'post');

        $entity->addTranslation('en', 10);
        $entity->addTranslation('id', 20);
        
        $entity->removeTranslation('en');

        $translations = $entity->getTranslations();
        
        $this->assertCount(1, $translations);
        $this->assertArrayNotHasKey('en', $translations);
        $this->assertArrayHasKey('id', $translations);
    }

    public function testHasTranslation(): void
    {
        $entity = new TranslationGroupEntity('group-123', 'post');

        $entity->addTranslation('en', 10);

        $this->assertTrue($entity->hasTranslation('en'));
        $this->assertFalse($entity->hasTranslation('id'));
    }

    public function testGetTranslationId(): void
    {
        $entity = new TranslationGroupEntity('group-123', 'post');

        $entity->addTranslation('en', 10);

        $this->assertSame(10, $entity->getTranslationId('en'));
        $this->assertNull($entity->getTranslationId('id'));
    }
}
