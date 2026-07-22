<?php

declare(strict_types=1);

namespace UniversityMultilang\Translation;

use UniversityMultilang\Core\ServiceProvider;
use UniversityMultilang\Translation\Metabox\LanguageMetabox;
use UniversityMultilang\Translation\Metabox\TermLanguageMetabox;
use UniversityMultilang\Translation\Contracts\TranslationRepositoryInterface;
use UniversityMultilang\Translation\Repositories\WpMetaTranslationRepository;
use UniversityMultilang\Translation\Services\TranslationService;
use UniversityMultilang\Language\Services\LanguageService;

class TranslationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind TranslationRepository
        $this->container->bind(TranslationRepositoryInterface::class, function ($container) {
            return new WpMetaTranslationRepository(
                $container->get(\UniversityMultilang\Language\Contracts\LanguageRepositoryInterface::class)
            );
        });

        // Bind TranslationService
        $this->container->bind(TranslationService::class, function ($container) {
            return new TranslationService(
                $container->get(TranslationRepositoryInterface::class),
                $container->get(LanguageService::class)
            );
        });

        // Bind LanguageMetabox
        $this->container->bind(LanguageMetabox::class, function ($container) {
            return new LanguageMetabox(
                $container->get(LanguageService::class),
                $container->get(TranslationService::class)
            );
        });

        // Bind TranslationProviderFactory
        $this->container->bind(\UniversityMultilang\Translation\Factories\TranslationProviderFactory::class, function ($container) {
            return new \UniversityMultilang\Translation\Factories\TranslationProviderFactory(
                $container->get(\UniversityMultilang\Settings\Services\SettingsService::class)
            );
        });

        // Bind ContentTranslatorInterface using Factory and CachedTranslator decorator
        $this->container->bind(\UniversityMultilang\Translation\Contracts\ContentTranslatorInterface::class, function ($container) {
            /** @var \UniversityMultilang\Translation\Factories\TranslationProviderFactory $factory */
            $factory = $container->get(\UniversityMultilang\Translation\Factories\TranslationProviderFactory::class);
            $innerTranslator = $factory->create();
            return new \UniversityMultilang\Translation\Services\CachedTranslator($innerTranslator);
        });

        // Bind PostRepository
        $this->container->bind(\UniversityMultilang\Translation\Contracts\PostRepositoryInterface::class, function () {
            return new \UniversityMultilang\Translation\Repositories\WpPostRepository();
        });

        // Bind AutoDuplicateService
        $this->container->bind(\UniversityMultilang\Translation\Services\AutoDuplicateService::class, function ($container) {
            $elementorService = $container->has(\UniversityMultilang\Elementor\Services\ElementorDataService::class)
                ? $container->get(\UniversityMultilang\Elementor\Services\ElementorDataService::class)
                : null;

            return new \UniversityMultilang\Translation\Services\AutoDuplicateService(
                $container->get(TranslationService::class),
                $container->get(LanguageService::class),
                $container->get(\UniversityMultilang\Translation\Factories\TranslationProviderFactory::class),
                $container->get(\UniversityMultilang\Translation\Contracts\PostRepositoryInterface::class),
                $elementorService
            );
        });

        // Bind TranslationQueueService
        $this->container->bind(\UniversityMultilang\Translation\Services\TranslationQueueService::class, function ($container) {
            return new \UniversityMultilang\Translation\Services\TranslationQueueService(
                $container->get(\UniversityMultilang\Translation\Services\AutoDuplicateService::class)
            );
        });

        // Register WP Cron Background Action
        $this->hooks->addAction('uml_process_translation_queue_event', $this->container->get(\UniversityMultilang\Translation\Services\TranslationQueueService::class), 'processPostTranslation');

        // Bind TermLanguageMetabox
        $this->container->bind(TermLanguageMetabox::class, function ($container) {
            return new TermLanguageMetabox(
                $container->get(LanguageService::class),
                $container->get(TranslationService::class)
            );
        });

        // Bind TranslationController
        $this->container->bind(TranslationController::class, function ($container) {
            return new TranslationController(
                $container->get(TranslationService::class),
                $container->get(LanguageService::class),
                $container->get(\UniversityMultilang\Translation\Services\AutoDuplicateService::class)
            );
        });

        // Bind TranslationColumnManager
        $this->container->bind(\UniversityMultilang\Translation\Admin\TranslationColumnManager::class, function ($container) {
            return new \UniversityMultilang\Translation\Admin\TranslationColumnManager(
                $container->get(LanguageService::class),
                $container->get(TranslationService::class)
            );
        });

        // Register hooks for Column Manager
        /** @var \UniversityMultilang\Translation\Admin\TranslationColumnManager $columnManager */
        $columnManager = $this->container->get(\UniversityMultilang\Translation\Admin\TranslationColumnManager::class);
        $columnManager->registerHooks();

        // Register hooks for Metabox
        $this->hooks->addAction('add_meta_boxes', $this->container->get(LanguageMetabox::class), 'registerMetabox');
        $this->hooks->addAction('save_post', $this->container->get(LanguageMetabox::class), 'savePostData');

        // Register hooks for Term Language Metabox
        /** @var TermLanguageMetabox $termMetabox */
        $termMetabox = $this->container->get(TermLanguageMetabox::class);
        $termMetabox->registerHooks();

        // Register hooks for linking new translations & AJAX
        $this->hooks->addAction('wp_insert_post', $this->container->get(TranslationController::class), 'linkNewTranslation', 10, 3);
        $this->hooks->addAction('save_post', $this->container->get(TranslationController::class), 'autoDuplicateTranslations', 20, 3);
        $this->hooks->addAction('before_delete_post', $this->container->get(TranslationController::class), 'handlePostDeletion');
        $this->hooks->addAction('wp_ajax_uml_link_existing_post', $this->container->get(TranslationController::class), 'handleLinkExistingPost');
    }
}
