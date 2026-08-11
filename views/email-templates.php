<?php defined('ABSPATH') || exit; ?>

<div id="alertx" class="main wrap">
    <?php include_once ALERTX_PATH .'/views/global/header.php'; ?>

    <main class="page">
        <div class="page-header">
            <div>
                <div class="page-eyebrow">Configure</div>
                <div class="page-h1">Email Templates</div>
                <div class="page-desc">Design what subscribers see when a product comes back — no code required.</div>
            </div>
        </div>

        <div class="stock-notification-settings">
            <!-- Settings form -->
            <form method="post">
                <?php wp_nonce_field( 'save_settings_action', 'settings_nonce' ); ?>

                <div class="grid-2">
                    <!-- Email Template Section -->
                    <div class="template-guide-line">
                        <h2><?php esc_html_e( 'Email Template', 'alertx' ); ?></h2>

                        <!-- Email template customization guidelines -->
                        <div class="guidelines">
                            <p><?php esc_html_e( 'Customize the email sent to customers when a product is back in stock. You can use the following placeholders:', 'alertx' ); ?></p>
                            <ul>
                                <li><code>{product_name}</code> - <?php esc_html_e( 'The name of the product', 'alertx' ); ?></li>
                                <li><code>{product_url}</code> - <?php esc_html_e( 'The URL of the product page', 'alertx' ); ?></li>
                                <li><code>{site_name}</code> - <?php esc_html_e( 'The name of your website', 'alertx' ); ?></li>
                            </ul>
                        </div>

                        <!-- TinyMCE Editor for email template -->
                        <div class="email-template">
                            <?php
                                $editor_settings = array( // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
                                    'textarea_name' => 'email_templates',
                                    'textarea_rows' => 20,
                                    'media_buttons' => true,
                                    'teeny'         => true,
                                    'quicktags'     => true,
                                );
                                wp_editor($email_templates, 'email_templates_editor', $editor_settings);
                            ?>
                        </div>

                        <!-- Submit button to save settings -->
                        <p class="submit">
                            <input type="submit" name="submit_settings" class="btn btn-primary" value="<?php esc_attr_e( 'Save Settings', 'alertx' ); ?>">
                        </p>
                    </div>

                    <!-- Notification Threshold Section -->
                    <div class="notification-threshold">
                        <h2><?php esc_html_e( 'Notification Threshold', 'alertx' ); ?></h2>

                        <!-- Input field for notification threshold -->
                        <label for="notification_threshold">
                            <?php esc_html_e( 'Notification Threshold:', 'alertx' ); ?>
                        </label>
                        <input type="number" id="notification_threshold" name="notification_threshold" value="<?php echo esc_attr( $threshold ); ?>" min="1">
                        <p class="description">
                            <?php esc_html_e( 'Send notifications when stock reaches or exceeds this number.', 'alertx' ); ?>
                        </p>




                        <section class="section">
                            <div class="section-head">
                                <div>
                                    <div class="section-eyebrow">Templates</div>
                                    <div class="section-title">Your emails</div>
                                </div>
                            </div>
                            <div class="template-grid">
                                <div class="template-card active">
                                    <div class="template-thumb" style="background:linear-gradient(135deg,var(--alertx-purple),var(--alertx-violet));">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2">
                                            <path d="M20 4H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2Z" />
                                            <path d="m22 6-10 7L2 6" />
                                        </svg>
                                    </div>
                                    <div class="template-meta">
                                        <div class="tname">Back in Stock</div>
                                        <div class="tdesc">Sent the moment a product becomes purchasable again</div>
                                    </div>
                                    <div class="switch on"></div>
                                </div>
                                <div class="template-card">
                                    <div class="template-thumb" style="background:linear-gradient(135deg,var(--alertx-violet),var(--alertx-magenta));">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2">
                                            <path d="M20 6 9 17l-5-5" />
                                        </svg>
                                    </div>
                                    <div class="template-meta">
                                        <div class="tname">Subscription Confirmation</div>
                                        <div class="tdesc">Confirms a customer has joined the waitlist</div>
                                    </div>
                                    <div class="switch on"></div>
                                </div>
                                <div class="template-card">
                                    <div class="template-thumb" style="background:linear-gradient(135deg,var(--alertx-magenta),var(--alertx-red));">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2">
                                            <path d="M3 3v18h18" />
                                            <path d="M7 15l4-5 3 3 5-7" />
                                        </svg>
                                    </div>
                                    <div class="template-meta">
                                        <div class="tname">Low Stock Admin Alert</div>
                                        <div class="tdesc">Notifies your team when demand is high but stock is low</div>
                                    </div>
                                    <div class="switch"></div>
                                </div>
                                <div class="template-card">
                                    <div class="template-thumb" style="background:linear-gradient(135deg,var(--alertx-orange),var(--alertx-red));">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2">
                                            <path d="M18 6 6 18M6 6l12 12" />
                                        </svg>
                                    </div>
                                    <div class="template-meta">
                                        <div class="tname">Unsubscribe Confirmation</div>
                                        <div class="tdesc">Sent after a subscriber opts out of alerts</div>
                                    </div>
                                    <div class="switch on"></div>
                                </div>
                            </div>
                            <div class="section-head" style="border-top:1px solid var(--line);">
                                <div>
                                    <div class="section-eyebrow">Editing</div>
                                    <div class="section-title">Back in Stock</div>
                                    <div class="section-desc">Applies to all products unless overridden by an alert rule.</div>
                                </div>
                            </div>
                            <div class="editor-panel">
                                <div class="form-group">
                                    <label class="form-label">Subject line</label>
                                    <input class="form-input" type="text" value="🎉 {product_name} is back in stock!">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Email body</label>
                                    <textarea class="form-textarea">Hi there — good news! {product_name} is back in stock and ready to order at {price}. Since it sold out fast last time, we'd grab it soon.</textarea>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Dynamic tags</label>
                                    <div class="tag-row">
                                        <span class="tag-chip">{product_name}</span>
                                        <span class="tag-chip">{product_url}</span>
                                        <span class="tag-chip">{price}</span>
                                        <span class="tag-chip">{customer_name}</span>
                                        <span class="tag-chip">{stock_quantity}</span>
                                        <span class="tag-chip">{unsubscribe_link}</span>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Button text</label>
                                    <input class="form-input" type="text" value="Shop now">
                                </div>
                            </div>
                        </section>







                    </div>
                </div>
            </form>
        </div>
    </main>

    <section class="page" id="page-templates">
        <div class="page-header">
            <div>
                <div class="page-eyebrow">Configure</div>
                <div class="page-h1">Email Templates</div>
                <div class="page-desc">Design what subscribers see when a product comes back — no code required.</div>
            </div>
            <div class="page-actions">
                <button class="btn btn-primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2">
                        <path d="M12 5v14M5 12h14" />
                    </svg> New template </button>
            </div>
        </div>
        <div class="grid-2">
            <section class="section">
                <div class="section-head">
                    <div>
                        <div class="section-eyebrow">Templates</div>
                        <div class="section-title">Your emails</div>
                    </div>
                </div>
                <div class="template-grid">
                    <div class="template-card active">
                        <div class="template-thumb" style="background:linear-gradient(135deg,var(--alertx-purple),var(--alertx-violet));">
                            <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2">
                                <path d="M20 4H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2Z" />
                                <path d="m22 6-10 7L2 6" />
                            </svg>
                        </div>
                        <div class="template-meta">
                            <div class="tname">Back in Stock</div>
                            <div class="tdesc">Sent the moment a product becomes purchasable again</div>
                        </div>
                        <div class="switch on"></div>
                    </div>
                    <div class="template-card">
                        <div class="template-thumb" style="background:linear-gradient(135deg,var(--alertx-violet),var(--alertx-magenta));">
                            <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2">
                                <path d="M20 6 9 17l-5-5" />
                            </svg>
                        </div>
                        <div class="template-meta">
                            <div class="tname">Subscription Confirmation</div>
                            <div class="tdesc">Confirms a customer has joined the waitlist</div>
                        </div>
                        <div class="switch on"></div>
                    </div>
                    <div class="template-card">
                        <div class="template-thumb" style="background:linear-gradient(135deg,var(--alertx-magenta),var(--alertx-red));">
                            <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2">
                                <path d="M3 3v18h18" />
                                <path d="M7 15l4-5 3 3 5-7" />
                            </svg>
                        </div>
                        <div class="template-meta">
                            <div class="tname">Low Stock Admin Alert</div>
                            <div class="tdesc">Notifies your team when demand is high but stock is low</div>
                        </div>
                        <div class="switch"></div>
                    </div>
                    <div class="template-card">
                        <div class="template-thumb" style="background:linear-gradient(135deg,var(--alertx-orange),var(--alertx-red));">
                            <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2">
                                <path d="M18 6 6 18M6 6l12 12" />
                            </svg>
                        </div>
                        <div class="template-meta">
                            <div class="tname">Unsubscribe Confirmation</div>
                            <div class="tdesc">Sent after a subscriber opts out of alerts</div>
                        </div>
                        <div class="switch on"></div>
                    </div>
                </div>
                <div class="section-head" style="border-top:1px solid var(--line);">
                    <div>
                        <div class="section-eyebrow">Editing</div>
                        <div class="section-title">Back in Stock</div>
                        <div class="section-desc">Applies to all products unless overridden by an alert rule.</div>
                    </div>
                </div>
                <div class="editor-panel">
                    <div class="form-group">
                        <label class="form-label">Subject line</label>
                        <input class="form-input" type="text" value="🎉 {product_name} is back in stock!">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email body</label>
                        <textarea class="form-textarea">Hi there — good news! {product_name} is back in stock and ready to order at {price}. Since it sold out fast last time, we'd grab it soon.</textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Dynamic tags</label>
                        <div class="tag-row">
                            <span class="tag-chip">{product_name}</span>
                            <span class="tag-chip">{product_url}</span>
                            <span class="tag-chip">{price}</span>
                            <span class="tag-chip">{customer_name}</span>
                            <span class="tag-chip">{stock_quantity}</span>
                            <span class="tag-chip">{unsubscribe_link}</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Button text</label>
                        <input class="form-input" type="text" value="Shop now">
                    </div>
                </div>
            </section>


            <section class="section">
                <div class="section-head">
                    <div>
                        <div class="section-eyebrow">Preview</div>
                        <div class="section-title">Live preview</div>
                    </div>
                </div>
                <div class="email-preview" style="margin:0 22px 22px;width:auto;">
                    <div class="email-preview-toolbar">
                        <span>Desktop preview</span>
                        <span>Sent from: alerts@yourstore.com</span>
                    </div>
                    <div class="email-preview-body">
                        <div class="email-card">
                            <div class="email-card-header">
                                <div class="logo-dot">
                                    <svg viewBox="0 0 24 24" fill="none">
                                        <path d="M12 2L4 13h6l-1 9 9-13h-6l1-7z" fill="#fff" />
                                    </svg>
                                </div>
                                <h3>It's back!</h3>
                            </div>
                            <div class="email-card-body">
                                <div class="email-product-thumb">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                        <rect x="3" y="3" width="18" height="18" rx="2" />
                                        <circle cx="9" cy="9" r="2" />
                                        <path d="m21 15-5-5L5 21" />
                                    </svg>
                                </div>
                                <h4>iPhone 15 Pro Silicone Case</h4>
                                <p>Good news — this item is back in stock and ready to order at <b>$34.00</b>. It sold out fast last time, so grab it soon. </p>
                                <span class="email-cta">Shop now</span>
                            </div>
                            <div class="email-card-footer">You're receiving this because you asked to be notified · Unsubscribe</div>
                        </div>
                    </div>
                </div>
                <div style="padding:0 22px 22px;display:flex;gap:10px;">
                    <button class="btn btn-secondary" style="flex:1;justify-content:center;">Send test email</button>
                    <button class="btn btn-primary" style="flex:1;justify-content:center;">Save template</button>
                </div>
            </section>
        </div>
    </section>
</div>
