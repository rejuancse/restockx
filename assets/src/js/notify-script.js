jQuery(document).ready(function($) {
    // Handle variation selection visibility for notify UI
    var $variationForm = $('form.variations_form');
    var $notifyWrap = $('.notify-me-button-wrap');
    var $notifyForm = $('#notify-me-form');
    var $notifyProductId = $('#notify-product-id');

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
            var anyOutOfStock = Array.isArray(variationsData) && variationsData.some(function(v){
                return v && v.is_in_stock === false;
            });
            if (anyOutOfStock) {
                $notifyWrap.show();
                moveNotifyAfterStock($variationForm);
            }
        } catch (e) {
            // Fallback: if a generic out-of-stock message is present, show the Notify UI
            if ($('.stock.out-of-stock').length) {
                $notifyWrap.show();
                moveNotifyAfterStock($(document));
            }
        }

        // When a valid variation is found
        $variationForm.on('found_variation', function(event, variation) {
            if (variation && variation.is_in_stock === false) {
                // Show notify UI and set product_id to variation_id
                $notifyWrap.show();
                if ($notifyProductId.length) {
                    $notifyProductId.val(variation.variation_id);
                }
                // Place under the variation's out-of-stock message if present
                moveNotifyAfterStock($variationForm);
            } else {
                // Hide notify UI and clear product_id
                $notifyWrap.hide();
                if ($notifyProductId.length) {
                    $notifyProductId.val('');
                }
            }
        });

        // When attributes reset or no variation
        $variationForm.on('reset_data hide_variation', function() {
            $notifyWrap.hide();
            if ($notifyProductId.length) {
                $notifyProductId.val('');
            }
        });
    }
    $('#notify-me-button').click(function() {
        $('#notify-me-form').toggle();
    });

    $('#submit-notify').click(function() {
        var email = $('#notify-email').val().trim();
        var product_id = $('#notify-product-id').val();

        // Enhanced email validation
        var emailRegex = /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/;
        if (!email || !emailRegex.test(email)) {
            showError('Please enter a valid email address.');
            return;
        }

        if (!product_id) {
            showError('Error: Product/Variation ID not found. Please select a variation (if applicable) or refresh the page and try again.');
            return;
        }

        // Check if notify_ajax object is available
        if (typeof notify_ajax === 'undefined' || !notify_ajax.ajax_url || !notify_ajax.nonce) {
            showError('Configuration error. Please refresh the page and try again.');
            return;
        }

        $('#submit-notify').prop('disabled', true).text('Submitting...');
        $('.error-message, .success-message').remove();

        $.ajax({
            url: notify_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'stock_notification',
                email: email,
                product_id: product_id,
                nonce: notify_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('#notify-me-form').html('<p class="success-message">' + response.data.message + '</p>');

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
                        $('#notify-me-form').after(alternativesHtml);
                    }
                } else {
                    showError(response.data || 'Unknown error occurred. Please try again.');
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                showError('An error occurred. Please try again later. Error: ' + textStatus);
            },
            complete: function() {
                $('#submit-notify').prop('disabled', false).text('Submit');
            }
        });
    });

    function showError(message) {
        $('.error-message').remove();
        $('#notify-me-form').prepend('<p class="error-message">' + escapeHtml(message) + '</p>');
    }

    function escapeHtml(unsafe) {
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
});
