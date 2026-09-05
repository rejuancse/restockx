/**
 * AlertX Pro - Admin settings page scripts.
 *
 * Config (alertxSettings) is localized from includes/Assets.php and contains:
 *  - ajaxUrl  {string} admin-ajax.php URL
 *  - nonce    {string} alertx_settings_nonce
 *  - iconMap  {Object} icon key -> inline SVG
 *  - settings {Object} saved "Notify Me" button settings
 */
document.addEventListener('DOMContentLoaded', function () {

    /* ---------- Settings tabs ---------- */

    const tabItems = document.querySelectorAll('.settings-nav-item');
    const panels = document.querySelectorAll('.settings-panel');

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
            }
        });
    });

    /* ---------- Notify Me button settings ---------- */
    const notifyConfig = typeof window.alertxSettings !== 'undefined' ? window.alertxSettings : null;
    const notifyPanel    = document.getElementById('settings-notify-me');
    const previewButton  = document.getElementById('alertx-preview-button');
    const previewTooltip = document.getElementById('alertx-preview-tooltip-text');
    const saveStatus     = document.getElementById('alertx-save-status');
    const saveButton     = document.getElementById('alertx-settings-save');
    const discardButton  = document.getElementById('alertx-settings-discard');

    if (!notifyConfig || !notifyPanel || !previewButton) {
        return;
    }

    let settingsSnapshot = Object.assign({}, notifyConfig.settings);

    // Sender email address lives in the Channels panel but is saved by the
    // same "Save changes" button as a separate POST field.
    const senderEmailInput  = document.getElementById('alertx-sender-email');
    let senderEmailSnapshot = senderEmailInput ? senderEmailInput.value : '';

    const isHex = function (value) {
        return /^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/.test(value);
    };

    /**
     * Finds the color picker + hex input pair for a given setting key.
     */
    const findColorPair = function (key) {
        return {
            picker:   notifyPanel.querySelector('input.alertx-color-picker[data-setting="' + key + '"]'),
            hexInput: notifyPanel.querySelector('input.alertx-color-hex[data-setting="' + key + '"]')
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

            if (field.classList.contains('alertx-color-picker') || field.classList.contains('alertx-color-hex')) {
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
        textSpan.className = 'alertx-notify-button-text';
        textSpan.id = 'alertx-preview-button-text';
        textSpan.textContent = settings.button_text || '';
        previewButton.appendChild(textSpan);

        if (iconHtml && settings.icon_position === 'after') {
            previewButton.insertAdjacentHTML('beforeend', iconHtml);
        }
    };

    const showStatus = function (message, isError) {
        saveStatus.textContent = message;
        saveStatus.classList.toggle('is-error', !!isError);
        saveStatus.classList.add('is-visible');
        clearTimeout(showStatus.timer);
        showStatus.timer = setTimeout(function () {
            saveStatus.classList.remove('is-visible');
        }, 3500);
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
        if (field.classList.contains('alertx-color-picker') || field.classList.contains('alertx-color-hex')) {
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
        if (field.classList && field.classList.contains('alertx-color-hex') && !isHex(field.value)) {
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
            if (saveStatus) {
                saveStatus.classList.remove('is-visible');
            }

            const formData = new FormData();
            formData.append('action', 'alertx_save_settings');
            formData.append('nonce', notifyConfig.nonce);
            formData.append('settings', JSON.stringify(collectSettings()));
            formData.append('sender_email', senderEmailInput ? senderEmailInput.value : '');

            fetch(notifyConfig.ajaxUrl, {
                method: 'POST',
                credentials: 'same-origin',
                body: formData
            })
                .then(function (response) { return response.json(); })
                .then(function (json) {
                    saveButton.disabled = false;

                    if (json && json.success) {
                        // Fix: sync the form with the sanitized settings the
                        // server actually saved (invalid values get corrected).
                        const saved = json.data && json.data.settings ? json.data.settings : null;
                        if (saved) {
                            fillForm(saved);
                            settingsSnapshot = Object.assign({}, saved);
                            applyPreview(collectSettings());
                        } else {
                            settingsSnapshot = Object.assign({}, collectSettings());
                        }

                        // Sync the sender email with the sanitized value the
                        // server saved.
                        if (senderEmailInput && json.data && typeof json.data.sender_email !== 'undefined') {
                            senderEmailInput.value = json.data.sender_email;
                            senderEmailSnapshot = json.data.sender_email;
                        }

                        showStatus(json.data && json.data.message ? json.data.message : 'Settings saved.', false);
                    } else {
                        const message = json && json.data && json.data.message ? json.data.message : 'Could not save settings.';
                        showStatus(message, true);
                    }
                })
                .catch(function () {
                    saveButton.disabled = false;
                    showStatus('Request failed. Please try again.', true);
                });
        });
    }

    // Discard: restore last saved values.
    if (discardButton) {
        discardButton.addEventListener('click', function () {
            fillForm(settingsSnapshot);
            applyPreview(collectSettings());

            if (senderEmailInput) {
                senderEmailInput.value = senderEmailSnapshot;
            }

            showStatus('Changes discarded.', false);
        });
    }

    // Initial render.
    applyPreview(notifyConfig.settings);

});
