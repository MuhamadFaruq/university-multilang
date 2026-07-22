<?php

declare(strict_types=1);

namespace UniversityMultilang\Tests\Integration;

use UniversityMultilang\Router\RequestProcessor;
use UniversityMultilang\Router\Contracts\WpRequestRepositoryInterface;
use UniversityMultilang\Router\Services\RoutingGuardService;

class RouterGuardsTest extends IntegrationTestCase
{
    private RequestProcessor $processor;
    private WpRequestRepositoryInterface $requestRepo;

    public function setUp(): void
    {
        parent::setUp();
        $this->processor = $this->getService(RequestProcessor::class);
        $this->requestRepo = $this->getService(WpRequestRepositoryInterface::class);
    }

    public function testRoutingGuardServiceClassExists(): void
    {
        $this->assertTrue(class_exists(RoutingGuardService::class));
    }

    public function testBypassEndpointsAreNotInterceptedByRequestProcessor(): void
    {
        $bypassUris = [
            '/wp-json/wp/v2/posts',
            '/wp-admin/admin-ajax.php',
            '/wp-login.php',
            '/wp-cron.php',
            '/?preview=true',
            '/?elementor-preview=123',
            '/sitemap.xml',
            '/robots.txt',
            '/favicon.ico',
        ];

        foreach ($bypassUris as $uri) {
            $this->requestRepo->setRequestUri($uri);
            $this->processor->interceptRequest();

            // After intercepting a bypass URI, request URI must remain unchanged
            // and current language must not be erroneously set to a bypass segment.
            $this->assertEquals($uri, $this->requestRepo->getRequestUri(), "Failed for URI: {$uri}");
            $this->assertEmpty($this->processor->getCurrentLanguage(), "Language should be empty for bypass URI: {$uri}");
        }
    }
}
