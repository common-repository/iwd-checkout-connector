<?php

/**
 * Class Iwd_Connector_Cart
 */
class Iwd_Connector_Cart {

	/**
	 * Return format cart data
	 *
	 * @param $session_data
	 * @return array
	 */
	public function getCart( $session_data) {
		$cart_totals = unserialize($session_data['cart_totals']);

		return [
            'version' => esc_html(IWD_CONNECTOR_VERSION) ?? null,
			'is_virtual' =>$this->getIsVirtual($session_data),
			'currency' => get_woocommerce_currency(),
			'currency_symbol' => html_entity_decode(get_woocommerce_currency_symbol()),
			'subtotal' => $cart_totals['subtotal'],
			'shipping' => $cart_totals['shipping_total'],
			'tax' => $cart_totals['total_tax'],
			'discount' => number_format(abs($cart_totals['discount_total']), 2, '.', ''),
			'grand_total' => $cart_totals['total'],
			'coupon_code' => !empty(unserialize($session_data['applied_coupons'])) ? unserialize($session_data['applied_coupons'])[0] : '',
			'country'     => unserialize($session_data['customer'])['shipping_country']
		];
	}

	/**
	 * Return format cart items data
	 *
	 * @param $session_data
	 * @return array
	 */
	public function getCartItems( $session_data) {
		$cartItem = unserialize($session_data['cart']);
		$_pf = new WC_Product_Factory();

		$cart_items = [];
		foreach ($cartItem as $cart_item) {
			$product_id = $cart_item['variation_id'] ? $cart_item['variation_id'] : $cart_item['product_id'];
			$image = wp_get_attachment_image_src(get_post_thumbnail_id($product_id), 'single-post-thumbnail');
			$_product = $_pf->get_product($product_id);

			$cart_items [] = [
				'name' => $_product->get_name(),
				'sku' => $_product->get_sku(),
				'price' => $_product->get_price(),
				'qty' => $cart_item['quantity'],
				'type' => get_post_meta($product_id, '_virtual', true) == 'yes' ? 'virtual' : 'simple',
				'image' => $image[0] ? $image[0] : wp_get_attachment_url( $_product->get_image_id()),
				'options' => $this->getProductOptions($cart_item['variation']),
			];
		}

		return $cart_items;
	}

	/**
	 * Return product options
	 *
	 * @param $options
	 * @return array
	 */
	public function getProductOptions( $options) {
		$item_data = [];
		foreach ($options as $name => $value) {

			$taxonomy = wc_attribute_taxonomy_name(str_replace('attribute_pa_', '', urldecode($name)));

			if ( taxonomy_exists( $taxonomy ) ) {
				$term = get_term_by('slug', $value, $taxonomy);
				$label = wc_attribute_label($taxonomy);
				if (!is_wp_error($term) && $term && $term->name) {
					$value = $term->name;
				}
			} else {
				$label = wc_attribute_label( str_replace( 'attribute_', '', $name ) );
			}

			$item_data[] = array(
				'label' => $label,
				'value' => $value,
			);

		}

		return $item_data;
	}

	/**
	 * Get if cart virtual
	 *
	 * @param $session_data
	 * @return bool
	 */
	public function getIsVirtual( $session_data) {
		$has_virtual_products = false;
		$virtual_products = 0;
		$cartItem = unserialize($session_data['cart']);

		foreach ($cartItem as $product) {
			if ($product['variation_id']) {
				$product_id = $product['variation_id'];
			} else {
				$product_id = $product['product_id'];
			}
			$is_virtual = get_post_meta($product_id, '_virtual', true);

			if ( 'yes' == $is_virtual ) {
				++$virtual_products;
			}
		}

		if (!is_bool($cartItem) && count($cartItem) == $virtual_products) {
			$has_virtual_products = true;
		}

		return $has_virtual_products;
	}
}
