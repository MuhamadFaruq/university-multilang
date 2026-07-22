<?php

declare(strict_types=1);

namespace UniversityMultilang\Tests\Integration;

use UniversityMultilang\Frontend\Widgets\LanguageSwitcherWidget;
use UniversityMultilang\Language\Services\LanguageService;
use UniversityMultilang\Language\Repositories\WpTermLanguageRepository;

class LanguageSwitcherFeatureTest extends IntegrationTestCase
{
    private LanguageService $languageService;

    public function setUp(): void
    {
        parent::setUp();

        $langRepo = new WpTermLanguageRepository();
        $this->languageService = new LanguageService($langRepo);

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
    }

    public function testGlobalHelperFunctionUmlLanguageSwitcherReturnsHtml(): void
    {
        $this->assertTrue(function_exists('uml_language_switcher'));

        $dropdownHtml = uml_language_switcher(['type' => 'dropdown']);
        $this->assertStringContainsString('uml-custom-dropdown', $dropdownHtml);
        $this->assertStringContainsString('English', $dropdownHtml);
        $this->assertStringContainsString('Indonesian', $dropdownHtml);

        $listHtml = uml_language_switcher(['type' => 'list']);
        $this->assertStringContainsString('uml-language-switcher', $listHtml);
        $this->assertStringContainsString('uml-lang-en', $listHtml);
        $this->assertStringContainsString('uml-lang-id', $listHtml);
    }

    public function testShortcodeUmlLanguageSwitcherRendersCorrectly(): void
    {
        $shortcodeOutput = do_shortcode('[uml_language_switcher type="list"]');
        $this->assertStringContainsString('uml-language-switcher', $shortcodeOutput);
        $this->assertStringContainsString('English', $shortcodeOutput);
    }

    public function testWidgetRegistrationHookIsActive(): void
    {
        $this->assertHasAction('widgets_init');
    }

    public function testWidgetRendersHtmlOutput(): void
    {
        $widget = new LanguageSwitcherWidget();

        ob_start();
        $widget->widget(
            [
                'before_widget' => '<div class="widget-test">',
                'after_widget'  => '</div>',
                'before_title'  => '<h2>',
                'after_title'   => '</h2>',
            ],
            [
                'title' => 'Select Language',
                'type'  => 'dropdown',
            ]
        );
        $output = ob_get_clean();

        $this->assertStringContainsString('<div class="widget-test">', $output);
        $this->assertStringContainsString('<h2>Select Language</h2>', $output);
        $this->assertStringContainsString('uml-custom-dropdown', $output);
    }
}
