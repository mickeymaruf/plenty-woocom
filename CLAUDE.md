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
npm run deploy   # build, then upload the theme to production (bin/deploy.mjs)
npm run deploy -- --rollback   # swap back to the previously deployed version
npm run pull     # replace the Local site's DB, uploads and plugins with production's (bin/pull.mjs)
npm run pull -- --db-only      # database (and live ACF JSON) only
```

`dist/` is gitignored — run `npm run build` before deploying. `inc/enqueue.php` skips the stylesheet if `dist/app.css` is missing, and cache-busts with `filemtime`.

## Production

- Vultr server `64.176.26.129` (Santiago) running CloudPanel; site user `plenty`, site root `/home/plenty/htdocs/64-176-26-129.sslip.io` (temporary sslip.io domain until the real one exists). PHP 8.3, cron runs `wp-cron.php` every 5 min (`DISABLE_WP_CRON`).
- **Code goes up, content stays there.** Theme changes are made locally and shipped with `npm run deploy`; content, products, menus and plugins are managed in the live wp-admin. Never import the Local database into production once the site has real content or orders.
- `bin/deploy.mjs` streams the theme over `ssh` with `tar` (skipping `src`, `node_modules`, `.git`, `bin`, package files) into `themes/.plenty-new`, then swaps it in and keeps the old one as `themes/.plenty-prev`. Target is overridable with `DEPLOY_HOST` / `DEPLOY_THEMES_DIR`. After deploying `acf-json/` changes, click "Sync available" in WP Admin → ACF. Deploy refuses to run when the live `acf-json/` has field groups newer than the repo's (edited in the live wp-admin): pull them first, or `--force` to discard them.
- **Content comes down with `npm run pull`** (Local for Windows only; the site must be running in Local). It downloads everything first, then replaces the local DB (URLs rewritten to the Local URL), `uploads` and `plugins`, and copies live `acf-json` files with a newer `modified` into the repo. Production is only read; the rest of the theme is never touched. Replaced local data and both DB dumps go to `<Local site>/pull-backups/<timestamp>/` (outside the repo; the live dump holds customer data). After a pull, log in with the live wp-admin account, and switch payment gateways (MercadoPago Plus has live `APP_USR-` keys and no sandbox toggle) to test credentials before testing checkout.

## Design

The layout is built from the Figma file **Plenty Website Design** (`sR6A87o4eDsGX9DwuOH9JB`), home page frame node `10:89` and product page frame node `31:7786`, cart drawer node `33:9628`, Contact `53:86`, About `55:1331`, shop page `27:5519`, Hotels `76:153` (a copy of the About layout), designed at 1440px wide. Colours, fonts and spacing are hard-coded from the design (tokens in `src/css/app.css` `@theme`); merchants edit text and images only, not styles.

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
inc/shipping.php          "A coordinar" shipping method (quoted after the order)
inc/cart-drawer.php       Cart drawer: wc-ajax endpoints, cart fragment, render helpers
inc/contact-form.php      Contact form handler (admin-post, wp_mail, honeypot, rate limit)
header.php / footer.php   Site header (logo, primary menu, cart) and footer
front-page.php            Home page: composes template-parts/home/*
template-parts/home/      hero, features (benefit cards), products (grid + tabs), split (hotel/factory)
template-parts/product/   summary, gallery, buy-panel (+ price, installments), sensation, stories, faq (product page sections)
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
bin/                      deploy.mjs, pull.mjs (production → Local); one-time seeds run with `wp eval-file`: seed-defaults.php (design defaults → ACF values), seed-menus.php (header + footer menus from Figma)
assets/images/            Images and icons exported from Figma (defaults for every image field)
assets/js/theme.js        Mobile nav, product filter tabs, product gallery, FAQ "Show all", contact form (plain JS, no build)
assets/js/cart-drawer.js  Cart drawer open/close, AJAX add to cart, quantity/remove, loading states
src/css/app.css           Tailwind entry + design tokens
woocommerce/single-product.php  Product page (replaces WooCommerce's default single-product layout)
woocommerce/single-product/add-to-cart/variable.php  Size (variation) buttons instead of dropdowns
woocommerce/archive-product.php Shop page + product category/tag archives (heading, category tabs, card grid, pagination, support)
```

