/**
 * New Campaign Page JavaScript
 *
 * Handles all interactive functionality for the campaign creation page
 */

(function($) {
    'use strict';

    // State management
    const state = {
        selectedTemplate: 'default',
        recipientType: 'subscribers',
        csvEmails: [],
        specificEmails: [],
        couponProducts: [],
        scheduleType: 'now',
        includeCoupon: false,
        currentEditorContent: '',
        templateContents: typeof alertxCampaign !== 'undefined' && alertxCampaign.templateContents ? alertxCampaign.templateContents : {}
    };

    // Initialize on document ready
    $(document).ready(function() {
        // Initialize template selector first (before other inits)
        initTemplateSelector();

        // After a short delay, initialize other components and set initial state
        setTimeout(function() {
            initTemplatePreview();
            initRecipientTabs();
            initCSVImport();
            initSpecificEmails();
            initCouponToggle();
            initScheduling();
            initFormActions();

            // Store initial content from default template
            const defaultContent = state.templateContents['default'];
            if (defaultContent && !state.currentEditorContent) {
                state.currentEditorContent = defaultContent;
            }

            // Editing an existing campaign: load and prefill its data
            initEditMode();
        }, 500);
    });

    /**
     * Edit mode - when opened as new-campaign&campaign=ID, fetch the
     * campaign and prefill the form.
     */
    function initEditMode() {
        const campaignId = parseInt($('input[name="campaign_id"]').val(), 10) || 0;

        if (!campaignId) {
            return;
        }

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'alertx_get_campaign',
                nonce: alertxCampaign.nonce,
                campaign_id: campaignId
            },
            success: function(response) {
                if (response.success) {
                    fillCampaignForm(response.data.campaign);
                } else {
                    showErrorMessage(response.data.message || 'Unable to load campaign.');
                }
            },
            error: function() {
                showErrorMessage('Unable to load campaign.');
            }
        });
    }

    /**
     * Prefill the form with an existing campaign's data
     */
    function fillCampaignForm(campaign) {
        if (!campaign) {
            return;
        }

        // Title & subject
        $('#campaign-title').val(campaign.camp_title || '');
        $('#campaign-subject').val(campaign.camp_subject || '');

        // Recipient type
        const recipientType = campaign.recipient_type || 'subscribers';
        state.recipientType = recipientType;
        $('input[name="recipient_type"][value="' + recipientType + '"]').prop('checked', true).trigger('change');

        // CSV emails
        try {
            state.csvEmails = JSON.parse(campaign.recipient_csv_data || '[]') || [];
        } catch (e) {
            state.csvEmails = [];
        }
        if (!Array.isArray(state.csvEmails)) {
            state.csvEmails = [];
        }
        if (state.csvEmails.length) {
            $('#csv-count').text(' - ' + state.csvEmails.length + ' valid emails imported');
            $('#csv-import-status').show();
        }

        // Specific emails
        try {
            state.specificEmails = JSON.parse(campaign.recipient_emails || '[]') || [];
        } catch (e) {
            state.specificEmails = [];
        }
        if (!Array.isArray(state.specificEmails)) {
            state.specificEmails = [];
        }
        renderEmailList();

        // Coupon
        if (campaign.discount_code) {
            $('#include-coupon').prop('checked', true);
            state.includeCoupon = true;
            $('#coupon-fields').show();

            $('#coupon-code').val(campaign.discount_code);
            if (campaign.discount_expiry) {
                $('#coupon-expiry').val(campaign.discount_expiry);
            }

            // Load the coupon's restricted products into the preview state
            validateCouponCode(campaign.discount_code);
        }

        // Schedule
        if (campaign.camp_status === 'scheduled' && campaign.scheduled_date) {
            const dt = String(campaign.scheduled_date).replace(' ', 'T').slice(0, 16);
            $('#scheduled-date').val(dt);
            state.scheduleType = 'later';
            $('input[name="schedule_type"][value="later"]').prop('checked', true);
            $('#schedule-fields').show();
        }

        // Email template selection (keep the saved message, don't overwrite)
        const templateId = campaign.email_template_id || 'default';
        if (state.templateContents[templateId]) {
            state.selectedTemplate = templateId;
            $('.template-card').removeClass('active');
            $('.template-card[data-template="' + templateId + '"]').addClass('active');
            $('.template-card[data-template="' + templateId + '"] input[type="radio"]').prop('checked', true);
        }

        // Saved message content - set after template selection so it wins
        if (campaign.camp_message) {
            setEditorContent(campaign.camp_message);
        }
    }

    /**
     * Set the editor content and baseline (avoids the
     * "unsaved changes" template-switch prompt after loading).
     */
    function setEditorContent(content, attempt) {
        attempt = attempt || 0;
        const editor = (typeof tinyMCE !== 'undefined') ? tinyMCE.get('campaign-message') : null;

        if (editor) {
            editor.setContent(content);
            editor.save();
            state.currentEditorContent = editor.getContent() || content;
            $('#campaign-message').val(state.currentEditorContent);
        } else if (attempt < 20) {
            // TinyMCE not ready yet - keep the textarea in sync and retry
            $('#campaign-message').val(content);
            setTimeout(function() {
                setEditorContent(content, attempt + 1);
            }, 250);
        } else {
            $('#campaign-message').val(content);
            state.currentEditorContent = content;
        }
    }

    /**
     * Template selector
     */
    function initTemplateSelector() {
        // Store template contents from localized data
        if (typeof alertxCampaign !== 'undefined' && alertxCampaign.templateContents) {
            state.templateContents = alertxCampaign.templateContents;
        } else {
            console.error('✗ Template contents NOT available!');
        }

        // Check if TinyMCE editor already exists or wait for it to initialize
        if (typeof tinyMCE !== 'undefined' && tinyMCE.get('campaign-message')) {
            // Editor already exists, initialize immediately
            setTimeout(function() {
                state.currentEditorContent = tinyMCE.get('campaign-message').getContent();
            }, 500);
        } else if (typeof tinyMCE !== 'undefined') {
            // Wait for TinyMCE to initialize
            tinyMCE.on('addEditor', function(e) {
                if (e.editor.id === 'campaign-message') {
                    // Store initial content after editor is ready
                    setTimeout(function() {
                        state.currentEditorContent = tinyMCE.get('campaign-message').getContent();
                    }, 1000);
                }
            });
        }

        $('.template-card').on('click', function(e) {
            // Don't trigger if preview button was clicked
            if ($(e.target).closest('.template-preview-btn').length > 0) {
                return;
            }

            const newTemplate = $(this).data('template');
            const previousTemplate = state.selectedTemplate;

            // If template hasn't changed, do nothing
            if (newTemplate === previousTemplate) {
                return;
            }

            // Check if editor has been modified
            const currentContent = getCurrentEditorContent();
            const hasChanges = currentContent !== state.currentEditorContent &&
                              currentContent !== (state.templateContents[previousTemplate] || '');

            if (hasChanges) {
                // Show confirmation dialog
                if (!confirm('You have unsaved changes in the email message. Switching templates will replace your current content. Do you want to continue?')) {
                    // Revert the template selection
                    $('.template-card').removeClass('active');
                    $('.template-card[data-template="' + previousTemplate + '"]').addClass('active');
                    $('.template-card[data-template="' + previousTemplate + '"] input[type="radio"]').prop('checked', true);
                    return false;
                }
            }

            // Update template
            $('.template-card').removeClass('active');
            $(this).addClass('active');
            $(this).find('input[type="radio"]').prop('checked', true);
            state.selectedTemplate = newTemplate;

            // Update editor content with new template
            updateEditorContent(newTemplate);
        });
    }

    /**
     * Get current editor content
     */
    function getCurrentEditorContent() {
        if (typeof tinyMCE !== 'undefined' && tinyMCE.get('campaign-message')) {
            return tinyMCE.get('campaign-message').getContent();
        } else {
            return $('#campaign-message').val();
        }
    }

    /**
     * Update editor content with template
     */
    function updateEditorContent(template) {
        const newContent = state.templateContents[template];

        if (!newContent) {
            console.error('✗ Template content not found for:', template);
            console.error('Available templates:', Object.keys(state.templateContents));
            return;
        }

        // Update TinyMCE if available
        if (typeof tinyMCE !== 'undefined') {
            const editor = tinyMCE.get('campaign-message');

            if (editor) {

                try {
                    // Set content in TinyMCE
                    editor.setContent(newContent);
                    editor.save(); // Sync with textarea

                    // Force trigger change event
                    editor.fire('change');
                } catch (error) {
                    console.error('✗ TinyMCE update error:', error);
                }
            }
        }

        // Always update textarea directly (backup/fallback)
        try {
            const textarea = $('#campaign-message');
            textarea.val(newContent);
        } catch (error) {
            console.error('✗ Textarea update error:', error);
        }

        // TinyMCE normalizes HTML during setContent()/getContent(), so its
        // output never matches the raw template string byte-for-byte. Reading
        // the content back from the editor gives us the real baseline and
        // prevents false "Content verification failed!" errors and false
        // unsaved-changes prompts on subsequent template switches.
        let storedContent = newContent;

        if (typeof tinyMCE !== 'undefined' && tinyMCE.get('campaign-message')) {
            const normalized = tinyMCE.get('campaign-message').getContent();

            if (normalized && normalized.trim()) {
                storedContent = normalized;
            }
        }

        state.currentEditorContent = storedContent;
    }

    /**
     * Template preview modal
     */
    function initTemplatePreview() {
        // Preview button click
        $('.template-preview-btn').on('click', function(e) {
            e.stopPropagation();
            const template = $(this).data('template');
            showTemplatePreview(template);
        });

        // Close modal
        $('.modal-close, .modal-overlay').on('click', function() {
            closeTemplatePreview();
        });

        // Close on escape key
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape' && $('#template-preview-modal').is(':visible')) {
                closeTemplatePreview();
            }
        });
    }

    /**
     * Show template preview
     */
    function showTemplatePreview(template) {
        // Build preview content
        const content = buildPreviewContent(template);

        // Update subject line
        const subject = $('#campaign-subject').val().trim() || 'Your email subject will appear here';
        $('#preview-subject-text').text(subject);

        // Update body content
        $('#preview-body-content').html(content);

        // Update template-specific class
        $('#template-preview-content').removeClass('template-default template-modern template-minimal template-product');
        $('#template-preview-content').addClass('template-' + template);

        // Show modal
        $('#template-preview-modal').fadeIn(200);
    }

    /**
     * Build preview content based on template
     */
    function buildPreviewContent(template) {
        const campaignTitle = $('#campaign-title').val().trim() || 'Campaign Title';
        const subject = $('#campaign-subject').val().trim() || 'Email Subject';
        const couponCode = $('#coupon-code').val().trim();
        const couponExpiry = $('#coupon-expiry').val().trim();
        const includeCoupon = $('#include-coupon').is(':checked');
        const couponProducts = state.couponProducts || [];
        const scheduleType = $('input[name="schedule_type"]:checked').val();
        const scheduledDate = $('#scheduled-date').val().trim();
        const shopUrl = (typeof alertxCampaign !== 'undefined' && alertxCampaign.shopUrl) ? alertxCampaign.shopUrl : '#';

        // Get custom message from wp_editor
        let customMessage = '';
        if (tinyMCE.get('campaign-message')) {
            customMessage = tinyMCE.get('campaign-message').getContent();
        } else {
            customMessage = $('#campaign-message').val();
        }
        customMessage = customMessage.trim() || '';

        let html = '<div class="preview-content">';

        // Greeting
        html += '<p class="greeting">Hi {first_name},</p>';

        // Custom message if available
        if (customMessage) {
            html += '<div class="message-body">' + customMessage + '</div>';
        }

        // Template-specific content
        switch(template) {
            case 'default':
                if (!customMessage) {
                    html += '<h2>' + escHtml(subject) + '</h2>';
                    html += '<div class="message-body">';
                    html += '<p>Thank you for being part of our community! We have some exciting news to share with you.</p>';
                    html += '<p>Check out the details below and take advantage of this special offer.</p>';
                    html += '</div>';
                }

                // Coupon product cards or shop page CTA
                if (couponProducts.length > 0) {
                    html += '<h3 style="margin-top: 25px;">Featured Products:</h3>';
                    html += buildProductCardsHtml(couponProducts);
                }

                // Coupon section
                if (includeCoupon && couponCode) {
                    html += '<div class="coupon-box">';
                    html += '<div class="label">Your Discount Code</div>';
                    html += '<div class="code">' + escHtml(couponCode) + '</div>';
                    if (couponExpiry) {
                        html += '<p style="margin: 10px 0 0; font-size: 12px; color: #666;">Expires on: ' + formatDate(couponExpiry) + '</p>';
                    }
                    html += '</div>';
                }

                // Shop CTA only when the coupon has no restricted products
                if (couponProducts.length === 0) {
                    html += '<a href="' + escHtml(shopUrl) + '" class="cta-button">All Products Discount</a>';
                }
                break;

            case 'modern':
                html += '<div class="preview-banner">';
                html += '<h1>' + (escHtml(campaignTitle).toUpperCase() || 'SPECIAL OFFER') + '</h1>';
                html += '<p>Exclusive deal just for you!</p>';
                html += '</div>';

                if (!customMessage) {
                    html += '<div class="message-body">';
                    html += '<p>You have been selected for this exclusive promotion. Don\'t miss out on these amazing savings!</p>';
                    html += '</div>';
                }

                // Coupon product cards
                if (couponProducts.length > 0) {
                    html += '<div style="margin: 25px 0;">';
                    html += '<h3 style="text-align: center; margin-bottom: 15px;">Featured Products</h3>';
                    html += buildProductCardsHtml(couponProducts);
                    html += '</div>';
                }

                if (includeCoupon && couponCode) {
                    html += '<div class="coupon-box">';
                    html += '<div class="label">Use this code at checkout</div>';
                    html += '<div class="code">' + escHtml(couponCode) + '</div>';
                    if (couponExpiry) {
                        html += '<p style="margin: 10px 0 0; font-size: 12px; color: #666;">Valid until: ' + formatDate(couponExpiry) + '</p>';
                    }
                    html += '</div>';
                }

                // Shop CTA only when the coupon has no restricted products
                if (couponProducts.length === 0) {
                    html += '<a href="' + escHtml(shopUrl) + '" class="cta-button">All Products Discount</a>';
                }
                break;

            case 'minimal':
                if (!customMessage) {
                    html += '<p>Thank you for your continued support. We wanted to share a quick update with you.</p>';
                }

                html += '<hr class="divider">';

                // Coupon product cards
                if (couponProducts.length > 0) {
                    html += '<p><strong>Featured Products:</strong></p>';
                    html += buildProductCardsHtml(couponProducts);
                    html += '<hr class="divider">';
                }

                if (includeCoupon && couponCode) {
                    html += '<p><strong>Your discount code:</strong> <span style="font-size: 18px; color: #3c06c5; letter-spacing: 1px;">' + escHtml(couponCode) + '</span></p>';
                    if (couponExpiry) {
                        html += '<p style="font-size: 13px; color: #666;">Valid until: ' + formatDate(couponExpiry) + '</p>';
                    }
                }

                // Shop CTA only when the coupon has no restricted products
                if (couponProducts.length === 0) {
                    html += '<p style="margin: 20px 0; text-align: center;"><a href="' + escHtml(shopUrl) + '" class="cta-button">All Products Discount</a></p>';
                }

                html += '<hr class="divider">';
                html += '<p style="font-size: 13px;">Visit our store to learn more.</p>';
                break;

            case 'product':
                html += '<h2>' + escHtml(subject) + '</h2>';

                if (!customMessage) {
                    html += '<p>We\'ve selected some amazing products just for you:</p>';
                }

                // Coupon product cards
                if (couponProducts.length > 0) {
                    html += buildProductCardsHtml(couponProducts);
                }

                if (includeCoupon && couponCode) {
                    html += '<div class="coupon-box">';
                    html += '<div class="label">Discount Code</div>';
                    html += '<div class="code">' + escHtml(couponCode) + '</div>';
                    if (couponExpiry) {
                        html += '<p style="margin: 10px 0 0; font-size: 12px; color: #666;">Expires: ' + formatDate(couponExpiry) + '</p>';
                    }
                    html += '</div>';
                }

                // Shop CTA only when the coupon has no restricted products
                if (couponProducts.length === 0) {
                    html += '<a href="' + escHtml(shopUrl) + '" class="cta-button">All Products Discount</a>';
                }
                break;
        }

        // Schedule information
        if (scheduleType === 'later' && scheduledDate) {
            html += '<div style="margin-top: 30px; padding: 15px; background: #f9f9f9; border-left: 3px solid #3c06c5; font-size: 12px; color: #666;">';
            html += '<strong>Scheduled to send:</strong> ' + formatDateTime(scheduledDate);
            html += '</div>';
        }

        html += '<div class="footer-note">';
        html += '<p>© ' + new Date().getFullYear() + ' ' + (window.siteName || 'Your Store') + '. All rights reserved.</p>';
        html += '</div>';

        html += '</div>';

        return html;
    }

    /**
     * Build product cards HTML (matches the email {product_list} output).
     * Cards include image, name, price and Buy Now - driven by the coupon's
     * restricted products.
     */
    function buildProductCardsHtml(products) {
        let html = '';
        products.forEach(function(product) {
            html += '<div style="display: inline-block; width: 46%; vertical-align: top; margin: 0 4% 16px 0; border: 1px solid #e8e8e8; border-radius: 8px; padding: 14px; text-align: center; background-color: #ffffff;">';

            if (product.image) {
                html += '<img src="' + escHtml(product.image) + '" alt="' + escHtml(product.name) + '" width="140" style="max-width: 100%; height: auto; margin: 0 auto 10px; display: block;" />';
            }

            html += '<div style="font-size: 14px; font-weight: 600; color: #333333; line-height: 1.4; margin-bottom: 6px;">' + escHtml(product.name) + '</div>';
            html += '<div style="font-size: 16px; font-weight: 700; color: #3c06c5; margin-bottom: 12px;">' + escHtml(product.price || '') + '</div>';

            if (product.url) {
                html += '<a href="' + escHtml(product.url) + '" style="display: inline-block; padding: 9px 24px; background: #3c06c5; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 13px;">Buy Now</a>';
            }

            html += '</div>';
        });
        return html;
    }

    /**
     * Format date for display
     */
    function formatDate(dateString) {
        if (!dateString) return '';
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            month: 'long',
            day: 'numeric',
            year: 'numeric'
        });
    }

    /**
     * Format date time for display
     */
    function formatDateTime(dateString) {
        if (!dateString) return '';
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    /**
     * Close template preview
     */
    function closeTemplatePreview() {
        $('#template-preview-modal').fadeOut(200);
    }

    /**
     * Recipient type tabs
     */
    function initRecipientTabs() {
        $('input[name="recipient_type"]').on('change', function() {
            state.recipientType = $(this).val();

            // Hide all areas
            $('#csv-import-area, #specific-emails-area').hide();

            // Show selected area
            if (state.recipientType === 'csv') {
                $('#csv-import-area').show();
            } else if (state.recipientType === 'specific') {
                $('#specific-emails-area').show();
            }
        });
    }

    /**
     * CSV import functionality
     */
    function initCSVImport() {
        const dropZone = $('#csv-drop-zone');
        const fileInput = $('#csv-file-input');

        // The hidden file input lives inside the drop zone. Its click would
        // bubble back up to the drop zone handler below and recurse forever,
        // so the file browser never opens. Stop it here.
        fileInput.on('click', function(e) {
            e.stopPropagation();
        });

        // Click to upload
        dropZone.on('click', function() {
            fileInput.click();
        });

        // Drag and drop
        dropZone.on('dragover', function(e) {
            e.preventDefault();
            e.stopPropagation();
            dropZone.addClass('dragover');
        });

        dropZone.on('dragleave', function(e) {
            e.preventDefault();
            e.stopPropagation();
            dropZone.removeClass('dragover');
        });

        dropZone.on('drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
            dropZone.removeClass('dragover');

            const files = e.originalEvent.dataTransfer.files;
            if (files.length > 0) {
                handleCSVFile(files[0]);
            }
        });

        // File input change
        fileInput.on('change', function() {
            if (this.files.length > 0) {
                handleCSVFile(this.files[0]);
            }
        });
    }

    /**
     * Handle CSV file processing
     */
    function handleCSVFile(file) {
        if (file.type !== 'text/csv' && !file.name.endsWith('.csv')) {
            showErrorMessage('Please upload a CSV file');
            return;
        }

        const reader = new FileReader();
        reader.onload = function(e) {
            const text = e.target.result;
            processCSVData(text);
        };
        reader.readAsText(file);
    }

    /**
     * Process CSV data
     */
    function processCSVData(csvText) {
        const lines = csvText.split(/\r\n|\n/);
        const emails = [];
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        lines.forEach(line => {
            const parts = line.split(',');
            parts.forEach(part => {
                const email = part.trim();
                if (email && emailRegex.test(email)) {
                    if (!emails.includes(email.toLowerCase())) {
                        emails.push(email.toLowerCase());
                    }
                }
            });
        });

        if (emails.length === 0) {
            showErrorMessage('No valid email addresses found in CSV file');
            return;
        }

        state.csvEmails = emails;
        $('#csv-count').text(' - ' + emails.length + ' valid emails imported');
        $('#csv-import-status').show();
    }

    /**
     * Specific email addresses management
     */
    function initSpecificEmails() {
        const emailInput = $('#email-input');
        const addBtn = $('#btn-add-email');

        addBtn.on('click', function() {
            addEmail();
        });

        emailInput.on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                addEmail();
            }
        });
    }

    /**
     * Add email to list
     */
    function addEmail() {
        const emailInput = $('#email-input');
        const email = emailInput.val().trim().toLowerCase();

        if (!email) {
            showErrorMessage('Please enter an email address');
            return;
        }

        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            showErrorMessage('Please enter a valid email address');
            return;
        }

        if (state.specificEmails.includes(email)) {
            showErrorMessage('This email has already been added');
            return;
        }

        state.specificEmails.push(email);
        renderEmailList();
        emailInput.val('');
    }

    /**
     * Render email list
     */
    function renderEmailList() {
        const container = $('#email-list');

        if (state.specificEmails.length === 0) {
            container.html('<div class="description">No emails added yet</div>');
            return;
        }

        let html = '';
        state.specificEmails.forEach(function(email, index) {
            html += '<div class="email-chip">' +
                '<span>' + escHtml(email) + '</span>' +
                '<button type="button" data-index="' + index + '">×</button>' +
                '</div>';
        });

        container.html(html);

        // Remove button click handlers
        container.find('.email-chip button').on('click', function() {
            const index = $(this).data('index');
            state.specificEmails.splice(index, 1);
            renderEmailList();
        });
    }

    /**
     * Coupon toggle
     */
    function initCouponToggle() {
        $('#include-coupon').on('change', function() {
            state.includeCoupon = $(this).is(':checked');
            $('#coupon-fields').slideToggle();
        });

        // Coupon code validation
        let validateTimeout;
        $('#coupon-code').on('input', function() {
            clearTimeout(validateTimeout);
            const code = $(this).val().trim();

            if (code.length < 3) {
                state.couponProducts = [];
                renderCouponProducts(null);
                $('#coupon-validation').html('');
                return;
            }

            validateTimeout = setTimeout(function() {
                validateCouponCode(code);
            }, 500);
        });
    }

    /**
     * Validate coupon code via AJAX
     */
    function validateCouponCode(code) {
        $('#coupon-validation').html('<span style="color: #666;">Validating...</span>');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'alertx_validate_coupon',
                nonce: alertxCampaign.nonce,
                code: code
            },
            success: function(response) {
                if (response.success) {
                    $('#coupon-validation').html(
                        '<span style="color: #46b450;">✓ ' + response.data.message + '</span>'
                    );

                    // Auto-fill expiry date from the WooCommerce coupon
                    if (response.data.expiry_date) {
                        $('#coupon-expiry').val(response.data.expiry_date);
                    }

                    // Store coupon-restricted products for preview cards
                    state.couponProducts = Array.isArray(response.data.products) ? response.data.products : [];

                    // Show the products below the expiry field
                    renderCouponProducts(state.couponProducts);
                } else {
                    state.couponProducts = [];
                    renderCouponProducts(null);
                    $('#coupon-validation').html(
                        '<span style="color: #dc3232;">⚠ ' + response.data.message + '</span>'
                    );
                }
            },
            error: function(xhr, status, error) {
                console.error('Coupon validation error:', error);
                console.error('Response:', xhr.responseText);
                state.couponProducts = [];
                renderCouponProducts(null);
                $('#coupon-validation').html(
                    '<span style="color: #dc3232;">⚠ Unable to validate coupon. Please check if WooCommerce is active.</span>'
                );
            }
        });
    }

    /**
     * Render the coupon's restricted products below the expiry field
     */
    function renderCouponProducts(products) {
        const container = $('#coupon-products-list');

        if (!products || products.length === 0) {
            container.html(
                '<p class="description" style="margin: 0;">Coupon has no product restrictions - the email will include an "All Products Discount" button instead.</p>'
            );
            return;
        }

        let html = '<div style="font-size: 12px; font-weight: 600; color: #666; margin-bottom: 6px;">This coupon applies to ' +
            products.length + ' product' + (products.length > 1 ? 's' : '') + ':</div>';

        html += '<div style="display: flex; flex-direction: column; gap: 6px; max-width: 480px;">';

        products.forEach(function(product) {
            html += '<div style="display: flex; align-items: center; gap: 10px; padding: 7px 10px; background: #fff; border: 1px solid #e0e0e0; border-radius: 4px;">';

            if (product.image) {
                html += '<img src="' + escHtml(product.image) + '" alt="" style="width: 30px; height: 30px; object-fit: cover; border-radius: 3px;" />';
            } else {
                html += '<div style="width: 30px; height: 30px; background: #f0f0f0; border-radius: 3px;"></div>';
            }

            html += '<span style="flex: 1; font-size: 12px; color: #333;">' + escHtml(product.name) + '</span>';

            if (product.price) {
                html += '<span style="font-size: 12px; font-weight: 600; color: #3c06c5;">' + escHtml(product.price) + '</span>';
            }

            html += '</div>';
        });

        html += '</div>';
        container.html(html);
    }

    /**
     * Scheduling options
     */
    function initScheduling() {
        $('input[name="schedule_type"]').on('change', function() {
            state.scheduleType = $(this).val();

            if (state.scheduleType === 'later') {
                $('#schedule-fields').slideDown();
            } else {
                $('#schedule-fields').slideUp();
            }
        });
    }

    /**
     * Form actions
     */
    function initFormActions() {
        // Save draft button
        $('#btn-save-draft').on('click', function() {
            saveCampaign('draft');
        });

        // Send campaign button
        $('#btn-send-campaign').on('click', function() {
            if (state.scheduleType === 'now') {
                saveCampaign('send');
            } else {
                saveCampaign('schedule');
            }
        });
    }

    /**
     * Inline field validation
     */

    /**
     * Show a red error message below a field and mark it invalid
     */
    function showFieldError($field, message) {
        // Remove any existing error for this field first
        clearFieldError($field);

        $field.addClass('alertx-invalid');

        const $error = $('<p class="alertx-field-error"><span class="alertx-error-icon">!</span>' + escHtml(message) + '</p>');
        $field.after($error);

        // Clear the error as soon as the user fixes the field
        $field.on('input.alertxFieldError change.alertxFieldError', function() {
            clearFieldError($field);
        });
    }

    /**
     * Clear error state from a single field
     */
    function clearFieldError($field) {
        $field.removeClass('alertx-invalid');
        $field.off('input.alertxFieldError change.alertxFieldError');

        if ($field.next('.alertx-field-error').length) {
            $field.next('.alertx-field-error').remove();
        }
    }

    /**
     * Clear all field errors in the form
     */
    function clearAllFieldErrors() {
        $('#new-campaign-form .alertx-invalid').each(function() {
            clearFieldError($(this));
        });
        $('#new-campaign-form .alertx-field-error').remove();
    }

    /**
     * Validate all required fields.
     * Returns true when the form is valid, otherwise shows
     * inline red messages below each invalid field.
     */
    function validateCampaignForm() {
        clearAllFieldErrors();

        let isValid = true;
        let $firstInvalid = null;

        const mark = function($field, message) {
            showFieldError($field, message);
            isValid = false;
            if (!$firstInvalid) {
                $firstInvalid = $field;
            }
        };

        // Required: Campaign Title
        if (!$('#campaign-title').val().trim()) {
            mark($('#campaign-title'), 'Campaign title is required.');
        }

        // Required: Email Subject
        if (!$('#campaign-subject').val().trim()) {
            mark($('#campaign-subject'), 'Email subject line is required.');
        }

        // Recipient specific validation
        if (state.recipientType === 'csv' && state.csvEmails.length === 0) {
            mark($('#csv-drop-zone'), 'Please upload a CSV file containing email addresses.');
        }

        if (state.recipientType === 'specific' && state.specificEmails.length === 0) {
            mark($('#email-list'), 'Please add at least one email address.');
        }

        // Coupon code required when coupon is included
        if ($('#include-coupon').is(':checked') && !$('#coupon-code').val().trim()) {
            mark($('#coupon-code'), 'Coupon code is required when including a discount.');
        }

        // Schedule date required when scheduling for later
        if (state.scheduleType === 'later' && !$('#scheduled-date').val()) {
            mark($('#scheduled-date'), 'Please select a schedule date and time.');
        }

        // Focus + scroll to the first invalid field
        if ($firstInvalid) {
            $firstInvalid.trigger('focus');
            if ($firstInvalid[0].scrollIntoView) {
                $firstInvalid[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }

        return isValid;
    }

    /**
     * Save campaign
     */
    function saveCampaign(action) {
        // Validate required fields (shows inline red messages below fields)
        if (!validateCampaignForm()) {
            return;
        }

        const title = $('#campaign-title').val().trim();
        const subject = $('#campaign-subject').val().trim();

        // Prepare campaign data
        const campaignData = {
            nonce: $('#campaign_nonce').val(),
            action: 'alertx_save_campaign',
            campaign_id: $('input[name="campaign_id"]').val() || 0,
            campaign_action: action,
            campaign_title: title,
            campaign_subject: subject,
            email_template: state.selectedTemplate,
            recipient_type: state.recipientType,
            recipient_csv_data: JSON.stringify(state.csvEmails),
            recipient_emails: JSON.stringify(state.specificEmails),
            include_coupon: state.includeCoupon ? '1' : '0',
            coupon_code: $('#coupon-code').val().trim(),
            coupon_expiry: $('#coupon-expiry').val().trim(),
            campaign_message: tinyMCE.get('campaign-message') ? tinyMCE.get('campaign-message').getContent() : $('#campaign-message').val(),
            schedule_type: state.scheduleType,
            scheduled_date: $('#scheduled-date').val()
        };

        // Show loading (remember original labels so they can be restored)
        const submitButtons = $('#btn-save-draft, #btn-send-campaign');
        submitButtons.each(function() {
            $(this).data('original-text', $(this).text());
        });
        submitButtons.prop('disabled', true).text('Processing...');

        // Send AJAX request
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: campaignData,
            timeout: 90000,
            success: function(response) {
                submitButtons.prop('disabled', false).each(function() {
                    $(this).text($(this).data('original-text'));
                });

                if (response.success) {
                    // Show success toast, then redirect to campaigns list
                    showToast(response.data.message || 'Campaign saved successfully!', 'success');

                    setTimeout(function() {
                        window.location.href = alertxCampaign.campaignsUrl;
                    }, 2000);
                } else {
                    showToast(response.data.message || 'An error occurred', 'error');
                }
            },
            error: function(xhr, status) {
                submitButtons.prop('disabled', false).each(function() {
                    $(this).text($(this).data('original-text'));
                });

                var errorMessage = 'Unable to save campaign. Please try again.';

                if (status === 'timeout') {
                    errorMessage = 'The server took too long to respond. This usually means outgoing mail (wp_mail) is hanging - please configure an SMTP plugin such as WP Mail SMTP.';
                } else if (xhr && xhr.responseText) {
                    console.error('Save campaign response:', xhr.responseText);
                }

                showToast(errorMessage, 'error');
            }
        });
    }

    /**
     * Helper functions
     */
    function escHtml(text) {
        // Convert to string if not already
        if (typeof text !== 'string') {
            text = String(text);
        }

        // Handle null, undefined, empty values
        if (!text) {
            return '';
        }

        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }

    /**
     * Toast notification
     *
     * Slides in from the top-right of the screen and auto-dismisses.
     *
     * @param {string} message Message text to display
     * @param {string} type    'success' or 'error'
     */
    function showToast(message, type) {
        type = type || 'success';

        const icons = {
            success: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>',
            error: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><line x1="12" y1="8" x2="12" y2="13"></line><line x1="12" y1="16.5" x2="12" y2="16.5"></line><circle cx="12" cy="12" r="10"></circle></svg>'
        };

        const titles = {
            success: 'Success',
            error: 'Error'
        };

        const $toast = $(
            '<div class="alertx-toast alertx-toast-' + escHtml(type) + '">' +
                '<span class="alertx-toast-icon">' + icons[type] + '</span>' +
                '<div class="alertx-toast-content">' +
                    '<div class="alertx-toast-title">' + titles[type] + '</div>' +
                    '<div class="alertx-toast-message">' + escHtml(message) + '</div>' +
                '</div>' +
                '<button type="button" class="alertx-toast-close" aria-label="Dismiss">&times;</button>' +
                '<span class="alertx-toast-progress"></span>' +
            '</div>'
        );

        // Container holds stacked toasts
        if ($('#alertx-toast-container').length === 0) {
            $('<div id="alertx-toast-container"></div>').appendTo('body');
        }

        $('#alertx-toast-container').append($toast);

        // Trigger slide-in animation
        setTimeout(function() {
            $toast.addClass('alertx-toast-visible');
        }, 10);

        const dismissToast = function() {
            $toast.removeClass('alertx-toast-visible');
            setTimeout(function() {
                $toast.remove();
            }, 350);
        };

        // Auto dismiss (matches CSS progress bar duration)
        const autoTimer = setTimeout(dismissToast, 4000);

        // Manual dismiss
        $toast.find('.alertx-toast-close').on('click', function() {
            clearTimeout(autoTimer);
            dismissToast();
        });
    }

    function showErrorMessage(message) {
        const statusEl = $('#status-message');
        statusEl.html('<div class="notice notice-error"><p>' + message + '</p></div>');
        setTimeout(function() {
            statusEl.find('.notice').fadeOut();
        }, 5000);
    }

})(jQuery);
