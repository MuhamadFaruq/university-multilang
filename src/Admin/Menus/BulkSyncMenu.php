<?php

declare(strict_types=1);

namespace UniversityMultilang\Admin\Menus;

use UniversityMultilang\Admin\Contracts\MenuInterface;

class BulkSyncMenu implements MenuInterface
{
    public function getSlug(): string
    {
        return 'uml-bulk-sync';
    }

    public function getParentSlug(): ?string
    {
        return 'university-multilang';
    }

    public function getPageTitle(): string
    {
        return 'Bulk Sync Translation';
    }

    public function getMenuTitle(): string
    {
        return 'Bulk Sync';
    }

    public function getCapability(): string
    {
        return 'manage_options';
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
            plugin_dir_url(__FILE__) . '../../../assets/js/bulk-sync.js',
            ['jquery'],
            '1.0.0',
            true
        );

        wp_localize_script('uml-bulk-sync-js', 'umlBulkSyncData', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('uml_bulk_sync_nonce')
        ]);
        
        ?>
        <style>
            /* Custom Professional Modal */
            .uml-modal-overlay {
                position: fixed;
                top: 0; left: 0; right: 0; bottom: 0;
                background: rgba(0, 0, 0, 0.5);
                backdrop-filter: blur(4px);
                z-index: 99999;
                display: none;
                align-items: center;
                justify-content: center;
                opacity: 0;
                transition: opacity 0.3s ease;
            }
            .uml-modal-overlay.uml-show {
                display: flex;
                opacity: 1;
            }
            .uml-modal-box {
                background: #fff;
                border-radius: 12px;
                box-shadow: 0 10px 30px rgba(0,0,0,0.2);
                width: 100%;
                max-width: 420px;
                padding: 30px;
                text-align: center;
                transform: scale(0.9);
                transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            }
            .uml-modal-overlay.uml-show .uml-modal-box {
                transform: scale(1);
            }
            .uml-modal-icon {
                font-size: 48px;
                color: #f5a623;
                margin-bottom: 15px;
            }
            .uml-modal-title {
                font-size: 20px;
                font-weight: 600;
                color: #1d2327;
                margin-bottom: 10px;
            }
            .uml-modal-text {
                font-size: 14px;
                color: #50575e;
                line-height: 1.5;
                margin-bottom: 25px;
            }
            .uml-modal-actions {
                display: flex;
                justify-content: center;
                gap: 12px;
            }
            .uml-btn-cancel {
                background: #f0f0f1;
                color: #2c3338;
                border: 1px solid #c3c4c7;
                padding: 8px 20px;
                border-radius: 6px;
                cursor: pointer;
                font-weight: 500;
                transition: all 0.2s;
            }
            .uml-btn-cancel:hover {
                background: #e6e6e6;
            }
            .uml-btn-confirm {
                background: #2271b1;
                color: #fff;
                border: 1px solid #2271b1;
                padding: 8px 20px;
                border-radius: 6px;
                cursor: pointer;
                font-weight: 500;
                transition: all 0.2s;
            }
            .uml-btn-confirm:hover {
                background: #135e96;
            }
        </style>

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

        <!-- Professional Modal HTML -->
        <div id="uml-confirm-modal" class="uml-modal-overlay">
            <div class="uml-modal-box">
                <div class="uml-modal-icon"><span class="dashicons dashicons-warning"></span></div>
                <div class="uml-modal-title">Confirm Bulk Sync</div>
                <div class="uml-modal-text">Are you sure you want to start the bulk sync? This process may take some time depending on your content size. Please do not close the browser window.</div>
                <div class="uml-modal-actions">
                    <button id="uml-modal-cancel" class="uml-btn-cancel">Cancel</button>
                    <button id="uml-modal-confirm" class="uml-btn-confirm">Yes, Start Sync</button>
                </div>
            </div>
        </div>
        <?php
    }
}
