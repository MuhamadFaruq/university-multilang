<?php

declare(strict_types=1);

namespace UniversityMultilang\Tests\Unit\Router\DTOs;

use PHPUnit\Framework\TestCase;
use UniversityMultilang\Router\DTOs\RequestContext;

class RequestContextTest extends TestCase
{
    public function testGettersReturnCorrectValues(): void
    {
        $context = new RequestContext('/en/about-us/?s=query');

        $this->assertSame('/en/about-us/?s=query', $context->getRawUri());
        $this->assertSame('en/about-us/', $context->getPath());
    }
}
