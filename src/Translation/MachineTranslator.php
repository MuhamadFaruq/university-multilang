<?php

declare(strict_types=1);

namespace UniversityMultilang\Translation;

class MachineTranslator
{
    /**
     * Translates text using the unofficial free Google Translate API.
     * Note: This is a best-effort approach without an API key.
     * 
     * @param string $text The text to translate.
     * @param string $sourceLang The source language name.
     * @param string $targetLang The target language name.
     * @return string The translated text.
     */
    public function translate(string $text, string $sourceLang, string $targetLang): string
    {
        // Check if original content is effectively empty
        if (empty(trim(strip_tags($text)))) {
            return $text;
        }

        $url = 'https://translate.googleapis.com/translate_a/single?' . http_build_query([
            'client' => 'gtx',
            'sl'     => $sourceLang,
            'tl'     => $targetLang,
            'dt'     => 't',
            'q'      => $text,
        ]);

        $response = wp_remote_get($url, [
            'timeout' => 15,
        ]);

        if (is_wp_error($response)) {
            return $text; // Return original if error
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (!is_array($data) || !isset($data[0])) {
            return $text;
        }

        $translatedText = '';
        foreach ($data[0] as $sentence) {
            if (isset($sentence[0])) {
                $translatedText .= $sentence[0];
            }
        }

        return $translatedText ?: $text;
    }
}
