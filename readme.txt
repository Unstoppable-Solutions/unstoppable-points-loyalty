=== Unstoppable Points & Loyalty for WooCommerce ===
Contributors: paramedic192
Tags: woocommerce, points, loyalty, rewards, discount
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.1.4
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A simple yet powerful points and loyalty system for WooCommerce. Reward customers for purchases and let them redeem points for discounts.

== Description ==

**Unstoppable Points & Loyalty for WooCommerce** is a lightweight loyalty program plugin that helps you reward customers for their purchases and increase customer retention.

= Key Features =

* **Earn Points on Purchases** - Customers automatically earn points when they complete orders
* **Redeem Points at Checkout** - Customers can apply their points for instant discounts
* **My Account Integration** - Customers can view their points balance and history
* **Customizable Ratios** - Set your own earning and redemption rates
* **Email Notifications** - Customers receive emails when they earn points
* **Simple Setup** - Get started in minutes with minimal configuration

= How It Works =

1. **Earning**: When a customer completes a purchase, they earn points based on order total
2. **Viewing**: Customers can see their balance in My Account → Rewards
3. **Redeeming**: At checkout, customers can apply points for a discount

= Upgrade to Pro =

Need more features? Check out [Unstoppable Points & Rewards Pro](https://unstoppablesolutions.app/PluginDetail?slug=unstoppable-points-rewards) for:

* **Membership Tiers** - Bronze, Silver, Gold levels with point multipliers
* **Referral Program** - Reward customers for referring friends
* **Points Expiration** - Set points to expire after a certain period
* **Spin Wheel** - Gamification with a spin-to-win wheel
* **Bonus Actions** - Points for signups, reviews, birthdays, daily login
* **Product-Level Overrides** - Set custom point values per product
* **Advanced Reporting** - Detailed analytics and insights
* **Priority Support** - Get help when you need it

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/unstoppable-points-loyalty/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to WooCommerce → Settings → Points & Loyalty to configure

== Frequently Asked Questions ==

= How do customers earn points? =

Customers earn points automatically when their order status changes to "completed". The points earned are based on the order total multiplied by your earning ratio.

= How do customers redeem points? =

Customers can apply their points at checkout. A "Redeem Points" section appears where they can enter the number of points to use for a discount.

= Can I customize the points labels? =

Yes! Go to WooCommerce → Settings → Points & Loyalty and change the singular/plural labels to whatever you prefer (e.g., "Coin/Coins", "Star/Stars").

= Is this compatible with WooCommerce HPOS? =

Yes, this plugin is fully compatible with WooCommerce High-Performance Order Storage (HPOS).

= Where can customers see their points? =

Customers can view their points balance and history in My Account → Rewards.

== Screenshots ==

1. Points & Loyalty settings page
2. Customer rewards page in My Account
3. Points redemption at checkout
4. Points earned email notification

== Changelog ==

= 1.1.4 =
* Updated brand URLs from unstoppablewp.com to unstoppablesolutions.app throughout the plugin (Plugin URI, Author URI, and Options Studio dashboard footer links)
* Updated footer link text from "Visit Unstoppable WP" to "Visit Unstoppable Solutions" to match the new destination

= 1.1.3 =
* SECURITY: Closed a TOCTOU race condition that allowed a customer to redeem the same points across two concurrent checkouts and receive a duplicate discount
* SECURITY: `deduct_points()` is now atomic per user — a MySQL advisory lock serializes concurrent redemptions so the balance check and decrement cannot race
* SECURITY: If point deduction fails at order placement (e.g. balance was consumed by a concurrent checkout), the order is now put on-hold with an explanatory note instead of silently keeping the discount
* SECURITY: The `apply_points` AJAX endpoint now enforces the cart-total cap server-side; previously the cap was only enforced via the form's HTML max attribute, so customers could submit more points than the cart was worth and lose the excess

= 1.1.2 =
* SECURITY: Escape all values returned by shortcode callbacks `[uprf_points_balance]` and `[uprf_points_history]` (per WordPress.org plugin review)
* Wrapped `number_format_i18n()` return values in `esc_html()` before output
* Wrapped class attribute and sign output in `esc_attr()` / `esc_html()` for consistency

= 1.1.1 =
* Maintenance release for directory resubmission under the new name and slug
* Verified compliance: no external service calls, upgrade prompts limited to the plugin's own settings page

= 1.1.0 =
* Renamed plugin to "Unstoppable Points & Loyalty for WooCommerce" (slug: unstoppable-points-loyalty)
* Updated text domain to match new slug
* No functional changes — settings, points balances, and customer data are preserved

= 1.0.7 =
* Use phpcs:disable/enable block for database query

= 1.0.6 =
* Fixed inline phpcs ignore for database queries

= 1.0.5 =
* Fixed phpcs ignore placement for SQL query

= 1.0.4 =
* Fixed Plugin Check escaping and phpcs warnings

= 1.0.3 =
* Updated WordPress tested up to 7.0

= 1.0.2 =
* Updated Pro upgrade links

= 1.0.1 =
* Added Options Studio integration
* Dashboard with stats overview
* Tab-based settings interface

= 1.0.0 =
* Initial release

== Upgrade Notice ==

= 1.1.4 =
Brand URL update only — no functional changes.

= 1.1.3 =
Important security update. Fixes a race condition that allowed double-spending of redeemed points across concurrent checkouts. All users should update.

= 1.1.2 =
Security hardening: shortcode output is now properly escaped. Recommended for all users.

= 1.1.1 =
Maintenance release for directory resubmission. No functional changes.

= 1.1.0 =
Plugin renamed to "Unstoppable Points & Loyalty for WooCommerce". No functional changes — all settings and customer data are preserved.

= 1.0.7 =
* Use phpcs:disable/enable block for database query

= 1.0.6 =
* Fixed inline phpcs ignore for database queries

= 1.0.5 =
* Fixed phpcs ignore placement for SQL query

= 1.0.4 =
* Fixed Plugin Check escaping and phpcs warnings

= 1.0.3 =
* Updated WordPress tested up to 7.0

= 1.0.2 =
* Updated Pro upgrade links

= 1.0.1 =
* Added Options Studio integration
* Dashboard with stats overview
* Tab-based settings interface

= 1.0.0 =
Initial release.
