jQuery(document).ready(function($) {
    // Handle variation selection visibility for notify UI
    var $variationForm = $('form.variations_form');
    var $notifyWrap = $('.notify-me-button-wrap');
    var $notifyForm = $('.alertx-notify-form');
    var $notifyProductId = $('.alertx-notify-product-id');

    // Place notify UI directly below the "Out of stock" text when present
    function moveNotifyAfterStock(scope) {
        if (!$notifyWrap.length) return;
        var $searchScope = scope && scope.length ? scope : $(document);
        var $stock = $searchScope.find('.stock.out-of-stock, .woocommerce-variation-availability .stock.out-of-stock').first();
        if ($stock.length && !$stock.next('.notify-me-button-wrap').length) {
            $stock.after($notifyWrap);
        }
    }

    // Initial placement: try under any existing out-of-stock message, else near cart/summary
    (function initialPlacement(){
        if (!$notifyWrap.length) return;
        moveNotifyAfterStock($(document));
        if (!$notifyWrap.parent().length) {
            var $fallbackTarget = $('form.cart').first();
            if (!$fallbackTarget.length) {
                $fallbackTarget = $('.summary.entry-summary, .product .summary').first();
            }
            if ($fallbackTarget.length && !$fallbackTarget.find('.notify-me-button-wrap').length) {
                $fallbackTarget.append($notifyWrap);
            }
        }
    })();

    if ($variationForm.length) {
        // Initial visibility: if any variation is out of stock, show the Notify UI
        try {
            var variationsData = $variationForm.data('product_variations') || [];

            // Handle empty variations data - wait for AJAX load
            if (!variationsData || variationsData.length === 0) {
                // Check if there's already an out of stock message visible
                if ($('.stock.out-of-stock').length || $('.woocommerce-variation-availability .stock.out-of-stock').length) {
                    $notifyWrap.removeClass('notify-hidden').show();
                    moveNotifyAfterStock($variationForm);
                }
            } else {
                // Check if any variation is out of stock
                var anyOutOfStock = Array.isArray(variationsData) && variationsData.some(function(v){
                    return v && v.is_in_stock === false;
                });
                if (anyOutOfStock) {
                    $notifyWrap.removeClass('notify-hidden').show();
                    moveNotifyAfterStock($variationForm);
                }
            }
        } catch (e) {
            // Fallback: if a generic out-of-stock message is present, show the Notify UI
            if ($('.stock.out-of-stock').length) {
                $notifyWrap.removeClass('notify-hidden').show();
                moveNotifyAfterStock($(document));
            }
        }

        // Watch for stock availability changes in variation form
        function checkStockAvailability() {
            var $availability = $variationForm.find('.woocommerce-variation-availability .stock.out-of-stock');
            var $availabilityInStock = $variationForm.find('.woocommerce-variation-availability .stock.in-stock');

            // Check if there's any out of stock message
            if ($availability.length) {
                // Out of stock message is visible, show notify button
                $notifyWrap.removeClass('notify-hidden').show();
                moveNotifyAfterStock($variationForm);
                return;
            }

            // Check if in stock message
            if ($availabilityInStock.length) {
                var stockText = $availabilityInStock.text().toLowerCase();
                // Hide if it's clearly in stock (not containing "out of stock")
                if (stockText.indexOf('out of stock') === -1 && stockText.indexOf('available') !== -1) {
                    $notifyWrap.hide().addClass('notify-hidden');
                } else if (stockText.indexOf('out of stock') !== -1) {
                    // Even in "in-stock" class, text says out of stock
                    $notifyWrap.removeClass('notify-hidden').show();
                    moveNotifyAfterStock($variationForm);
                }
            }
        }

        // Monitor attribute changes
        $variationForm.on('change', '[name^="attribute_"]', function() {
            setTimeout(checkStockAvailability, 100);
        });

        // Also monitor WooCommerce variation updates
        $(document).on('woocommerce_update_variation_value', function() {
            setTimeout(checkStockAvailability, 100);
        });

        // Monitor when variation form is reset (user clears selection)
        $variationForm.on('reset_data', function() {
            setTimeout(checkStockAvailability, 100);
        });

        // Check stock availability after variation is shown
        $variationForm.on('show_variation', function() {
            setTimeout(checkStockAvailability, 100);
        });

        // When a valid variation is found
        $variationForm.on('found_variation', function(event, variation) {
            if (!variation) {
                return;
            }

            // Check if variation is out of stock or has stock status indicating no availability
            var isOutOfStock = variation.is_in_stock === false ||
                              variation.is_in_stock === 'no' ||
                              variation.stock_status === 'outofstock';

            if (isOutOfStock) {
                // Show notify UI and set product_id to variation_id
                $notifyWrap.removeClass('notify-hidden').show();
                if ($notifyProductId.length) {
                    $notifyProductId.val(variation.variation_id);
                }
                // Place under the variation's out-of-stock message if present
                setTimeout(function() {
                    moveNotifyAfterStock($variationForm);
                    // Double-check stock availability in the DOM
                    checkStockAvailability();
                }, 50);
            } else {
                // Hide notify UI and clear product_id
                $notifyWrap.hide().addClass('notify-hidden');
                if ($notifyProductId.length) {
                    $notifyProductId.val('');
                }
            }
        });

        // When attributes reset or no variation
        $variationForm.on('reset_data hide_variation', function() {
            $notifyWrap.hide().addClass('notify-hidden');
            if ($notifyProductId.length) {
                $notifyProductId.val('');
            }
        });
    }
    $(document).on('click', '.alertx-notify-button', function() {
        var $button = $(this);
        // The form lives outside .alertx-wrap (next to the tooltip),
        // so look it up from the common product wrapper instead of siblings.
        var $form = $button.closest('.notify-me-button-wrap').find('.alertx-notify-form');
        $form.toggleClass('notify-hidden');
    });

    $(document).on('click', '.alertx-submit-notify', function(e) {
        e.preventDefault();

        var $button = $(this);
        var $form = $button.closest('.alertx-notify-form');
        var $emailField = $form.find('.alertx-notify-email');
        var $productIdField = $form.find('.alertx-notify-product-id');
        var $parentIdField = $form.find('.alertx-notify-parent-id');
        var $nonceField = $form.find('.alertx-notify-nonce');

        var email = $emailField.val().trim();
        var product_id = $productIdField.val();
        var parent_id = $parentIdField.length ? $parentIdField.val() : 0;
        var nonce = $nonceField.val();

        // Enhanced email validation
        var emailRegex = /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/;
        if (!email || !emailRegex.test(email)) {
            showError($form, 'Please enter a valid email address.');
            return;
        }

        if (!product_id && !parent_id) {
            showError($form, 'Error: Product/Variation ID not found. Please select a variation (if applicable) or refresh the page and try again.');
            return;
        }

        if (!nonce) {
            showError($form, 'Security error. Please refresh the page and try again.');
            return;
        }

        // Check if notify_ajax object is available
        if (typeof notify_ajax === 'undefined' || !notify_ajax.ajax_url || !notify_ajax.nonce) {
            showError($form, 'Configuration error. Please refresh the page and try again.');
            return;
        }

        $button.prop('disabled', true).text('Submitting...');
        $form.find('.error-message, .success-message').remove();

        $.ajax({
            url: notify_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'stock_notification',
                email: email,
                product_id: product_id,
                parent_id: parent_id,
                nonce: nonce
            },
            success: function(response) {
                if (response.success) {
                    $form.html('<p class="success-message">' + response.data.message + '</p>');

                    if (response.data.alternatives && response.data.alternatives.length > 0) {
                        var alternativesHtml = '<h3>In the meantime, you might also like:</h3><ul class="product-alternatives">';
                        response.data.alternatives.forEach(function(product) {
                            alternativesHtml += '<li>' +
                                '<a href="' + escapeHtml(product.url) + '">' +
                                '<img src="' + escapeHtml(product.image) + '" alt="' + escapeHtml(product.name) + '" width="50" height="50">' +
                                '<span class="product-name">' + escapeHtml(product.name) + '</span>' +
                                '<span class="product-price">' + escapeHtml(product.price) + '</span>' +
                                '</a></li>';
                        });
                        alternativesHtml += '</ul>';
                        $form.after(alternativesHtml);
                    }
                } else {
                    showError($form, response.data || 'Unknown error occurred. Please try again.');
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                showError($form, 'An error occurred. Please try again later. Error: ' + textStatus);
            },
            complete: function() {
                $button.prop('disabled', false).text('Submit');
            }
        });
    });

    function showError($form, message) {
        $form.find('.error-message').remove();
        $form.prepend('<p class="error-message">' + escapeHtml(message) + '</p>');
    }

    function escapeHtml(unsafe) {
        // Convert to string first to handle non-string types (numbers, false, etc.)
        var str = String(unsafe || '');
        return str
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    // ============================================
    // Campaign Form Handling
    // ============================================

    // Initialize campaign form state
    function initCampaignForm() {
        // Check initial state of discount switch
        if ($('#discount-switch').is(':checked')) {
            $('#discount-fields').show();
        } else {
            $('#discount-fields').hide();
        }

        // Check initial state of All Products toggle
        // If unchecked (OFF), product fields should be visible
        if ($('#discount-all-products').is(':checked')) {
            // All Products is ON - hide product fields
            $('#product-fields').hide();
        } else {
            // All Products is OFF - show product fields
            $('#product-fields').show();
        }

        // Clear field errors when user starts typing
        $('#campaignForm input, #campaignForm textarea').on('input change', function() {
            var $field = $(this);
            if ($field.hasClass('error')) {
                $field.removeClass('error');
                $field.closest('.field').find('.field-error').remove();
            }
        });
    }

    // Run initialization on page load
    initCampaignForm();

    // Initialize date picker for expiry field
    if ($('#discount-expiry').length) {
        $('#discount-expiry').datepicker({
            dateFormat: 'MM d, yy',
            minDate: 0,
            altField: '#discount-expiry-alt',
            altFormat: 'yy-mm-dd',
            beforeShow: function(input, inst) {
                // Add custom styling if needed
                $( '#ui-datepicker-div' ).addClass('alertx-datepicker');
            }
        });

        // Also allow manual text input (e.g., "48 hours")
        $('#discount-expiry').on('change', function() {
            // If user enters text instead of picking date, allow it
            var val = $(this).val();
            if (val && !val.match(/^[\d]/)) {
                // It's a text input like "48 hours", keep it as is
                $(this).datepicker('option', 'altField', '');
            }
        });
    }

    // Handle discount switch toggle
    $('#discount-switch').on('change', function() {
        if ($(this).is(':checked')) {
            $('#discount-fields').slideDown(300);
            // Re-initialize product fields state
            if ($('#discount-all-products').is(':checked')) {
                $('#product-fields').hide();
            } else {
                $('#product-fields').show();
            }
        } else {
            $('#discount-fields').slideUp(300);
            // Reset all discount fields when disabled
            $('#discount-all-products').prop('checked', false);
        }
    });

    // Handle discount all products toggle
    // When "All Products" is OFF, show product-specific fields
    $('#discount-all-products').on('change', function() {
        if ($(this).is(':checked')) {
            // All Products is ON - hide product-specific fields and show message
            $('#product-fields').slideUp(300);
            $('#product-fields').find('input').val('');
            $('#discount-all-products-message').slideDown(300);
        } else {
            // All Products is OFF - show product-specific fields and hide message
            $('#product-fields').slideDown(300);
            $('#discount-all-products-message').slideUp(300);
        }
    });

    // Cancel button handler
    $('#modal-cancel').on('click', function(e) {
        e.preventDefault();
        closeCampaignModal();
    });

    // Modal close button handler
    $('#modal-close').on('click', function(e) {
        e.preventDefault();
        closeCampaignModal();
    });

    /**
     * Close campaign modal and reset form
     */
    function closeCampaignModal() {
        var $overlay = $('#campaign-overlay');
        $overlay.fadeOut(300, function() {
            $overlay.removeClass('active show').fadeOut(300);
            // Reset form
            $('#campaignForm')[0].reset();
            $('#discount-fields').hide();
            $('#product-fields').hide();
        });
    }

    /**
     * Open campaign modal
     */
    function openCampaignModal() {
        var $overlay = $('#campaign-overlay');
        $overlay.fadeIn(300, function() {
            $(this).addClass('active show');
        });
    }

    // Expose functions globally for inline script compatibility
    window.openModal = function() {
        openCampaignModal();
    };

    window.closeModal = function() {
        closeCampaignModal();
    };

    // Global variable to track current page for pagination
    var currentCampaignPage = 1;
    // Global cache for campaigns data
    var campaignsCache = {};
    // Preloader timeout reference
    var preloaderTimeout = null;

    // ============================================
    // Preloader Helper Functions
    // ============================================

    // Show preloader with custom message
    function showPreloader(message) {
        message = message || 'Processing...';
        $('#preloader-text').text(message);
        $('#alertx-preloader').addClass('show');

        // Clear any existing timeout
        if (preloaderTimeout) {
            clearTimeout(preloaderTimeout);
            preloaderTimeout = null;
        }
    }

    // Hide preloader
    function hidePreloader() {
        $('#alertx-preloader').removeClass('show');

        // Clear any existing timeout
        if (preloaderTimeout) {
            clearTimeout(preloaderTimeout);
            preloaderTimeout = null;
        }
    }

    // Format campaign date - handles zero/null dates
    function formatCampaignDate(dateString) {
        // Check for invalid/zero dates - these are unsaved drafts
        if (!dateString || dateString === '0000-00-00 00:00:00' || dateString === '0000-00-00') {
            return 'Draft';
        }

        // Parse the date
        var date = new Date(dateString);

        // Check if date is invalid
        if (isNaN(date.getTime())) {
            return 'Draft';
        }

        // Format like WordPress posts: "22 aug, 10:53pm"
        var months = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'];
        var day = date.getDate();
        var month = months[date.getMonth()];
        var year = date.getFullYear();

        // Format time: 10:53pm
        var hours = date.getHours();
        var minutes = date.getMinutes();
        var ampm = hours >= 12 ? 'pm' : 'am';
        hours = hours % 12;
        hours = hours ? hours : 12; // the hour '0' should be '12'
        minutes = minutes < 10 ? '0' + minutes : minutes;

        var timeString = hours + ':' + minutes + ampm;

        // Return format: "22 aug, 10:53pm" or "22 aug, 2023, 10:53pm"
        if (year === new Date().getFullYear()) {
            // Same year - don't show year
            return day + ' ' + month + ', ' + timeString;
        } else {
            // Different year - show year
            return day + ' ' + month + ', ' + year + ', ' + timeString;
        }
    }

    // ============================================
    // Campaign List Management
    // ============================================

    // Load campaigns on page load
    function loadCampaigns(page, showPreloaderFlag) {
        page = page || 1;
        currentCampaignPage = page;

        // showPreloaderFlag: true = show, false = don't show, undefined = auto-detect
        if (showPreloaderFlag === true) {
            showPreloader('Loading campaigns...');
        } else if (showPreloaderFlag === undefined) {
            // Auto-detect: show preloader only when not initial load
            if (page !== 1 || arguments.length > 1) {
                showPreloader('Loading campaigns...');
            }
        }
        // If showPreloaderFlag === false, don't show (already showing from elsewhere)

        $.ajax({
            url: alertx_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'alertx_get_campaigns',
                nonce: alertx_ajax.nonce,
                page: page
            },
            success: function(response) {
                if (response.success) {
                    renderCampaignTable(response.data.campaigns);
                    renderPagination(response.data.pagination);
                    updateNotificationCount(response.data.pagination.total);
                    hidePreloader(); // Hide preloader after successful load
                } else {
                    showToast('Failed to load campaigns.', 'error');
                    hidePreloader(); // Hide preloader on error
                }
            },
            error: function() {
                showToast('Error loading campaigns.', 'error');
                hidePreloader(); // Hide preloader on AJAX error
            }
        });
    }

    // Update notification count display
    function updateNotificationCount(total) {
        var countText = total + ' item' + (total !== 1 ? 's' : '');
        $('.notification-count .displaying-num').text(countText);
    }

    // Render campaigns table
    function renderCampaignTable(campaigns) {
        var $tbody = $('#tableBody');
        $tbody.empty();

        // Clear and rebuild cache
        campaignsCache = {};

        if (!campaigns || campaigns.length === 0) {
            $tbody.html(
                '<tr><td colspan="6">' +
                    '<div class="empty-state">' +
                        '<div class="empty-state-icon">✉️</div>' +
                        '<div>No campaigns found. Create your first campaign!</div>' +
                    '</div>' +
                '</td></tr>'
            );
            return;
        }

        campaigns.forEach(function(campaign) {
            // Store in cache for instant access
            campaignsCache[campaign.id] = campaign;
            var row = renderCampaignRow(campaign);
            $tbody.append(row);
        });

        // Initialize kebab menus for new rows
        initKebabMenus();
        initCampaignCheckboxes();
    }

    // Render single campaign row
    function renderCampaignRow(campaign) {
        var status = campaign.camp_status || 'draft';
        var statusBadge = status === 'sent' ? 'active' : 'draft';
        var switchState = status === 'sent' ? 'on' : '';
        var discountInfo = '';

        // Build discount info - simplified version
        if (campaign.discount_switch === 'on') {
            discountInfo = '<span class="code-pill">' + escapeHtml(campaign.discount_code || 'Discount') + '</span>';
        } else {
            discountInfo = '-';
        }

        var schedule = '-';
        if (campaign.discount_expiry) {
            schedule = '<div class="dates">Expires: ' + escapeHtml(campaign.discount_expiry) + '</div>';
        }

        var row = '<tr data-campaign-id="' + campaign.id + '">' +
            '<th scope="row" class="check-column">' +
                '<input type="checkbox" class="campaign-checkbox" name="campaign_ids[]" value="' + campaign.id + '">' +
            '</th>' +
            '<td>' +
                '<div class="pulse-info">' +
                    '<div class="pname">' + escapeHtml(campaign.camp_subject) + '</div>' +
                    '<div class="pvariant">Created: ' + formatCampaignDate(campaign.created_at) + '</div>' +
                '</div>' +
            '</td>' +
            '<td>' + discountInfo + '</td>' +
            '<td>' + schedule + '</td>' +
            '<td>' +
                '<div class="status">' +
                    '<span class="badge ' + statusBadge + '">' + status + '</span>' +
                    '<div class="switch ' + switchState + '" data-campaign-id="' + campaign.id + '"></div>' +
                '</div>' +
            '</td>' +
            '<td>' +
                '<div class="actions">' +
                    '<button class="kebab" data-id="' + campaign.id + '" aria-label="More actions">' +
                        '<svg fill="#000000" viewBox="0 0 16 16">' +
                            '<path d="M8,6.5A1.5,1.5,0,1,1,6.5,8,1.5,1.5,0,0,1,8,6.5ZM.5,8A1.5,1.5,0,1,0,2,6.5,1.5,1.5,0,0,0,.5,8Zm12,0A1.5,1.5,0,1,0,14,6.5,1.5,1.5,0,0,0,12.5,8Z"/>' +
                        '</svg>' +
                    '</button>' +
                    '<div class="menu" data-menu="' + campaign.id + '">' +
                        '<button data-action="preview" data-id="' + campaign.id + '">' +
                            'Preview Email' +
                        '</button>' +
                        '<button data-action="edit" data-id="' + campaign.id + '">Edit campaign</button>' +
                        '<button data-action="duplicate" data-id="' + campaign.id + '">Duplicate</button>' +
                        '<button data-action="delete" data-id="' + campaign.id + '" class="danger">Delete</button>' +
                    '</div>' +
                '</div>' +
            '</td>' +
        '</tr>';

        return row;
    }

    // Initialize kebab menu dropdowns
    function initKebabMenus() {
        // Remove old event handlers to avoid duplicates
        $('.kebab').off('click');

        // Handle kebab menu toggle
        $('.kebab').on('click', function(e) {
            e.stopPropagation();
            var $button = $(this);
            var campaignId = $button.data('id');
            var $menu = $('.menu[data-menu="' + campaignId + '"]');

            // Close all other menus
            $('.menu').not($menu).removeClass('show');

            // Toggle current menu
            $menu.toggleClass('show');
        });

        // Close menu when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.kebab').length && !$(e.target).closest('.menu').length) {
                $('.menu').removeClass('show');
            }
        });

        // Handle menu button clicks
        $('.menu button').off('click').on('click', function(e) {
            e.stopPropagation();
            var $button = $(this);
            var action = $button.data('action');
            var campaignId = $button.data('id');

            // Store original button content and state before closing menu
            var originalText = $button.html();
            var originalClass = $button.attr('class');

            // Close menu immediately
            $('.menu').removeClass('show');

            // Handle action with loading state
            switch(action) {
                case 'preview':
                    previewCampaignWithButton(campaignId, $button, originalText);
                    break;
                case 'edit':
                    editCampaignWithButton(campaignId, $button, originalText);
                    break;
                case 'duplicate':
                    duplicateCampaignWithButton(campaignId, $button, originalText);
                    break;
                case 'delete':
                    deleteCampaignWithButton(campaignId, $button, originalText);
                    break;
            }
        });
    }

    // Initialize campaign checkboxes for bulk actions
    function initCampaignCheckboxes() {
        // Select all checkbox
        $('#cb-select-all').off('change').on('change', function() {
            var checked = $(this).is(':checked');
            $('.campaign-checkbox').prop('checked', checked).trigger('change');
        });

        // Individual checkboxes
        $('.campaign-checkbox').off('change').on('change', function() {
            var anyChecked = $('.campaign-checkbox:checked').length > 0;
            // You could enable/disable bulk actions button here
        });
    }

    // Preview campaign email with global preloader
    function previewCampaignWithButton(campaignId, $button, originalText) {
        // Show global preloader immediately
        showPreloader('Loading preview...');

        $.ajax({
            url: alertx_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'alertx_preview_campaign',
                nonce: alertx_ajax.nonce,
                campaign_id: campaignId
            },
            success: function(response) {
                if (response.success) {
                    // Hide preloader when modal appears
                    hidePreloader();
                    showEmailPreviewModal(response.data);
                } else {
                    hidePreloader();
                    showToast(response.data.message || 'Failed to load preview.', 'error');
                }
            },
            error: function() {
                hidePreloader();
                showToast('Error loading preview.', 'error');
            }
        });
    }

    // Show email preview modal
    function showEmailPreviewModal(data) {
        // Remove existing preview modal if any
        $('#email-preview-modal').remove();

        // Create preview modal HTML
        var modalHtml =
            '<div id="email-preview-modal" class="overlay preview-overlay" style="display:flex;">' +
                '<div class="modal preview-modal">' +
                    '<div class="modal-head">' +
                        '<div>' +
                            '<h3>Email Preview</h3>' +
                            '<p>This is how your email will appear to subscribers</p>' +
                        '</div>' +
                        '<button class="modal-close" onclick="jQuery(\'#email-preview-modal\').remove();">' +
                            '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">' +
                                '<path d="M18 6 6 18" />' +
                                '<path d="m6 6 12 12" />' +
                            '</svg>' +
                        '</button>' +
                    '</div>' +
                    '<div class="modal-body preview-body">' +
                        '<div class="email-preview-container">' +
                            data.html_content +
                        '</div>' +
                    '</div>' +
                    '<div class="modal-foot">' +
                        '<div class="preview-info">' +
                            '<div class="preview-info-item">' +
                                '<span class="preview-info-label">Subject</span>' +
                                '<span>' + escapeHtml(data.subject) + '</span>' +
                            '</div>' +
                        '</div>' +
                        '<button type="button" class="btn btn-secondary" onclick="jQuery(\'#email-preview-modal\').remove();">Close Preview</button>' +
                    '</div>' +
                '</div>' +
            '</div>';

        // Append to body
        $('body').append(modalHtml);
    }

    // Edit campaign with global preloader - uses cached data for instant response
    function editCampaignWithButton(campaignId, $button, originalText) {
        // Show global preloader
        showPreloader('Loading campaign...');

        // Use cached campaign data for instant access
        setTimeout(function() {
            var campaign = campaignsCache[campaignId];

            if (campaign) {
                // Hide preloader and open modal
                hidePreloader();
                populateCampaignForm(campaign);
                openCampaignModal();
            } else {
                // Fallback to AJAX if not in cache
                $.ajax({
                    url: alertx_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'alertx_get_campaigns',
                        nonce: alertx_ajax.nonce,
                        page: currentCampaignPage
                    },
                    success: function(response) {
                        if (response.success) {
                            var foundCampaign = null;
                            response.data.campaigns.forEach(function(c) {
                                if (c.id == campaignId) {
                                    foundCampaign = c;
                                }
                            });

                            if (foundCampaign) {
                                populateCampaignForm(foundCampaign);
                                openCampaignModal();
                            } else {
                                showToast('Campaign not found.', 'error');
                            }
                        } else {
                            showToast('Failed to load campaign.', 'error');
                        }
                    },
                    error: function() {
                        showToast('Error loading campaign.', 'error');
                    },
                    complete: function() {
                        hidePreloader();
                    }
                });
            }
        }, 300); // Small delay to show loading state for UX
    }

    // Populate form with campaign data
    function populateCampaignForm(campaign) {
        var $form = $('#campaignForm');

        $form.find('input[name="camp-subject"]').val(campaign.camp_subject);
        $form.find('textarea[name="camp-descriptions"]').val(campaign.camp_descriptions);

        // Discount switch
        if (campaign.discount_switch === 'on') {
            $('#discount-switch').prop('checked', true);
            $('#discount-fields').show();
        } else {
            $('#discount-switch').prop('checked', false);
            $('#discount-fields').hide();
        }

        // All products toggle
        if (campaign.discount_all_products === 'on') {
            $('#discount-all-products').prop('checked', true);
            $('#product-fields').hide();
            $('#discount-all-products-message').show();
        } else {
            $('#discount-all-products').prop('checked', false);
            $('#product-fields').show();
            $('#discount-all-products-message').hide();
        }

        $form.find('input[name="camp-product-name"]').val(campaign.camp_product_name);
        $form.find('input[name="camp-product-url"]').val(campaign.camp_product_url);
        $form.find('input[name="discount-code"]').val(campaign.discount_code);
        $form.find('input[name="discount-expiry"]').val(campaign.discount_expiry);

        // Add hidden field for campaign ID to update instead of insert
        if ($form.find('input[name="campaign_id"]').length === 0) {
            $form.append('<input type="hidden" name="campaign_id" value="">');
        }
        $form.find('input[name="campaign_id"]').val(campaign.id);

        // Change button text to indicate update mode
        $('#save-draft-btn .btn-label').text('Update Draft');
        $('#send-campaign-btn .btn-label').text('Update and Send');
    }

    // Delete campaign with global preloader
    function deleteCampaignWithButton(campaignId, $button, originalText) {
        if (!confirm('Are you sure you want to delete this campaign?')) {
            return;
        }

        // Show global preloader
        showPreloader('Deleting campaign...');

        $.ajax({
            url: alertx_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'alertx_delete_campaign',
                nonce: alertx_ajax.nonce,
                campaign_id: campaignId
            },
            success: function(response) {
                if (response.success) {
                    showToast(response.data.message, 'success');
                    // Don't hide preloader - let loadCampaigns handle it
                    loadCampaigns(currentCampaignPage, false);
                } else {
                    hidePreloader();
                    showToast(response.data.message || 'Failed to delete campaign.', 'error');
                }
            },
            error: function() {
                hidePreloader();
                showToast('Error deleting campaign.', 'error');
            }
        });
    }

    // Duplicate campaign with global preloader
    function duplicateCampaignWithButton(campaignId, $button, originalText) {
        // Show global preloader
        showPreloader('Duplicating campaign...');

        $.ajax({
            url: alertx_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'alertx_duplicate_campaign',
                nonce: alertx_ajax.nonce,
                campaign_id: campaignId
            },
            success: function(response) {
                if (response.success) {
                    showToast(response.data.message, 'success');
                    // Don't hide preloader - let loadCampaigns handle it
                    // Pass false to prevent showing another preloader
                    loadCampaigns(1, false);
                    // Update URL to page 1
                    var url = new URL(window.location.href);
                    url.searchParams.set('paged', '1');
                    window.history.replaceState({}, '', url);
                } else {
                    hidePreloader();
                    showToast(response.data.message || 'Failed to duplicate campaign.', 'error');
                }
            },
            error: function() {
                hidePreloader();
                showToast('Error duplicating campaign.', 'error');
            }
        });
    }

    // Handle campaign status toggle
    $(document).on('click', '.switch[data-campaign-id]', function() {
        var $switch = $(this);
        var campaignId = $switch.data('campaign-id');

        // Check if campaign is currently in draft status
        var isDraft = !$switch.hasClass('on');

        // If trying to publish (activate) a draft campaign, show confirmation
        if (isDraft) {
            if (!confirm('Are you sure you want to publish this campaign and send emails to users?')) {
                return;
            }
        }

        // Show preloader for status change
        showPreloader(isDraft ? 'Publishing campaign...' : 'Updating status...');

        $.ajax({
            url: alertx_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'alertx_activate_campaign',
                nonce: alertx_ajax.nonce,
                campaign_id: campaignId
            },
            success: function(response) {
                if (response.success) {
                    showToast(response.data.message, 'success');
                    // Don't hide preloader - let loadCampaigns handle it
                    loadCampaigns(currentCampaignPage, false);
                } else {
                    hidePreloader();
                    showToast(response.data.message || 'Failed to update campaign status.', 'error');
                }
            },
            error: function() {
                hidePreloader();
                showToast('Error updating campaign status.', 'error');
            }
        });
    });

    // Bulk delete
    $('#bulk-action-selector').closest('form').on('submit', function(e) {
        e.preventDefault();

        var action = $('#bulk-action-selector').val();
        if (action !== 'delete') {
            return;
        }

        var selectedIds = [];
        $('.campaign-checkbox:checked').each(function() {
            selectedIds.push($(this).val());
        });

        if (selectedIds.length === 0) {
            showToast('Please select at least one campaign.', 'error');
            return;
        }

        if (!confirm('Are you sure you want to delete ' + selectedIds.length + ' campaign(s)?')) {
            return;
        }

        // Show preloader for bulk delete
        showPreloader('Deleting campaigns...');

        $.ajax({
            url: alertx_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'alertx_bulk_delete_campaigns',
                nonce: alertx_ajax.nonce,
                campaign_ids: selectedIds
            },
            success: function(response) {
                if (response.success) {
                    showToast(response.data.message, 'success');
                    $('#cb-select-all').prop('checked', false);
                    // Don't hide preloader - let loadCampaigns handle it
                    loadCampaigns(currentCampaignPage, false);
                } else {
                    hidePreloader();
                    showToast(response.data.message || 'Failed to delete campaigns.', 'error');
                }
            },
            error: function() {
                hidePreloader();
                showToast('Error deleting campaigns.', 'error');
            }
        });
    });

    // Render pagination with URL-based navigation
    function renderPagination(pagination) {
        var $pagination = $('.campaign-pagination');
        if ($pagination.length === 0) {
            $('.notifications-list-tablenav').append('<div class="campaign-pagination tablenav-pages"></div>');
            $pagination = $('.campaign-pagination');
        }

        if (!pagination || pagination.total_pages <= 1) {
            $pagination.empty();
            return;
        }

        var html = '';
        var baseUrl = window.location.href.split('?')[0];
        var currentUrl = new URL(window.location.href);

        // Previous button
        if (pagination.current_page > 1) {
            currentUrl.searchParams.set('paged', pagination.current_page - 1);
            html += '<a class="page-numbers" href="' + currentUrl.toString() + '">‹</a>';
        }

        // Page numbers
        for (var i = 1; i <= pagination.total_pages; i++) {
            var className = i === pagination.current_page ? 'page-numbers current' : 'page-numbers';
            currentUrl.searchParams.set('paged', i);
            if (i === pagination.current_page) {
                html += '<span class="' + className + '">' + i + '</span>';
            } else {
                html += '<a class="' + className + '" href="' + currentUrl.toString() + '">' + i + '</a>';
            }
        }

        // Next button
        if (pagination.current_page < pagination.total_pages) {
            currentUrl.searchParams.set('paged', pagination.current_page + 1);
            html += '<a class="page-numbers" href="' + currentUrl.toString() + '">›</a>';
        }

        $pagination.html(html);
    }

    // Load campaigns on page ready - check for paged parameter in URL
    if ($('#tableBody').length) {
        // Get page number from URL parameter
        var urlParams = new URLSearchParams(window.location.search);
        var pagedParam = urlParams.get('paged');
        var pageToLoad = pagedParam ? parseInt(pagedParam) : 1;
        loadCampaigns(pageToLoad);
    }

    // ============================================
    // Form Submit Handlers (with edit support)
    // ============================================

    // Reset form to "create" mode when opening for new campaign
    window.openModal = function() {
        // Clear campaign ID to create new campaign
        var $form = $('#campaignForm');
        $form.find('input[name="campaign_id"]').remove();
        $('#save-draft-btn .btn-label').text('Save Draft');
        $('#send-campaign-btn .btn-label').text('Send campaign');

        openCampaignModal();
    };

    // Save Draft button handler
    $('#save-draft-btn').on('click', function(e) {
        e.preventDefault();
        handleCampaignForm('draft');
    });

    // Send Campaign button handler
    $('#send-campaign-btn').on('click', function(e) {
        e.preventDefault();
        handleCampaignForm('send');
    });

    function handleCampaignForm(action) {
        var $form = $('#campaignForm');
        var $button = action === 'draft' ? $('#save-draft-btn') : $('#send-campaign-btn');
        var subject = $form.find('input[name="camp-subject"]').val().trim();
        var campaignId = $form.find('input[name="campaign_id"]').val(); // Check if editing existing campaign

        // Clear all previous error messages
        clearFieldErrors();

        // Validation: Subject is required
        if (!subject) {
            showFieldError('camp-subject', 'Please enter a subject line.');
            return;
        }

        // Validation: If discount switch is ON, validate discount fields
        if ($('#discount-switch').is(':checked')) {
            var discountCode = $form.find('input[name="discount-code"]').val().trim();
            var discountExpiry = $form.find('input[name="discount-expiry"]').val().trim();

            if (!discountCode) {
                showFieldError('discount-code', 'Please enter a discount code.');
                return;
            }

            if (!discountExpiry) {
                showFieldError('discount-expiry', 'Please enter discount expiry.');
                return;
            }

            // Validation: If All Products is OFF, validate product fields
            if (!$('#discount-all-products').is(':checked')) {
                var productName = $form.find('input[name="camp-product-name"]').val().trim();
                var productUrl = $form.find('input[name="camp-product-url"]').val().trim();

                if (!productName) {
                    showFieldError('camp-product-name', 'Please enter a product name.');
                    return;
                }

                if (!productUrl) {
                    showFieldError('camp-product-url', 'Please enter a product URL.');
                    return;
                }

                // Validate URL format
                var urlPattern = /^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/;
                if (!urlPattern.test(productUrl)) {
                    showFieldError('camp-product-url', 'Please enter a valid product URL.');
                    return;
                }
            }
        }

        // Get form data
        var formData = {
            action: action === 'draft' ? 'alertx_save_campaign' : 'alertx_send_campaign',
            nonce: alertx_ajax.nonce,
            camp_subject: subject,
            discount_switch: $('#discount-switch').is(':checked') ? 'on' : 'off',
            discount_all_products: $('#discount-all-products').is(':checked') ? 'on' : 'off',
            camp_product_name: $form.find('input[name="camp-product-name"]').val(),
            camp_product_url: $form.find('input[name="camp-product-url"]').val(),
            discount_code: $form.find('input[name="discount-code"]').val(),
            discount_expiry: $form.find('input[name="discount-expiry"]').val(),
            camp_descriptions: $form.find('textarea[name="camp-descriptions"]').val()
        };

        // Add campaign_id if editing existing campaign
        if (campaignId) {
            formData.campaign_id = campaignId;
        }

        // Show loading state
        $button.prop('disabled', true).find('.btn-label').text('Processing...');

        $.ajax({
            url: alertx_ajax.ajax_url,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    // Show appropriate message based on action and edit mode
                    var message = response.data.message;
                    if (campaignId) {
                        // Editing existing campaign
                        if (action === 'draft') {
                            message = 'Campaign updated successfully!';
                        } else {
                            message = 'Campaign updated and sent successfully!';
                        }
                    }
                    showToast(message, 'success');

                    // Refresh campaign list to show new/updated campaign
                    loadCampaigns(currentCampaignPage);

                    if (action === 'draft') {
                        // Close modal and reset form after saving draft
                        setTimeout(function() {
                            closeCampaignModal();
                        }, 1000);
                    } else {
                        // For sent campaign, close modal immediately
                        setTimeout(function() {
                            closeCampaignModal();
                        }, 2000);
                    }
                } else {
                    showToast(response.data.message || 'An error occurred. Please try again.', 'error');
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('AJAX Error:', textStatus, errorThrown);
                console.error('Response:', jqXHR.responseText);
                showToast('An error occurred. Please try again later.', 'error');
            },
            complete: function() {
                $button.prop('disabled', false).find('.btn-label').text(action === 'draft' ? 'Save Draft' : 'Send campaign');
            }
        });
    }

    function showCampaignMessage(message, type) {
        // Deprecated - use showToast instead
        showToast(message, type);
    }

    /**
     * Show validation error below a specific field
     * @param {string} fieldName - The name attribute of the field
     * @param {string} message - Error message to display
     */
    function showFieldError(fieldName, message) {
        var $field = $('input[name="' + fieldName + '"], textarea[name="' + fieldName + '"]');
        var $fieldWrapper = $field.closest('.field');

        // Add error class to field
        $field.addClass('error');

        // Remove existing error message if any
        $fieldWrapper.find('.field-error').remove();

        // Add new error message
        var $error = $('<div class="field-error show">' + escapeHtml(message) + '</div>');
        $fieldWrapper.append($error);

        // Focus on the field
        $field.focus();
    }

    /**
     * Clear all field error messages
     */
    function clearFieldErrors() {
        $('.field-error').remove();
        $('.field input.error, .field textarea.error').removeClass('error');
    }

    /**
     * Show toast notification
     * @param {string} message - Message to display
     * @param {string} type - 'success' or 'error'
     */
    function showToast(message, type) {
        type = type || 'success';

        // Create toast container if not exists
        if ($('#alertx-toast-container').length === 0) {
            $('body').append('<div id="alertx-toast-container"></div>');
        }

        // Create toast element
        var $toast = $('<div class="alertx-toast alertx-toast-' + type + '"></div>');

        // Icon based on type
        var icon = type === 'success' ?
            '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg>' :
            '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>';

        $toast.html(
            '<div class="toast-icon">' + icon + '</div>' +
            '<div class="toast-content">' + escapeHtml(message) + '</div>' +
            '<button class="toast-close" onclick="jQuery(this).parent().remove()">×</button>'
        );

        // Remove existing toasts of same type
        $('#alertx-toast-container').find('.alertx-toast-' + type).remove();

        // Add new toast
        $('#alertx-toast-container').append($toast);

        // Trigger animation
        setTimeout(function() {
            $toast.addClass('show');
        }, 10);

        // Auto-hide after 5 seconds
        setTimeout(function() {
            $toast.removeClass('show');
            setTimeout(function() {
                $toast.remove();
            }, 300);
        }, 5000);
    }
});
