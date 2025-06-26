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
    $json_path = plugin_dir_path(__FILE__) . '../data/print-houses.json';
    if (!file_exists($json_path)) return [];

    $json_data = file_get_contents($json_path);
    $data = json_decode($json_data, true);

    return is_array($data) ? $data : [];
}
