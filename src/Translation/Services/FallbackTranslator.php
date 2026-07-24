<?php

declare(strict_types=1);

namespace UniversityMultilang\Translation\Services;

use UniversityMultilang\Translation\Contracts\ContentTranslatorInterface;
use UniversityMultilang\Translation\Providers\GoogleTranslateProvider;

/**
 * Decorator that wraps a primary translator with a Google Translate fallback.
 * If the primary translator returns the original text (meaning it failed silently),
 * this decorator tries Google Translate as a fallback.
 */
class FallbackTranslator implements ContentTranslatorInterface
{
    private ContentTranslatorInterface $primary;
    private GoogleTranslateProvider $fallback;

    public function __construct(ContentTranslatorInterface $primary)
    {
        $this->primary = $primary;
        $this->fallback = new GoogleTranslateProvider();
    }

    public function translate(string $text, string $sourceLanguageSlug, string $targetLanguageSlug): string
    {
        $result = $this->primary->translate($text, $sourceLanguageSlug, $targetLanguageSlug);

        // If primary returned the same text, it likely failed — try fallback
        if ($result === $text && $sourceLanguageSlug !== $targetLanguageSlug && !empty(trim($text))) {
            $result = $this->fallback->translate($text, $sourceLanguageSlug, $targetLanguageSlug);
        }

        return $result;
    }
}
