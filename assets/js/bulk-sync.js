(function($) {
    $(document).ready(function() {
        var $btn = $('#uml-start-sync-btn');
        var $container = $('#uml-sync-progress-container');
        var $bar = $('#uml-sync-progress-bar');
        var $status = $('#uml-sync-status');
        
        var totalPosts = 0;
        var processedPosts = 0;
        var batchSize = 5;

        var $modal = $('#uml-confirm-modal');
        var $btnCancel = $('#uml-modal-cancel');
        var $btnConfirm = $('#uml-modal-confirm');

        $btn.on('click', function(e) {
            e.preventDefault();
            // Show custom modal
            $modal.addClass('uml-show');
        });

        $btnCancel.on('click', function() {
            $modal.removeClass('uml-show');
        });

        $btnConfirm.on('click', function() {
            $modal.removeClass('uml-show');
            
            $btn.prop('disabled', true);
            $container.show();
            $status.text('Counting posts to sync...');
            $bar.css('width', '0%');

            // 1. Init: Get total posts
            $.ajax({
                url: umlBulkSyncData.ajax_url,
                type: 'POST',
                data: {
                    action: 'uml_bulk_sync_init',
                    nonce: umlBulkSyncData.nonce
                },
                success: function(response) {
                    if (response.success) {
                        totalPosts = response.data.total;
                        if (totalPosts === 0) {
                            $status.text('No posts found to sync!');
                            $bar.css('width', '100%');
                            $btn.prop('disabled', false);
                            return;
                        }

                        $status.text('Found ' + totalPosts + ' posts. Starting sync...');
                        processedPosts = 0;
                        processBatch();
                    } else {
                        $status.text('Error: ' + response.data);
                        $btn.prop('disabled', false);
                    }
                },
                error: function() {
                    $status.text('Network error occurred during initialization.');
                    $btn.prop('disabled', false);
                }
            });
        });

        function processBatch() {
            $.ajax({
                url: umlBulkSyncData.ajax_url,
                type: 'POST',
                data: {
                    action: 'uml_bulk_sync_process',
                    nonce: umlBulkSyncData.nonce,
                    offset: processedPosts,
                    limit: batchSize
                },
                success: function(response) {
                    if (response.success) {
                        var processedInBatch = response.data.processed;
                        processedPosts += processedInBatch;
                        
                        // Just in case offset logic gets weird, cap it at total
                        if (processedPosts > totalPosts) {
                            processedPosts = totalPosts;
                        }

                        var percentage = Math.round((processedPosts / totalPosts) * 100);
                        $bar.css('width', percentage + '%');
                        $status.text('Processed ' + processedPosts + ' of ' + totalPosts + ' posts (' + percentage + '%)');

                        if (processedPosts < totalPosts && processedInBatch > 0) {
                            // Continue with next batch
                            processBatch();
                        } else {
                            // Done!
                            $bar.css('width', '100%');
                            $status.text('Sync completed successfully!');
                            $btn.prop('disabled', false);
                        }
                    } else {
                        $status.text('Error: ' + response.data);
                        $btn.prop('disabled', false);
                    }
                },
                error: function() {
                    $status.text('Network error occurred during processing batch.');
                    $btn.prop('disabled', false);
                }
            });
        }
    });
})(jQuery);
