<?php

declare(strict_types=1);

namespace UniversityMultilang\Tests\Integration;

use UniversityMultilang\Language\Repositories\WpTermLanguageRepository;
use UniversityMultilang\Language\Domain\LanguageEntity;
use DomainException;
use InvalidArgumentException;

class WpTermLanguageRepositoryTest extends IntegrationTestCase
{
    private WpTermLanguageRepository $repository;

    public function setUp(): void
    {
        parent::setUp();
        $this->repository = new WpTermLanguageRepository();
        
        // Ensure taxonomy is registered for tests
        if (!taxonomy_exists(WpTermLanguageRepository::TAXONOMY)) {
            $provider = new \UniversityMultilang\Language\LanguageServiceProvider(
                $this->app->getContainer(), 
                $this->getService(\UniversityMultilang\Core\HookManager::class)
            );
            $provider->registerTaxonomy();
        }
    }

    public function testCanCreateLanguage(): void
    {
        $language = new LanguageEntity(0, 'English', 'en', 'en_US');
        $saved = $this->repository->save($language);

        $this->assertInstanceOf(LanguageEntity::class, $saved);
        $this->assertEquals('English', $saved->getName());
        $this->assertEquals('en', $saved->getSlug());
        $this->assertEquals('en_US', $saved->getLocale());
        $this->assertGreaterThan(0, $saved->getId());

        // Verify in DB via WP functions
        $term = get_term($saved->getId(), WpTermLanguageRepository::TAXONOMY);
        $this->assertNotWPError($term);
        $this->assertEquals('English', $term->name);
        $this->assertEquals('en', $term->slug);
        
        $localeMeta = get_term_meta($saved->getId(), 'locale', true);
        $this->assertEquals('en_US', $localeMeta);
    }

    public function testDuplicateSlugThrowsException(): void
    {
        $this->repository->save(new LanguageEntity(0, 'English Duplicate', 'en-dup', 'en_US'));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to insert language');
        
        $this->repository->save(new LanguageEntity(0, 'Another English Duplicate', 'en-dup', 'en_UK'));
    }

    public function testCanReadLanguageById(): void
    {
        $language = $this->repository->save(new LanguageEntity(0, 'Indonesian', 'id', 'id_ID'));

        $found = $this->repository->findById($language->getId());

        $this->assertNotNull($found);
        $this->assertEquals('Indonesian', $found->getName());
        $this->assertEquals('id', $found->getSlug());
        $this->assertEquals('id_ID', $found->getLocale());
    }

    public function testReadNonExistentLanguageReturnsNull(): void
    {
        $found = $this->repository->findById(99999);
        $this->assertNull($found);
    }

    public function testCanReadLanguageBySlug(): void
    {
        $this->repository->save(new LanguageEntity(0, 'Arabic', 'ar', 'ar_SA'));

        $found = $this->repository->findBySlug('ar');

        $this->assertNotNull($found);
        $this->assertEquals('Arabic', $found->getName());
    }

    public function testCanUpdateLanguage(): void
    {
        $language = $this->repository->save(new LanguageEntity(0, 'Spanish', 'es', 'es_ES'));
        
        // Entity is immutable, create a new one with same ID for update
        $toUpdate = new LanguageEntity($language->getId(), $language->getName(), $language->getSlug(), 'es_MX');
        
        $updated = $this->repository->save($toUpdate);
        $this->assertEquals('es_MX', $updated->getLocale());

        // Verify changes
        $found = $this->repository->findById($language->getId());
        $this->assertEquals('es_MX', $found->getLocale());
        
        // Direct DB check
        $this->assertEquals('es_MX', get_term_meta($language->getId(), 'locale', true));
    }

    public function testCanDeleteLanguage(): void
    {
        $language = $this->repository->save(new LanguageEntity(0, 'French', 'fr', 'fr_FR'));
        $id = $language->getId();
        
        $result = $this->repository->delete($id);
        $this->assertTrue($result);

        // Verify it's gone
        $this->assertNull($this->repository->findById($id));
        
        $term = get_term($id, WpTermLanguageRepository::TAXONOMY);
        $this->assertNull($term);
    }

    public function testGetAllReturnsAllLanguages(): void
    {
        $this->repository->save(new LanguageEntity(0, 'Lang 1', 'l1', ''));
        $this->repository->save(new LanguageEntity(0, 'Lang 2', 'l2', ''));
        $this->repository->save(new LanguageEntity(0, 'Lang 3', 'l3', ''));

        $languages = $this->repository->findAll();
        
        $this->assertGreaterThanOrEqual(3, count($languages));
        
        $slugs = array_map(fn($l) => $l->getSlug(), $languages);
        $this->assertContains('l1', $slugs);
        $this->assertContains('l2', $slugs);
        $this->assertContains('l3', $slugs);
    }

    public function testCreateWithEmptyNameThrowsException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->repository->save(new LanguageEntity(0, '', 'slug', ''));
    }

    public function testCreateWithEmptySlugThrowsException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->repository->save(new LanguageEntity(0, 'Name', '', ''));
    }
}
