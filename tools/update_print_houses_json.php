<?php
// Detect CLI
$is_cli = (php_sapi_name() === 'cli');

// Load WordPress if not CLI
if (!$is_cli) {
    $wp_load_path = realpath(__DIR__ . '/../../../../wp-load.php');
    if (!$wp_load_path || !file_exists($wp_load_path)) {
        echo "❌ Error: wp-load.php not found. Please check the path.";
        exit;
    }
    require_once($wp_load_path);

    if (!current_user_can('manage_options')) {
        wp_die('Access denied: You do not have permission to update print house data.');
    }
}

// Include shared functions
require_once __DIR__ . '/../includes/api/functions.php';

// Google Sheets configuration
$sheet_ids = [
    'Calculation16' => '1bRgqFMUy_q3z6UWz93u-2KtWJM_2sJ6Kyt6lDUGbFpw',
    'Calculation32' => '1MvU22-zM21JYoTqq6U9bdoMwbcCu5bWh3k6O8l8JbwQ',
];
$api_key = 'AIzaSyCFDTHb9fiNdiiAvBFe-qB49dVqtEF-CT4';

// Output path for the JSON file
$json_output_path = WP_CONTENT_DIR . '/plugins/print-price-pro-corrected/data/print-houses.json';

$all_data = [];

foreach ($sheet_ids as $sheet_name => $sheet_id) {
    echo "📥 Fetching data from {$sheet_name}...\n";

    $raw = get_google_sheet_data($sheet_id, $sheet_name, $api_key);
    if ($raw && isset($raw['values'])) {
        $processed = process_print_house_data($raw['values']);
        $all_data[$sheet_name] = $processed;
        echo "✅ {$sheet_name} processed (" . count($processed) . " entries).\n";
    } else {
        echo "⚠️ Warning: Failed to retrieve data from {$sheet_name}.\n";
    }
}

// Write JSON if data is available
if (!empty($all_data)) {
    // Add metadata timestamp
    $all_data['_meta'] = [
        'updated_at' => date('c'),
        'source' => basename(__FILE__)
    ];

    $success = file_put_contents($json_output_path, json_encode($all_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    if ($success !== false) {
        echo "✅ print-houses.json successfully updated.\n→ {$json_output_path}\n";
    } else {
        echo "❌ Error: Failed to write to print-houses.json.\n";
    }
} else {
    echo "❌ Error: No data available to write to print-houses.json.\n";
}
