<?php

declare(strict_types=1);

namespace UniversityMultilang\Translation;

use UniversityMultilang\Translation\Contracts\ContentTranslatorInterface;

class MachineTranslator implements ContentTranslatorInterface
{
    /**
     * Translates text using the unofficial free Google Translate API.
     * Note: This is a best-effort approach without an API key.
     *
     * @param string $text The text to translate.
     * @param string $sourceLang The source language code (e.g. 'id' or 'auto').
     * @param string $targetLang The target language code (e.g. 'en').
     * @return string The translated text.
     */
    public function translate(string $text, string $sourceLang, string $targetLang): string
    {
        if (empty(trim($text))) {
            return $text;
        }

        // The free endpoint only supports up to ~5000 characters per request.
        // We'll just truncate or limit if it's too long to prevent errors,
        // or just let it fail gracefully.

        $url = 'https://translate.googleapis.com/translate_a/single?' . http_build_query([
            'client' => 'gtx',
            'sl'     => $sourceLang,
            'tl'     => $targetLang,
            'dt'     => 't',
            'q'      => $text,
        ]);

        // Sleep for 300 milliseconds to avoid hitting Google's rate limits (429 Too Many Requests)
        // during bulk operations.
        usleep(300000);

        $response = wp_remote_get($url, [
            'timeout' => 15,
        ]);

        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
            return $text; // Return original if error or rate limited
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
