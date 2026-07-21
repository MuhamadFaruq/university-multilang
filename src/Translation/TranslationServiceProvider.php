<?php

declare(strict_types=1);

namespace UniversityMultilang\Translation;

use UniversityMultilang\Core\ServiceProvider;
use UniversityMultilang\Translation\Metabox\LanguageMetabox;
use UniversityMultilang\Translation\Metabox\TermLanguageMetabox;
use UniversityMultilang\Language\LanguageManager;

class TranslationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind TranslationManager
        $this->container->bind(TranslationManager::class, function () {
            return new TranslationManager();
        });

        // Bind LanguageMetabox
        $this->container->bind(LanguageMetabox::class, function ($container) {
            return new LanguageMetabox(
                $container->get(LanguageManager::class),
                $container->get(TranslationManager::class)
            );
        });

        // Bind MachineTranslator
        $this->container->bind(MachineTranslator::class, function () {
            return new MachineTranslator();
        });

        // Bind TermTranslationManager
        $this->container->bind(TermTranslationManager::class, function () {
            return new TermTranslationManager();
        });

        // Bind TermLanguageMetabox
        $this->container->bind(TermLanguageMetabox::class, function ($container) {
            return new TermLanguageMetabox(
                $container->get(LanguageManager::class),
                $container->get(TermTranslationManager::class)
            );
        });

        // Bind TranslationController
        $this->container->bind(TranslationController::class, function ($container) {
            return new TranslationController(
                $container->get(TranslationManager::class),
                $container->get(LanguageManager::class),
                $container->get(MachineTranslator::class),
                $container->get(TermTranslationManager::class)
            );
        });

        // Register hooks for Metabox
        $this->hooks->addAction('add_meta_boxes', $this->container->get(LanguageMetabox::class), 'registerMetabox');
        $this->hooks->addAction('save_post', $this->container->get(LanguageMetabox::class), 'savePostData');

        // Register hooks for Term Language Metabox
        /** @var TermLanguageMetabox $termMetabox */
        $termMetabox = $this->container->get(TermLanguageMetabox::class);
        $termMetabox->registerHooks();

        // Register hooks for linking new translations
        $this->hooks->addAction('wp_insert_post', $this->container->get(TranslationController::class), 'linkNewTranslation', 10, 3);
        $this->hooks->addAction('save_post', $this->container->get(TranslationController::class), 'autoDuplicateTranslations', 20, 3);
    }
}
