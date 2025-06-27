<?php

/**
 * Get data from a Google Sheet.
 *
 * @param string $sheet_id
 * @param string $sheet_name
 * @param string $api_key
 * @return array|false
 */
function get_google_sheet_data($sheet_id, $sheet_name, $api_key) {
    $url = "https://sheets.googleapis.com/v4/spreadsheets/{$sheet_id}/values/{$sheet_name}?key={$api_key}";
    $response = wp_remote_get($url, ['timeout' => 10]);

    if (is_wp_error($response)) return false;

    return json_decode(wp_remote_retrieve_body($response), true);
}

/**
 * Process raw Google Sheets data into an associative array.
 *
 * @param array $rows
 * @return array
 */
function process_print_house_data($rows) {
    if (empty($rows) || !is_array($rows)) return [];

    $headers = array_shift($rows);
    if (!is_array($headers) || count($headers) === 0) return [];

    $data = [];
    foreach ($rows as $row) {
        $row = array_pad($row, count($headers), '');
        $combined = array_combine($headers, $row);
        if ($combined) {
            $data[] = $combined;
        }
    }

    return $data;
}

/**
 * Load print house data from local JSON file.
 *
 * @return array
 */
function cpd_load_print_house_data() {
    $json_path = plugin_dir_path(__FILE__) . '../../data/print-houses.json';
    if (!file_exists($json_path)) return [];

    $json_data = file_get_contents($json_path);
    $data = json_decode($json_data, true);

    return is_array($data) ? $data : [];
}

/**
 * Store print order data from AI into custom post type with ACF.
 *
 * @param array $data
 * @return int|false
 */
function cpd_store_print_order_data($data) {
    // Crear el post
    $post_id = wp_insert_post([
        'post_type'   => 'print_order',
        'post_status' => 'publish',
        'post_title'  => 'AI Print Order - ' . current_time('Y-m-d H:i:s'),
    ]);

    if (is_wp_error($post_id) || !$post_id) {
        return false;
    }

    // Guardar campos individuales
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
    ];

    foreach ($fields as $field) {
        if (isset($data[$field])) {
            update_field($field, sanitize_text_field($data[$field]), $post_id);
        }
    }

    // Guardar imprentas compatibles
    if (!empty($data['print_houses']) && is_array($data['print_houses'])) {
        update_field('print_houses', $data['print_houses'], $post_id);
    }

    // Guardar imprenta seleccionada
    if (!empty($data['selected_print_house']) && is_array($data['selected_print_house'])) {
        $house = $data['selected_print_house'];

        if (!empty($house['print_house'])) {
            update_field('selected_print_house', sanitize_text_field($house['print_house']), $post_id);
        }
        if (!empty($house['total_cost'])) {
            update_field('selected_print_house_total_cost', sanitize_text_field($house['total_cost']), $post_id);
        }
        if (!empty($house['estimated_delivery_time'])) {
            update_field('selected_print_house_estimated_delivery_time', sanitize_text_field($house['estimated_delivery_time']), $post_id);
        }
    }

    return $post_id;
}

/**
 * Cargar plantilla personalizada para single print_order.
 */
add_filter('template_include', function ($template) {
    if (is_singular('print_order')) {
        $custom_template = plugin_dir_path(__FILE__) . '../../templates/single-print_order.php';
        if (file_exists($custom_template)) return $custom_template;
    }
    return $template;
});

/**
 * Permite cambiar de imprenta seleccionada manualmente.
 */
add_action('template_redirect', function () {
    if (!is_singular('print_order') || !isset($_GET['switch_print_house'], $_GET['post_id'])) {
        return;
    }

    $post_id = absint($_GET['post_id']);
    $new_house_name = sanitize_text_field($_GET['switch_print_house']);

    // Obtener imprentas compatibles
    $houses = get_field('print_houses', $post_id);
    if (!$houses || !is_array($houses)) return;

    foreach ($houses as $house) {
        if (sanitize_title($house['print_house']) === sanitize_title($new_house_name)) {
            update_field('selected_print_house', $house['print_house'], $post_id);
            update_field('selected_print_house_total_cost', $house['total_cost'], $post_id);
            update_field('selected_print_house_estimated_delivery_time', $house['estimated_delivery_time'], $post_id);

            wp_redirect(get_permalink($post_id));
            exit;
        }
    }
});

/**
 * Enqueue external styles.
 */
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css');
});
