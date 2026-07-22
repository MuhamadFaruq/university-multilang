<?php

declare(strict_types=1);

namespace UniversityMultilang\Setup;

class Activator
{
    public static function activate(): void
    {
        update_option(
            'uml_plugin_installed',
            current_time('mysql')
        );

        flush_rewrite_rules();
    }
}
