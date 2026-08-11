

<div id="alertx" class="main wrap">
    <?php include_once ALERTX_PATH .'/views/global/header.php'; ?>

    <main class="content">
        <section class="page" id="page-settings">
            <div class="page-header">
                <div>
                    <div class="page-eyebrow">Configure</div>
                    <div class="page-h1">Settings</div>
                    <div class="page-desc">Channels, branding, GDPR and developer access, all in one place.</div>
                </div>
            </div>
            <div class="settings-layout">
                <nav class="settings-nav">
                    <a class="settings-nav-item active" href="#" data-settings="general">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="9" />
                            <path d="M12 7v5l3 3" />
                        </svg> General </a>
                    <a class="settings-nav-item" href="#" data-settings="channels">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 8a6 6 0 1 0-12 0c0 7-3 9-3 9h18s-3-2-3-9" />
                        </svg> Channels </a>
                    <a class="settings-nav-item" href="#" data-settings="branding">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 2v20M2 12h20" />
                        </svg> Branding </a>
                    <a class="settings-nav-item" href="#" data-settings="privacy">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z" />
                        </svg> Privacy &amp; GDPR </a>
                    <a class="settings-nav-item" href="#" data-settings="developer">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="m16 18 6-6-6-6" />
                            <path d="m8 6-6 6 6 6" />
                        </svg> Developer </a>
                </nav>
                <section class="section">
                    <div id="settings-general" class="settings-panel">
                        <div class="section-title" style="margin-bottom:4px;">General</div>
                        <div class="section-desc" style="margin-bottom:18px;">Basic behaviour for how AlertX runs on your store.</div>
                        <div class="form-row-flex">
                            <div>
                                <div class="frf-title">Enable back-in-stock alerts</div>
                                <div class="frf-desc">Turn the entire plugin on or off store-wide</div>
                            </div>
                            <div class="switch on"></div>
                        </div>
                        <div class="form-row-flex">
                            <div>
                                <div class="frf-title">Show "Notify me" button automatically</div>
                                <div class="frf-desc">Adds the button to every out-of-stock product page</div>
                            </div>
                            <div class="switch on"></div>
                        </div>
                        <div class="form-row-flex">
                            <div>
                                <div class="frf-title">Auto-hide form once back in stock</div>
                                <div class="frf-desc">Removes the subscription form the moment stock is available</div>
                            </div>
                            <div class="switch on"></div>
                        </div>
                        <div class="form-row-flex">
                            <div>
                                <div class="frf-title">Show demand message on product page</div>
                                <div class="frf-desc">e.g. "37 people are waiting for this product"</div>
                            </div>
                            <div class="switch on"></div>
                        </div>
                        <div class="form-group" style="margin-top:16px;">
                            <label class="form-label">Expired subscription cleanup</label>
                            <select class="form-select" style="max-width:260px;">
                                <option>After 90 days</option>
                                <option>After 30 days</option>
                                <option>Never</option>
                            </select>
                            <span class="form-hint">Removes subscriptions that never converted after this period.</span>
                        </div>
                    </div>
                    <div id="settings-channels" class="settings-panel" style="display:none;">
                        <div class="section-title" style="margin-bottom:4px;">Notification channels</div>
                        <div class="section-desc" style="margin-bottom:18px;">Choose how subscribers hear from you.</div>
                        <div class="form-row-flex">
                            <div>
                                <div class="frf-title">Email</div>
                                <div class="frf-desc">Sent via your store's WordPress mail settings</div>
                            </div>
                            <div class="switch on"></div>
                        </div>
                        <div class="form-row-flex">
                            <div>
                                <div class="frf-title">Browser push notification</div>
                                <div class="frf-desc">Requires subscriber to allow notifications</div>
                            </div>
                            <div class="switch on"></div>
                        </div>
                        <div class="form-row-flex">
                            <div>
                                <div class="frf-title">SMS</div>
                                <div class="frf-desc">Business plan — connect a Twilio account</div>
                            </div>
                            <div class="switch"></div>
                        </div>
                        <div class="form-row-flex">
                            <div>
                                <div class="frf-title">WhatsApp</div>
                                <div class="frf-desc">Business plan — connect WhatsApp Business API</div>
                            </div>
                            <div class="switch"></div>
                        </div>
                        <div class="form-group" style="margin-top:16px;">
                            <label class="form-label">Sender email address</label>
                            <input class="form-input" style="max-width:320px;" type="text" value="alerts@yourstore.com">
                        </div>
                    </div>
                    <div id="settings-branding" class="settings-panel" style="display:none;">
                        <div class="section-title" style="margin-bottom:4px;">Branding</div>
                        <div class="section-desc" style="margin-bottom:18px;">Match alert emails and popups to your store.</div>
                        <div class="form-group">
                            <label class="form-label">Brand colour</label>
                            <div class="color-row">
                                <div class="color-dot active" style="background:var(--alertx-purple);"></div>
                                <div class="color-dot" style="background:var(--alertx-violet);"></div>
                                <div class="color-dot" style="background:var(--alertx-magenta);"></div>
                                <div class="color-dot" style="background:var(--alertx-orange);"></div>
                                <div class="color-dot" style="background:var(--alertx-red);"></div>
                            </div>
                        </div>
                        <div class="form-2col">
                            <div class="form-group">
                                <label class="form-label">Store logo</label>
                                <button class="btn btn-secondary" style="width:100%;justify-content:center;">Upload logo</button>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Email footer text</label>
                                <input class="form-input" type="text" value="© Your Store — all rights reserved">
                            </div>
                        </div>
                    </div>
                    <div id="settings-privacy" class="settings-panel" style="display:none;">
                        <div class="section-title" style="margin-bottom:4px;">Privacy &amp; GDPR</div>
                        <div class="section-desc" style="margin-bottom:18px;">Keep subscription collection compliant.</div>
                        <div class="form-row-flex">
                            <div>
                                <div class="frf-title">Require consent checkbox</div>
                                <div class="frf-desc">Subscriber must tick a box before joining a waitlist</div>
                            </div>
                            <div class="switch on"></div>
                        </div>
                        <div class="form-row-flex">
                            <div>
                                <div class="frf-title">Double opt-in confirmation</div>
                                <div class="frf-desc">Sends a confirmation email before activating the alert</div>
                            </div>
                            <div class="switch"></div>
                        </div>
                        <div class="form-group" style="margin-top:16px;">
                            <label class="form-label">Consent checkbox text</label>
                            <textarea class="form-textarea">I agree to be notified by email when this product is back in stock.</textarea>
                        </div>
                    </div>
                    <div id="settings-developer" class="settings-panel" style="display:none;">
                        <div class="section-title" style="margin-bottom:4px;">Developer</div>
                        <div class="section-desc" style="margin-bottom:18px;">REST API, webhooks and WP-CLI access — Agency plan.</div>
                        <div class="form-group">
                            <label class="form-label">REST API key</label>
                            <div class="api-key-box">
                                <code>alx_live_9f2a7c1e4b8d0f3a6c5e2b1d</code>
                                <button class="btn btn-secondary btn-sm">Copy</button>
                                <button class="btn btn-secondary btn-sm">Regenerate</button>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Webhook URL</label>
                            <input class="form-input" type="text" placeholder="https://yourapp.com/webhooks/alertx">
                            <span class="form-hint">Fires on: new subscriber, product back in stock, notification sent, notification clicked.</span>
                        </div>
                        <div class="form-row-flex">
                            <div>
                                <div class="frf-title">Enable WP-CLI commands</div>
                                <div class="frf-desc">Bulk-manage subscriptions and notifications from the terminal</div>
                            </div>
                            <div class="switch on"></div>
                        </div>
                    </div>
                    <div class="save-bar">
                        <button class="btn btn-secondary">Discard</button>
                        <button class="btn btn-primary">Save changes</button>
                    </div>
                </section>
            </div>
        </section>
    </div>
</div>
<!--PAGE:SETTINGS-->