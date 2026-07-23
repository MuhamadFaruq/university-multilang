<?php

declare(strict_types=1);

namespace UniversityMultilang\Translation\Services;

use UniversityMultilang\Router\RequestProcessor;
use UniversityMultilang\Language\Services\LanguageService;

class StringTranslationService
{
    private array $dictionary = [];
    private array $missingStrings = [];
    private string $currentLang = '';
    private bool $isLoaded = false;
    private bool $shouldTrackMissing = false;
    
    // Ignore default WP strings and our own plugin to avoid translating the translation UI!
    private const IGNORED_DOMAINS = ['default', 'university-multilang'];

    public function __construct(
        private RequestProcessor $requestProcessor,
        private LanguageService $languageService
    ) {}

    public function registerHooks(): void
    {
        add_filter('gettext', [$this, 'translateGettext'], 20, 3);
        add_filter('gettext_with_context', [$this, 'translateGettextWithContext'], 20, 4);
        
        // Save missing strings on shutdown
        add_action('shutdown', [$this, 'saveMissingStrings']);
    }

    private function loadDictionary(): void
    {
        if ($this->isLoaded) return;
        
        $this->currentLang = $this->requestProcessor->getCurrentLanguage();

        if (empty($this->currentLang)) {
            // Not resolved yet (e.g. before wp_loaded), try again later
            return;
        }

        $this->isLoaded = true;

        if ($this->currentLang === $this->getDefaultLanguageSlug()) {
            return;
        }
        
        // Load the full dictionary for this language
        $cache = get_option('uml_string_translations_' . $this->currentLang, []);
        $this->dictionary = is_array($cache) ? $cache : [];
        
        // Only track missing strings on frontend to avoid admin spam
        $this->shouldTrackMissing = !is_admin() || wp_doing_ajax();
    }

    public function translateGettext(string $translation, string $text, string $domain): string
    {
        $this->loadDictionary();

        if (empty($this->currentLang) || $this->currentLang === $this->getDefaultLanguageSlug()) {
            return $translation;
        }

        if (in_array($domain, self::IGNORED_DOMAINS, true)) {
            return $translation;
        }

        $key = md5($text . '|' . $domain);
        
        if (isset($this->dictionary[$key])) {
            return $this->dictionary[$key];
        }

        if ($this->shouldTrackMissing) {
            $this->missingStrings[$key] = [
                'text' => $text,
                'domain' => $domain,
                'context' => ''
            ];
        }

        return $translation;
    }

    public function translateGettextWithContext(string $translation, string $text, string $context, string $domain): string
    {
        $this->loadDictionary();

        if (empty($this->currentLang) || $this->currentLang === $this->getDefaultLanguageSlug()) {
            return $translation;
        }

        if (in_array($domain, self::IGNORED_DOMAINS, true)) {
            return $translation;
        }

        $key = md5($text . '|' . $context . '|' . $domain);
        
        if (isset($this->dictionary[$key])) {
            return $this->dictionary[$key];
        }

        if ($this->shouldTrackMissing) {
            $this->missingStrings[$key] = [
                'text' => $text,
                'domain' => $domain,
                'context' => $context
            ];
        }

        return $translation;
    }

    public function saveMissingStrings(): void
    {
        if (empty($this->missingStrings) || empty($this->currentLang)) {
            return;
        }

        $queueKey = 'uml_missing_strings_' . $this->currentLang;
        $existingQueue = get_option($queueKey, []);
        if (!is_array($existingQueue)) {
            $existingQueue = [];
        }

        $changed = false;
        foreach ($this->missingStrings as $key => $data) {
            if (!isset($existingQueue[$key])) {
                $existingQueue[$key] = $data;
                $changed = true;
            }
        }

        if ($changed) {
            update_option($queueKey, $existingQueue, false);
            // Schedule the async cron job if not scheduled
            if (!wp_next_scheduled('uml_process_string_translation_event')) {
                wp_schedule_single_event(time() + 5, 'uml_process_string_translation_event');
            }
        }
    }

    private function getDefaultLanguageSlug(): string
    {
        $default = get_option('uml_default_language', 'id');
        return is_string($default) ? $default : 'id';
    }
}
