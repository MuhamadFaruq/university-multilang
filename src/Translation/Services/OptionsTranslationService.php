<?php

declare(strict_types=1);

namespace UniversityMultilang\Translation\Services;

use UniversityMultilang\Router\RequestProcessor;
use UniversityMultilang\Translation\Contracts\ContentTranslatorInterface;
use UniversityMultilang\Language\Services\LanguageService;

class OptionsTranslationService
{
    /**
     * Cache for translated options to avoid querying DB for the same option twice in one request.
     * @var array<string, string>
     */
    private array $runtimeCache = [];

    public function __construct(
        private RequestProcessor $requestProcessor,
        private ContentTranslatorInterface $translator,
        private LanguageService $languageService
    ) {
    }

    public function registerHooks(): void
    {
        add_filter('option_blogname', [$this, 'translateOptionBlogname'], 10, 1);
        add_filter('option_blogdescription', [$this, 'translateOptionBlogdescription'], 10, 1);
        add_action('updated_option', [$this, 'clearOptionTranslationCache'], 10, 3);
    }

    public function clearOptionTranslationCache(string $optionName, mixed $oldValue, mixed $newValue): void
    {
        if (in_array($optionName, ['blogname', 'blogdescription'], true)) {
            $languages = $this->languageService->getAllLanguages();
            foreach ($languages as $lang) {
                delete_option('uml_translate_' . $optionName . '_' . $lang->getSlug());
            }
        }
    }

    public function translateOptionBlogname(mixed $value): mixed
    {
        return $this->translateOption('blogname', $value);
    }

    public function translateOptionBlogdescription(mixed $value): mixed
    {
        return $this->translateOption('blogdescription', $value);
    }

    private function translateOption(string $optionName, mixed $value): mixed
    {
        // We only translate strings
        if (!is_string($value) || empty($value)) {
            return $value;
        }

        // Only translate on the frontend where we have a target language
        if (is_admin() && !wp_doing_ajax()) {
            return $value;
        }

        $currentLang = $this->requestProcessor->getCurrentLanguage();
        if (empty($currentLang)) {
            return $value; // Default language or not resolved
        }

        $defaultLang = $this->getDefaultLanguageSlug();
        if ($currentLang === $defaultLang) {
            return $value; // No translation needed for default lang
        }

        // Check runtime cache
        $cacheKey = $optionName . '_' . $currentLang;
        if (isset($this->runtimeCache[$cacheKey])) {
            return $this->runtimeCache[$cacheKey];
        }

        // Check persistent DB cache (wp_options)
        $dbCacheKey = 'uml_translate_' . $optionName . '_' . $currentLang;
        $savedTranslation = get_option($dbCacheKey);

        if ($savedTranslation !== false && is_string($savedTranslation) && !empty($savedTranslation)) {
            $this->runtimeCache[$cacheKey] = $savedTranslation;
            return $savedTranslation;
        }

        // We don't have a cached translation, translate it on the fly using ContentTranslatorInterface
        try {
            $translated = $this->translator->translate($value, $defaultLang, $currentLang);
            if (!empty($translated) && $translated !== $value) {
                // Update runtime cache
                $this->runtimeCache[$cacheKey] = $translated;
                
                // Save to DB (autoload = false to save memory since we only need it conditionally)
                update_option($dbCacheKey, $translated, false);
                return $translated;
            }
        } catch (\Exception $e) {
            // If translation fails, fallback to original value
        }

        return $value;
    }

    private function getDefaultLanguageSlug(): string
    {
        // Get it from the global setting
        $default = get_option('uml_default_language', 'id');
        return is_string($default) ? $default : 'id';
    }
}
