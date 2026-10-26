# Overview Fashion WordPress Theme

This is a classic, installable WordPress theme based on the Overview Fashion React storefront. WooCommerce provides the product catalog, product pages, cart, checkout, customer orders, and cash-on-delivery payment method.

## Install

1. Install and activate WooCommerce.
2. In WordPress, open **Appearance → Themes → Add New → Upload Theme** and upload `overview-fashion.zip`, or copy the `overview-fashion` folder into `wp-content/themes/`.
3. Activate **Overview Fashion**.
4. In **WooCommerce → Settings → Payments**, enable **Cash on delivery**. Set currency to BDT and configure shipping zones/rates for inside and outside Dhaka.
5. Add products, product categories, prices, sizes (as product attributes/variations), and product photos in WooCommerce.
6. In **Appearance → Customize → Site Identity**, set the store logo. Use **Store contact details** in the Customizer to set the phone, email, address, social links, and optional homepage cover banner.
7. Set a static homepage in **Settings → Reading** if needed. The theme's home sections are rendered by `front-page.php` on the site front page.

The shop, cart, checkout, and account pages should be created through WooCommerce's setup wizard. Delivery rates and checkout validation belong to WooCommerce configuration or a shipping/checkout extension; the React prototype's browser-only order storage is replaced by WooCommerce's persistent order handling.

## Included

- Responsive shop header, category navigation, search, and mobile menu.
- Desktop mini cart, mobile cart drawer, and a mobile cart total bar.
- Rotating homepage campaign banner (including an optional uploaded cover slide), category strip, delivery perks, newest products, and product category sections.
- Product search results, category archive sorting, variable product selection, and WooCommerce order history under My Account.
- WooCommerce product, category archive, cart, checkout, order, and account compatibility.
- Custom logo support, theme color styling, and customizable store contact/social links.

## Theme package layout

- `style.css`: WordPress theme metadata and storefront styles.
- `functions.php`: theme setup, WooCommerce support, product helpers, and Customizer controls.
- `front-page.php`: home storefront sections.
- `header.php` / `footer.php`: responsive storefront shell.
- `assets/`: local campaign images and small interaction script.
