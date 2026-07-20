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

    public function renderPage(): void
    {
        $languages = $this->languageManager->getLanguages();

        echo '<div class="wrap">';
        echo '<h1>Manage Languages</h1>';
        
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr><th>Name</th><th>Slug</th><th>Count</th></tr></thead>';
        echo '<tbody>';
        
        if (empty($languages)) {
            echo '<tr><td colspan="3">No languages registered yet.</td></tr>';
        } else {
            foreach ($languages as $language) {
                echo '<tr>';
                echo '<td>' . esc_html($language->name) . '</td>';
                echo '<td>' . esc_html($language->slug) . '</td>';
                echo '<td>' . intval($language->count) . '</td>';
                echo '</tr>';
            }
        }

        echo '</tbody></table>';
        
        echo '<h2>Add New Language (Coming Soon)</h2>';
        echo '<p>In the next phase, we will add a form to create/edit languages with proper metadata (locale, flag, etc).</p>';
        echo '</div>';
    }
}
