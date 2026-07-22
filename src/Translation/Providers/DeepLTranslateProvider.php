<?php

declare(strict_types=1);

namespace UniversityMultilang\Translation\Providers;

use UniversityMultilang\Translation\Contracts\ContentTranslatorInterface;

class DeepLTranslateProvider implements ContentTranslatorInterface
{
    private string $apiKey;

    public function __construct(string $apiKey = '')
    {
        $this->apiKey = $apiKey;
    }

    public function translate(string $text, string $sourceLanguageSlug, string $targetLanguageSlug): string
    {
        if (empty($this->apiKey) || empty(trim($text))) {
            return $text;
        }

        $endpoint = str_ends_with($this->apiKey, ':fx')
            ? 'https://api-free.deepl.com/v2/translate'
            : 'https://api.deepl.com/v2/translate';

        $response = wp_remote_post($endpoint, [
            'timeout' => 15,
            'headers' => [
                'Authorization' => 'DeepL-Auth-Key ' . $this->apiKey,
                'Content-Type'  => 'application/json',
            ],
            'body' => json_encode([
                'text'        => [$text],
                'target_lang' => strtoupper($targetLanguageSlug),
                'source_lang' => strtoupper($sourceLanguageSlug),
            ]),
        ]);

        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
            return $text;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (isset($data['translations'][0]['text'])) {
            return (string) $data['translations'][0]['text'];
        }

        return $text;
    }
}
