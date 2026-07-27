<?php

declare(strict_types=1);

namespace UniversityMultilang\Router\Services {
    if (!function_exists('UniversityMultilang\Router\Services\home_url')) {
        function home_url(string $path = '/'): string {
            return $GLOBALS['test_mock_home_url'] ?? 'https://example.com' . $path;
        }
    }
}

namespace UniversityMultilang\Tests\Unit\Router\Services {
    use PHPUnit\Framework\TestCase;
    use UniversityMultilang\Router\DTOs\RequestContext;
    use UniversityMultilang\Router\DTOs\RoutingResult;
    use UniversityMultilang\Router\Services\UriModifierService;

    class UriModifierServiceTest extends TestCase
    {
        protected function setUp(): void
        {
            parent::setUp();
            unset($GLOBALS['test_mock_home_url']);
        }

        public function testModifyUriAtRoot(): void
        {
            $GLOBALS['test_mock_home_url'] = 'https://example.com/';
            $service = new UriModifierService();
            $context = new RequestContext('/en/sample-page/?s=query');
            $result = new RoutingResult('en');

            $modified = $service->modifyUri($context, $result);

            $this->assertSame('/sample-page/?s=query', $modified);
        }

        public function testModifyUriInSubdirectory(): void
        {
            $GLOBALS['test_mock_home_url'] = 'https://ush.ac.id/staging/';
            $service = new UriModifierService();
            $context = new RequestContext('/staging/en/contoh-tulisan/');
            $result = new RoutingResult('en');

            $modified = $service->modifyUri($context, $result);

            $this->assertSame('/staging/contoh-tulisan/', $modified);
        }

        public function testModifyUriInSubdirectoryWithQueryStringOnly(): void
        {
            $GLOBALS['test_mock_home_url'] = 'https://ush.ac.id/staging/';
            $service = new UriModifierService();
            $context = new RequestContext('/staging/en?foo=bar');
            $result = new RoutingResult('en');

            $modified = $service->modifyUri($context, $result);

            $this->assertSame('/staging/?foo=bar', $modified);
        }

        public function testModifyUriDoesNotStripPrefixOfSimilarWord(): void
        {
            $GLOBALS['test_mock_home_url'] = 'https://ush.ac.id/staging/';
            $service = new UriModifierService();
            $context = new RequestContext('/staging/energy-drink/');
            $result = new RoutingResult('en');

            $modified = $service->modifyUri($context, $result);

            $this->assertSame('/staging/energy-drink/', $modified);
        }
    }
}
