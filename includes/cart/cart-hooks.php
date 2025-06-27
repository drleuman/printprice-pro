<?php
defined('ABSPATH') || exit;

// Set custom price
add_action('woocommerce_before_calculate_totals', function ($cart) {
    if (is_admin() && !defined('DOING_AJAX')) return;

    foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
        if (isset($cart_item['custom_price'])) {
            $cart_item['data']->set_price(floatval($cart_item['custom_price']));
        }
    }
});
add_action('woocommerce_checkout_create_order', function ($order, $data) {
    if (!WC()->cart) return;

    foreach (WC()->cart->get_cart() as $cart_item) {
        if (!isset($cart_item['print_order_id'])) continue;

        $print_order_id = $cart_item['print_order_id'];
        $fields = [
            'copies',
            'total_page_count',
            'book_size',
            'interior_print',
            'cover_print',
            'paper_weight_interior',
            'paper_weight_cover',
            'binding_method',
            'finishing_options',
            'delivery_country',
            'selected_print_house',
            'selected_print_house_total_cost',
            'selected_print_house_estimated_delivery_time',
        ];

        foreach ($fields as $field) {
            $value = get_field($field, $print_order_id);
            if ($value) {
                $order->add_meta_data($field, $value);
            }
        }

        // Asocia el ID del print_order al pedido
        $order->add_meta_data('print_order_id', $print_order_id);
    }
}, 10, 2);
