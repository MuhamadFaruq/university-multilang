<?php

declare(strict_types=1);

namespace UniversityMultilang\Translation\Metabox;

use UniversityMultilang\Language\LanguageManager;
use UniversityMultilang\Translation\TranslationManager;

class LanguageMetabox
{
    private LanguageManager $languageManager;
    private TranslationManager $translationManager;

    public function __construct(LanguageManager $languageManager, TranslationManager $translationManager)
    {
        $this->languageManager = $languageManager;
        $this->translationManager = $translationManager;
    }

    public function registerMetabox(): void
    {
        // Add metabox to 'post' and 'page' for now.
        $screens = ['post', 'page'];
        foreach ($screens as $screen) {
            add_meta_box(
                'uml_language_metabox',
                'University Multilang - Language',
                [$this, 'renderMetabox'],
                $screen,
                'side',
                'high'
            );
        }
    }

    public function renderMetabox(\WP_Post $post): void
    {
        wp_nonce_field('uml_save_post_language', 'uml_language_metabox_nonce');

        $languages = $this->languageManager->getLanguages();
        $currentLanguage = $this->translationManager->getPostLanguage($post->ID);
        
        if (empty($languages)) {
            echo '<p>No languages configured yet. Please add languages in the settings.</p>';
            return;
        }

        echo '<p><label for="uml_post_language">Select Language:</label></p>';
        echo '<select name="uml_post_language" id="uml_post_language" style="width: 100%;">';
        echo '<option value="">-- Select Language --</option>';
        foreach ($languages as $lang) {
            $selected = selected($currentLanguage, $lang->slug, false);
            echo '<option value="' . esc_attr($lang->slug) . '" ' . $selected . '>' . esc_html($lang->name) . '</option>';
        }
        echo '</select>';

        // Translation connections info
        echo '<hr />';
        echo '<p><strong>Translations:</strong></p>';
        echo '<ul>';

        $translations = $this->translationManager->getTranslations($post->ID);

        foreach ($languages as $lang) {
            // Skip the language of the current post
            if ($lang->slug === $currentLanguage) {
                continue;
            }

            if (isset($translations[$lang->slug])) {
                $translatedPostId = $translations[$lang->slug];
                $editLink = get_edit_post_link($translatedPostId);
                echo '<li>' . esc_html($lang->name) . ': <a href="' . esc_url($editLink) . '">Edit</a></li>';
            } else {
                // Determine post type and create 'add new' link
                $postTypeObj = get_post_type_object($post->post_type);
                $newPostLink = admin_url('post-new.php?post_type=' . $post->post_type . '&from_post=' . $post->ID . '&new_lang=' . $lang->slug);
                echo '<li>' . esc_html($lang->name) . ': <a href="' . esc_url($newPostLink) . '" style="color: green;">+ Add</a></li>';
            }
        }
        echo '</ul>';
    }

    public function savePostData(int $postId): void
    {
        // Check if nonce is set
        if (!isset($_POST['uml_language_metabox_nonce'])) {
            return;
        }

        // Verify nonce
        if (!wp_verify_nonce($_POST['uml_language_metabox_nonce'], 'uml_save_post_language')) {
            return;
        }

        // If this is an autosave, our form has not been submitted, so we don't want to do anything.
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check the user's permissions.
        if (isset($_POST['post_type']) && 'page' === $_POST['post_type']) {
            if (!current_user_can('edit_page', $postId)) {
                return;
            }
        } else {
            if (!current_user_can('edit_post', $postId)) {
                return;
            }
        }

        // Save language
        if (isset($_POST['uml_post_language'])) {
            $languageSlug = sanitize_title($_POST['uml_post_language']);
            if (!empty($languageSlug)) {
                $this->translationManager->setPostLanguage($postId, $languageSlug);
                
                // Initialize translation group if not exist
                $this->translationManager->getTranslationGroupId($postId);
            }
        }
    }
}
