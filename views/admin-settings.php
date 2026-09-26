<?php
/**
 * Admin Settings template
 *
 * The "Notify Me" tab is fully available in the free version. The
 * "Channels" and "Newsletter" tabs are premium: their navigation items
 * show a lock icon and clicking them renders the Go Premium screen
 * (same teaser pattern as the Campaign pages).
 *
 * @version 1.1.0
 */
defined( 'ABSPATH' ) || exit;

// "Notify Me" button settings (saved via the Notify Me panel).
$restockx_notify_settings = \RestockX\Frontend\Add_Notify_Me_Button::get_settings();
$restockx_notify_fonts    = \RestockX\Frontend\Add_Notify_Me_Button::get_allowed_font_families();
$restockx_notify_icons    = array(
    'none'  => __( 'No icon', 'restockx' ),
    'bell'  => __( 'Bell', 'restockx' ),
    'clock' => __( 'Clock', 'restockx' ),
    'mail'  => __( 'Envelope', 'restockx' ),
    'tag'   => __( 'Tag', 'restockx' ),
);
?>

<div id="restockx" class="restockx wrap alartx-sections">
	<?php include_once RESTOCKX_PATH .'/views/global/header.php'; ?>

    <section class="page">
        <div class="page-header">
            <div>
                <div class="page-h1">
                    <?php esc_html_e( 'Settings', 'restockx' ); ?>
                </div>
                <div class="page-desc">
                    <?php esc_html_e( 'Channels, branding, GDPR and developer access, all in one place.', 'restockx' ); ?>
                </div>
            </div>
        </div>

        <div class="settings-layout">
            <nav class="settings-nav">
                <a class="settings-nav-item active" href="#" data-settings="notify-me">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="9" />
                        <path d="M12 7v5l3 3" />
                    </svg>
                    <?php esc_html_e( 'Notify Me', 'restockx' ); ?>
                </a>

                <a class="settings-nav-item" href="#" data-settings="channels">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M18 8a6 6 0 1 0-12 0c0 7-3 9-3 9h18s-3-2-3-9" />
                    </svg>
                    <?php esc_html_e( 'Channels', 'restockx' ); ?>
                    <svg class="settings-nav-lock" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2"></rect>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                </a>

                <a class="settings-nav-item" href="#" data-settings="newsletter">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="2" y="4" width="20" height="16" rx="2" />
                        <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7" />
                    </svg>
                    <?php esc_html_e( 'Newsletter', 'restockx' ); ?>
                    <svg class="settings-nav-lock" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2"></rect>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                </a>
            </nav>

            <section class="section">
                <div id="settings-notify-me" class="settings-panel">
                    <div class="section-title" style="margin-bottom:4px;">
                        <?php esc_html_e( 'Notify Me button', 'restockx' ); ?>
                    </div>
                    <div class="section-desc" style="margin-bottom:18px;">
                        <?php esc_html_e( 'Customize the text, tooltip and appearance of the "Notify Me" button shown on out-of-stock products.', 'restockx' ); ?>
                    </div>

                    <!-- Live preview -->
                    <div class="restockx-preview-box">
                        <div class="restockx-preview-label">
                            <?php esc_html_e( 'Live preview', 'restockx' ); ?>
                        </div>
                        <div class="restockx-preview-canvas" id="restockx-preview-canvas">
                            <button type="button" class="restockx-notify-button" id="restockx-preview-button">
                                <span class="restockx-notify-button-text" id="restockx-preview-button-text"></span>
                            </button>
                            <div class="restockx-tooltip tooltip"><span class="tooltip-mark">?</span><span class="tooltiptext" id="restockx-preview-tooltip-text"></span></div>
                        </div>
                        <div class="restockx-preview-hint">
                            <?php esc_html_e( 'Hover over the ? icon to preview the tooltip.', 'restockx' ); ?>
                        </div>
                    </div>

                    <!-- Texts -->
                    <div class="restockx-settings-block">
                        <div class="restockx-settings-block-title">
                            <?php esc_html_e( 'Texts', 'restockx' ); ?>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="restockx-btn-text">
                                <?php esc_html_e( 'Button text', 'restockx' ); ?>
                            </label>
                            <input id="restockx-btn-text" class="form-input" type="text" data-setting="button_text" value="<?php echo esc_attr( $restockx_notify_settings['button_text'] ); ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="restockx-tooltip-text">
                                <?php esc_html_e( 'Tooltip text', 'restockx' ); ?>
                            </label>
                            <input id="restockx-tooltip-text" class="form-input" type="text" data-setting="tooltip_text" value="<?php echo esc_attr( $restockx_notify_settings['tooltip_text'] ); ?>">
                            <span class="form-hint">
                                <?php esc_html_e( 'Short helper message shown next to the button.', 'restockx' ); ?>
                            </span>
                        </div>
                    </div>

                    <!-- Colors & typography -->
                    <div class="restockx-settings-block">
                        <div class="restockx-settings-block-title">
                            <?php esc_html_e( 'Colors & typography', 'restockx' ); ?>
                        </div>
                        <div class="form-2col">
                            <div class="form-group">
                                <label class="form-label">
                                    <?php esc_html_e( 'Text color', 'restockx' ); ?>
                                </label>
                                <div class="restockx-color-control">
                                    <input type="color" class="restockx-color-picker" data-setting="text_color" value="<?php echo esc_attr( $restockx_notify_settings['text_color'] ); ?>">
                                    <input type="text" class="form-input restockx-color-hex" data-setting="text_color" value="<?php echo esc_attr( $restockx_notify_settings['text_color'] ); ?>" maxlength="7" spellcheck="false">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="restockx-btn-font">
                                    <?php esc_html_e( 'Font family', 'restockx' ); ?>
                                </label>
                                <select id="restockx-btn-font" class="form-select" data-setting="font_family">
                                    <?php foreach ( $restockx_notify_fonts as $restockx_font_value => $restockx_font_label ) : ?>
                                        <option value="<?php echo esc_attr( $restockx_font_value ); ?>" <?php selected( $restockx_notify_settings['font_family'], $restockx_font_value ); ?>><?php echo esc_html( $restockx_font_label ); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">
                                    <?php esc_html_e( 'Background color', 'restockx' ); ?>
                                </label>
                                <div class="restockx-color-control">
                                    <input type="color" class="restockx-color-picker" data-setting="bg_color" value="<?php echo esc_attr( $restockx_notify_settings['bg_color'] ); ?>">
                                    <input type="text" class="form-input restockx-color-hex" data-setting="bg_color" value="<?php echo esc_attr( $restockx_notify_settings['bg_color'] ); ?>" maxlength="7" spellcheck="false">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">
                                    <?php esc_html_e( 'Hover background color', 'restockx' ); ?>
                                </label>
                                <div class="restockx-color-control">
                                    <input type="color" class="restockx-color-picker" data-setting="hover_bg_color" value="<?php echo esc_attr( $restockx_notify_settings['hover_bg_color'] ); ?>">
                                    <input type="text" class="form-input restockx-color-hex" data-setting="hover_bg_color" value="<?php echo esc_attr( $restockx_notify_settings['hover_bg_color'] ); ?>" maxlength="7" spellcheck="false">
                                </div>
                            </div>
                        </div>
                        <div class="form-group" style="max-width:200px;">
                            <label class="form-label" for="restockx-btn-font-size">
                                <?php esc_html_e( 'Font size (px)', 'restockx' ); ?>
                            </label>
                            <input id="restockx-btn-font-size" class="form-input" type="number" min="8" max="60" step="1" data-setting="font_size" value="<?php echo esc_attr( $restockx_notify_settings['font_size'] ); ?>">
                        </div>
                    </div>

                    <!-- Icon -->
                    <div class="restockx-settings-block">
                        <div class="restockx-settings-block-title">
                            <?php esc_html_e( 'Icon', 'restockx' ); ?>
                        </div>
                        <div class="form-2col">
                            <div class="form-group">
                                <label class="form-label" for="restockx-btn-icon">
                                    <?php esc_html_e( 'Icon', 'restockx' ); ?>
                                </label>
                                <select id="restockx-btn-icon" class="form-select" data-setting="icon">
                                    <?php foreach ( $restockx_notify_icons as $restockx_icon_value => $restockx_icon_label ) : ?>
                                        <option value="<?php echo esc_attr( $restockx_icon_value ); ?>" <?php selected( $restockx_notify_settings['icon'], $restockx_icon_value ); ?>><?php echo esc_html( $restockx_icon_label ); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="restockx-btn-icon-position">
                                    <?php esc_html_e( 'Icon position', 'restockx' ); ?>
                                </label>
                                <select id="restockx-btn-icon-position" class="form-select" data-setting="icon_position">
                                    <option value="before" <?php selected( $restockx_notify_settings['icon_position'], 'before' ); ?>>Before text</option>
                                    <option value="after" <?php selected( $restockx_notify_settings['icon_position'], 'after' ); ?>>After text</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Spacing -->
                    <div class="restockx-settings-block">
                        <div class="restockx-settings-block-title">
                            <?php esc_html_e( 'Spacing (px)', 'restockx' ); ?>
                        </div>
                        <div class="form-group">
                            <label class="form-label">
                                <?php esc_html_e( 'Padding', 'restockx' ); ?>
                            </label>
                            <div class="restockx-dim-grid">
                                <div class="restockx-dim-field"><span><?php esc_html_e( 'Top', 'restockx' ); ?></span><input class="form-input" type="number" min="0" max="100" data-setting="padding_top" value="<?php echo esc_attr( $restockx_notify_settings['padding_top'] ); ?>"></div>
                                <div class="restockx-dim-field"><span><?php esc_html_e( 'Right', 'restockx' ); ?></span><input class="form-input" type="number" min="0" max="100" data-setting="padding_right" value="<?php echo esc_attr( $restockx_notify_settings['padding_right'] ); ?>"></div>
                                <div class="restockx-dim-field"><span><?php esc_html_e( 'Bottom', 'restockx' ); ?></span><input class="form-input" type="number" min="0" max="100" data-setting="padding_bottom" value="<?php echo esc_attr( $restockx_notify_settings['padding_bottom'] ); ?>"></div>
                                <div class="restockx-dim-field"><span><?php esc_html_e( 'Left', 'restockx' ); ?></span><input class="form-input" type="number" min="0" max="100" data-setting="padding_left" value="<?php echo esc_attr( $restockx_notify_settings['padding_left'] ); ?>"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label"><?php esc_html_e( 'Margin', 'restockx' ); ?></label>
                            <div class="restockx-dim-grid">
                                <div class="restockx-dim-field"><span><?php esc_html_e( 'Top', 'restockx' ); ?></span><input class="form-input" type="number" min="0" max="100" data-setting="margin_top" value="<?php echo esc_attr( $restockx_notify_settings['margin_top'] ); ?>"></div>
                                <div class="restockx-dim-field"><span><?php esc_html_e( 'Right', 'restockx' ); ?></span><input class="form-input" type="number" min="0" max="100" data-setting="margin_right" value="<?php echo esc_attr( $restockx_notify_settings['margin_right'] ); ?>"></div>
                                <div class="restockx-dim-field"><span><?php esc_html_e( 'Bottom', 'restockx' ); ?></span><input class="form-input" type="number" min="0" max="100" data-setting="margin_bottom" value="<?php echo esc_attr( $restockx_notify_settings['margin_bottom'] ); ?>"></div>
                                <div class="restockx-dim-field"><span><?php esc_html_e( 'Left', 'restockx' ); ?></span><input class="form-input" type="number" min="0" max="100" data-setting="margin_left" value="<?php echo esc_attr( $restockx_notify_settings['margin_left'] ); ?>"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Border -->
                    <div class="restockx-settings-block">
                        <div class="restockx-settings-block-title">
                            <?php esc_html_e( 'Border (px)', 'restockx' ); ?>
                        </div>
                        <div class="form-3col">
                            <div class="form-group">
                                <label class="form-label" for="restockx-btn-border-width">
                                    <?php esc_html_e( 'Border width', 'restockx' ); ?>
                                </label>
                                <input id="restockx-btn-border-width" class="form-input" type="number" min="0" max="10" data-setting="border_width" value="<?php echo esc_attr( $restockx_notify_settings['border_width'] ); ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="restockx-btn-border-radius"><?php esc_html_e( 'Border radius', 'restockx' ); ?></label>
                                <input id="restockx-btn-border-radius" class="form-input" type="number" min="0" max="100" data-setting="border_radius" value="<?php echo esc_attr( $restockx_notify_settings['border_radius'] ); ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">
                                    <?php esc_html_e( 'Border color', 'restockx' ); ?>
                                </label>
                                <div class="restockx-color-control">
                                    <input type="color" class="restockx-color-picker" data-setting="border_color" value="<?php echo esc_attr( $restockx_notify_settings['border_color'] ); ?>">
                                    <input type="text" class="form-input restockx-color-hex" data-setting="border_color" value="<?php echo esc_attr( $restockx_notify_settings['border_color'] ); ?>" maxlength="7" spellcheck="false">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Channels: premium tab, renders the Go Premium screen. -->
                <div id="settings-channels" class="settings-panel settings-panel-locked" style="display:none;">
                    <div class="blur">
                        <img src="<?php echo esc_url( RESTOCKX_URL . 'assets/images/settings.jpg' ); ?>" alt="">

                        <?php if ( restockx_show_upgrade_cta() ) : ?>
                            <a class="go-premium" href="https://contra.com/products/hgwSzl7o-restock-x-for-woo-commerce" target="_blank" rel="noopener">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="11" width="18" height="11" rx="2"></rect>
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                </svg>
                                <?php esc_html_e( 'Go Premium', 'restockx' ); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Newsletter: premium tab, renders the Go Premium screen. -->
                <div id="settings-newsletter" class="settings-panel settings-panel-locked" style="display:none;">
                    <div class="blur">
                        <img src="<?php echo esc_url( RESTOCKX_URL . 'assets/images/settings.jpg' ); ?>" alt="">

                        <?php if ( restockx_show_upgrade_cta() ) : ?>
                            <a class="go-premium" href="https://contra.com/products/hgwSzl7o-restock-x-for-woo-commerce" target="_blank" rel="noopener">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="11" width="18" height="11" rx="2"></rect>
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                </svg>
                                <?php esc_html_e( 'Go Premium', 'restockx' ); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="save-bar">
                    <span class="restockx-save-status" id="restockx-save-status" role="status" aria-live="polite"></span>
                    <button class="btn btn-secondary" type="button" id="restockx-settings-discard">
                        <?php esc_html_e( 'Discard', 'restockx' ); ?>
                    </button>
                    <button class="btn btn-primary" type="button" id="restockx-settings-save">
                        <?php esc_html_e( 'Save changes', 'restockx' ); ?>
                    </button>
                </div>
            </section>
        </div>
    </section>
</div>
