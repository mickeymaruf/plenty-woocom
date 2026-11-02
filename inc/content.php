<?php
/**
 * Merchant-editable content: design defaults and accessors.
 *
 * Home page sections and site-wide header/footer content are ACF fields on the page set as
 * the static front page (ACF free has no options pages). Every field falls back to the
 * Figma design copy/imagery below, so the site renders as designed before anything is edited.
 *
 * @package Plenty
 */

defined( 'ABSPATH' ) || exit;

/**
 * Default text and link values, keyed by ACF field name. Links use the ACF link array shape.
 */
function plenty_content_defaults(): array {
	static $defaults = null;

	if ( null !== $defaults ) {
		return $defaults;
	}

	$shop   = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
	$hotels = get_page_by_path( 'hotels' ); // Hotels page (About template), if one exists.
	$hotels = $hotels && 'publish' === $hotels->post_status ? get_permalink( $hotels ) : '#';

	$defaults = array(
		// Hero.
		'hero_title'             => 'Discover the Plenty difference.',
		'hero_text'              => 'We make sleep easy. Every mattress is designed, engineered and tested to solve real sleep needs. Take our 2-minute quiz and change your sleep for good.',
		'hero_button'            => plenty_default_link( 'Take the quiz', $shop ),

		// Feature strip.
		'feature_1_title'        => 'From as low as $35/mo*',
		'feature_1_link'         => plenty_default_link( 'Prequalify Now' ),
		'feature_2_title'        => '365-Night Home Trial',
		'feature_3_title'        => 'Forever Warranty™',
		'feature_4_title'        => 'Free Shipping & Returns',

		// Products.
		'products_title'         => 'Find your perfect mattress',
		'products_all_label'     => 'All',
		'products_limit'         => 12,
		'products_price_label'   => 'Starting From',
		'products_button_label'  => 'Shop Now',
		'products_compare'       => plenty_default_link( 'Compare Mattresses', $shop ),

		// Support.
		'support_title'          => 'Sleep Specialists Available 24/7',
		'support_1_title'        => 'WhatsApp',
		'support_1_link'         => plenty_default_link( 'Message us' ),
		'support_2_title'        => 'Email',
		'support_2_link'         => plenty_default_link( 'Send us an email' ),
		'support_3_title'        => 'Call Us',
		'support_3_link'         => plenty_default_link( '+1 (888) 863-2827', 'tel:+18888632827' ),

		// Hotel line.
		'hotel_eyebrow'          => 'For Hotels & Hospitality',
		'hotel_title'            => 'The Plenty Hotel Line',
		'hotel_text'             => 'The same comfort your guests remember, built to hotel standards. Durable, easy-care mattresses designed for daily use — with volume pricing and delivery for properties of every size.',
		'hotel_bullet_1'         => 'Commercial-grade durability',
		'hotel_bullet_2'         => 'Volume pricing for hotels and rentals',
		'hotel_bullet_3'         => 'Dedicated account support',
		'hotel_button'           => plenty_default_link( 'Explore Hotel Line', $hotels ),

		// Factory.
		'factory_eyebrow'        => 'Made by us',
		'factory_title'          => 'Made in Our Own Factory',
		'factory_text'           => 'Every Plenty mattress is designed, built and quality-checked by our own team. No middlemen — just better materials, careful craftsmanship and a fair price.',
		'factory_bullet_1'       => 'Designed and assembled in-house',
		'factory_bullet_2'       => 'Quality-checked by hand',
		'factory_bullet_3'       => 'Factory-direct pricing',
		'factory_button'         => plenty_default_link( 'See Our Story' ),

		// Product page: buy panel.
		'pdp_add_to_cart_label'    => 'Add to Cart',
		'pdp_discount_badge'       => '{percent}% OFF',
		'pdp_pretax_label'         => 'Precio sin impuestos nacionales:',
		'pdp_size_heading'         => 'Seleccioná la medida',
		'pdp_benefits_title'       => '¡Aprovechá los beneficios bancarios!',
		'pdp_plan_1_payments'      => 12,
		'pdp_plan_1_discount'      => 0,
		'pdp_plan_2_payments'      => 6,
		'pdp_plan_2_discount'      => 20,
		'pdp_plan_3_payments'      => 1,
		'pdp_plan_3_discount'      => 30,
		'pdp_installments_label'   => 'cuotas',
		'pdp_installments_text'    => 'sin interés de',
		'pdp_single_label'         => 'pago',
		'pdp_single_text'          => 'de',
		'pdp_payments_button'      => plenty_default_link( 'Ver cuotas y medios de pago' ),
		'pdp_specialist_title'     => 'Available 24/7',
		'pdp_specialist_text'      => 'Our Sleep Specialists are here for you.',
		'pdp_chat_label'           => 'Chat',
		'pdp_call_label'           => 'Call',
		'pdp_video_label'          => 'Watch Video',

		// Product page: sections.
		'pdp_sensation_title'      => 'Sensation',
		'story_1_title'            => 'Safer Sleep Starts Here',
		'story_1_text'             => 'At Plenty, every material is precision-selected for your health and comfort. Our mattresses are CertiPUR-US® certified, free from harmful chemicals, and wrapped in a cooling, breathable cover — so the only thing you feel is relief.',
		'story_1_button'           => plenty_default_link( 'Shop All Frames', $shop ),
		'story_2_title'            => 'We\'ve Got You Covered',
		'story_2_text'             => 'Every Plenty mattress comes with our Forever Warranty™ — the industry\'s most comprehensive coverage. If your mattress is ever defective, we\'ll replace it. No fine print, no expiry, no hassle. Your investment is protected for life.',
		'story_2_button'           => plenty_default_link( 'Shop All Frames', $shop ),
		'story_3_eyebrow'          => 'Accessories',
		'story_3_title'            => 'A Perfect Match',
		'story_3_text'             => 'Your mattress performs best on the right foundation. Explore our range of bed frames and adjustable bases — engineered to complement every Plenty mattress and elevate your entire sleep setup.',
		'story_3_button'           => plenty_default_link( 'Shop All Frames', $shop ),
		'pdp_faq_title'            => 'Frequently asked questions',
		'pdp_faq_showing'          => 'Showing {shown} of {total}',
		'pdp_faq_show_all'         => 'Show all',

		// Cart drawer (site settings).
		'cart_title'              => 'Your Cart',
		'cart_free_shipping_min'  => 0,
		'cart_free_shipping_done' => 'You qualify for free shipping!',
		'cart_free_shipping_todo' => 'Add {amount} more for free shipping',
		'cart_subtotal_label'     => 'Subtotal',
		'cart_shipping_label'     => 'Shipping',
		'cart_shipping_free'      => 'Free',
		'cart_shipping_pending'   => 'Calculated at checkout',
		'cart_total_label'        => 'Total',
		'cart_checkout_label'     => 'Checkout',
		'cart_trust_1'            => '365-night trial',
		'cart_trust_2'            => 'Free returns',
		'cart_trust_3'            => 'Lifetime warranty',
		'cart_empty_text'         => 'Your cart is empty.',
		'cart_empty_button'       => plenty_default_link( 'Shop mattresses', $shop ),

		// Contact page (page template "Contact").
		'contact_eyebrow'        => 'Contact Us',
		'contact_title'          => 'Get in touch with us. We\'re here to assist you.',
		'contact_name_label'     => 'Your Name',
		'contact_email_label'    => 'Email Address',
		'contact_phone_label'    => 'Phone Number (optional)',
		'contact_message_label'  => 'Message',
		'contact_button_label'   => 'Leave us a Message',
		'contact_success'        => 'Thanks for your message! We’ll get back to you soon.',

		// About page (page template "About").
		'about_title'            => 'Who we are',
		'about_text'             => 'At Plenty, we\'re here to help you create a space that reflects you and what you love. We\'ll put it all together for you. Free one-on-one design help, in our store or in your home!',
		'about_stat_1_value'     => '4.9',
		'about_stat_1_label'     => 'Average Rating',
		'about_stat_2_value'     => '200+',
		'about_stat_2_label'     => 'Sleepers served',
		'about_stat_3_value'     => '30+',
		'about_stat_3_label'     => 'Years crafting',
		'about_values_title'     => 'We are best solution for your home',
		'about_value_1_title'    => 'Considered design',
		'about_value_1_text'     => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam diam massa sapien pulvinar eu pretium arcu mauris.',
		'about_value_2_title'    => 'We Create For You',
		'about_value_2_text'     => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam diam massa sapien pulvinar eu pretium arcu mauris.',
		'about_value_3_title'    => 'Attention To Details',
		'about_value_3_text'     => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam diam massa sapien pulvinar eu pretium arcu mauris.',

		// Footer (site settings).
		'footer_address'         => 'Av. Paseo de la Reforma 296, Piso 31, Col. Juárez, alcaldía Cuauhtémoc, C.P. 06600, CDMX',
		'footer_hours'           => 'Ventas de lunes a domingo de 8:00 am a 11:00 pm. Atención a clientes de lunes a domingo de 8:00 am a 10:00 pm o escríbenos a hola@luuna.mx',
		'footer_follow_label'    => 'Síguenos',
		'footer_facebook_url'    => '#',
		'footer_instagram_url'   => '#',
		'footer_x_url'           => '#',
		'footer_payments_label'  => 'Métodos de pago',
	);

	return $defaults;
}

