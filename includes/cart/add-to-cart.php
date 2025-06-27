<?php
defined('ABSPATH') || exit;

// Registrar endpoint REST para añadir al carrito
add_action('rest_api_init', function () {
    register_rest_route('ppp/v1', '/add-to-cart', [
        'methods'  => 'POST',
        'callback' => 'ppp_add_to_cart',
        'permission_callback' => '__return_true',
    ]);
});

function ppp_add_to_cart($request) {
    if (!class_exists('WooCommerce')) {
        return new WP_Error('no_woocommerce', 'WooCommerce is not active.', ['status' => 500]);
    }

    // Asegurar que el carrito esté disponible
    if (null === WC()->cart) {
        wc_load_cart();
    }

    $params = $request->get_json_params();
    $product_id = intval($params['product_id'] ?? 0);
    $price = floatval($params['price'] ?? 0);
    $print_order_id = intval($params['print_order_id'] ?? 0);

    if (!$product_id || !$price || !$print_order_id) {
        return new WP_Error('invalid_data', 'Missing required parameters.', ['status' => 400]);
    }

    $cart_item_data = [
        'custom_price' => $price,
        'print_order_id' => $print_order_id,
    ];

    $added = WC()->cart->add_to_cart($product_id, 1, 0, [], $cart_item_data);

    if (!$added) {
        return new WP_Error('add_to_cart_failed', 'Could not add product to cart.', ['status' => 500]);
    }

    return new WP_REST_Response(['success' => true, 'cart_item_key' => $added], 200);
}
add_filter('woocommerce_get_item_data', function ($item_data, $cart_item) {
    if (!isset($cart_item['print_order_id'])) return $item_data;

    $post_id = $cart_item['print_order_id'];

    $fields = [
        'copies' => 'Copies',
        'total_page_count' => 'Total Pages',
        'book_size' => 'Book Size',
        'interior_print' => 'Interior Print',
        'cover_print' => 'Cover Print',
        'paper_weight_interior' => 'Interior Paper',
        'paper_weight_cover' => 'Cover Paper',
        'binding_method' => 'Binding',
        'finishing_options' => 'Finishing',
        'delivery_country' => 'Delivery',
        'selected_print_house' => 'Print House',
        'selected_print_house_estimated_delivery_time' => 'Delivery Time'
    ];

    foreach ($fields as $key => $label) {
        $value = get_field($key, $post_id);
        if (!empty($value)) {
            $item_data[] = [
                'key'   => $label,
                'value' => $value,
                'display' => $value
            ];
        }
    }

    return $item_data;
}, 10, 2);
