<?php

declare(strict_types=1);

namespace UniversityMultilang\Settings\Menus;

use UniversityMultilang\Admin\Contracts\MenuInterface;
use UniversityMultilang\Settings\Services\SettingsService;
use UniversityMultilang\Language\Services\LanguageService;

class SettingsMenu implements MenuInterface
{
    private SettingsService $settingsService;
    private LanguageService $languageService;

    public function __construct(SettingsService $settingsService, LanguageService $languageService)
    {
        $this->settingsService = $settingsService;
        $this->languageService = $languageService;
    }

    public function getSlug(): string
    {
        return 'uml-settings';
    }

    public function getPageTitle(): string
    {
        return 'University Multilang Settings';
    }

    public function getMenuTitle(): string
    {
        return 'Settings';
    }

    public function getCapability(): string
    {
        return 'manage_options';
    }

    public function getParentSlug(): ?string
    {
        return 'university-multilang';
    }

    public function getIcon(): string
    {
        return '';
    }

    public function getPosition(): ?int
    {
        return 30;
    }

    public function render(): void
    {
        $languages = $this->languageService->getAllLanguages();
        $defaultLang = $this->settingsService->getDefaultLanguage();
        $hideDefault = $this->settingsService->isHideDefaultLanguageEnabled();
        $urlStructure = $this->settingsService->getUrlStructure();
        $browserDetection = $this->settingsService->isBrowserDetectionEnabled();
        $geoRedirect = $this->settingsService->isGeoRedirectEnabled();
        $hreflang = $this->settingsService->isHreflangEnabled();
        $canonical = $this->settingsService->isCanonicalEnabled();
        $autoDuplicate = $this->settingsService->isAutoDuplicateDraftsEnabled();

        if (isset($_GET['settings-updated'])) {
            echo '<div class="notice notice-success is-dismissible"><p>Settings saved successfully.</p></div>';
        }

        ?>
        <div class="wrap">
            <h1>University Multilang Settings</h1>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <input type="hidden" name="action" value="uml_save_settings">
                <?php wp_nonce_field('uml_save_settings_nonce', 'uml_settings_nonce'); ?>

                <h2>General Settings</h2>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="uml_default_language">Default Language</label></th>
                        <td>
                            <select name="uml_default_language" id="uml_default_language">
                                <option value="">-- Select Default Language --</option>
                                <?php foreach ($languages as $lang) : ?>
                                    <option value="<?php echo esc_attr($lang->getSlug()); ?>" <?php selected($defaultLang, $lang->getSlug()); ?>>
                                        <?php echo esc_html($lang->getName()); ?> (<?php echo esc_html($lang->getSlug()); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Hide Default Language Prefix</th>
                        <td>
                            <label for="uml_hide_default_language">
                                <input type="checkbox" name="uml_hide_default_language" id="uml_hide_default_language" value="1" <?php checked($hideDefault); ?>>
                                Hide language slug from URL for default language
                            </label>
                        </td>
                    </tr>
                </table>

                <h2>Routing & Detection Settings</h2>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="uml_url_structure">URL Structure</label></th>
                        <td>
                            <select name="uml_url_structure" id="uml_url_structure">
                                <option value="directory" <?php selected($urlStructure, 'directory'); ?>>Directory (example.com/en/)</option>
                                <option value="query" <?php selected($urlStructure, 'query'); ?>>Query Parameter (example.com/?lang=en)</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Browser Detection</th>
                        <td>
                            <label for="uml_browser_detection">
                                <input type="checkbox" name="uml_browser_detection" id="uml_browser_detection" value="1" <?php checked($browserDetection); ?>>
                                Redirect visitors based on browser language preference
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">GeoRedirect</th>
                        <td>
                            <label for="uml_geo_redirect">
                                <input type="checkbox" name="uml_geo_redirect" id="uml_geo_redirect" value="1" <?php checked($geoRedirect); ?>>
                                Redirect visitors based on Geolocation
                            </label>
                        </td>
                    </tr>
                </table>

                <h2>Automatic Translation Engine</h2>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="uml_translation_provider">Translation Provider</label></th>
                        <td>
                            <?php $provider = $this->settingsService->getTranslationProvider(); ?>
                            <select name="uml_translation_provider" id="uml_translation_provider">
                                <option value="null" <?php selected($provider, 'null'); ?>>Disabled / Offline (Null)</option>
                                <option value="google" <?php selected($provider, 'google'); ?>>Google Translate (Free)</option>
                                <option value="deepl" <?php selected($provider, 'deepl'); ?>>DeepL API</option>
                            </select>
                            <p class="description">Select the translation provider engine used when auto-duplicating posts.</p>
                        </td>
                    </tr>
                    <tr id="uml_deepl_key_row" style="<?php echo $provider === 'deepl' ? '' : 'display:none;'; ?>">
                        <th scope="row"><label for="uml_deepl_api_key">DeepL API Key</label></th>
                        <td>
                            <?php $deeplKey = $this->settingsService->getDeepLApiKey(); ?>
                            <input type="password" name="uml_deepl_api_key" id="uml_deepl_api_key" value="<?php echo esc_attr($deeplKey); ?>" class="regular-text">
                            <p class="description">Supports DeepL Free (ends with :fx) and DeepL Pro keys.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">API Connection Test</th>
                        <td>
                            <button type="button" id="uml_test_connection_btn" class="button button-secondary">Test Translation Connection</button>
                            <span id="uml_connection_test_result" style="margin-left: 10px; font-weight: bold;"></span>
                        </td>
                    </tr>
                </table>

                <h2>Developer & SEO Settings</h2>
                <table class="form-table">
                    <tr>
                        <th scope="row">Hreflang Meta Tags</th>
                        <td>
                            <label for="uml_hreflang_enabled">
                                <input type="checkbox" name="uml_hreflang_enabled" id="uml_hreflang_enabled" value="1" <?php checked($hreflang); ?>>
                                Output rel="alternate" hreflang meta tags on wp_head
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Canonical Redirect Guard</th>
                        <td>
                            <label for="uml_canonical_enabled">
                                <input type="checkbox" name="uml_canonical_enabled" id="uml_canonical_enabled" value="1" <?php checked($canonical); ?>>
                                Enable canonical URL protection for multilingual routes
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Auto Duplicate Drafts</th>
                        <td>
                            <label for="uml_auto_duplicate_drafts">
                                <input type="checkbox" name="uml_auto_duplicate_drafts" id="uml_auto_duplicate_drafts" value="1" <?php checked($autoDuplicate); ?>>
                                Automatically create draft translations on post publication
                            </label>
                        </td>
                    </tr>
                </table>

                <?php submit_button('Save Settings'); ?>
            </form>
        </div>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            var providerSelect = document.getElementById('uml_translation_provider');
            var deeplRow = document.getElementById('uml_deepl_key_row');
            var testBtn = document.getElementById('uml_test_connection_btn');
            var resultSpan = document.getElementById('uml_connection_test_result');

            if (providerSelect && deeplRow) {
                providerSelect.addEventListener('change', function() {
                    deeplRow.style.display = (this.value === 'deepl') ? '' : 'none';
                });
            }

            if (testBtn && resultSpan) {
                testBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    resultSpan.style.color = '#666';
                    resultSpan.textContent = 'Testing connection...';

                    var formData = new URLSearchParams();
                    formData.append('action', 'uml_test_translation_connection');
                    formData.append('nonce', document.getElementById('uml_settings_nonce') ? document.getElementById('uml_settings_nonce').value : '');
                    formData.append('provider', providerSelect ? providerSelect.value : '');
                    formData.append('api_key', document.getElementById('uml_deepl_api_key') ? document.getElementById('uml_deepl_api_key').value : '');

                    jQuery.ajax({
                        url: (typeof ajaxurl !== 'undefined' && ajaxurl) ? ajaxurl : '/wp-admin/admin-ajax.php',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            action: 'uml_test_translation_connection',
                            nonce: document.getElementById('uml_settings_nonce') ? document.getElementById('uml_settings_nonce').value : '',
                            provider: providerSelect ? providerSelect.value : '',
                            api_key: document.getElementById('uml_deepl_api_key') ? document.getElementById('uml_deepl_api_key').value : ''
                        },
                        success: function(data) {
                            if (data.success) {
                                resultSpan.style.color = 'green';
                                resultSpan.textContent = '✔ ' + (data.data.message || 'Connection successful!');
                            } else {
                                resultSpan.style.color = 'red';
                                resultSpan.textContent = '✖ ' + (data.data.message || 'Connection failed.');
                            }
                        },
                        error: function(xhr, status, error) {
                            resultSpan.style.color = 'red';
                            var errMsg = '✖ Error: ' + status + ' (' + error + ')';
                            if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                                errMsg = '✖ ' + xhr.responseJSON.data.message;
                            } else if (xhr.responseText) {
                                console.error("AJAX Error Response:", xhr.responseText);
                                // Just show a snippet of response if it's not JSON
                                errMsg += ' - Check Console for details.';
                            }
                            resultSpan.textContent = errMsg;
                        }
                    });
                });
            }
        });
        </script>
        <?php
    }
}
