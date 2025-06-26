<?php

require_once plugin_dir_path(__FILE__) . 'tcpdf/tcpdf.php';

/**
 * Generate a PDF summary of a print order.
 *
 * @param int $post_id
 * @return string|false Path to the generated PDF file or false on failure.
 */
function cpd_generate_print_order_pdf($post_id) {
    $post = get_post($post_id);
    if (!$post || $post->post_type !== 'print_order') return false;

    $pdf = new TCPDF();
    $pdf->SetCreator('PrintPrice Pro');
    $pdf->SetAuthor('PrintPrice Pro');
    $pdf->SetTitle('Print Order Summary');
    $pdf->SetMargins(15, 20, 15);
    $pdf->AddPage();

    $fields = get_fields($post_id);
    if (!$fields || !is_array($fields)) return false;

    $html = '<h1 style="text-align:center;">Print Order Summary</h1>';
    $html .= '<table border="1" cellpadding="6" cellspacing="0" style="width:100%;">';
    foreach ($fields as $key => $value) {
        $key = esc_html(ucwords(str_replace('_', ' ', $key)));
        $value = esc_html(is_array($value) ? implode(', ', $value) : $value);
        $html .= "<tr><td><strong>{$key}</strong></td><td>{$value}</td></tr>";
    }
    $html .= '</table>';

    $pdf->writeHTML($html, true, false, true, false, '');

    $upload_dir = wp_upload_dir();
    $pdf_dir = $upload_dir['basedir'] . '/print-orders';
    $pdf_url = $upload_dir['baseurl'] . '/print-orders';

    if (!file_exists($pdf_dir)) {
        wp_mkdir_p($pdf_dir);
    }

    $pdf_path = $pdf_dir . "/print_order_{$post_id}.pdf";
    $pdf->Output($pdf_path, 'F');

    return $pdf_path;
}
