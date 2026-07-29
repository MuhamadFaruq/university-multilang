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

        add_action('bulk_edit_custom_box', [$this, 'renderBulkEditBox'], 10, 2);

        add_action('manage_posts_custom_column', [$this, 'renderCustomColumn'], 10, 2);
        add_action('manage_pages_custom_column', [$this, 'renderCustomColumn'], 10, 2);

        // Language filter dropdown
        add_action('restrict_manage_posts', [$this, 'renderLanguageFilterDropdown']);
        add_action('pre_get_posts', [$this, 'filterPostsByLanguage']);
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

    public function renderLanguageFilterDropdown(string $postType): void
    {
        if (!in_array($postType, ['post', 'page'], true)) {
            return;
        }

        $languages = $this->languageService->getAllLanguages();
        if (empty($languages)) {
            return;
        }

        $currentLang = isset($_GET['uml_filter_lang']) ? sanitize_title($_GET['uml_filter_lang']) : '';

        echo '<select name="uml_filter_lang" id="uml_filter_lang">';
        echo '<option value="">All Languages</option>';
        foreach ($languages as $lang) {
            $selected = ($currentLang === $lang->getSlug()) ? ' selected="selected"' : '';
            echo '<option value="' . esc_attr($lang->getSlug()) . '"' . $selected . '>' . esc_html($lang->getName()) . ' (' . esc_html(strtoupper($lang->getSlug())) . ')</option>';
        }
        echo '</select>';
    }

    public function filterPostsByLanguage(\WP_Query $query): void
    {
        if (!is_admin() || !$query->is_main_query()) {
            return;
        }

        if ($query->get('uml_admin_filter_applied')) {
            return;
        }

        $filterLang = isset($_GET['uml_filter_lang']) ? sanitize_title($_GET['uml_filter_lang']) : '';
        if (empty($filterLang)) {
            return;
        }

        $query->set('uml_admin_filter_applied', true);

        $taxQuery = $query->get('tax_query') ?: [];
        if (!is_array($taxQuery)) {
            $taxQuery = [];
        }

        $taxQuery[] = [
            'taxonomy' => \UniversityMultilang\Language\Repositories\WpTermLanguageRepository::TAXONOMY,
            'field'    => 'slug',
            'terms'    => $filterLang,
        ];

        $query->set('tax_query', $taxQuery);
    }

    private bool $bulkEditRendered = false;

    public function renderBulkEditBox(string $columnName, string $postType): void
    {
        if ($this->bulkEditRendered) {
            return;
        }

        if (strpos($columnName, 'uml_lang_') !== 0) {
            return;
        }

        $languages = $this->languageService->getAllLanguages();
        if (empty($languages)) {
            return;
        }

        $this->bulkEditRendered = true;

        echo '<fieldset class="inline-edit-col-right inline-edit-uml-language">';
        echo '<div class="inline-edit-col">';
        echo '<label class="inline-edit-group">';
        echo '<span class="title">Language</span>';
        echo '<select name="uml_bulk_language">';
        echo '<option value="">— No Change —</option>';
        foreach ($languages as $lang) {
            echo '<option value="' . esc_attr($lang->getSlug()) . '">' . esc_html($lang->getName()) . '</option>';
        }
        echo '</select>';
        echo '</label>';
        echo '</div>';
        echo '</fieldset>';
    }
}