## Conventions

- Prefix all functions with `plenty_`; text domain is `plenty`. Every PHP file starts with `defined( 'ABSPATH' ) || exit;`.
- **The store is Spanish-only (Argentina, voseo: "Seleccioná", "vos").** Theme strings are written in Spanish directly in the `__()` calls (no `.po`/`.mo` for `plenty`). WordPress, WooCommerce and ACF are translated by their `es_AR` language packs: the site language must be Español de Argentina (Settings → General), locally and on production.
- Escape all output (`esc_html`, `esc_attr`, `esc_url`, `wp_kses_post`).
- Layout containers: `container-site` (1240px content, 100px gutters at 1440) and `container-narrow` (1120px, 160px gutters), defined as `@utility` in `app.css`.
- Style with Tailwind utility classes directly in the PHP templates. Use `@layer components` in `src/css/app.css` only for markup we don't control (e.g. `the_content()` output via `.prose-content`, WooCommerce markup).
- **Tailwind sources are explicit.** `app.css` uses `source(none)` and lists folders with `@source` (root `*.php`, `inc`, `template-parts`, `page-templates`, `woocommerce`, `assets/js`). If you add a new folder containing classes, add an `@source` line or its classes won't be generated.
- **WooCommerce CSS is unlayered, Tailwind's is layered**, so on WooCommerce pages WooCommerce rules beat Tailwind utilities regardless of specificity. `app.css` rolls back WooCommerce's `img { height: auto }` with `revert-layer`, and styles WooCommerce's own markup (the add-to-cart form) with plain unlayered CSS prefixed `body.single-product div.plenty-buy` to outrank `.woocommerce div.product form.cart …`. Do the same for any other WooCommerce markup you restyle.

## ACF

