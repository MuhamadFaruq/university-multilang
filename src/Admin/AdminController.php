<?php

declare(strict_types=1);

namespace UniversityMultilang\Admin;

class AdminController
{
    public function renderDashboard(): void
    {
        echo '<div class="wrap">';
        echo '<h1>University Multilang Dashboard</h1>';
        echo '<p>Welcome to University Multilang! Plugin architecture is now powered by a DI Container and Hook Manager.</p>';
        echo '</div>';
    }
}
