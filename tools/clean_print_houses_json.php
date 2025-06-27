<?php
/**
 * Advanced Cleaner for print-houses.json
 *
 * Ejecutar desde CLI:
 * php clean_print_houses_advanced.php
 *
 * O desde navegador estando logueado como administrador.
 */

// Ruta del JSON original
$json_input_path = __DIR__ . '/../data/print-houses.json';

// Ruta del JSON limpio
$json_output_path = __DIR__ . '/../data/print-houses-clean-advanced.json';

// Autorización mínima si se ejecuta vía navegador
if (php_sapi_name() !== 'cli') {
    require_once('../../../../wp-load.php');
    if (!current_user_can('manage_options')) {
        wp_die('Access denied: You do not have permission to clean print house data.');
    }
}

// Leer archivo original
if (!file_exists($json_input_path)) {
    die("❌ Input file not found: {$json_input_path}\n");
}
$raw_content = file_get_contents($json_input_path);
$entries = json_decode($raw_content, true);
if (!is_array($entries)) {
    die("❌ Invalid JSON format.\n");
}

$cleaned = [];
foreach ($entries as $entry) {
    // Solo procesar si hay un coste numérico
    $cost_raw = $entry['detected_cost'];
    if (!$cost_raw) {
        continue;
    }

    // Convertir coste a float
    $cost_clean = floatval(str_replace(',', '.', preg_replace('/[^\d,\.]/', '', $cost_raw)));

    // Intentar extraer algunos campos relevantes del raw_entry
    $raw = $entry['raw_entry'];
    $copies = null;
    $size = null;
    $binding = null;

    foreach ($raw as $k => $v) {
        if (!$v) continue;
        // Detectar número de copias
        if (preg_match('/cop(ie|y|ies)/i', $k)) {
            $copies = intval($v);
        }
        // Detectar tamaño
        if (stripos($k, 'size') !== false || stripos($k, 'format') !== false) {
            $size = trim($v);
        }
        // Detectar encuadernado
        if (stripos($k, 'binding') !== false || stripos($k, 'bind') !== false) {
            $binding = trim($v);
        }
    }

    $cleaned[] = [
        'sheet' => $entry['sheet'],
        'printer_name' => $entry['printer_name'],
        'cost' => $cost_clean,
        'copies' => $copies,
        'size' => $size,
        'binding' => $binding,
        'raw_entry' => $raw,
    ];
}

// Guardar el nuevo JSON
file_put_contents($json_output_path, json_encode($cleaned, JSON_PRETTY_PRINT));

echo "✅ Limpieza avanzada completada.\n";
echo "👉 Se generaron " . count($cleaned) . " registros limpios.\n";
echo "📄 Archivo limpio creado en:\n→ {$json_output_path}\n";
