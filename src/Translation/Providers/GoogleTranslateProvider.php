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

        $response = wp_remote_get($url, [
            'timeout' => 15,
        ]);

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
