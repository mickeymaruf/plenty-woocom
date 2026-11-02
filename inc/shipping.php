<?php
/**
 * "A coordinar" shipping: delivery is quoted after the order, and the store contacts the customer
 * to arrange it. A WooCommerce shipping method with no cost at checkout; add it to a zone in
 * WooCommerce → Settings → Shipping. Its label ("A coordinar") and the note under it at checkout
 * are Site Settings fields (front page, "Cart & shipping" tab), shared with the cart drawer, which
 * switches from the free-shipping banner to the note while the method is enabled in any zone.
 *
 * @package Plenty
 */

defined( 'ABSPATH' ) || exit;

class Plenty_Quote_Shipping extends WC_Shipping_Method {

	public const ID = 'plenty_quote';

	/**
	 * @param int $instance_id Shipping zone method instance.
	 */
	public function __construct( $instance_id = 0 ) {
		$this->id                 = self::ID;
		$this->instance_id        = absint( $instance_id );
		$this->method_title       = 'A coordinar';
		$this->method_description = __( 'Sin costo de envío en el checkout: la tienda se comunica con el cliente después del pedido para coordinar la entrega y su costo. La etiqueta y la nota que ven los clientes se editan en la página de inicio, Site Settings → Cart & shipping.', 'plenty' );
		$this->supports           = array( 'shipping-zones' );
		$this->enabled            = 'yes';
		$this->title              = (string) plenty_content( 'shipping_quote_label' );
	}

	/**
	 * @param array $package Cart package.
	 */
	public function calculate_shipping( $package = array() ): void {
		$this->add_rate(
			array(
				'label'   => $this->title,
				'cost'    => 0,
				'package' => $package,
			)
		);
	}
}

add_filter( 'woocommerce_shipping_methods', 'plenty_register_quote_shipping' );

function plenty_register_quote_shipping( array $methods ): array {
	$methods[ Plenty_Quote_Shipping::ID ] = Plenty_Quote_Shipping::class;

	return $methods;
}

/**
 * Whether "A coordinar" is enabled in any shipping zone (including "Locations not covered").
 */
function plenty_shipping_is_quoted(): bool {
	static $quoted = null;

	if ( null === $quoted ) {
		$zones   = array_column( WC_Shipping_Zones::get_zones(), 'shipping_methods' );
		$zones[] = ( new WC_Shipping_Zone( 0 ) )->get_shipping_methods();
		$quoted  = false;

		foreach ( array_merge( ...$zones ) as $method ) {
			if ( Plenty_Quote_Shipping::ID === $method->id && 'yes' === $method->enabled ) {
				$quoted = true;
				break;
			}
		}
	}

	return $quoted;
}

// Checkout (classic) and cart page: the note under the "A coordinar" option.
add_action( 'woocommerce_after_shipping_rate', 'plenty_quote_shipping_note' );

function plenty_quote_shipping_note( $rate ): void {
	$note = plenty_content( 'shipping_quote_note' );

	if ( $rate instanceof WC_Shipping_Rate && Plenty_Quote_Shipping::ID === $rate->get_method_id() && $note ) {
		echo '<span class="mt-1 block text-[12px] font-normal leading-4 text-muted">' . esc_html( $note ) . '</span>';
	}
}
