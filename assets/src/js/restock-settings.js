/**
 * RestockX - Admin settings page scripts.
 *
 * The "Notify Me" tab is fully free. The "Channels" and "Newsletter" tabs
 * render the premium lock screens; the save bar is hidden while one of
 * those tabs is active.
 *
 * Config (restockxSettings) is localized from includes/Assets.php and contains:
 *  - ajaxUrl  {string} admin-ajax.php URL
 *  - nonce    {string} restockx_settings_nonce
 *  - iconMap  {Object} icon key -> inline SVG
 *  - settings {Object} saved "Notify Me" button settings
 */
document.addEventListener('DOMContentLoaded', function () {

    /* ---------- Settings tabs ---------- */

    const tabItems = document.querySelectorAll('.settings-nav-item');
    const panels = document.querySelectorAll('.settings-panel');
    const saveBar = document.querySelector('#restockx .save-bar');

    /**
     * Shows or hides the save bar. Locked tabs (Channels, Newsletter) have
     * nothing to save, so the bar is hidden while they are active.
     */
    const toggleSaveBar = function (target) {
        if (!saveBar) {
            return;
        }

        const locked = target && target.classList.contains('settings-panel-locked');
        saveBar.style.display = locked ? 'none' : 'flex';
    };

    tabItems.forEach(function (tab) {
        tab.addEventListener('click', function (e) {
            e.preventDefault();

            const target = this.getAttribute('data-settings');

            // Remove active class from all tabs.
            tabItems.forEach(function (item) {
                item.classList.remove('active');
            });

            // Hide all panels.
            panels.forEach(function (panel) {
                panel.style.display = 'none';
            });

            // Activate clicked tab and show matching panel.
            this.classList.add('active');

            const targetPanel = document.getElementById('settings-' + target);
            if (targetPanel) {
                targetPanel.style.display = 'block';
                toggleSaveBar(targetPanel);
            }
        });
    });

    /* ---------- Notify Me button settings ---------- */
    const notifyConfig = typeof window.restockxSettings !== 'undefined' ? window.restockxSettings : null;
    const notifyPanel    = document.getElementById('settings-notify-me');
    const previewButton  = document.getElementById('restockx-preview-button');
    const previewTooltip = document.getElementById('restockx-preview-tooltip-text');
    const saveStatus     = document.getElementById('restockx-save-status');
    const saveButton     = document.getElementById('restockx-settings-save');
    const discardButton  = document.getElementById('restockx-settings-discard');

    if (!notifyConfig || !notifyPanel || !previewButton) {
        return;
    }

    let settingsSnapshot = Object.assign({}, notifyConfig.settings);

    const isHex = function (value) {
        return /^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/.test(value);
    };

    /**
     * Finds the color picker + hex input pair for a given setting key.
     */
    const findColorPair = function (key) {
        return {
            picker:   notifyPanel.querySelector('input.restockx-color-picker[data-setting="' + key + '"]'),
            hexInput: notifyPanel.querySelector('input.restockx-color-hex[data-setting="' + key + '"]')
        };
    };

    const collectSettings = function () {
        const settings = {};
        const seen = {};

        notifyPanel.querySelectorAll('[data-setting]').forEach(function (field) {
            const key = field.getAttribute('data-setting');

            // Color pickers and hex inputs share the same data-setting.
            // Only collect once, preferring a valid value from the pair.
            if (seen[key]) {
                return;
            }

            if (field.classList.contains('restockx-color-picker') || field.classList.contains('restockx-color-hex')) {
                seen[key] = true;
                const pair = findColorPair(key);

                if (pair.picker && pair.hexInput) {
                    // fall back to the (always valid) color picker value.
                    settings[key] = isHex(pair.hexInput.value) ? pair.hexInput.value : pair.picker.value;
                } else {
                    settings[key] = field.value;
                }
                return;
            }

            settings[key] = field.value;
        });

        return settings;
    };

    const applyPreview = function (settings) {
        const int = function (value, fallback) {
            const parsed = parseInt(value, 10);
            return isNaN(parsed) || parsed < 0 ? fallback : parsed;
        };
        const color = function (value, fallback) {
            return isHex(value) ? value : fallback;
        };

        previewButton.style.color = color(settings.text_color, '#ffffff');
        previewButton.style.backgroundColor = color(settings.bg_color, '#3c06c5');
        previewButton.style.fontFamily = settings.font_family || 'inherit';
        previewButton.style.fontSize = int(settings.font_size, 16) + 'px';
        previewButton.style.padding = int(settings.padding_top, 10) + 'px ' + int(settings.padding_right, 20) + 'px ' + int(settings.padding_bottom, 10) + 'px ' + int(settings.padding_left, 20) + 'px';
        previewButton.style.margin = int(settings.margin_top, 10) + 'px ' + int(settings.margin_right, 0) + 'px ' + int(settings.margin_bottom, 20) + 'px ' + int(settings.margin_left, 0) + 'px';
        previewButton.style.borderWidth = int(settings.border_width, 0) + 'px';
        previewButton.style.borderStyle = 'solid';
        previewButton.style.borderColor = color(settings.border_color, '#3c06c5');
        previewButton.style.borderRadius = int(settings.border_radius, 4) + 'px';

        previewTooltip.textContent = settings.tooltip_text || '';

        // Icon: rebuild button content (icon before/after + text span).
        const iconMap = notifyConfig.iconMap || {};
        const iconHtml = iconMap[settings.icon] ? iconMap[settings.icon] : '';
        previewButton.innerHTML = '';

        if (iconHtml && settings.icon_position === 'before') {
            previewButton.insertAdjacentHTML('beforeend', iconHtml);
        }

        const textSpan = document.createElement('span');
        textSpan.className = 'restockx-notify-button-text';
        textSpan.id = 'restockx-preview-button-text';
        textSpan.textContent = settings.button_text || '';
        previewButton.appendChild(textSpan);

        if (iconHtml && settings.icon_position === 'after') {
            previewButton.insertAdjacentHTML('beforeend', iconHtml);
        }
    };

    /**
     * Toast notification (same design as the campaign screen toast).
     *
     * Slides in from the top-right of the screen and auto-dismisses.
     *
     * @param {string} message Message text to display
     * @param {string} type    'success' or 'error'
     */
    const showToast = function (message, type) {
        type = type === 'error' ? 'error' : 'success';

        const icons = {
            success: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>',
            error: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><line x1="12" y1="8" x2="12" y2="13"></line><line x1="12" y1="16.5" x2="12" y2="16.5"></line><circle cx="12" cy="12" r="10"></circle></svg>'
        };

        const titles = {
            success: 'Success',
            error: 'Error'
        };

        let container = document.getElementById('restockx-toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'restockx-toast-container';
            document.body.appendChild(container);
        }

        const toast = document.createElement('div');
        toast.className = 'restockx-toast restockx-toast-' + type;
        toast.innerHTML =
            '<span class="restockx-toast-icon">' + icons[type] + '</span>' +
            '<div class="restockx-toast-content">' +
                '<div class="restockx-toast-title">' + titles[type] + '</div>' +
                '<div class="restockx-toast-message"></div>' +
            '</div>' +
            '<button type="button" class="restockx-toast-close" aria-label="Dismiss">&times;</button>' +
            '<span class="restockx-toast-progress"></span>';

        // Message goes in via textContent so it can never inject HTML.
        toast.querySelector('.restockx-toast-message').textContent = message;

        // Keep the aria-live status region in sync for screen readers.
        if (saveStatus) {
            saveStatus.textContent = message;
        }

        container.appendChild(toast);

        // Trigger slide-in animation.
        setTimeout(function () {
            toast.classList.add('restockx-toast-visible');
        }, 10);

        const dismissToast = function () {
            toast.classList.remove('restockx-toast-visible');
            setTimeout(function () {
                toast.remove();
            }, 350);
        };

        // Auto dismiss (matches CSS progress bar duration).
        const autoTimer = setTimeout(dismissToast, 4000);

        // Manual dismiss.
        toast.querySelector('.restockx-toast-close').addEventListener('click', function () {
            clearTimeout(autoTimer);
            dismissToast();
        });
    };

    /**
     * Pushes settings values back into the form fields, syncing color pairs.
     */
    const fillForm = function (settings) {
        Object.keys(settings).forEach(function (key) {
            notifyPanel.querySelectorAll('[data-setting="' + key + '"]').forEach(function (field) {
                field.value = settings[key];
            });
        });
    };

    // Live preview on any control change.
    notifyPanel.addEventListener('input', function (e) {
        const field = e.target;

        // Keep color picker + hex input in sync.
        if (field.classList.contains('restockx-color-picker') || field.classList.contains('restockx-color-hex')) {
            const pair = findColorPair(field.getAttribute('data-setting'));

            if (pair.picker && pair.hexInput) {
                if (field === pair.picker) {
                    pair.hexInput.value = pair.picker.value;
                } else if (isHex(field.value)) {
                    pair.picker.value = field.value;
                }
            }
        }

        applyPreview(collectSettings());
    });

    notifyPanel.addEventListener('change', function (e) {
        const field = e.target;

        // Fix: on blur, revert an invalid hex value back to the picker color
        // so an invalid value never stays in the field.
        if (field.classList && field.classList.contains('restockx-color-hex') && !isHex(field.value)) {
            const pair = findColorPair(field.getAttribute('data-setting'));
            if (pair.picker) {
                field.value = pair.picker.value;
            }
        }

        applyPreview(collectSettings());
    });

    // Save via AJAX.
    if (saveButton) {
        saveButton.addEventListener('click', function () {
            saveButton.disabled = true;

            const formData = new FormData();
            formData.append('action', 'restockx_save_settings');
            formData.append('nonce', notifyConfig.nonce);
            formData.append('settings', JSON.stringify(collectSettings()));

            fetch(notifyConfig.ajaxUrl, {
                method: 'POST',
                credentials: 'same-origin',
                body: formData
            })
                .then(function (response) { return response.json(); })
                .then(function (json) {
                    saveButton.disabled = false;

                    if (json && json.success) {
                        // Sync the form with the sanitized settings the
                        // server actually saved (invalid values get corrected).
                        const saved = json.data && json.data.settings ? json.data.settings : null;
                        if (saved) {
                            fillForm(saved);
                            settingsSnapshot = Object.assign({}, saved);
                            applyPreview(collectSettings());
                        } else {
                            settingsSnapshot = Object.assign({}, collectSettings());
                        }

                        showToast(json.data && json.data.message ? json.data.message : 'Settings saved.', 'success');
                    } else {
                        const message = json && json.data && json.data.message ? json.data.message : 'Could not save settings.';
                        showToast(message, 'error');
                    }
                })
                .catch(function () {
                    saveButton.disabled = false;
                    showToast('Request failed. Please try again.', 'error');
                });
        });
    }

    // Discard: restore last saved values.
    if (discardButton) {
        discardButton.addEventListener('click', function () {
            fillForm(settingsSnapshot);
            applyPreview(collectSettings());

            showToast('Changes discarded.', 'success');
        });
    }

    // Initial render.
    applyPreview(notifyConfig.settings);

});