/**
 * Default images bundled in assets/images: field name => [file, width, height].
 */
function plenty_image_defaults(): array {
	return array(
		'hero_image'      => array( 'hero.png', 1774, 887 ),
		'feature_1_icon'  => array( 'affirm.png', 68, 28 ),
		'feature_2_icon'  => array( 'icon-calendar.svg', 44, 44 ),
		'feature_3_icon'  => array( 'icon-infinity.svg', 44, 44 ),
		'feature_4_icon'  => array( 'icon-truck.svg', 44, 44 ),
		'support_1_icon'  => array( 'icon-whatsapp.svg', 42, 42 ),
		'support_2_icon'  => array( 'icon-mail.svg', 42, 42 ),
		'support_3_icon'  => array( 'icon-phone.svg', 42, 42 ),
		'hotel_image'     => array( 'hotel.jpg', 1200, 900 ),
		'factory_image'   => array( 'factory.jpg', 1200, 900 ),
		'product_image'   => array( 'hero.png', 1774, 887 ),
		'story_1_image'   => array( 'hero.png', 1774, 887 ),
		'story_2_image'   => array( 'hero.png', 1774, 887 ),
		'story_3_image'   => array( 'hero.png', 1774, 887 ),
		'pdp_specialist_image' => array( 'sleep-specialist.jpg', 80, 53 ),
		'pdp_payment_1'   => array( 'pay-sm-visa.png', 40, 27.34 ),
		'pdp_payment_2'   => array( 'pay-sm-mastercard.png', 40, 27.34 ),
		'pdp_payment_3'   => array( 'pay-sm-amex.png', 40, 27.34 ),
		'pdp_payment_4'   => array( 'pay-sm-cabal.png', 40, 27.34 ),
		'pdp_payment_5'   => array( 'pay-sm-naranjax.png', 40, 27.34 ),
		'about_image'     => array( 'hero.png', 1774, 887 ),
		'about_value_1_icon' => array( 'value-design.png', 52, 54 ),
		'about_value_2_icon' => array( 'value-create.png', 54, 54 ),
		'about_value_3_icon' => array( 'value-details.png', 60, 54 ),
	);
}

