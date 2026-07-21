<?php

declare(strict_types=1);

namespace UniversityMultilang\Language;

class LanguageController
{
    private LanguageManager $languageManager;

    public function __construct(LanguageManager $languageManager)
    {
        $this->languageManager = $languageManager;
    }

    public function handleFormSubmission(): void
    {
        // Only process if this is our page and form is submitted
        if (!isset($_GET['page']) || $_GET['page'] !== 'university-multilang-languages') {
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'delete_language' && isset($_GET['term_id'])) {
            $this->handleDeleteLanguage();
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['action']) || $_POST['action'] !== 'add_language') {
            return;
        }

        // Verify nonce
        if (!isset($_POST['uml_language_nonce']) || !wp_verify_nonce($_POST['uml_language_nonce'], 'uml_add_language')) {
            wp_die('Security check failed.');
        }

        $name = sanitize_text_field($_POST['language_name'] ?? '');
        $slug = sanitize_title($_POST['language_slug'] ?? '');
        $locale = sanitize_text_field($_POST['language_locale'] ?? '');

        if (empty($name) || empty($slug)) {
            $redirectUrl = add_query_arg(['page' => 'university-multilang-languages', 'error' => 'empty_fields'], admin_url('admin.php'));
            wp_safe_redirect($redirectUrl);
            exit;
        }

        $result = $this->languageManager->addLanguage($name, $slug, $locale);

        if (is_wp_error($result)) {
            $redirectUrl = add_query_arg(['page' => 'university-multilang-languages', 'error' => 'insert_failed'], admin_url('admin.php'));
        } else {
            $redirectUrl = add_query_arg(['page' => 'university-multilang-languages', 'success' => '1'], admin_url('admin.php'));
        }

