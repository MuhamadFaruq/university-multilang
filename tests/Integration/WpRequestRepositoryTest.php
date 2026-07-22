<?php

declare(strict_types=1);

namespace UniversityMultilang\Tests\Integration;

use UniversityMultilang\Router\Repositories\WpRequestRepository;
use Exception;

class WpRequestRepositoryTest extends IntegrationTestCase
{
    private WpRequestRepository $repository;
    private ?string $originalRequestUri = null;

    public function setUp(): void
    {
        parent::setUp();
        $this->repository = new WpRequestRepository();
        $this->originalRequestUri = $_SERVER['REQUEST_URI'] ?? null;
    }

    public function tearDown(): void
    {
        if ($this->originalRequestUri !== null) {
            $_SERVER['REQUEST_URI'] = $this->originalRequestUri;
        } else {
            unset($_SERVER['REQUEST_URI']);
        }
        parent::tearDown();
    }

    public function testGetRequestUriReturnsServerRequestUri(): void
    {
        $_SERVER['REQUEST_URI'] = '/about-us/';
        $this->assertEquals('/about-us/', $this->repository->getRequestUri());

        $_SERVER['REQUEST_URI'] = '/id/fakultas?category=engineering';
        $this->assertEquals('/id/fakultas?category=engineering', $this->repository->getRequestUri());
    }

    public function testGetRequestUriWhenUnsetReturnsEmptyString(): void
    {
        unset($_SERVER['REQUEST_URI']);
        $this->assertEquals('', $this->repository->getRequestUri());
    }

    public function testSetRequestUriUpdatesServerSuperglobal(): void
    {
        $this->repository->setRequestUri('/en/courses/?level=undergraduate');
        $this->assertEquals('/en/courses/?level=undergraduate', $_SERVER['REQUEST_URI']);
    }

    public function testIsAdminReturnsCorrectBoolean(): void
    {
        $this->assertIsBool($this->repository->isAdmin());
        $this->assertEquals(is_admin(), $this->repository->isAdmin());
    }

    public function testRedirectFiresWpRedirectFilterWithCorrectLocationAndStatus(): void
    {
        $targetUrl = 'https://example.org/id/berita/';
        $targetStatus = 302;
        $capturedLocation = null;
        $capturedStatus = null;

        add_filter('wp_redirect', function ($location, $status) use (&$capturedLocation, &$capturedStatus) {
            $capturedLocation = $location;
            $capturedStatus = $status;
            // Throw exception to intercept exit; statement in repository
            throw new Exception('RedirectIntercepted');
        }, 10, 2);

        try {
            $this->repository->redirect($targetUrl, $targetStatus);
            $this->fail('Expected RedirectIntercepted exception was not thrown.');
        } catch (Exception $e) {
            $this->assertEquals('RedirectIntercepted', $e->getMessage());
            $this->assertEquals($targetUrl, $capturedLocation);
            $this->assertEquals($targetStatus, $capturedStatus);
        }
    }
}
