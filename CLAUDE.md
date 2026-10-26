# Plenty — WooCommerce theme

Custom **classic** (PHP template, non-block) WordPress theme for a WooCommerce store. Runs locally in [Local](https://localwp.com) at `~/Local Sites/plenty/app/public`; this repo is only the theme folder (`wp-content/themes/plenty`).

## Stack

- **WordPress** classic theme, PHP 8.1+ (Local runs PHP 8.2). No `theme.json`, no block templates.
- **WooCommerce** — declared via `add_theme_support( 'woocommerce' )` plus gallery zoom/lightbox/slider.
- **Tailwind CSS v4** via `@tailwindcss/cli` — CSS-first config, no `tailwind.config.js`.
- **ACF (free)** for editable content. Field groups are stored as JSON in `acf-json/`.

## Commands

```sh
npm install      # once
npm run dev      # watch src/css/app.css -> dist/app.css
npm run build    # minified production build
```

`dist/` is gitignored — run `npm run build` before deploying. `inc/enqueue.php` skips the stylesheet if `dist/app.css` is missing, and cache-busts with `filemtime`.

## Design

The layout is built from the Figma file **Plenty Website Design** (`sR6A87o4eDsGX9DwuOH9JB`), home page frame node `10:89` and product page frame node `31:7786`, cart drawer node `33:9628`, Contact `53:86`, About `55:1331`, designed at 1440px wide. Colours, fonts and spacing are hard-coded from the design (tokens in `src/css/app.css` `@theme`); merchants edit text and images only, not styles.

## Layout

```
style.css                 Theme header only (no styles)
functions.php             Defines PLENTY_* constants, requires inc/*
inc/setup.php             Theme supports, WooCommerce support, nav menus (primary, footer_1–3)
inc/enqueue.php           Inter (Google Fonts), dist/app.css, assets/js/theme.js
inc/acf.php               ACF JSON save/load paths, plenty_field(), default-as-placeholder filter
inc/content.php           Design defaults + plenty_content() / plenty_content_image() accessors
inc/template-tags.php     plenty_asset_img(), plenty_logo(), ACF link helpers
inc/woocommerce.php       WooCommerce hooks (only loaded when WooCommerce is active)
inc/cart-drawer.php       Cart drawer: wc-ajax endpoints, cart fragment, render helpers
inc/contact-form.php      Contact form handler (admin-post, wp_mail, honeypot, rate limit)
header.php / footer.php   Site header (logo, primary menu, cart) and footer
front-page.php            Home page: composes template-parts/home/*
template-parts/home/      hero, features (benefit cards), products (grid + tabs), split (hotel/factory)
template-parts/product/   summary, gallery, buy-panel, sensation, stories, faq (product page sections)
template-parts/sections/  support (shared by home and product pages)
template-parts/cart/      drawer (shell), drawer-content (re-rendered as a cart fragment)
template-parts/contact/   form
template-parts/about/     intro (who we are + stats), values
template-parts/footer/    payment-methods
template-parts/product-card.php  Home grid card
page.php                  Default page (also renders Cart / Checkout / My Account)
page-templates/           contact.php, about.php — page templates ("Template Name"), chosen in the page editor
index.php                 Fallback: blog, archives, search
acf-json/                 ACF field group JSON (committed)
assets/images/            Images and icons exported from Figma (defaults for every image field)
assets/js/theme.js        Mobile nav, product filter tabs, product gallery, FAQ "Show all", contact form (plain JS, no build)
assets/js/cart-drawer.js  Cart drawer open/close, AJAX add to cart, quantity/remove, loading states
src/css/app.css           Tailwind entry + design tokens
woocommerce/single-product.php  Product page (replaces WooCommerce's default single-product layout)
```

## Conventions

- Prefix all functions with `plenty_`; text domain is `plenty`. Every PHP file starts with `defined( 'ABSPATH' ) || exit;`.
- Escape all output (`esc_html`, `esc_attr`, `esc_url`, `wp_kses_post`).
- Layout containers: `container-site` (1240px content, 100px gutters at 1440) and `container-narrow` (1120px, 160px gutters), defined as `@utility` in `app.css`.
- Style with Tailwind utility classes directly in the PHP templates. Use `@layer components` in `src/css/app.css` only for markup we don't control (e.g. `the_content()` output via `.prose-content`, WooCommerce markup).
- **Tailwind sources are explicit.** `app.css` uses `source(none)` and lists folders with `@source` (root `*.php`, `inc`, `template-parts`, `page-templates`, `woocommerce`, `assets/js`). If you add a new folder containing classes, add an `@source` line or its classes won't be generated.
- **WooCommerce CSS is unlayered, Tailwind's is layered**, so on WooCommerce pages WooCommerce rules beat Tailwind utilities regardless of specificity. `app.css` rolls back WooCommerce's `img { height: auto }` with `revert-layer`, and styles WooCommerce's own markup (the add-to-cart form) with plain unlayered CSS prefixed `body.single-product div.plenty-buy` to outrank `.woocommerce div.product form.cart …`. Do the same for any other WooCommerce markup you restyle.

## ACF

- ACF **free** only: no repeater, flexible content, gallery, clone fields or options pages (those are Pro). Design field groups around this — e.g. use multiple plain fields, or a custom post type for repeatable items.
- Edit field groups in **WP Admin → ACF**; saving writes the JSON to `acf-json/` automatically. Commit those files. After pulling JSON changes, use the "Sync available" link in ACF to update the DB copy.
- **Where content lives** (ACF free has no options pages):
  - `group_plenty_home` ("Home Page", tabbed per section) and `group_plenty_site_settings` ("Site Settings (Footer & Cart)", tabs Footer / Cart drawer) are both attached to the page set as the static front page (Settings → Reading). Site-wide footer content is therefore edited on the home page.
  - `group_plenty_product_settings` ("Product Page Settings (All Products)") is also on the front page: buy-panel labels, installment plans, payment logos, sleep specialist box, the three story rows and FAQ labels — shared by every product page.
  - Per product: `group_plenty_product_card` ("Home Page Card": card text + three feature lines; icons fixed per position: bed, shield, snowflake) and `group_plenty_product_page` ("Product Page": tagline, video URL, firmness 1–4, six Sensation features, ten FAQ items). Product sections without data are hidden.
  - Page templates have their own groups, attached by template: `group_plenty_contact_page` (Contact: intro, form labels, recipient email) and `group_plenty_about_page` (About: intro, stats, values, FAQ). Read them with `plenty_content( 'name', $page_id )` / `plenty_content_image( ..., $page_id )` — the optional post ID defaults to the front page.
  - The About page reuses the story rows (Product Page Settings), the product grid without tabs and "Sleep Specialists" (Home Page); `template-parts/product/faq.php` takes `post_id` + `prefix` (`page_faq`) for page FAQs. Contact social links are the Site Settings Facebook/Instagram/X URLs.
  - The product page reuses home content: benefits under the gallery are home benefit cards 2–4; the Chat/Call buttons use the home support cards 1 and 3 links; the support section is the same template part.
  - Header and footer link columns are WordPress menus (`primary`, `footer_1`–`footer_3`); a footer column's heading is the menu's name.
  - The header logo uses the Customizer logo if set, otherwise the bundled Plenty wordmark.
- Repeating items (benefit cards, support cards, bullets, payment logos) are fixed numbered fields (`feature_1_title` … `feature_4_title`), not repeaters. An empty title hides that item.
- **Defaults:** every home/site field falls back to the design copy in `plenty_content_defaults()` and to bundled images in `plenty_image_defaults()` (`inc/content.php`). Empty text fields show their default as placeholder in the editor. When adding a field: add it to `acf-json`, add its default, and read it with `plenty_content()` / `plenty_content_image()`.
- Product templates read fields with `plenty_field( 'name', $fallback, $post_id )` rather than `get_field()` so pages still render when ACF is inactive or a field is empty.
- Button/link fields are ACF **link** fields (array with `title`, `url`, `target`); render them with `plenty_link_attrs()` and `plenty_link_title()`.

## WooCommerce

- The home product grid (`template-parts/home/products.php`) lists real products; filter tabs are the categories picked in the Home Page → Products tab (or all top-level categories). Filtering is client-side in `theme.js`, matching parent categories too.
- The product page (`woocommerce/single-product.php`) uses its own gallery (product image + gallery images), so the `wc-product-gallery-*` theme supports are off and variation images don't swap the main image. It renders WooCommerce's add-to-cart form (quantity hidden per design) and calls `WC()->structured_data->generate_product_data()` itself because the default summary hooks aren't used. Reviews, related products and upsells aren't in the design and aren't rendered.
- **Contact form** (`inc/contact-form.php`): posts to `admin-post.php?action=plenty_contact`, validates, and sends with `wp_mail()` to the Contact page's "Send messages to" (default admin email), Reply-To the sender. `theme.js` submits with fetch (`ajax=1` → JSON with per-field errors); without JS it redirects back with `?contact=sent|error`. No nonce by design (guest nonces change when WooCommerce starts a session); spam protection is a honeypot field (`website`) and 5 messages / 10 min per IP. Locally, mail lands in Local's Mailpit. Production needs working mail (an SMTP plugin).
- **Cart drawer** (`inc/cart-drawer.php`, `assets/js/cart-drawer.js`): the header cart icon and the product page add-to-cart open an off-canvas cart instead of navigating. The product form is posted to `?wc-ajax=plenty_add_to_cart` with `add-to-cart` renamed to `plenty_add_to_cart` (so WooCommerce's own `wp_loaded` handler, which can redirect, doesn't also run); the endpoint calls `WC_Form_Handler::add_to_cart_action()` with redirects disabled, so all product types and validation behave like the normal form. `?wc-ajax=plenty_update_cart_item` sets a line's quantity (0 removes). Both return `{ fragments, cart_hash, error }`; the drawer content is the `div[data-cart-drawer-content]` fragment, so keep `template-parts/cart/drawer-content.php` renderable from the cart alone. WooCommerce's archive AJAX buttons (`added_to_cart` jQuery event) also open it. The drawer is not rendered on the cart and checkout pages. Loading states: the add button gets `aria-busy` (spinner via CSS), the drawer root gets `aria-busy` (overlay with message).
- Free-shipping banner/"Shipping: Free" in the drawer use the "Free shipping from" setting (Site Settings → Cart drawer; 0 = always free), not WooCommerce's shipping zones — keep them in line.
- Content wrappers and the sidebar are replaced in `inc/woocommerce.php` so shop/product pages use the theme's container.
- `plenty_cart_link()` renders the header cart count; it is refreshed after AJAX add-to-cart through `woocommerce_add_to_cart_fragments`.
- To override a WooCommerce template, copy it from `plugins/woocommerce/templates/` into `woocommerce/` with the same relative path. Don't add a `woocommerce.php` root template — it bypasses those overrides.
