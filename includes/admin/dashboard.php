
<?php
add_action('admin_menu', function () {
    add_menu_page('Pedidos de impresión', 'Pedidos de impresión', 'manage_options', 'print-orders-dashboard', 'cpd_print_orders_dashboard', 'dashicons-media-document');
});

function cpd_print_orders_dashboard() {
    $args = array('post_type' => 'print_order', 'posts_per_page' => 20);
    $orders = get_posts($args);
    echo '<div class="wrap"><h1>Panel de pedidos</h1><table class="widefat"><thead><tr><th>ID</th><th>Título</th><th>Fecha</th><th>PDF</th></tr></thead><tbody>';
    foreach ($orders as $order) {
        $upload_dir = wp_upload_dir();
        $pdf_url = $upload_dir['baseurl'] . '/print_orders/print_order_' . $order->ID . '.pdf';
        echo '<tr>';
        echo '<td>' . $order->ID . '</td>';
        echo '<td><a href="' . get_edit_post_link($order->ID) . '">' . esc_html($order->post_Título) . '</a></td>';
        echo '<td>' . $order->post_Fecha . '</td>';
        echo '<td><a href="' . esc_url($pdf_url) . '" target="_blank">Ver PDF</a></td>';
        echo '</tr>';
    }
    echo '</tbody></table></div>';
}