- ACF **free** only: no repeater, flexible content, gallery, clone fields or options pages (those are Pro). Design field groups around this — e.g. use multiple plain fields, or a custom post type for repeatable items.
- Edit field groups in **WP Admin → ACF**; saving writes the JSON to `acf-json/` automatically. Commit those files. After pulling JSON changes, use the "Sync available" link in ACF to update the DB copy.
- **Where content lives** (ACF free has no options pages):
  - `group_plenty_home` ("Home Page", tabbed per section) and `group_plenty_site_settings` ("Site Settings (Footer & Cart)", tabs Footer / Cart & shipping) are both attached to the page set as the static front page (Settings → Reading). Site-wide footer content is therefore edited on the home page.
  - `group_plenty_product_settings` ("Product Page Settings (All Products)") is also on the front page: buy-panel labels, installment plans, payment logos, sleep specialist box, the three story rows and FAQ labels — shared by every product page.
  - Both site-wide groups start collapsed on the front page editor (`plenty_acf_collapse_site_wide_groups()` in `inc/acf.php` closes them once per user; after that WordPress remembers each user's own open/closed choice).
  - Per product: `group_plenty_product_card` ("Home Page Card": card text + three feature lines; icons fixed per position: bed, shield, snowflake) and `group_plenty_product_page` ("Product Page": tagline, video URL, firmness 1–4, six Sensation features, ten FAQ items). Product sections without data are hidden.
  - Page templates have their own groups, attached by template: `group_plenty_contact_page` (Contact: intro, form labels, recipient email) and `group_plenty_about_page` (About: intro, stats, values, FAQ). Read them with `plenty_content( 'name', $page_id )` / `plenty_content_image( ..., $page_id )` — the optional post ID defaults to the front page.
  - The About page reuses the story rows (Product Page Settings), the product grid without tabs and "Sleep Specialists" (Home Page); `template-parts/product/faq.php` takes `post_id` + `prefix` (`page_faq`) for page FAQs. Contact social links are the Site Settings Facebook/Instagram/X URLs.
  - The Hotels page (`/hotels/`, slug `hotels`) is a page using the About template, so its intro, stats, values and FAQ are its own fields. The home "Explore Hotel Line" button defaults to that page when it is published.
  - The product page reuses home content: benefits under the gallery are home benefit cards 2–4; the Chat/Call buttons use the home support cards 1 and 3 links; the support section is the same template part.
  - Header and footer link columns are WordPress menus (`primary`, `footer_1`–`footer_3`); a footer column's heading is the menu's name. `bin/seed-menus.php` creates the design's menus where a location is still empty — header (Colchones, Quienes Somos, Contacto) and the footer columns Habitación, Ayuda, Legales; links without a published target page (Shop, About/Contact template pages, privacy, terms, blog) are `#`.
  - The header logo uses the Customizer logo if set, otherwise `assets/images/logo.png` (Figma node 6:52 exported at 4×, white made transparent; an `<img>` is sharper than the CSS-mask `plenty_logo()`, which the footer still uses in white).
- Repeating items (benefit cards, support cards, bullets, payment logos) are fixed numbered fields (`feature_1_title` … `feature_4_title`), not repeaters. An empty title hides that item.
- **Defaults:** every home/site field falls back to the design copy in `plenty_content_defaults()` and to bundled images in `plenty_image_defaults()` (`inc/content.php`). Empty text fields show their default as placeholder in the editor; empty image fields show the bundled default under the field (`plenty_acf_default_image_preview()`). `bin/seed-defaults.php` stores the defaults as real values (empty fields only; SVG icons are imported only when the **Safe SVG** plugin is active — it allows, sanitizes and sizes SVG uploads, without it WordPress rejects them and renders no `<img>`; the Affirm logo stays empty because the template sizes the bundled file differently). When adding a field: add it to `acf-json`, add its default, and read it with `plenty_content()` / `plenty_content_image()`.
- Product templates read fields with `plenty_field( 'name', $fallback, $post_id )` rather than `get_field()` so pages still render when ACF is inactive or a field is empty.
- Button/link fields are ACF **link** fields (array with `title`, `url`, `target`); render them with `plenty_link_attrs()` and `plenty_link_title()`.

## WooCommerce

- The home product grid (`template-parts/home/products.php`) lists real products; filter tabs are the categories picked in the Home Page → Products tab (or all top-level categories). Filtering is client-side in `theme.js`, matching parent categories too. Every tab has the same padding (labels never move); the active background is one `[data-filter-pill]` that `theme.js` slides/resizes behind the active tab (300ms ease-out, none with reduced motion). Until the script runs, the active tab paints its own `aria-pressed:bg-navy`, which the script then removes.
- The shop page and category archives (`woocommerce/archive-product.php`) use the same heading, cards and tab categories (`plenty_product_filter_categories()`), but the tabs are links to the category archives (current one `aria-current`; a subcategory highlights its parent's tab), so filtering works with pagination. Products per page = Customizer → WooCommerce → Product Catalog, columns × rows (theme default 3 × 4 as in the design). Result count and sorting dropdown are removed; tag/attribute/search archives show WooCommerce's page title instead of the design heading.
- The product page (`woocommerce/single-product.php`) uses its own gallery (product image + gallery images), so the `wc-product-gallery-*` theme supports are off and variation images don't swap the main image. It renders WooCommerce's add-to-cart form (quantity hidden per design) and calls `WC()->structured_data->generate_product_data()` itself because the default summary hooks aren't used. Reviews, related products and upsells aren't in the design and aren't rendered.
- **Price and installments** (`template-parts/product/price.php`, `installments.php`, prices from `plenty_product_prices()`): the badge is the discount calculated from regular vs. sale price; the "beneficios bancarios" box shows up to three plans from Product Page Settings (payments + % discount: price × (1 − discount) ÷ payments, e.g. 12 × price/12, 6 × (price − 20 %)/6, 1 × price − 30 %). A variable product shows its cheapest variation until a size is picked.
- **Sizes** are WooCommerce variations. `variable.php` renders each attribute as option buttons (radios; second line = the global attribute term's description, e.g. Products → Attributes → Medida → "160 × 200 cm" / "Queen") and keeps WooCommerce's `<select>` hidden, so its variation script still drives stock, variation ID and add to cart; `theme.js` mirrors radios ↔ selects through WooCommerce's jQuery events. Each variation's data carries its rendered price and installments markup (`plenty_price` / `plenty_installments`, from `woocommerce_available_variation`), swapped into the `[data-variation-html]` wrappers when it's found. Out-of-stock variations are inactive (`woocommerce_variation_is_active`), so their buttons are disabled. The product's "Default form values" pick the initial size, otherwise the first in-stock variation.
- **Contact form** (`inc/contact-form.php`): posts to `admin-post.php?action=plenty_contact`, validates, and sends with `wp_mail()` to the Contact page's "Send messages to" (default admin email), Reply-To the sender. `theme.js` submits with fetch (`ajax=1` → JSON with per-field errors); without JS it redirects back with `?contact=sent|error`. No nonce by design (guest nonces change when WooCommerce starts a session); spam protection is a honeypot field (`website`) and 5 messages / 10 min per IP. Locally, mail lands in Local's Mailpit. Production needs working mail (an SMTP plugin).
- **Cart drawer** (`inc/cart-drawer.php`, `assets/js/cart-drawer.js`): the header cart icon and the product page add-to-cart open an off-canvas cart instead of navigating. The product form is posted to `?wc-ajax=plenty_add_to_cart` with `add-to-cart` renamed to `plenty_add_to_cart` (so WooCommerce's own `wp_loaded` handler, which can redirect, doesn't also run); the endpoint calls `WC_Form_Handler::add_to_cart_action()` with redirects disabled, so all product types and validation behave like the normal form. `?wc-ajax=plenty_update_cart_item` sets a line's quantity (0 removes). Both return `{ fragments, cart_hash, error }`; the drawer content is the `div[data-cart-drawer-content]` fragment, so keep `template-parts/cart/drawer-content.php` renderable from the cart alone. WooCommerce's archive AJAX buttons (`added_to_cart` jQuery event) also open it. The drawer is not rendered on the cart and checkout pages. Loading states: the add button gets `aria-busy` (spinner via CSS), the drawer root gets `aria-busy` (overlay with message).
- **Shipping is "A coordinar"** (quoted after the order; the store contacts the customer): `inc/shipping.php` registers the `plenty_quote` shipping method ($0), added to the Argentina zone in WooCommerce → Settings → Shipping. Its label and note are Site Settings fields (`shipping_quote_label`, `shipping_quote_note`), shown at checkout/cart (note via `woocommerce_after_shipping_rate`), in orders and emails (rate label), and in the drawer. While the method is enabled in any zone (`plenty_shipping_is_quoted()`), the drawer shows the note instead of the free-shipping banner and "A coordinar" as its shipping value; otherwise the banner/"Shipping: Free" use the "Free shipping from" setting (0 = always free), not WooCommerce's zones — keep them in line.
- **Cart and Checkout are WooCommerce's classic (shortcode) pages**, not the Cart/Checkout blocks: the block checkout can't show "A coordinar" in place of the shipping price, and the classic one is styled/extended in PHP like the rest of the theme. Layout: customer details and payment (moved out of `#order_review` to `woocommerce_checkout_after_customer_details`) on the left, the order summary (wrapped in `.plenty-checkout-summary`) on the right and sticky from `lg`; on phones details → summary → payment (CSS grid in `app.css`). Payment is **MercadoPago Plus** (CRPlugins, gateway `wc_mp_plus_gateway_bricks`); its installment plans and surcharges/discounts are set in its own settings (WP Admin → MercadoPago Plus → Configuración) and should match the product page's installments box. Production uses live credentials: a test purchase there is a real payment.
- Content wrappers and the sidebar are replaced in `inc/woocommerce.php` so shop/product pages use the theme's container.
- `plenty_cart_link()` renders the header cart count; it is refreshed after AJAX add-to-cart through `woocommerce_add_to_cart_fragments`.
- To override a WooCommerce template, copy it from `plugins/woocommerce/templates/` into `woocommerce/` with the same relative path. Don't add a `woocommerce.php` root template — it bypasses those overrides.
