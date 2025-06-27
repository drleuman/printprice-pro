<?php
// Salir si se accede directamente
if (!defined('ABSPATH')) exit;

$sheet_ids = [
    'Calculation16' => '1bRgqFMUy_q3z6UWz93u-2KtWJM_2sJ6Kyt6lDUGbFpw',
    'Calculation32' => '1MvU22-zM21JYoTqq6U9bdoMwbcCu5bWh3k6O8l8JbwQ'
];

$api_key = 'AIzaSyCFDTHb9fiNdiiAvBFe-qB49dVqtEF-CT4';

// --- GET: Buscar datos por imprenta específica ---
if (isset($_GET['house']) && isset($_GET['file'])) {
    $print_house = sanitize_text_field($_GET['house']);
    $calc_file = sanitize_text_field($_GET['file']);

    if (!in_array($calc_file, ['Calculation16', 'Calculation32'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid calculation file']);
        exit;
    }

    $sheet_id = $sheet_ids[$calc_file];
    $sheet_name = $calc_file;

    require_once __DIR__ . '/../functions.php';

    $raw_data = get_google_sheet_data($sheet_id, $sheet_name, $api_key);

    if (!$raw_data || !isset($raw_data['values'])) {
        http_response_code(500);
        echo json_encode(['error' => 'Error fetching sheet data']);
        exit;
    }

    $clean_data = process_print_house_data($raw_data['values']);

    $result = array_filter($clean_data, function ($item) use ($print_house) {
        return strtolower($item['Print House'] ?? '') === strtolower($print_house);
    });

    header('Content-Type: application/json');
    echo json_encode(array_values($result));
    exit;
}

// --- POST: Guardar pedido desde IA ---
add_action('rest_api_init', function () {
    register_rest_route('custom-print/v1', '/store-order', [
        'methods' => 'POST',
        'callback' => 'cpd_store_order_from_ai',
        'permission_callback' => '__return_true',
    ]);
});

function cpd_store_order_from_ai(WP_REST_Request $request) {
    $data = $request->get_json_params();

    // Validar campos esenciales
    $required = ['copies', 'total_page_count', 'book_size', 'print_houses'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            return new WP_REST_Response(['error' => "Missing field: $field"], 400);
        }
    }

    if (!function_exists('cpd_store_print_order_data')) {
        return new WP_REST_Response(['error' => 'Storage function not available'], 500);
    }

    $post_id = cpd_store_print_order_data($data);

    if (!$post_id) {
        return new WP_REST_Response(['error' => 'Could not create order'], 500);
    }

    $permalink = get_permalink($post_id);

    return new WP_REST_Response([
        'success' => true,
        'post_id' => $post_id,
        'permalink' => $permalink,
        'message' => '✅ Print order stored successfully.'
    ], 200);
}
