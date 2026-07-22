<?php

declare(strict_types=1);

namespace UniversityMultilang\Tests\Unit\Language\Domain;

use PHPUnit\Framework\TestCase;
use UniversityMultilang\Language\Domain\LanguageEntity;

class LanguageEntityTest extends TestCase
{
    public function testGettersReturnCorrectValues(): void
    {
        $entity = new LanguageEntity(1, 'English', 'en', 'en_US');

        $this->assertSame(1, $entity->getId());
        $this->assertSame('English', $entity->getName());
        $this->assertSame('en', $entity->getSlug());
        $this->assertSame('en_US', $entity->getLocale());
    }
}
