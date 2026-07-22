<?php

declare(strict_types=1);

namespace UniversityMultilang\Translation\Providers;

use UniversityMultilang\Translation\Contracts\ContentTranslatorInterface;

class NullTranslator implements ContentTranslatorInterface
{
    public function translate(string $text, string $sourceLanguageSlug, string $targetLanguageSlug): string
    {
        return $text;
    }
}
