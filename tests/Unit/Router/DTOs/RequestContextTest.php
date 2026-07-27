<?php

declare(strict_types=1);

namespace UniversityMultilang\Router\DTOs {
    function home_url(string $path = '/'): string {
        return $GLOBALS['test_mock_home_url'] ?? 'https://example.com' . $path;
    }
}

namespace UniversityMultilang\Tests\Unit\Router\DTOs {
    use PHPUnit\Framework\TestCase;
    use UniversityMultilang\Router\DTOs\RequestContext;

    class RequestContextTest extends TestCase
    {
        protected function setUp(): void
        {
            parent::setUp();
            unset($GLOBALS['test_mock_home_url']);
        }

        public function testGettersReturnCorrectValues(): void
        {
            $context = new RequestContext('/en/about-us/?s=query');

            $this->assertSame('/en/about-us/?s=query', $context->getRawUri());
            $this->assertSame('en/about-us/', $context->getPath());
        }

        public function testSubdirectoryIsStrippedFromPath(): void
        {
            $GLOBALS['test_mock_home_url'] = 'https://ush.ac.id/staging/';
            $context = new RequestContext('/staging/en/contoh-tulisan/?foo=bar');

            $this->assertSame('/staging/en/contoh-tulisan/?foo=bar', $context->getRawUri());
            $this->assertSame('en/contoh-tulisan/', $context->getPath());
        }
    }
}
