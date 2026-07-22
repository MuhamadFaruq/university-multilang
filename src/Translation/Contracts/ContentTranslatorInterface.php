<?php

declare(strict_types=1);

namespace UniversityMultilang\Translation\Contracts;

interface ContentTranslatorInterface
{
    /**
     * Translate text from a source language to a target language.
     *
     * @param string $text The original text.
     * @param string $sourceLanguageSlug The slug of the source language (e.g., 'en').
     * @param string $targetLanguageSlug The slug of the target language (e.g., 'id').
     * @return string The translated text, or the original text if translation fails.
     */
    public function translate(string $text, string $sourceLanguageSlug, string $targetLanguageSlug): string;
}
