=== Alertx for WooCommerce ===

Contributors: rejuancse
Tags: woocommerce, stock alert, back in stock, stock notification, notify me
Requires at least: 6.2
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Recover lost sales with automatic back-in-stock alerts. Customers click "Notify Me" on out-of-stock products and get an email the moment items are available again.

== Description ==

Alertx recovers lost sales on your WooCommerce store by letting interested customers subscribe to restock alerts for out-of-stock products. When a product comes back in stock, every confirmed subscriber is notified automatically by email — turning missed sales into completed orders.

Customers click the "Notify Me" button on any out-of-stock product, enter their email, and confirm the subscription. Store owners get a clean admin dashboard to manage subscribers, customize emails, and export data.

= Features =

* **"Notify Me" button on out-of-stock products** — displayed automatically on single product pages, with support for both simple and variable products (per-variation detection).
* **Automatic back-in-stock emails** — subscribers are emailed as soon as a product or variation's stock status changes to "in stock".
* **Double opt-in confirmation** — optional email confirmation protects your list from fake or mistyped addresses. Can be enabled or disabled.
* **One-click unsubscribe** — every alert email includes an unsubscribe link and a `List-Unsubscribe` email header, so subscribers can opt out anytime.
* **Customizable button appearance** — change button text, tooltip, colors, font, icons, padding, margin, border width, color, and border radius from the admin area.
* **Customizable email template** — personalize the back-in-stock email with placeholders for product name, product URL, site name, and unsubscribe link.
* **Stock notification threshold** — choose the stock quantity at which notifications should be triggered.
* **Subscriber management** — view subscribers with product, email, date, and status (pending / confirmed / unsubscribed); delete entries in bulk.
* **CSV export** — export the full subscriber list for reporting or external marketing tools.
* **Dashboard statistics widget** — see total notifications, unique products, and unique subscribers at a glance on the WordPress dashboard.
* **Alternative product suggestions** — after subscribing, customers are shown up to 5 in-stock alternatives from the same categories or tags.
* **Rate limiting** — subscription requests are limited per email address to prevent abuse and spam.
* **Email deliverability friendly** — sender address is kept domain-aligned (with automatic Reply-To handling) so alert emails don't get dropped by recipients' providers.
* **Translation ready** — fully translatable via WordPress.org translate.wordpress.org.

= How it works =

1. A customer visits an out-of-stock product and clicks the "Notify Me" button.
2. They enter their email address and submit the form.
3. Depending on your settings, they either confirm via a double opt-in email or are subscribed instantly.
4. When you restock the product, Alertx automatically emails all confirmed subscribers with a link back to the product.

== Installation ==

= Minimum requirements =

* WordPress 6.2 or greater
* PHP 7.4 or greater (PHP 8.0+ recommended)
* MySQL 5.7 or greater (or MariaDB 10.3 or greater)
* WooCommerce (active)

= Automatic installation =

Automatic installation is the easiest option — WordPress handles the file transfer itself, and you won't need to leave your web browser. To install Alertx, log in to your WordPress dashboard, navigate to the **Plugins** menu, and click **Add New**.

In the search field, type "Alertx for WooCommerce" and click **Search Plugins**. Once you find the plugin, you can view its details and install it by clicking **Install Now**. Afterwards, activate the plugin.

= Manual installation =

1. Download the plugin ZIP file from WordPress.org.
2. Go to **Dashboard > Plugins > Add New > Upload Plugin**.
3. Choose `alertx.zip`, click **Install Now**, and then activate the plugin.

The AlertX menu will appear in your WordPress admin, where you can view subscribers, customize email templates, and configure the notify button.

== Frequently Asked Questions ==

= Does this plugin work with variable products? =

Yes. Alertx supports variable products — the notify button appears when a product (or all of its variations) is out of stock, and subscribers are alerted when the specific variation is back in stock.

= Do subscribers need to create an account? =

No. Logged-out customers simply enter their email address. Logged-in customers have their email filled in automatically.

= Can I turn off the double opt-in confirmation email? =

Yes. Double opt-in is enabled by default to keep your subscriber list clean, but you can disable it from the **AlertX > Email Templates** page. Note that unsubscribe links are always included in alert emails.

= Is the plugin GDPR friendly? =

Yes. Subscriptions are confirmed by email (when double opt-in is enabled), every alert contains a one-click unsubscribe link, and subscribers can be deleted at any time from the Subscribers page.

= Can I export my subscribers? =

Yes. Go to **AlertX > Subscribers** and click the CSV export button to download the complete subscriber list.

= Where can I get support? =

You can get support by posting in the support section of this plugin on the WordPress plugin directory, or by email: rejuan.17bd@gmail.com

= Where can I report a bug? =

Found a bug? Please let us know by opening a topic in the support section of this plugin on the WordPress plugin directory, or email us directly: rejuan.17bd@gmail.com

== Screenshots ==

1. "Notify Me" button shown on an out-of-stock product page.
2. Email subscription form with double opt-in confirmation message.
3. AlertX admin dashboard with notification statistics.
4. Subscribers page with subscriber list, bulk actions, and CSV export.
5. Email template editor with stock threshold setting.

== Changelog ==

= 1.0.0 =
* Initial version released.

== Upgrade Notice ==

= 1.0.0 =
Initial release. Enjoy!
