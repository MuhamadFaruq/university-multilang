<?php

declare(strict_types=1);

namespace UniversityMultilang\Tests\Integration;

use UniversityMultilang\Frontend\Repositories\WpContextRepository;
use WP_Query;

class WpContextRepositoryTest extends IntegrationTestCase
{
    private WpContextRepository $repository;

    public function setUp(): void
    {
        parent::setUp();
        $this->repository = new WpContextRepository();
    }

    public function testIsSingularReturnsCorrectStatus(): void
    {
        // Default state (not singular)
        $this->assertFalse($this->repository->isSingular());

        // Simulate singular post context in WP_Query
        $postId = $this->createPost(['post_title' => 'Singular Test Post']);
        
        global $wp_query;
        $wp_query = new WP_Query([
            'p'         => $postId,
            'post_type' => 'post',
        ]);

        $this->assertTrue($this->repository->isSingular());
    }

    public function testIsFrontPageReturnsTrueOnLatestPosts(): void
    {
        update_option('show_on_front', 'posts');

        global $wp_query;
        $wp_query = new WP_Query();
        $wp_query->is_home = true;
        $wp_query->is_front_page = true;

        $this->assertTrue($this->repository->isFrontPage());
    }

    public function testIsFrontPageReturnsTrueOnStaticPage(): void
    {
        $homePageId = $this->createPost(['post_type' => 'page', 'post_title' => 'Home Page']);
        update_option('show_on_front', 'page');
        update_option('page_on_front', $homePageId);

        global $wp_query;
        $wp_query = new WP_Query([
            'page_id' => $homePageId,
        ]);
        $wp_query->is_front_page = true;
        $wp_query->is_page = true;

        $this->assertTrue($this->repository->isFrontPage());
    }

    public function testGetQueriedObjectIdReturnsCorrectId(): void
    {
        $postId = $this->createPost(['post_title' => 'Queried Object Test']);

        global $wp_query;
        $wp_query = new WP_Query([
            'p' => $postId,
        ]);
        $wp_query->queried_object_id = $postId;

        $this->assertEquals($postId, $this->repository->getQueriedObjectId());
    }

    public function testGetQueriedObjectIdForInvalidContextReturnsZero(): void
    {
        global $wp_query;
        $wp_query = new WP_Query();

        $this->assertEquals(0, $this->repository->getQueriedObjectId());
    }

    public function testGetPermalinkReturnsValidUrl(): void
    {
        $post1 = $this->createPost(['post_title' => 'Post One']);
        $post2 = $this->createPost(['post_title' => 'Post Two']);

        $url1 = $this->repository->getPermalink($post1);
        $url2 = $this->repository->getPermalink($post2);

        $this->assertNotEmpty($url1);
        $this->assertNotEmpty($url2);
        $this->assertNotEquals($url1, $url2);
    }

    public function testGetPermalinkForInvalidPostIdReturnsEmptyString(): void
    {
        $url = $this->repository->getPermalink(999999);
        $this->assertEmpty($url);
    }

    public function testGetHomeUrlReturnsValidSiteUrl(): void
    {
        $homeUrl = $this->repository->getHomeUrl('/');
        $this->assertNotEmpty($homeUrl);
        $this->assertStringContainsString('http', $homeUrl);

        $pathUrl = $this->repository->getHomeUrl('/about');
        $this->assertStringEndsWith('/about', $pathUrl);
    }

    public function testGetPostStatusReturnsCorrectStatus(): void
    {
        $publishedId = $this->createPost(['post_status' => 'publish']);
        $draftId = $this->createPost(['post_status' => 'draft']);

        $this->assertEquals('publish', $this->repository->getPostStatus($publishedId));
        $this->assertEquals('draft', $this->repository->getPostStatus($draftId));
    }

    public function testGetPostStatusForInvalidPostIdReturnsEmptyString(): void
    {
        $status = $this->repository->getPostStatus(999999);
        $this->assertEmpty($status);
    }
}
