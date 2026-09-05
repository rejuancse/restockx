/**
 * Campaigns Listing Page JavaScript
 *
 * The campaigns list itself is rendered server-side (PHP query in
 * Alertx_Menu::alertx_campaigns(), like the Subscribers page). This file
 * only handles the interactive row actions (quick view, edit, duplicate,
 * delete, status switcher, bulk delete) which still go through AJAX.
 */

(function($) {
    'use strict';

    // Initialize on document ready
    $(document).ready(function() {
        initRowActions();
        initBulkActions();
    });

    /**
     * Initialize row action handlers
     */
    function initRowActions() {
        // Kebab (3 dots) button toggles the actions dropdown menu
        $('.actions .kebab').on('click', function(e) {
            e.stopPropagation();

            const $menu = $(this).siblings('.menu');

            // Close any other open menus first
            $('.actions .menu').not($menu).hide();

            $menu.toggle();
        });

        // Clicking anywhere outside a menu closes all menus
        // (namespaced + off() so re-renders don't stack handlers)
        $(document).off('click.alertxCampaignMenus').on('click.alertxCampaignMenus', function() {
            $('.actions .menu').hide();
        });

        // Any menu action closes the menu
        $('.actions .menu button').on('click', function() {
            $(this).closest('.menu').hide();
        });

        // View action - open the email quick view modal
        $('.actions button[data-action="view"]').on('click', function() {
            const campaignId = $(this).closest('tr').data('campaign-id');
            viewCampaign(campaignId);
        });

        // Edit action - open the campaign in the edit screen
        $('.actions button[data-action="edit"]').on('click', function() {
            const campaignId = $(this).closest('tr').data('campaign-id');
            editCampaign(campaignId);
        });

        // Duplicate action
        $('.actions button[data-action="duplicate"]').on('click', function() {
            const campaignId = $(this).closest('tr').data('campaign-id');
            duplicateCampaign(campaignId);
        });

        // Delete action
        $('.actions button[data-action="delete"]').on('click', function() {
            const campaignId = $(this).closest('tr').data('campaign-id');
            deleteCampaign(campaignId);
        });

        // Status switcher (draft <-> sent)
        $('.status-toggle').on('change', function() {
            const $toggle = $(this);
            const campaignId = $toggle.closest('tr').data('campaign-id');
            toggleCampaignStatus(campaignId, $toggle);
        });

        // Quick view modal close handlers
        $('#preview-modal-close, #preview-modal-close-footer, #campaign-preview-overlay').off('click.alertxPreview').on('click.alertxPreview', function(e) {
            // Ignore clicks coming from inside the modal card itself
            if (e.target === this || this.id !== 'campaign-preview-overlay') {
                closePreviewModal();
            }
        });

        // Close quick view on Escape
        $(document).off('keydown.alertxPreview').on('keydown.alertxPreview', function(e) {
            if (e.key === 'Escape' && $('#campaign-preview-overlay').is(':visible')) {
                closePreviewModal();
            }
        });
    }

    /**
     * Open the campaign in the edit screen
     */
    function editCampaign(campaignId) {
        const baseUrl = alertxCampaign.newCampaignUrl || 'admin.php?page=new-campaign';
        window.location.href = baseUrl + '&campaign=' + campaignId;
    }

    /**
     * Quick view - show the email that will be sent to customers
     */
    function viewCampaign(campaignId) {
        showLoading();

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'alertx_preview_campaign',
                nonce: alertxCampaign.nonce,
                campaign_id: campaignId
            },
            success: function(response) {
                hideLoading();

                if (response.success) {
                    $('#preview-modal-subject').text(response.data.subject || '');
                    $('#preview-email-container').html(response.data.html_content || '');
                    $('#campaign-preview-overlay').addClass('show');
                } else {
                    showError(response.data.message || 'Failed to load campaign preview');
                }
            },
            error: function() {
                hideLoading();
                showError('Failed to load campaign preview');
            }
        });
    }

    /**
     * Close the quick view modal
     */
    function closePreviewModal() {
        $('#campaign-preview-overlay').removeClass('show');
        $('#preview-email-container').html('');
    }

    /**
     * Toggle campaign status between draft (off) and sent/published (on)
     */
    function toggleCampaignStatus(campaignId, $toggle) {
        const isPublishing = $toggle.prop('checked');
        const message = isPublishing
            ? 'Publish this campaign?'
            : 'Move this campaign back to draft?';

        if (!confirm(message)) {
            // Revert the switch when cancelled
            $toggle.prop('checked', !isPublishing);
            return;
        }

        showLoading();

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'alertx_toggle_campaign_status',
                nonce: alertxCampaign.nonce,
                campaign_id: campaignId
            },
            success: function(response) {
                hideLoading();

                if (response.success) {
                    showSuccess(response.data.message || 'Campaign status updated.');
                    window.location.reload();
                } else {
                    // Revert the switch on failure
                    $toggle.prop('checked', !isPublishing);
                    showError(response.data.message || 'Failed to update campaign status');
                }
            },
            error: function() {
                hideLoading();
                $toggle.prop('checked', !isPublishing);
                showError('Failed to update campaign status');
            }
        });
    }

    /**
     * Duplicate campaign
     */
    function duplicateCampaign(campaignId) {
        if (!confirm('Are you sure you want to duplicate this campaign?')) {
            return;
        }

        showLoading();

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'alertx_duplicate_campaign',
                nonce: alertxCampaign.nonce,
                campaign_id: campaignId
            },
            success: function(response) {
                hideLoading();

                if (response.success) {
                    showSuccess(response.data.message);
                    window.location.reload();
                } else {
                    showError(response.data.message || 'Failed to duplicate campaign');
                }
            },
            error: function() {
                hideLoading();
                showError('Failed to duplicate campaign');
            }
        });
    }

    /**
     * Delete campaign
     */
    function deleteCampaign(campaignId) {
        if (!confirm('Are you sure you want to delete this campaign? This action cannot be undone.')) {
            return;
        }

        showLoading();

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'alertx_delete_campaign',
                nonce: alertxCampaign.nonce,
                campaign_id: campaignId
            },
            success: function(response) {
                hideLoading();

                if (response.success) {
                    showSuccess(response.data.message);
                    window.location.reload();
                } else {
                    showError(response.data.message || 'Failed to delete campaign');
                }
            },
            error: function() {
                hideLoading();
                showError('Failed to delete campaign');
            }
        });
    }

    /**
     * Initialize bulk actions
     */
    function initBulkActions() {
        $('#cb-select-all').on('change', function() {
            const isChecked = $(this).prop('checked');
            $('tbody input[name="campaign_ids[]"]').prop('checked', isChecked);
        });

        // Bulk delete runs only when the Apply button is clicked
        $('.action[value="Apply"]').on('click', function(e) {
            e.preventDefault();
            const action = $('#bulk-action-selector').val();
            if (action === 'delete') {
                bulkDelete();
            }
        });
    }

    /**
     * Bulk delete campaigns
     */
    function bulkDelete() {
        const selectedIds = [];
        $('tbody input[name="campaign_ids[]"]:checked').each(function() {
            selectedIds.push($(this).val());
        });

        if (selectedIds.length === 0) {
            showError('Please select at least one campaign');
            return;
        }

        showLoading();

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'alertx_bulk_delete_campaigns',
                nonce: alertxCampaign.nonce,
                campaign_ids: selectedIds
            },
            success: function(response) {
                hideLoading();

                if (response.success) {
                    // Reload so the server-rendered list reflects the deletion
                    window.location.reload();
                } else {
                    showError(response.data.message || 'Failed to delete campaigns');
                }
            },
            error: function() {
                hideLoading();
                showError('Failed to delete campaigns');
            }
        });
    }

    /**
     * Helper functions
     */
    function showLoading() {
        $('.alertx-preloader').addClass('show');
    }

    function hideLoading() {
        $('.alertx-preloader').removeClass('show');
    }

    function showSuccess(message) {
        // Could be enhanced with proper notification system
        alert(message);
    }

    function showError(message) {
        // Could be enhanced with proper notification system
        alert(message);
    }

})(jQuery);
