<?php

declare(strict_types=1);

namespace UniversityMultilang\Settings;

use UniversityMultilang\Settings\Services\SettingsService;

class SettingsController
{
    private SettingsService $settingsService;

    public function __construct(SettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
    }

    public function handleSaveSettings(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized user', 403);
        }

        $nonce = $_POST['uml_settings_nonce'] ?? '';
        if (!wp_verify_nonce((string) $nonce, 'uml_save_settings_nonce')) {
            wp_die('Invalid nonce', 403);
        }

        if (isset($_POST['uml_default_language'])) {
            $this->settingsService->setDefaultLanguage(sanitize_title($_POST['uml_default_language']));
        }

        $this->settingsService->setHideDefaultLanguage(!empty($_POST['uml_hide_default_language']));

        if (isset($_POST['uml_url_structure'])) {
            $this->settingsService->setUrlStructure(sanitize_text_field($_POST['uml_url_structure']));
        }

        if (isset($_POST['uml_translation_provider'])) {
            $this->settingsService->setTranslationProvider(sanitize_text_field($_POST['uml_translation_provider']));
        }

        if (isset($_POST['uml_deepl_api_key'])) {
            $this->settingsService->setDeepLApiKey(sanitize_text_field($_POST['uml_deepl_api_key']));
        }

        $this->settingsService->setBrowserDetection(!empty($_POST['uml_browser_detection']));
        $this->settingsService->setGeoRedirect(!empty($_POST['uml_geo_redirect']));
        $this->settingsService->setHreflangEnabled(!empty($_POST['uml_hreflang_enabled']));
        $this->settingsService->setCanonicalEnabled(!empty($_POST['uml_canonical_enabled']));
        $this->settingsService->setAutoDuplicateDraftsEnabled(!empty($_POST['uml_auto_duplicate_drafts']));

        $redirectUrl = admin_url('admin.php?page=uml-settings&settings-updated=1');
        if (!headers_sent()) {
            wp_safe_redirect($redirectUrl);
            exit;
        }
    }

    public function handleTestTranslationConnection(): void
    {
        // Clean any stale output from other plugins (e.g. W3 Total Cache error notices)
        // that would corrupt our JSON response
        while (ob_get_level()) {
            ob_end_clean();
        }
        ob_start();

        if (!current_user_can('manage_options')) {
            ob_end_clean();
            wp_send_json_error(['message' => 'Unauthorized user']);
        }

        $nonce = $_POST['nonce'] ?? '';
        if (!wp_verify_nonce((string) $nonce, 'uml_save_settings_nonce')) {
            ob_end_clean();
            wp_send_json_error(['message' => 'Invalid or expired security token. Please refresh the page.']);
        }

        $provider = sanitize_text_field($_POST['provider'] ?? $this->settingsService->getTranslationProvider());
        $apiKey = sanitize_text_field($_POST['api_key'] ?? $this->settingsService->getDeepLApiKey());

        if ($provider === 'null') {
            $this->sendCleanJson(true, 'Offline / Disabled provider active. No connection test required.');
        } elseif ($provider === 'google') {
            $testProvider = new \UniversityMultilang\Translation\Providers\GoogleTranslateProvider();
            $result = $testProvider->translate('Hello', 'en', 'id');
            if (!empty($result) && strtolower($result) !== 'hello') {
                $this->sendCleanJson(true, 'Google Translate connection OK! Test translation: "Hello" -> "' . $result . '"');
            } else {
                $this->sendCleanJson(false, 'Google Translate request failed or rate-limited.');
            }
        } elseif ($provider === 'deepl') {
            if (empty($apiKey)) {
                $this->sendCleanJson(false, 'DeepL API Key is required.');
            } else {
                $testProvider = new \UniversityMultilang\Translation\Providers\DeepLTranslateProvider($apiKey);
                $result = $testProvider->translate('Hello', 'en', 'id');
                if (!empty($result) && strtolower($result) !== 'hello') {
                    $this->sendCleanJson(true, 'DeepL API Connection OK! Test translation: "Hello" -> "' . $result . '"');
                } else {
                    $this->sendCleanJson(false, 'DeepL API request failed. Please check your API key.');
                }
            }
        } else {
            $this->sendCleanJson(false, 'Unknown provider');
        }
    }

    /**
     * Send a clean JSON response, flushing any output buffer garbage
     * from other plugins (e.g. W3 Total Cache error notices).
     */
    private function sendCleanJson(bool $success, string $message): void
    {
        // Discard ALL buffered output (W3TC errors, notices, etc.)
        while (ob_get_level()) {
            ob_end_clean();
        }

        if ($success) {
            wp_send_json_success(['message' => $message]);
        } else {
            wp_send_json_error(['message' => $message]);
        }
    }
}
