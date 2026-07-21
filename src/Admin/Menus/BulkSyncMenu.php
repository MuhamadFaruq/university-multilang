<?php

declare(strict_types=1);

namespace UniversityMultilang\Admin\Menus;

use UniversityMultilang\Admin\MenuInterface;

class BulkSyncMenu implements MenuInterface
{
    public function getSlug(): string
    {
        return 'uml-bulk-sync';
    }

    public function getIcon(): string
    {
        return 'dashicons-update-alt';
    }

    public function getPosition(): int
    {
        return 40;
    }

    public function render(): void
    {
        // Enqueue JS
        wp_enqueue_script(
            'uml-bulk-sync-js',
            plugin_dir_url(__FILE__) . '../../../../assets/js/bulk-sync.js',
            ['jquery'],
            '1.0.0',
            true
        );

        wp_localize_script('uml-bulk-sync-js', 'umlBulkSyncData', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('uml_bulk_sync_nonce')
        ]);
        
        ?>
        <div class="wrap">
            <h1>Bulk Sync Translation</h1>
            <p>Use this tool to automatically duplicate and translate all existing posts and pages that haven't been translated yet.</p>
            <p><strong>Note:</strong> Depending on the amount of content, this may take a few minutes. Do not close this page while the sync is running.</p>
            
            <div style="margin-top: 30px;">
                <button id="uml-start-sync-btn" class="button button-primary button-hero">Start Bulk Sync</button>
            </div>

            <div id="uml-sync-progress-container" style="display: none; margin-top: 30px; max-width: 600px;">
                <h3>Sync Progress</h3>
                <div style="background: #e0e0e0; border-radius: 4px; height: 30px; width: 100%; overflow: hidden;">
                    <div id="uml-sync-progress-bar" style="background: #2271b1; height: 100%; width: 0%; transition: width 0.3s;"></div>
                </div>
                <p id="uml-sync-status" style="margin-top: 10px; font-weight: bold;">Initializing...</p>
            </div>
        </div>
        <?php
    }
}
