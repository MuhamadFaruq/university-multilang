<?php

declare(strict_types=1);

namespace UniversityMultilang\Tests\Unit\Frontend\DTOs;

use PHPUnit\Framework\TestCase;
use UniversityMultilang\Frontend\DTOs\UrlContext;

class UrlContextTest extends TestCase
{
    public function testGettersReturnCorrectValues(): void
    {
        $context = new UrlContext(true, false, 123);

        $this->assertTrue($context->isSingular());
        $this->assertFalse($context->isFrontPage());
        $this->assertSame(123, $context->getQueriedObjectId());
    }
}
