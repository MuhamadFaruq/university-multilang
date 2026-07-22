<?php

declare(strict_types=1);

namespace UniversityMultilang\Translation\Services;

use UniversityMultilang\Translation\Contracts\ContentTranslatorInterface;

class CachedTranslator implements ContentTranslatorInterface
{
    private ContentTranslatorInterface $translator;
    private array $memoryCache = [];

    public function __construct(ContentTranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function translate(string $text, string $sourceLanguageSlug, string $targetLanguageSlug): string
    {
        if (empty(trim($text))) {
            return $text;
        }

        $cacheKey = 'uml_tr_' . md5($sourceLanguageSlug . '_' . $targetLanguageSlug . '_' . $text);

        // 1. Check in-memory cache first (for current request performance)
        if (isset($this->memoryCache[$cacheKey])) {
            return $this->memoryCache[$cacheKey];
        }

        // 2. Check WordPress Transient cache (if WP is loaded)
        if (function_exists('get_transient')) {
            $cached = get_transient($cacheKey);
            if ($cached !== false && is_string($cached)) {
                $this->memoryCache[$cacheKey] = $cached;
                return $cached;
            }
        }

        // 3. Delegate to inner translator
        $translated = $this->translator->translate($text, $sourceLanguageSlug, $targetLanguageSlug);

        // 4. Save to cache
        $this->memoryCache[$cacheKey] = $translated;
        if (function_exists('set_transient') && $translated !== $text) {
            set_transient($cacheKey, $translated, 30 * DAY_IN_SECONDS);
        }

        return $translated;
    }
}
