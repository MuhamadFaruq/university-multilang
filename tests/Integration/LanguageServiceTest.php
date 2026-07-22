<?php

declare(strict_types=1);

namespace UniversityMultilang\Tests\Integration;

use UniversityMultilang\Language\Services\LanguageService;
use UniversityMultilang\Language\Repositories\WpTermLanguageRepository;
use UniversityMultilang\Language\Domain\LanguageEntity;
use InvalidArgumentException;
use Exception;

class LanguageServiceTest extends IntegrationTestCase
{
    private LanguageService $service;
    private WpTermLanguageRepository $repository;

    public function setUp(): void
    {
        parent::setUp();
        $this->repository = new WpTermLanguageRepository();
        $this->service = new LanguageService($this->repository);

        if (!taxonomy_exists(WpTermLanguageRepository::TAXONOMY)) {
            $provider = new \UniversityMultilang\Language\LanguageServiceProvider(
                $this->app->getContainer(),
                $this->getService(\UniversityMultilang\Core\HookManager::class)
            );
            $provider->registerTaxonomy();
        }
    }

    public function testCanAddLanguageSuccessfully(): void
    {
        $lang = $this->service->addLanguage('Japanese', 'ja', 'ja_JP');

        $this->assertInstanceOf(LanguageEntity::class, $lang);
        $this->assertEquals('Japanese', $lang->getName());
        $this->assertEquals('ja', $lang->getSlug());
        $this->assertEquals('ja_JP', $lang->getLocale());
        $this->assertGreaterThan(0, $lang->getId());
    }

    public function testAddLanguageWithEmptyFieldsThrowsInvalidArgumentException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->service->addLanguage('', 'slug', 'locale');
    }

    public function testAddDuplicateLanguageSlugThrowsException(): void
    {
        $this->service->addLanguage('German', 'de', 'de_DE');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Language with slug 'de' already exists.");

        $this->service->addLanguage('Another German', 'de', 'de_AT');
    }

    public function testCanUpdateExistingLanguage(): void
    {
        $lang = $this->service->addLanguage('Italian', 'it', 'it_IT');

        $updated = $this->service->updateLanguage($lang->getId(), 'Italian (Italy)', 'it', 'it_IT');

        $this->assertEquals('Italian (Italy)', $updated->getName());
        $this->assertEquals($lang->getId(), $updated->getId());
    }

    public function testUpdateNonExistentLanguageThrowsException(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Language with ID '99999' does not exist.");

        $this->service->updateLanguage(99999, 'Name', 'slug', 'locale');
    }

    public function testUpdateLanguageWithConflictingSlugThrowsException(): void
    {
        $lang1 = $this->service->addLanguage('Lang Alpha', 'alpha', 'al_AL');
        $lang2 = $this->service->addLanguage('Lang Beta', 'beta', 'be_BE');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Another language with slug 'alpha' already exists.");

        $this->service->updateLanguage($lang2->getId(), 'Lang Beta', 'alpha', 'be_BE');
    }

    public function testCanRemoveLanguage(): void
    {
        $lang = $this->service->addLanguage('Portuguese', 'pt', 'pt_PT');
        $id = $lang->getId();

        $result = $this->service->removeLanguage($id);
        $this->assertTrue($result);

        $this->assertNull($this->service->getLanguageById($id));
    }

    public function testCanAssignAndRetrieveLanguageForObject(): void
    {
        $this->service->addLanguage('Swedish', 'sv', 'sv_SE');
        $postId = $this->createPost(['post_title' => 'Swedish Post']);

        $this->service->setLanguageForObject($postId, 'post', 'sv');

        $slug = $this->service->getLanguageSlugForObject($postId, 'post');
        $this->assertEquals('sv', $slug);
    }
}
