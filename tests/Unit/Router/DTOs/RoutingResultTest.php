<?php

declare(strict_types=1);

namespace UniversityMultilang\Tests\Unit\Router\DTOs;

use PHPUnit\Framework\TestCase;
use UniversityMultilang\Router\DTOs\RoutingResult;

class RoutingResultTest extends TestCase
{
    public function testGettersReturnCorrectValues(): void
    {
        $result = new RoutingResult('en', true, 'https://example.com/en/about/');

        $this->assertSame('en', $result->getLanguageSlug());
        $this->assertTrue($result->needsRedirect());
        $this->assertSame('https://example.com/en/about/', $result->getRedirectUrl());
    }
}
