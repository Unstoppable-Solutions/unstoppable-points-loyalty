# Unstoppable Points & Loyalty for WooCommerce

[![WordPress Plugin Version](https://img.shields.io/wordpress/plugin/v/unstoppable-points-loyalty.svg)](https://wordpress.org/plugins/unstoppable-points-loyalty/)
[![WordPress Plugin Downloads](https://img.shields.io/wordpress/plugin/dt/unstoppable-points-loyalty.svg)](https://wordpress.org/plugins/unstoppable-points-loyalty/)
[![License: GPL v2+](https://img.shields.io/badge/License-GPLv2+-blue.svg)](https://www.gnu.org/licenses/gpl-2.0)

A lightweight points-and-rewards program for WooCommerce. Customers earn points on purchases and redeem them for discounts at checkout — no bolted-on third-party service, no monthly fee, no SaaS lock-in.

> **End users:** install from the WordPress Plugin Directory — [wordpress.org/plugins/unstoppable-points-loyalty](https://wordpress.org/plugins/unstoppable-points-loyalty/)

## Features

- **Earn on purchases** — configurable points-per-dollar
- **Redeem for discounts** — customer picks how many points to apply at checkout
- **Customer dashboard** — My Account page shows balance and history
- **Admin adjustments** — manually credit or debit points for a customer
- **Points for actions** — earn on registration, review, referral (optional)
- **No external service** — all data stored on your site

## Development

### Release workflow

Releases are deployed to WordPress.org automatically by GitHub Actions:

1. Finish the feature work on `main`
2. Bump the version in `unstoppable-points-loyalty.php` (header + any constant), `readme.txt` (Stable tag), and add a Changelog entry
3. Commit and push `main`
4. On GitHub, create a new **Release** tagged `vX.Y.Z` — the deploy workflow fires and pushes to WordPress.org SVN

### CI

Every push and pull request runs the official WordPress Plugin Check against the codebase.

## License

GPL-2.0-or-later. See the `License` header in `unstoppable-points-loyalty.php`.

## Author

Developed and maintained by [Unstoppable Solutions](https://unstoppablesolutions.app).
