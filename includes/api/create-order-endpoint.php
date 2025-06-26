<?php
add_action('rest_api_init', function () {
    register_rest_route('custom-print/v1', '/create-order', [
        'methods' => 'POST',
        'callback' => 'cpd_create_print_order',
        'permission_callback' => '__return_true'
    ]);
});

function cpd_create_print_order($request) {
    $params = $request->get_json_params();
    if (!$params || empty($params['print_data']) || !is_array($params['print_data'])) {
        return new WP_Error('missing_data', 'Missing or invalid print_data', ['status' => 400]);
    }

    $print_data = array_map('sanitize_text_field', $params['print_data']);
    $required_keys = ['copies', 'book_size', 'print_house']; // Añadir más si aplica

    foreach ($required_keys as $key) {
        if (empty($print_data[$key])) {
            return new WP_Error('missing_field', "Missing field: $key", ['status' => 400]);
        }
    }

    $post_id = wp_insert_post([
        'post_type'   => 'print_order',
        'post_title'  => 'Print Order - ' . current_time('Y-m-d H:i:s'),
        'post_status' => 'publish',
    ]);

    if (is_wp_error($post_id)) return $post_id;

    foreach ($print_data as $key => $value) {
        update_field($key, $value, $post_id);
    }

    return ['success' => true, 'post_id' => $post_id];
}
