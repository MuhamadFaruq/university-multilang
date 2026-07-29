<?php

declare(strict_types=1);

namespace UniversityMultilang\Translation\Metabox;

use UniversityMultilang\Language\Services\LanguageService;
use UniversityMultilang\Translation\Services\TranslationService;

class LanguageMetabox
{
    private LanguageService $languageService;
    private TranslationService $translationService;

    public function __construct(LanguageService $languageService, TranslationService $translationService)
    {
        $this->languageService = $languageService;
        $this->translationService = $translationService;
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
        if (isset($_GET['uml_unlink_lang']) && isset($_GET['_wpnonce'])) {
            $nonce = sanitize_text_field($_GET['_wpnonce']);
            if (wp_verify_nonce($nonce, 'uml_unlink_translation_' . $post->ID)) {
                try {
                    $this->translationService->unlinkTranslation($post->ID, 'post');
                } catch (\Exception $e) {
                    // Ignore error
                }
            }
        }

        wp_nonce_field('uml_save_post_language', 'uml_language_metabox_nonce');

        $languages = $this->languageService->getAllLanguages();
        $currentLanguage = $this->languageService->getLanguageSlugForObject($post->ID, 'post');

        if (empty($languages)) {
            echo '<p>No languages configured yet. Please add languages in the settings.</p>';
            return;
        }

        echo '<p><label for="uml_post_language">Select Language:</label></p>';
        echo '<select name="uml_post_language" id="uml_post_language" style="width: 100%;">';
        echo '<option value="">-- Select Language --</option>';
        foreach ($languages as $lang) {
            $selected = selected($currentLanguage, $lang->getSlug(), false);
            echo '<option value="' . esc_attr($lang->getSlug()) . '" ' . $selected . '>' . esc_html($lang->getName()) . '</option>';
        }
        echo '</select>';

        // Translation connections info
        echo '<hr />';
        echo '<p><strong>Translations:</strong></p>';
        echo '<ul>';

        $translations = $this->translationService->getTranslations($post->ID, 'post');

        foreach ($languages as $lang) {
            // Skip the language of the current post
            if ($lang->getSlug() === $currentLanguage) {
                continue;
            }

            if (isset($translations[$lang->getSlug()])) {
                $translatedPostId = $translations[$lang->getSlug()];
                $editLink = get_edit_post_link($translatedPostId) ?: admin_url('post.php?post=' . $translatedPostId . '&action=edit');
                $status = ucfirst(get_post_status($translatedPostId) ?: 'publish');
                $unlinkUrl = wp_nonce_url(
                    admin_url('post.php?post=' . $post->ID . '&action=edit&uml_unlink_lang=' . $lang->getSlug()),
                    'uml_unlink_translation_' . $post->ID
                );

                echo '<li style="margin-bottom:6px;">';
                echo '<strong>' . esc_html($lang->getName()) . ':</strong> ';
                echo '<a href="' . esc_url($editLink) . '">Edit</a> ';
                echo '<span class="post-state" style="font-size:11px; background:#e0e0e0; padding:2px 5px; border-radius:3px;">(' . esc_html($status) . ')</span> ';
                echo '<a href="' . esc_url($unlinkUrl) . '" style="color:#d9534f; font-size:11px; text-decoration:none;" onclick="return confirm(\'Unlink this translation?\');">[Unlink]</a>';
                echo '</li>';
            } else {
                // Determine post type and create 'add new' link
                $newPostLink = admin_url('post-new.php?post_type=' . $post->post_type . '&from_post=' . $post->ID . '&new_lang=' . $lang->getSlug());
                echo '<li style="margin-bottom:6px;">' . esc_html($lang->getName()) . ': <a href="' . esc_url($newPostLink) . '" style="color: green;">+ Add New</a>';
                echo '<div style="margin-top: 4px; font-size: 11px;">Or Link ID: <input type="number" name="uml_link_existing[' . esc_attr($lang->getSlug()) . ']" style="width:60px; height: 22px; padding: 0 4px;" placeholder="ID"></div>';
                echo '</li>';
            }
        }
        echo '</ul>';
    }

    public function savePostData(int $postId): void
    {
        // Handle Bulk Edit saving
        if (isset($_REQUEST['bulk_edit']) && !empty($_REQUEST['uml_bulk_language'])) {
            // WordPress validates the bulk edit nonce internally
            if (isset($_REQUEST['post_type']) && 'page' === $_REQUEST['post_type']) {
                if (!current_user_can('edit_page', $postId)) return;
            } else {
                if (!current_user_can('edit_post', $postId)) return;
            }

            $languageSlug = sanitize_title($_REQUEST['uml_bulk_language']);
            try {
                $this->languageService->setLanguageForObject($postId, 'post', $languageSlug);
            } catch (\Exception $e) {
                // Ignore error
            }
            return;
        }

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
                try {
                    $this->languageService->setLanguageForObject($postId, 'post', $languageSlug);
                } catch (\Exception $e) {
                    // Ignore or log error
                }
            }
        }

        // Handle Linking Existing Posts
        if (isset($_POST['uml_link_existing']) && is_array($_POST['uml_link_existing'])) {
            foreach ($_POST['uml_link_existing'] as $targetLang => $targetPostId) {
                $targetPostId = (int) $targetPostId;
                if ($targetPostId > 0) {
                    try {
                        // Make sure target post has the target language
                        $this->languageService->setLanguageForObject($targetPostId, 'post', sanitize_title($targetLang));
                        // Link them
                        $this->translationService->linkTranslations($postId, $targetPostId, sanitize_title($targetLang), 'post');
                    } catch (\Exception $e) {
                        // Ignore linking errors
                    }
                }
            }
        }
    }
}
