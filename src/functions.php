<?php

declare(strict_types=1);

use UniversityMultilang\Core\Application;
use UniversityMultilang\Frontend\LanguageSwitcher;

if (!function_exists('uml_language_switcher')) {
    /**
     * Public Template API for Language Switcher.
     *
     * Usage in theme templates:
     * <?php echo uml_language_switcher(['type' => 'dropdown']); ?>
     * <?php echo uml_language_switcher(['type' => 'list']); ?>
     *
     * @param array $args Options for rendering.
     * @return string HTML output.
     */
    function uml_language_switcher(array $args = []): string
    {
        global $university_multilang_app;
        if ($university_multilang_app instanceof Application) {
            $container = $university_multilang_app->getContainer();
            if ($container->has(LanguageSwitcher::class)) {
                /** @var LanguageSwitcher $switcher */
                $switcher = $container->get(LanguageSwitcher::class);
                return $switcher->renderSwitcher($args);
            }
        }
        return '';
    }
}
