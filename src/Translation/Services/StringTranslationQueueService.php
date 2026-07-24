<?php

declare(strict_types=1);

namespace UniversityMultilang\Translation\Services;

use UniversityMultilang\Translation\Contracts\ContentTranslatorInterface;
use UniversityMultilang\Language\Services\LanguageService;

class StringTranslationQueueService
{
    public function __construct(
        private ContentTranslatorInterface $translator,
        private LanguageService $languageService
    ) {}

    public function registerHooks(): void
    {
        add_action('uml_process_string_translation_event', [$this, 'processQueue']);
    }

    public function processQueue(): void
    {
        $languages = $this->languageService->getAllLanguages();
        $defaultLang = $this->getDefaultLanguageSlug();
        
        foreach ($languages as $lang) {
            $slug = $lang->getSlug();
            if ($slug === $defaultLang) continue;
            
            $queueKey = 'uml_missing_strings_' . $slug;
            $queue = get_option($queueKey, []);
            if (empty($queue) || !is_array($queue)) continue;

            $dictionaryKey = 'uml_string_translations_' . $slug;
            $dictionary = get_option($dictionaryKey, []);
            if (!is_array($dictionary)) $dictionary = [];

            $processed = 0;
            $updatedQueue = $queue;
            
            foreach ($queue as $key => $data) {
                if ($processed >= 20) { // Limit 20 per batch to prevent timeouts
                    break;
                }
                
                try {
                    // Ignore empty strings
                    if (empty(trim($data['text']))) {
                        unset($updatedQueue[$key]);
                        continue;
                    }
                    
                    $translated = $this->translator->translate($data['text'], $defaultLang, $slug);
                    if (!empty($translated)) {
                        $dictionary[$key] = $translated;
                    }
                } catch (\Exception $e) {
                    // Fail silently to continue processing the queue
                }
                
                unset($updatedQueue[$key]);
                $processed++;
            }
            
            // Save updated dictionary and queue
            update_option($dictionaryKey, $dictionary, false);
            update_option($queueKey, $updatedQueue, false);

            if (!empty($updatedQueue)) {
                // If there are still items, schedule another batch
                if (!wp_next_scheduled('uml_process_string_translation_event')) {
                    wp_schedule_single_event(time() + 10, 'uml_process_string_translation_event');
                }
            }
        }
    }

    private function getDefaultLanguageSlug(): string
    {
        $default = get_option('uml_default_language', 'id');
        return is_string($default) ? $default : 'id';
    }
}
