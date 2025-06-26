<?php
// Mostrar datos del pedido en el carrito y checkout
add_filter('woocommerce_get_item_data', function ($item_data, $cart_item) {
    if (isset($cart_item['print_order_data']) && is_array($cart_item['print_order_data'])) {
        foreach ($cart_item['print_order_data'] as $key => $value) {
            $item_data[] = [
                'key'   => esc_html(ucwords(str_replace('_', ' ', $key))),
                'value' => esc_html(is_array($value) ? implode(', ', $value) : $value)
            ];
        }
    }
    return $item_data;
}, 10, 2);

// Guardar datos personalizados en la orden
add_action('woocommerce_add_order_item_meta', function ($item_id, $values, $cart_item_key) {
    if (!empty($values['print_order_data']) && is_array($values['print_order_data'])) {
        foreach ($values['print_order_data'] as $key => $value) {
            wc_add_order_item_meta($item_id, esc_html($key), esc_html(is_array($value) ? implode(', ', $value) : $value));
        }
    }
}, 10, 3);


// Adjuntar PDF generado al email de nuevo pedido
add_filter('woocommerce_email_attachments', function($attachments, $email_id, $order, $email) {
    if ($email_id === 'new_order' && $order instanceof WC_Order) {
        foreach ($order->get_items() as $item) {
            $print_order_id = $item->get_meta('print_order_id');
            if ($print_order_id) {
                $upload_dir = wp_upload_dir();
                $pdf_path = $upload_dir['basedir'] . '/print_orders/print_order_' . $print_order_id . '.pdf';
                if (file_exists($pdf_path)) {
                    $attachments[] = $pdf_path;
                }
            }
        }
    }
    return $attachments;
}, 10, 4);
