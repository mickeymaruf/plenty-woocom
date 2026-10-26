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

## Layout

```
style.css           Theme header only (no styles)
functions.php       Defines PLENTY_* constants, requires inc/*
inc/setup.php       Theme supports, WooCommerce support, nav menus (primary, footer)
inc/enqueue.php     Front-end assets
inc/acf.php         ACF JSON save/load paths, plenty_field() helper
inc/woocommerce.php WooCommerce hooks (only loaded when WooCommerce is active)
header.php / footer.php
front-page.php      Static front page: ACF hero + featured products
page.php            Default page (also renders Cart / Checkout / My Account)
index.php           Fallback: blog, archives, search
acf-json/           ACF field group JSON (committed)
src/css/app.css     Tailwind entry
woocommerce/        (create as needed) WooCommerce template overrides
template-parts/     (create as needed) get_template_part() partials
```

## Conventions

- Prefix all functions with `plenty_`; text domain is `plenty`. Every PHP file starts with `defined( 'ABSPATH' ) || exit;`.
- Escape all output (`esc_html`, `esc_attr`, `esc_url`, `wp_kses_post`).
- Style with Tailwind utility classes directly in the PHP templates. Use `@layer components` in `src/css/app.css` only for markup we don't control (e.g. `the_content()` output via `.prose-content`, WooCommerce markup).
- **Tailwind sources are explicit.** `app.css` uses `source(none)` and lists folders with `@source` (root `*.php`, `inc`, `template-parts`, `woocommerce`, `src/js`). If you add a new folder containing classes, add an `@source` line or its classes won't be generated.
- Tailwind's preflight resets base styles; WooCommerce still loads its own stylesheets on top. If WooCommerce styles get in the way, dequeue them with the `woocommerce_enqueue_styles` filter.

## ACF

- ACF **free** only: no repeater, flexible content, gallery, clone fields or options pages (those are Pro). Design field groups around this — e.g. use multiple plain fields, or a custom post type for repeatable items.
- Edit field groups in **WP Admin → ACF**; saving writes the JSON to `acf-json/` automatically. Commit those files. After pulling JSON changes, use the "Sync available" link in ACF to update the DB copy.
- In templates, read fields with `plenty_field( 'name', $fallback )` rather than `get_field()` so pages still render when ACF is inactive or a field is empty.
- The front-page hero group (`group_plenty_front_page.json`) applies to the page set as the static front page (Settings → Reading).

## WooCommerce

- Content wrappers and the sidebar are replaced in `inc/woocommerce.php` so shop/product pages use the theme's container.
- `plenty_cart_link()` renders the header cart count; it is refreshed after AJAX add-to-cart through `woocommerce_add_to_cart_fragments`.
- To override a WooCommerce template, copy it from `plugins/woocommerce/templates/` into `woocommerce/` with the same relative path. Don't add a `woocommerce.php` root template — it bypasses those overrides.
