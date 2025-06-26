
<?php
/* Plantilla Name: Vista previa de PDF */
get_header();
if (!current_user_can('edit_posts')) { wp_die('Acceso denegado'); }
$print_order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($print_order_id) {
    $upload_dir = wp_upload_dir();
    $pdf_url = $upload_dir['baseurl'] . '/print_orders/print_order_' . $print_order_id . '.pdf';
    echo '<h2>Vista previa de PDF for Order #' . $print_order_id . '</h2>';
    echo '<iframe src="' . esc_url($pdf_url) . '" width="100%" height="800px"></iframe>';
} else {
    echo '<p>No print order ID provided.</p>';
}
get_footer();
