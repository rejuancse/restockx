=== AlertX for WooCommerce ===

Contributors: rejuancse
Tags: stock alert, alert email, in stock, out of stock, woocommerce stock, notification
Requires at least: 5.9
Tested up to: 6.9
Requires PHP: 7.4
Stable tag:  1.1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Inform customers when out-of-stock WooCommerce products return to stock. "Notify Me" functionality and automatic email reminders.

== Description ==
AlertX improves WooCommerce by alerting consumers when out-of-stock items become available once more. Email subscription to alerts is simple for users. Better product suggestions, a user-friendly admin interface, customizable email templates, and rate limiting to effectively control alerts define the plugin. With a well-kept subscriber list and automatic stock alerts, keep your consumers interested and boost sales.

= Features =

☛ Automated Customer Notifications: Automatically notifies customers via email when an out-of-stock product becomes available again.
☛ Admin Notification Management: A user-friendly admin interface allows store owners to manage subscriptions, view notifications, and oversee stock alerts.
☛ Product Notification Listing: Displays a list of products with active stock alerts and their respective subscribers.
☛ CSV Export Functionality: Export subscriber lists and product notification data in CSV format for external use and reporting.
☛ Customizable Email Templates: Tailor email templates to match your store’s branding and communication style for personalized notifications.
☛ Rate Limiting for Notifications: Prevents excessive notifications by controlling the frequency of stock alert emails sent to customers

== Installation ==

= Minimum Requirements =

* PHP version 5.6.0 or greater (PHP 7.4 or greater is recommended)
* MySQL version 5.0 or greater (MySQL 5.6 or greater is recommended)


= Automatic installation =

The automatic installation is the easiest way to install any plugin in WordPress. You can perform an automatic installation of logging in to your WordPress dashboard, navigating to the “Plugins” menu and click on the "Add New" button.

This will open up a page showing all the available plugins in WordPress. In the search field, type Product Banner Image. The search result will show you our Product Banner Image plugin, you can then see the detailed info by clicking on "More Details" and to install just click on the "Install Now" button.


= Manual installation =

Go to Dashboard > Plugins > Add New, then upload alertx.zip file and click Install Now.

== Frequently Asked Questions ==

= Q. Where can I get support? =
A. You can get support by posting on the support section of this plugin on WordPress plugin directory, or on the support mail: hello@thebitcraft.com

= Q. Can I use my existing WordPress theme? =
A. Sure, you can use your existing WordPress theme with Enhanced AlertX.

= Q. Where can I report a bug? =
A. Found a bug? Please let us know by posting on the support section of this plugin on WordPress plugin directory or directly on our support mail: hello@thebitcraft.com


== Screenshots ==
1. screenshot-1.png
2. screenshot-2.png
3. screenshot-3.png
4. screenshot-4.png
5. screenshot-5.png


== Changelog ==

= 1.1.0 [04/01/2026] =
* **Bug Fix:** Fixed critical issue where confirmed subscribers weren't receiving email notifications when products came back in stock
* **Security:** Added comprehensive nonce verification to all AJAX handlers and forms
* **Code Quality:** Fixed all WordPress PHP_CodeSniffer warnings for full compliance
* **Improvement:** Added translators comments for all internationalization placeholders
* **Security:** Enhanced SQL escaping and database query handling throughout the plugin
* **Security:** Proper output escaping added to all user-facing messages
* **UX:** Added feature to hide other plugin admin notices on plugin pages for cleaner interface
* **Improvement:** Better error messages with specific feedback for security failures
* **Enhancement:** JavaScript validation to check `notify_ajax` object availability
* **Refactoring:** Organized phpcs comments with class-level disable/enable patterns
* **Enhancement:** Double opt-in subscription flow (pending → confirmed)
* **Enhancement:** Unsubscribe endpoint and `{unsubscribe_url}` placeholder in email template
* **Enhancement:** Send back-in-stock alerts only to confirmed subscribers
* **Enhancement:** Variation-level (color/size) notifications on product pages
* **Enhancement:** Automatic DB schema upgrade for `status` and `token` columns on load
* **Enhancement:** Frontend JS updated to detect selected variation and toggle UI accordingly

= 1.0.1 [13/05/2025] =
* Bug Fixed
* CSS issue fixed

= 1.0.0 [19/09/2024] =
* Initial version released

== Upgrade Notice ==
Nothing here
