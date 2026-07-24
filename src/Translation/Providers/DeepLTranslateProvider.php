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

        $url = (substr($this->apiKey, -3) === ':fx')
            ? 'https://api-free.deepl.com/v2/translate'
            : 'https://api.deepl.com/v2/translate';

        $args = [
            'timeout' => 15,
            'headers' => [
                'Authorization' => 'DeepL-Auth-Key ' . $this->apiKey,
                'Content-Type'  => 'application/json',
                'User-Agent'    => 'UniversityMultilang/1.0',
            ],
            'body' => wp_json_encode([
                'text'        => [$text],
                'source_lang' => strtoupper($sourceLanguageSlug),
                'target_lang' => strtoupper($targetLanguageSlug),
            ]),
        ];

        $response = wp_remote_post($url, $args);

        // Fallback for SSL verification failures (common on local/shared hosting)
        if (is_wp_error($response)) {
            $args['sslverify'] = false;
            $response = wp_remote_post($url, $args);
        }

        if (is_wp_error($response)) {
            return $text;
        }

        $httpCode = wp_remote_retrieve_response_code($response);
        if ($httpCode !== 200) {
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