        wp_safe_redirect($redirectUrl);
        exit;
    }

    private function handleDeleteLanguage(): void
    {
        if (!isset($_GET['uml_delete_nonce']) || !wp_verify_nonce($_GET['uml_delete_nonce'], 'uml_delete_language')) {
            wp_die('Security check failed.');
        }

        $termId = (int) $_GET['term_id'];
        $this->languageManager->removeLanguage($termId);
        
        $redirectUrl = add_query_arg(['page' => 'university-multilang-languages', 'success_delete' => '1'], admin_url('admin.php'));
        wp_safe_redirect($redirectUrl);
        exit;
    }

    public function renderPage(): void
    {
        $languages = $this->languageManager->getLanguages();

        echo '<div class="wrap">';
        echo '<h1 class="wp-heading-inline">Manage Languages</h1>';
        echo '<hr class="wp-header-end">';

        // Display notices
        if (isset($_GET['success'])) {
            echo '<div class="notice notice-success is-dismissible"><p>Language added successfully.</p></div>';
        } elseif (isset($_GET['success_delete'])) {
            echo '<div class="notice notice-success is-dismissible"><p>Language deleted successfully.</p></div>';
        } elseif (isset($_GET['error'])) {
            $errorMsg = $_GET['error'] === 'empty_fields' ? 'Name and Slug are required.' : 'Failed to add language. Slug might already exist.';
            echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($errorMsg) . '</p></div>';
        }
        
        echo '<div id="col-container" class="wp-clearfix">';

        // Right column: Table
        echo '<div id="col-right"><div class="col-wrap">';
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr><th>Name</th><th>Slug</th><th>Locale</th><th>Count</th><th>Action</th></tr></thead>';
        echo '<tbody>';
        
        if (empty($languages)) {
            echo '<tr><td colspan="4">No languages registered yet.</td></tr>';
        } else {
            foreach ($languages as $language) {
                $termId = (int) $language->term_id;
                $locale = $this->languageManager->getLocale($termId);
                
                $deleteUrl = wp_nonce_url(admin_url('admin.php?page=university-multilang-languages&action=delete_language&term_id=' . $termId), 'uml_delete_language', 'uml_delete_nonce');
                
                echo '<tr>';
                echo '<td><strong>' . esc_html($language->name) . '</strong></td>';
                echo '<td>' . esc_html($language->slug) . '</td>';
                echo '<td>' . esc_html($locale ?: '-') . '</td>';
                echo '<td>' . intval($language->count) . '</td>';
                echo '<td><a href="' . esc_url($deleteUrl) . '" onclick="return confirm(\'Are you sure you want to delete this language?\');" style="color: #d63638;">Delete</a></td>';
                echo '</tr>';
            }
        }

        echo '</tbody></table>';
        echo '</div></div>';

        // Left column: Form
        echo '<div id="col-left"><div class="col-wrap">';
        echo '<div class="form-wrap">';
        echo '<h2>Add New Language</h2>';
        echo '<form method="post" action="">';
        echo '<input type="hidden" name="action" value="add_language">';
        wp_nonce_field('uml_add_language', 'uml_language_nonce');
        
        echo '<div class="form-field">';
        echo '<label for="preset_language">Quick Preset (Auto-fill)</label>';
        echo '<select id="preset_language">';
        echo '<option value="">-- Choose a preset or type below --</option>';
        echo '<option value="English|en|en_US">English</option>';
        echo '<option value="Indonesia|id|id_ID">Indonesia</option>';
        echo '<option value="Arabic|ar|ar_SA">Arabic</option>';
        echo '<option value="Chinese (Simplified)|zh-CN|zh_CN">Chinese (Simplified)</option>';
        echo '<option value="Spanish|es|es_ES">Spanish</option>';
        echo '<option value="French|fr|fr_FR">French</option>';
        echo '<option value="German|de|de_DE">German</option>';
        echo '<option value="Japanese|ja|ja">Japanese</option>';
        echo '<option value="Korean|ko|ko_KR">Korean</option>';
        echo '<option value="Hindi|hi|hi_IN">Hindi</option>';
        echo '</select>';
        echo '<p>Selecting a preset will automatically fill the fields below correctly.</p>';
        echo '</div>';
        
        echo '<div class="form-field form-required term-name-wrap">';
        echo '<label for="language_name">Name</label>';
        echo '<input name="language_name" id="language_name" type="text" value="" size="40" aria-required="true" required>';
        echo '<p>The name is how it appears on your site (e.g. English, Indonesian).</p>';
        echo '</div>';

        echo '<div class="form-field form-required term-slug-wrap">';
        echo '<label for="language_slug">Slug</label>';
        echo '<input name="language_slug" id="language_slug" type="text" value="" size="40" required>';
        echo '<p>The "slug" is the URL-friendly version of the name (e.g. en, id).</p>';
        echo '</div>';

        echo '<div class="form-field term-locale-wrap">';
        echo '<label for="language_locale">Locale</label>';
        echo '<input name="language_locale" id="language_locale" type="text" value="" size="40">';
        echo '<p>WordPress Locale code (e.g. en_US, id_ID).</p>';
        echo '</div>';

        echo '<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Add New Language"></p>';
        echo '</form>';
        
        // JavaScript for preset dropdown
        echo '<script>
        document.addEventListener("DOMContentLoaded", function() {
            var preset = document.getElementById("preset_language");
            var nameInput = document.getElementById("language_name");
            var slugInput = document.getElementById("language_slug");
            var localeInput = document.getElementById("language_locale");
            
            preset.addEventListener("change", function() {
                if (this.value === "") {
                    nameInput.value = "";
                    slugInput.value = "";
                    localeInput.value = "";
                    return;
                }
                
                var parts = this.value.split("|");
                if (parts.length === 3) {
                    nameInput.value = parts[0];
                    slugInput.value = parts[1];
                    localeInput.value = parts[2];
                }
            });
        });
        </script>';

        echo '</div>';
        echo '</div></div>';

        echo '</div>'; // End col-container
        echo '</div>'; // End wrap
    }
}
