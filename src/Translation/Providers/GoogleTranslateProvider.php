<?php

declare(strict_types=1);

namespace UniversityMultilang\Translation\Providers;

use UniversityMultilang\Translation\Contracts\ContentTranslatorInterface;

class GoogleTranslateProvider implements ContentTranslatorInterface
{
    public function translate(string $text, string $sourceLanguageSlug, string $targetLanguageSlug): string
    {
        if (empty(trim($text))) {
            return $text;
        }

        $url = 'https://translate.googleapis.com/translate_a/single?' . http_build_query([
            'client' => 'gtx',
            'sl'     => $sourceLanguageSlug,
            'tl'     => $targetLanguageSlug,
            'dt'     => 't',
            'q'      => $text,
        ]);

        $args = [
            'timeout' => 15,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36',
                'Accept' => 'application/json'
            ]
        ];

        $response = wp_remote_get($url, $args);

        // Fallback for SSL verification failures (common on local/shared hosting)
        if (is_wp_error($response)) {
            $args['sslverify'] = false;
            $response = wp_remote_get($url, $args);
        }

        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
            return $text;
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
