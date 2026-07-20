<?php

declare(strict_types=1);

namespace UniversityMultilang\Translation;

class TranslationController
{
    private TranslationManager $translationManager;

    public function __construct(TranslationManager $translationManager)
    {
        $this->translationManager = $translationManager;
    }

    /**
     * Hooked to 'wp_insert_post'.
     * Runs when a new post (including auto-draft) is created in the database.
     */
    public function linkNewTranslation(int $postId, \WP_Post $post, bool $update): void
    {
        // Only run for new posts (not updates)
        if ($update) {
            return;
        }

        // We only care if the request comes from our "Add Translation" link
        if (!isset($_GET['from_post']) || !isset($_GET['new_lang'])) {
            return;
        }

        $fromPostId = (int) $_GET['from_post'];
        $newLang = sanitize_title($_GET['new_lang']);

        // Set the language for the new post
        $this->translationManager->setPostLanguage($postId, $newLang);

        // Link the translation
        $this->translationManager->linkTranslations($fromPostId, $postId);
    }
}
