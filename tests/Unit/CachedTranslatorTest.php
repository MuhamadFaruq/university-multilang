<?php

declare(strict_types=1);

namespace UniversityMultilang\Tests\Unit;

use PHPUnit\Framework\TestCase;
use UniversityMultilang\Translation\Contracts\ContentTranslatorInterface;
use UniversityMultilang\Translation\Services\CachedTranslator;

class CachedTranslatorTest extends TestCase
{
    public function testCachedTranslatorDelegatesAndCachesResult(): void
    {
        $innerTranslator = $this->createMock(ContentTranslatorInterface::class);
        $innerTranslator->expects($this->once())
            ->method('translate')
            ->with('Hello World', 'en', 'id')
            ->willReturn('Halo Dunia');

        $cachedTranslator = new CachedTranslator($innerTranslator);

        // First call - should call inner translator
        $result1 = $cachedTranslator->translate('Hello World', 'en', 'id');
        $this->assertEquals('Halo Dunia', $result1);

        // Second call - should hit cache and NOT call inner translator again
        $result2 = $cachedTranslator->translate('Hello World', 'en', 'id');
        $this->assertEquals('Halo Dunia', $result2);
    }
}