function plenty_default_link( string $title, string $url = '#' ): array {
	return array(
		'title'  => $title,
		'url'    => $url,
		'target' => '',
	);
}

/**
 * ID of the static front page, which holds the editable content. 0 when the front page shows posts.
 */
function plenty_front_page_id(): int {
	return 'page' === get_option( 'show_on_front' ) ? (int) get_option( 'page_on_front' ) : 0;
}

/**
 * Read an editable content field, falling back to the design default.
 *
 * @param int|null $post_id Page holding the field; defaults to the front page (site-wide content).
 * @return mixed String, number or ACF link array.
 */
function plenty_content( string $name, ?int $post_id = null ) {
	$page_id = $post_id ?? plenty_front_page_id();
	$value   = $page_id ? plenty_field( $name, null, $page_id ) : null;

	return $value ?? ( plenty_content_defaults()[ $name ] ?? '' );
}

/**
 * Render an editable image field (attachment ID), falling back to the bundled design image.
 *
 * @param array    $fallback_attrs Overrides $attrs for the bundled image only (e.g. to keep its design size).
 * @param int|null $post_id        Page holding the field; defaults to the front page.
 */
function plenty_content_image( string $name, string $size = 'large', array $attrs = array(), array $fallback_attrs = array(), ?int $post_id = null ): string {
	$page_id = $post_id ?? plenty_front_page_id();
	$id      = $page_id ? (int) plenty_field( $name, 0, $page_id ) : 0;

	if ( $id ) {
		return (string) wp_get_attachment_image( $id, $size, false, $attrs );
	}

	$default = plenty_image_defaults()[ $name ] ?? null;

	return $default ? plenty_asset_img( $default[0], $default[1], $default[2], array_merge( $attrs, $fallback_attrs ) ) : '';
}
