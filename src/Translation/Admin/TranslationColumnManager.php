<?php

declare(strict_types=1);

namespace UniversityMultilang\Translation\Admin;

use UniversityMultilang\Language\Services\LanguageService;
use UniversityMultilang\Translation\Services\TranslationService;

class TranslationColumnManager
{
    private LanguageService $languageService;
    private TranslationService $translationService;

    public function __construct(LanguageService $languageService, TranslationService $translationService)
    {
        $this->languageService = $languageService;
        $this->translationService = $translationService;
    }

    public function registerHooks(): void
    {
        // Post list columns
        add_filter('manage_posts_columns', [$this, 'addLanguageColumns']);
        add_filter('manage_pages_columns', [$this, 'addLanguageColumns']);

        add_action('manage_posts_custom_column', [$this, 'renderCustomColumn'], 10, 2);
        add_action('manage_pages_custom_column', [$this, 'renderCustomColumn'], 10, 2);
    }

    public function addLanguageColumns(array $columns): array
    {
        $languages = $this->languageService->getAllLanguages();
        if (empty($languages)) {
            return $columns;
        }

        foreach ($languages as $lang) {
            $colKey = 'uml_lang_' . $lang->getSlug();
            $columns[$colKey] = esc_html($lang->getName());
        }

        return $columns;
    }

    public function renderCustomColumn(string $columnName, int $postId): void
    {
        if (strpos($columnName, 'uml_lang_') !== 0) {
            return;
        }

        $targetLangSlug = substr($columnName, 9);
        $currentPostLang = $this->languageService->getLanguageSlugForObject($postId, 'post');

        // Get translation group for this post
        $translations = $this->translationService->getTranslations($postId, 'post');

        if (!empty($translations[$targetLangSlug])) {
            $translatedPostId = $translations[$targetLangSlug];
            $editUrl = get_edit_post_link($translatedPostId) ?: admin_url("post.php?post={$translatedPostId}&action=edit");
            $status = get_post_status($translatedPostId);
            $icon = ($status === 'publish') ? '✓' : '✎';

            echo '<a href="' . esc_url($editUrl) . '" title="' . esc_attr("Edit {$targetLangSlug} translation ({$status})") . '" style="text-decoration:none; font-weight:bold; color:#0073aa;">';
            echo esc_html($icon);
            echo '</a>';
        } else {
            // Missing translation: render '+' link
            $addUrl = admin_url("post-new.php?from_post={$postId}&new_lang={$targetLangSlug}");
            echo '<a href="' . esc_url($addUrl) . '" title="' . esc_attr("Add {$targetLangSlug} translation") . '" style="text-decoration:none; font-weight:bold; color:#28a745; font-size:16px;">+</a>';
        }
    }
}
