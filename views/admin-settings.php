<?php
/**
 * Admin settings template
 *
 * @version 1.0.0
 */
defined( 'ABSPATH' ) || exit;

// "Notify Me" button settings (saved via the Notify Me panel).
$alertx_notify_settings = \Alertx\Frontend\Add_Notify_Me_Button::get_settings();
$alertx_notify_fonts    = \Alertx\Frontend\Add_Notify_Me_Button::get_allowed_font_families();
$alertx_notify_icons    = array(
    'none'  => __( 'No icon', 'alertx-pro' ),
    'bell'  => __( 'Bell', 'alertx-pro' ),
    'clock' => __( 'Clock', 'alertx-pro' ),
    'mail'  => __( 'Envelope', 'alertx-pro' ),
    'tag'   => __( 'Tag', 'alertx-pro' ),
);
?>

<div id="alertx" class="alertx wrap alartx-sections">
    <?php include_once ALERTX_PATH .'/views/global/header.php'; ?>

    <section class="page">
        <div class="page-header">
            <div>
                <div class="page-h1">
                    <?php esc_html_e( 'Settings', 'alertx-pro' ); ?>
                </div>
                <div class="page-desc">
                    <?php esc_html_e( 'Channels, branding, GDPR and developer access, all in one place.', 'alertx-pro' ); ?>
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
                    <?php esc_html_e( 'Notify Me', 'alertx-pro' ); ?>
                </a>

                <a class="settings-nav-item" href="#" data-settings="channels">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M18 8a6 6 0 1 0-12 0c0 7-3 9-3 9h18s-3-2-3-9" />
                    </svg>
                    <?php esc_html_e( 'Channels', 'alertx-pro' ); ?>
                </a>
            </nav>

            <section class="section">
                <div id="settings-notify-me" class="settings-panel">
                    <div class="section-title" style="margin-bottom:4px;">
                        <?php esc_html_e( 'Notify Me button', 'alertx-pro' ); ?>
                    </div>
                    <div class="section-desc" style="margin-bottom:18px;">
                        <?php esc_html_e( 'Customize the text, tooltip and appearance of the "Notify Me" button shown on out-of-stock products.', 'alertx-pro' ); ?>
                    </div>

                    <!-- Live preview -->
                    <div class="alertx-preview-box">
                        <div class="alertx-preview-label">
                            <?php esc_html_e( 'Live preview', 'alertx-pro' ); ?>
                        </div>
                        <div class="alertx-preview-canvas" id="alertx-preview-canvas">
                            <button type="button" class="alertx-notify-button" id="alertx-preview-button">
                                <span class="alertx-notify-button-text" id="alertx-preview-button-text"></span>
                            </button>
                            <div class="alertx-tooltip tooltip"><span class="tooltip-mark">?</span><span class="tooltiptext" id="alertx-preview-tooltip-text"></span></div>
                        </div>
                        <div class="alertx-preview-hint">
                            <?php esc_html_e( 'Hover over the ? icon to preview the tooltip.', 'alertx-pro' ); ?>
                        </div>
                    </div>

                    <!-- Texts -->
                    <div class="alertx-settings-block">
                        <div class="alertx-settings-block-title">
                            <?php esc_html_e( 'Texts', 'alertx-pro' ); ?>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="alertx-btn-text">
                                <?php esc_html_e( 'Button text', 'alertx-pro' ); ?>
                            </label>
                            <input id="alertx-btn-text" class="form-input" type="text" data-setting="button_text" value="<?php echo esc_attr( $alertx_notify_settings['button_text'] ); ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="alertx-tooltip-text">
                                <?php esc_html_e( 'Tooltip text', 'alertx-pro' ); ?>
                            </label>
                            <input id="alertx-tooltip-text" class="form-input" type="text" data-setting="tooltip_text" value="<?php echo esc_attr( $alertx_notify_settings['tooltip_text'] ); ?>">
                            <span class="form-hint">
                                <?php esc_html_e( 'Short helper message shown next to the button.', 'alertx-pro' ); ?>
                            </span>
                        </div>
                    </div>

                    <!-- Colors & typography -->
                    <div class="alertx-settings-block">
                        <div class="alertx-settings-block-title">
                            <?php esc_html_e( 'Colors & typography', 'alertx-pro' ); ?>
                        </div>
                        <div class="form-2col">
                            <div class="form-group">
                                <label class="form-label">
                                    <?php esc_html_e( 'Text color', 'alertx-pro' ); ?>
                                </label>
                                <div class="alertx-color-control">
                                    <input type="color" class="alertx-color-picker" data-setting="text_color" value="<?php echo esc_attr( $alertx_notify_settings['text_color'] ); ?>">
                                    <input type="text" class="form-input alertx-color-hex" data-setting="text_color" value="<?php echo esc_attr( $alertx_notify_settings['text_color'] ); ?>" maxlength="7" spellcheck="false">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="alertx-btn-font">
                                    <?php esc_html_e( 'Font family', 'alertx-pro' ); ?>
                                </label>
                                <select id="alertx-btn-font" class="form-select" data-setting="font_family">
                                    <?php foreach ( $alertx_notify_fonts as $alertx_font_value => $alertx_font_label ) : ?>
                                        <option value="<?php echo esc_attr( $alertx_font_value ); ?>" <?php selected( $alertx_notify_settings['font_family'], $alertx_font_value ); ?>><?php echo esc_html( $alertx_font_label ); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">
                                    <?php esc_html_e( 'Background color', 'alertx-pro' ); ?>
                                </label>
                                <div class="alertx-color-control">
                                    <input type="color" class="alertx-color-picker" data-setting="bg_color" value="<?php echo esc_attr( $alertx_notify_settings['bg_color'] ); ?>">
                                    <input type="text" class="form-input alertx-color-hex" data-setting="bg_color" value="<?php echo esc_attr( $alertx_notify_settings['bg_color'] ); ?>" maxlength="7" spellcheck="false">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">
                                    <?php esc_html_e( 'Hover background color', 'alertx-pro' ); ?>
                                </label>
                                <div class="alertx-color-control">
                                    <input type="color" class="alertx-color-picker" data-setting="hover_bg_color" value="<?php echo esc_attr( $alertx_notify_settings['hover_bg_color'] ); ?>">
                                    <input type="text" class="form-input alertx-color-hex" data-setting="hover_bg_color" value="<?php echo esc_attr( $alertx_notify_settings['hover_bg_color'] ); ?>" maxlength="7" spellcheck="false">
                                </div>
                            </div>
                        </div>
                        <div class="form-group" style="max-width:200px;">
                            <label class="form-label" for="alertx-btn-font-size">
                                <?php esc_html_e( 'Font size (px)', 'alertx-pro' ); ?>
                            </label>
                            <input id="alertx-btn-font-size" class="form-input" type="number" min="8" max="60" step="1" data-setting="font_size" value="<?php echo esc_attr( $alertx_notify_settings['font_size'] ); ?>">
                        </div>
                    </div>

                    <!-- Icon -->
                    <div class="alertx-settings-block">
                        <div class="alertx-settings-block-title">
                            <?php esc_html_e( 'Icon', 'alertx-pro' ); ?>
                        </div>
                        <div class="form-2col">
                            <div class="form-group">
                                <label class="form-label" for="alertx-btn-icon">
                                    <?php esc_html_e( 'Icon', 'alertx-pro' ); ?>
                                </label>
                                <select id="alertx-btn-icon" class="form-select" data-setting="icon">
                                    <?php foreach ( $alertx_notify_icons as $alertx_icon_value => $alertx_icon_label ) : ?>
                                        <option value="<?php echo esc_attr( $alertx_icon_value ); ?>" <?php selected( $alertx_notify_settings['icon'], $alertx_icon_value ); ?>><?php echo esc_html( $alertx_icon_label ); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="alertx-btn-icon-position">
                                    <?php esc_html_e( 'Icon position', 'alertx-pro' ); ?>
                                </label>
                                <select id="alertx-btn-icon-position" class="form-select" data-setting="icon_position">
                                    <option value="before" <?php selected( $alertx_notify_settings['icon_position'], 'before' ); ?>>Before text</option>
                                    <option value="after" <?php selected( $alertx_notify_settings['icon_position'], 'after' ); ?>>After text</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Spacing -->
                    <div class="alertx-settings-block">
                        <div class="alertx-settings-block-title">
                            <?php esc_html_e( 'Spacing (px)', 'alertx-pro' ); ?>
                        </div>
                        <div class="form-group">
                            <label class="form-label">
                                <?php esc_html_e( 'Padding', 'alertx-pro' ); ?>
                            </label>
                            <div class="alertx-dim-grid">
                                <div class="alertx-dim-field"><span><?php esc_html_e( 'Top', 'alertx-pro' ); ?></span><input class="form-input" type="number" min="0" max="100" data-setting="padding_top" value="<?php echo esc_attr( $alertx_notify_settings['padding_top'] ); ?>"></div>
                                <div class="alertx-dim-field"><span><?php esc_html_e( 'Right', 'alertx-pro' ); ?></span><input class="form-input" type="number" min="0" max="100" data-setting="padding_right" value="<?php echo esc_attr( $alertx_notify_settings['padding_right'] ); ?>"></div>
                                <div class="alertx-dim-field"><span><?php esc_html_e( 'Bottom', 'alertx-pro' ); ?></span><input class="form-input" type="number" min="0" max="100" data-setting="padding_bottom" value="<?php echo esc_attr( $alertx_notify_settings['padding_bottom'] ); ?>"></div>
                                <div class="alertx-dim-field"><span><?php esc_html_e( 'Left', 'alertx-pro' ); ?></span><input class="form-input" type="number" min="0" max="100" data-setting="padding_left" value="<?php echo esc_attr( $alertx_notify_settings['padding_left'] ); ?>"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label"><?php esc_html_e( 'Margin', 'alertx-pro' ); ?></label>
                            <div class="alertx-dim-grid">
                                <div class="alertx-dim-field"><span><?php esc_html_e( 'Top', 'alertx-pro' ); ?></span><input class="form-input" type="number" min="0" max="100" data-setting="margin_top" value="<?php echo esc_attr( $alertx_notify_settings['margin_top'] ); ?>"></div>
                                <div class="alertx-dim-field"><span><?php esc_html_e( 'Right', 'alertx-pro' ); ?></span><input class="form-input" type="number" min="0" max="100" data-setting="margin_right" value="<?php echo esc_attr( $alertx_notify_settings['margin_right'] ); ?>"></div>
                                <div class="alertx-dim-field"><span><?php esc_html_e( 'Bottom', 'alertx-pro' ); ?></span><input class="form-input" type="number" min="0" max="100" data-setting="margin_bottom" value="<?php echo esc_attr( $alertx_notify_settings['margin_bottom'] ); ?>"></div>
                                <div class="alertx-dim-field"><span><?php esc_html_e( 'Left', 'alertx-pro' ); ?></span><input class="form-input" type="number" min="0" max="100" data-setting="margin_left" value="<?php echo esc_attr( $alertx_notify_settings['margin_left'] ); ?>"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Border -->
                    <div class="alertx-settings-block">
                        <div class="alertx-settings-block-title">
                            <?php esc_html_e( 'Border (px)', 'alertx-pro' ); ?>
                        </div>
                        <div class="form-3col">
                            <div class="form-group">
                                <label class="form-label" for="alertx-btn-border-width">
                                    <?php esc_html_e( 'Border width', 'alertx-pro' ); ?>
                                </label>
                                <input id="alertx-btn-border-width" class="form-input" type="number" min="0" max="10" data-setting="border_width" value="<?php echo esc_attr( $alertx_notify_settings['border_width'] ); ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="alertx-btn-border-radius"><?php esc_html_e( 'Border radius', 'alertx-pro' ); ?></label>
                                <input id="alertx-btn-border-radius" class="form-input" type="number" min="0" max="100" data-setting="border_radius" value="<?php echo esc_attr( $alertx_notify_settings['border_radius'] ); ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">
                                    <?php esc_html_e( 'Border color', 'alertx-pro' ); ?>
                                </label>
                                <div class="alertx-color-control">
                                    <input type="color" class="alertx-color-picker" data-setting="border_color" value="<?php echo esc_attr( $alertx_notify_settings['border_color'] ); ?>">
                                    <input type="text" class="form-input alertx-color-hex" data-setting="border_color" value="<?php echo esc_attr( $alertx_notify_settings['border_color'] ); ?>" maxlength="7" spellcheck="false">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="settings-channels" class="settings-panel" style="display:none;">
                    <div class="section-title" style="margin-bottom:4px;">
                        <?php esc_html_e( 'Notification channels', 'alertx-pro' ); ?>
                    </div>
                    <div class="section-desc" style="margin-bottom:18px;">
                        <?php esc_html_e( 'Choose how subscribers hear from you.', 'alertx-pro' ); ?>
                    </div>
                    <div class="form-group" style="margin-top:16px;">
                        <label class="form-label" for="alertx-sender-email">
                            <?php esc_html_e( 'Sender email address', 'alertx-pro' ); ?>
                        </label>
                        <input id="alertx-sender-email" class="form-input" name="alertx-email-sender" style="max-width:320px;" type="text" placeholder="alerts@yourstore.com" value="<?php echo esc_attr( get_option( 'alertx_sender_email', '' ) ); ?>">
                        <span class="form-hint">
                            <?php esc_html_e( 'Use an address on your site domain (e.g. alerts@yourstore.com) for best delivery. Personal addresses (Gmail, Outlook) are used as the Reply-To instead, because sending From them gets blocked by mail providers.', 'alertx-pro' ); ?>
                        </span>
                    </div>
                </div>

                <div class="save-bar">
                    <span class="alertx-save-status" id="alertx-save-status" role="status" aria-live="polite"></span>
                    <button class="btn btn-secondary" type="button" id="alertx-settings-discard">
                        <?php esc_html_e( 'Discard', 'alertx-pro' ); ?>
                    </button>
                    <button class="btn btn-primary" type="button" id="alertx-settings-save">
                        <?php esc_html_e( 'Save changes', 'alertx-pro' ); ?>
                    </button>
                </div>
            </section>
        </div>
    </section>

    <div class="alertx-settings-documentation">
        <div class="alertx-settings-row">
            <div class="alertx-more-docs-wrapper">
                <div class="alertx-docs-content-wrapper alertx-content-details">
                    <div class="img-wrap">
                        <img src="<?php echo esc_url( ALERTX_URL . 'assets/images/love.svg' ); ?>" alt="Leave a Review">
                    </div>
                    <h3>Express Your Love</h3>
                    <p>Welcome to AlertX! Boost your e-commerce efficiency with our evolving plugin. Please take a moment to review and share your experience. Your feedback drives improvement and helps fellow sellers maximize conversions. Thanks for choosing us!</p>
                    <a class="alertx-resource-link" target="_blank" href="https://thebitcraft.com/support/">Leave a Review <img src="<?php echo esc_url( ALERTX_URL . 'assets/images/link.svg' ); ?>" alt="Leave a Review">
                    </a>
                </div>
                <div class="alertx-docs-content-wrapper alertx-content-details">
                    <div class="img-wrap">
                        <img src="<?php echo esc_url( ALERTX_URL . 'assets/images/knowledgebase.svg' ); ?>" alt="Discover Our Knowledge Base">
                    </div>
                    <h3>Discover Our Knowledge Base</h3>
                    <p>Get started by spending some time with the documentation to familiarize yourself with AlertX and boost your website conversions immediately.</p>
                    <a class="alertx-resource-link" target="_blank" href="https://thebitcraft.com/docs/multi-order-tracker/">Documentation <img src="<?php echo esc_url( ALERTX_URL . 'assets/images/link.svg' ); ?>" alt="Discover Our Knowledge Base">
                    </a>
                </div>
                <div class="alertx-docs-content-wrapper alertx-content-details">
                    <div class="img-wrap">
                        <img src="<?php echo esc_url( ALERTX_URL . 'assets/images/support.svg' ); ?>" alt="Need Help?">
                    </div>
                    <h3>Need Help?</h3>
                    <p>If you experience any issues or need help, please reach out to us or report problems on our support page.</p>
                    <a class="alertx-resource-link" target="_blank" href="https://thebitcraft.com/support/">Report a Bug <img src="<?php echo esc_url( ALERTX_URL . 'assets/images/link.svg' ); ?>" alt="Need Help?">
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
