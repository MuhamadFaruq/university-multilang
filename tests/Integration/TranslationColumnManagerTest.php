<?php

declare(strict_types=1);

namespace UniversityMultilang\Tests\Integration;

use UniversityMultilang\Translation\Admin\TranslationColumnManager;
use UniversityMultilang\Language\Services\LanguageService;
use UniversityMultilang\Language\Repositories\WpTermLanguageRepository;
use UniversityMultilang\Translation\Services\TranslationService;
use UniversityMultilang\Translation\Repositories\WpMetaTranslationRepository;

class TranslationColumnManagerTest extends IntegrationTestCase
{
    private TranslationColumnManager $columnManager;
    private LanguageService $languageService;
    private TranslationService $translationService;

    public function setUp(): void
    {
        parent::setUp();

        $langRepo = new WpTermLanguageRepository();
        $this->languageService = new LanguageService($langRepo);

        $transRepo = new WpMetaTranslationRepository($langRepo);
        $this->translationService = new TranslationService($transRepo, $this->languageService);

        if (!taxonomy_exists(WpTermLanguageRepository::TAXONOMY)) {
            $provider = new \UniversityMultilang\Language\LanguageServiceProvider(
                $this->app->getContainer(),
                $this->getService(\UniversityMultilang\Core\HookManager::class)
            );
            $provider->registerTaxonomy();
        }

        if (!$this->languageService->getLanguageBySlug('en')) {
            $this->languageService->addLanguage('English', 'en', 'en_US');
        }
        if (!$this->languageService->getLanguageBySlug('id')) {
            $this->languageService->addLanguage('Indonesian', 'id', 'id_ID');
        }

        $this->columnManager = new TranslationColumnManager($this->languageService, $this->translationService);
    }

    public function testAddLanguageColumnsAddsColumnsForPostListTable(): void
    {
        $columns = [
            'cb'    => '<input type="checkbox" />',
            'title' => 'Title',
            'date'  => 'Date',
        ];

        $updatedColumns = $this->columnManager->addLanguageColumns($columns);

        $this->assertArrayHasKey('uml_lang_en', $updatedColumns);
        $this->assertArrayHasKey('uml_lang_id', $updatedColumns);
        $this->assertEquals('English', $updatedColumns['uml_lang_en']);
        $this->assertEquals('Indonesian', $updatedColumns['uml_lang_id']);
    }

    public function testRenderCustomColumnOutputsTranslationStatusLink(): void
    {
        $postEn = $this->createPost(['post_title' => 'EN Post Column Test']);
        $postId = $this->createPost(['post_title' => 'ID Post Column Test']);

        $this->languageService->setLanguageForObject($postEn, 'post', 'en');
        $this->languageService->setLanguageForObject($postId, 'post', 'id');

        $this->translationService->linkTranslations($postEn, $postId, 'id', 'post');

        ob_start();
        $this->columnManager->renderCustomColumn('uml_lang_id', $postEn);
        $outputEnToId = ob_get_clean();

        // Should render edit link for existing ID translation
        $this->assertStringContainsString('post.php?post=' . $postId, $outputEnToId);

        ob_start();
        $this->columnManager->renderCustomColumn('uml_lang_en', $postId);
        $outputIdToEn = ob_get_clean();

        // Should render edit link for existing EN translation
        $this->assertStringContainsString('post.php?post=' . $postEn, $outputIdToEn);
    }

    public function testRenderCustomColumnOutputsAddTranslationLinkWhenMissing(): void
    {
        $postEnOnly = $this->createPost(['post_title' => 'EN Only Post']);
        $this->languageService->setLanguageForObject($postEnOnly, 'post', 'en');

        ob_start();
        $this->columnManager->renderCustomColumn('uml_lang_id', $postEnOnly);
        $output = ob_get_clean();

        // Should render add translation link '+'
        $this->assertStringContainsString('post-new.php', $output);
        $this->assertStringContainsString('from_post=' . $postEnOnly, $output);
        $this->assertStringContainsString('new_lang=id', $output);
    }
}
