<?php

declare(strict_types=1);

namespace UniversityMultilang\Router\Services {
    // Mock home_url for unit testing subdirectory support in RouteBuilderService
    function home_url(string $path = '/'): string {
        return $GLOBALS['test_mock_home_url'] ?? 'https://example.com' . $path;
    }
}

namespace UniversityMultilang\Tests\Unit\Router\Services {
    use PHPUnit\Framework\TestCase;
    use UniversityMultilang\Router\Services\RouteBuilderService;

    class RouteBuilderServiceTest extends TestCase
    {
        protected function setUp(): void
        {
            parent::setUp();
            unset($GLOBALS['test_mock_home_url']);
        }

        public function testAddLanguagePrefixAtRoot(): void
        {
            $GLOBALS['test_mock_home_url'] = 'https://example.com/';
            $service = new RouteBuilderService();

            $url = 'https://example.com/sample-page/';
            $prefixed = $service->addLanguagePrefix($url, 'en');

            $this->assertSame('https://example.com/en/sample-page/', $prefixed);
        }

        public function testAddLanguagePrefixInSubdirectory(): void
        {
            $GLOBALS['test_mock_home_url'] = 'https://ush.ac.id/staging/';
            $service = new RouteBuilderService();

            $url = 'https://ush.ac.id/staging/contoh-tulisan/';
            $prefixed = $service->addLanguagePrefix($url, 'en');

            $this->assertSame('https://ush.ac.id/staging/en/contoh-tulisan/', $prefixed);
        }

        public function testPreventDoublePrefixingInSubdirectory(): void
        {
            $GLOBALS['test_mock_home_url'] = 'https://ush.ac.id/staging/';
            $service = new RouteBuilderService();

            $url = 'https://ush.ac.id/staging/en/contoh-tulisan/';
            $prefixed = $service->addLanguagePrefix($url, 'en');

            $this->assertSame('https://ush.ac.id/staging/en/contoh-tulisan/', $prefixed);
        }
    }
}
