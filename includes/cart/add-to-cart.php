<?php
add_action('template_redirect', function () {
    if (!function_exists('WC') || !is_page('seleccion-imprenta') || $_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }

    // Sanitizar datos del formulario
    $sanitized = [];
    foreach ($_POST as $key => $value) {
        $sanitized[$key] = is_array($value) ? array_map('sanitize_text_field', $value) : sanitize_text_field($value);
    }

    // Validar campos mínimos
    if (empty($sanitized['print_house']) || empty($sanitized['copies'])) {
        wc_add_notice(__('Missing required print order information.'), 'error');
        return;
    }

    $product_id = 989377; // AI Print Order
    $cart_item_data = ['print_order_data' => $sanitized];

    // Prevención básica contra duplicados en recarga
    if (!WC()->session->get('print_order_added')) {
        WC()->cart->add_to_cart($product_id, 1, 0, [], $cart_item_data);
        WC()->session->set('print_order_added', true);
    }

    wp_safe_redirect(wc_get_cart_url());
    exit;
});
