<?php

declare(strict_types=1);

namespace UniversityMultilang\Tests\Integration;

use UniversityMultilang\Translation\Repositories\WpMetaTranslationRepository;
use UniversityMultilang\Translation\Repositories\WpPostRepository;
use UniversityMultilang\Language\Repositories\WpTermLanguageRepository;
use UniversityMultilang\Language\Domain\LanguageEntity;
use UniversityMultilang\Translation\Domain\TranslationGroupEntity;
use DomainException;
use RuntimeException;

class TranslationRepositoryTest extends IntegrationTestCase
{
    private WpMetaTranslationRepository $translationRepository;
    private WpPostRepository $postRepository;
    private WpTermLanguageRepository $languageRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->languageRepository = new WpTermLanguageRepository();
        $this->translationRepository = new WpMetaTranslationRepository($this->languageRepository);
        $this->postRepository = new WpPostRepository();

        // Ensure language taxonomy is registered
        if (!taxonomy_exists(WpTermLanguageRepository::TAXONOMY)) {
            $provider = new \UniversityMultilang\Language\LanguageServiceProvider(
                $this->app->getContainer(),
                $this->getService(\UniversityMultilang\Core\HookManager::class)
            );
            $provider->registerTaxonomy();
        }

        // Setup test languages
        if (!$this->languageRepository->findBySlug('en')) {
            $this->languageRepository->save(new LanguageEntity(0, 'English', 'en', 'en_US'));
        }
        if (!$this->languageRepository->findBySlug('id')) {
            $this->languageRepository->save(new LanguageEntity(0, 'Indonesian', 'id', 'id_ID'));
        }
        if (!$this->languageRepository->findBySlug('es')) {
            $this->languageRepository->save(new LanguageEntity(0, 'Spanish', 'es', 'es_ES'));
        }
    }

    public function testCanCreateAndSaveTranslationGroupForPosts(): void
    {
        $postEn = $this->createPost(['post_title' => 'English Post']);
        $postId = $this->createPost(['post_title' => 'Indonesian Post']);

        $this->languageRepository->setLanguageForObject($postEn, 'post', 'en');
        $this->languageRepository->setLanguageForObject($postId, 'post', 'id');

        $uuid = $this->generateUuid();
        $group = new TranslationGroupEntity($uuid, 'post', [
            'en' => $postEn,
            'id' => $postId,
        ]);

        $this->translationRepository->saveGroup($group);

        // Verify metadata directly via WP function
        $metaEn = get_post_meta($postEn, WpMetaTranslationRepository::META_GROUP_ID, true);
        $metaId = get_post_meta($postId, WpMetaTranslationRepository::META_GROUP_ID, true);

        $this->assertEquals($uuid, $metaEn);
        $this->assertEquals($uuid, $metaId);
    }

    public function testCanGetGroupByObjectId(): void
    {
        $postEn = $this->createPost(['post_title' => 'EN Post 2']);
        $postId = $this->createPost(['post_title' => 'ID Post 2']);

        $this->languageRepository->setLanguageForObject($postEn, 'post', 'en');
        $this->languageRepository->setLanguageForObject($postId, 'post', 'id');

        $uuid = $this->generateUuid();
        $group = new TranslationGroupEntity($uuid, 'post', [
            'en' => $postEn,
            'id' => $postId,
        ]);

        $this->translationRepository->saveGroup($group);

        $retrievedGroup = $this->translationRepository->getGroupByObjectId($postEn, 'post');

        $this->assertNotNull($retrievedGroup);
        $this->assertEquals($uuid, $retrievedGroup->getGroupId());
        $this->assertEquals('post', $retrievedGroup->getType());
        $this->assertTrue($retrievedGroup->hasTranslation('en'));
        $this->assertTrue($retrievedGroup->hasTranslation('id'));
        $this->assertEquals($postEn, $retrievedGroup->getTranslationId('en'));
        $this->assertEquals($postId, $retrievedGroup->getTranslationId('id'));
    }

    public function testGetGroupForNonExistentObjectReturnsNull(): void
    {
        $group = $this->translationRepository->getGroupByObjectId(99999, 'post');
        $this->assertNull($group);
    }

    public function testCanLinkNewTranslationToExistingGroup(): void
    {
        $postEn = $this->createPost(['post_title' => 'EN Link']);
        $postId = $this->createPost(['post_title' => 'ID Link']);
        $postEs = $this->createPost(['post_title' => 'ES Link']);

        $this->languageRepository->setLanguageForObject($postEn, 'post', 'en');
        $this->languageRepository->setLanguageForObject($postId, 'post', 'id');
        $this->languageRepository->setLanguageForObject($postEs, 'post', 'es');

        $uuid = $this->generateUuid();
        $group = new TranslationGroupEntity($uuid, 'post', [
            'en' => $postEn,
            'id' => $postId,
        ]);

        $this->translationRepository->saveGroup($group);

        // Add ES to group
        $group->addTranslation('es', $postEs);
        $this->translationRepository->saveGroup($group);

        $updatedGroup = $this->translationRepository->getGroupByObjectId($postEs, 'post');

        $this->assertNotNull($updatedGroup);
        $this->assertEquals($uuid, $updatedGroup->getGroupId());
        $this->assertEquals($postEs, $updatedGroup->getTranslationId('es'));
    }

    public function testDuplicateLanguageInGroupThrowsDomainException(): void
    {
        $group = new TranslationGroupEntity('group-1', 'post', ['en' => 10]);

        $this->expectException(DomainException::class);
        $group->addTranslation('en', 20);
    }

    public function testCanRemoveFromGroup(): void
    {
        $postEn = $this->createPost(['post_title' => 'EN Remove']);
        $postId = $this->createPost(['post_title' => 'ID Remove']);

        $this->languageRepository->setLanguageForObject($postEn, 'post', 'en');
        $this->languageRepository->setLanguageForObject($postId, 'post', 'id');

        $uuid = $this->generateUuid();
        $group = new TranslationGroupEntity($uuid, 'post', [
            'en' => $postEn,
            'id' => $postId,
        ]);

        $this->translationRepository->saveGroup($group);

        // Remove postEn from group
        $this->translationRepository->removeFromGroup($postEn, 'post');

        $metaEn = get_post_meta($postEn, WpMetaTranslationRepository::META_GROUP_ID, true);
        $this->assertEmpty($metaEn);

        // The group for postId should still exist without postEn
        $remainingGroup = $this->translationRepository->getGroupByObjectId($postId, 'post');
        $this->assertNotNull($remainingGroup);
        $this->assertFalse($remainingGroup->hasTranslation('en'));
        $this->assertTrue($remainingGroup->hasTranslation('id'));
    }

    public function testWpPostRepositoryOperations(): void
    {
        // Test insertPost
        $postId = $this->postRepository->insertPost([
            'post_title'   => 'Repo Test Post',
            'post_content' => 'Content',
            'post_status'  => 'publish',
        ]);
        $this->assertGreaterThan(0, $postId);

        // Test addPostMeta and getPostMeta
        $this->postRepository->addPostMeta($postId, '_test_key', 'test_value');
        $meta = $this->postRepository->getPostMeta($postId);
        $this->assertArrayHasKey('_test_key', $meta);
        $this->assertEquals('test_value', $meta['_test_key'][0]);

        // Test deletePostMeta
        $this->postRepository->deletePostMeta($postId, '_test_key');
        $metaAfter = $this->postRepository->getPostMeta($postId);
        $this->assertArrayNotHasKey('_test_key', $metaAfter);

        // Test getPostTaxonomies
        $taxonomies = $this->postRepository->getPostTaxonomies('post');
        $this->assertContains('category', $taxonomies);

        // Test setObjectTerms and getObjectTerms
        $termId = $this->createTerm('Test Category', 'category');
        $this->postRepository->setObjectTerms($postId, [$termId], 'category');

        $terms = $this->postRepository->getObjectTerms($postId, 'category');
        $this->assertCount(1, $terms);
        $this->assertEquals($termId, $terms[0]->term_id);
    }
}
