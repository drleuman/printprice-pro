<?php
// Sanitizar los datos POST
$print_data = [];
foreach ($_POST as $key => $value) {
    $print_data[$key] = is_array($value) ? array_map('sanitize_text_field', $value) : sanitize_text_field($value);
}

// Decodificar print_houses si viene como JSON string
if (!empty($print_data['print_houses']) && is_string($print_data['print_houses'])) {
    $decoded = json_decode($print_data['print_houses'], true);
    if (is_array($decoded)) {
        $print_data['print_houses'] = $decoded;
    }
}

$print_houses = $print_data['print_houses'] ?? [];
?>

<div class="seleccion-imprenta">
    <h1>Selecciona una imprenta</h1>

    <?php if (empty($print_houses)): ?>
        <p>No hay imprentas disponibles para tu configuración. Intenta modificar los parámetros del pedido.</p>
    <?php else: ?>
        <form method="post" action="">
            <?php foreach ($print_houses as $house): ?>
                <label style="display:block; margin-bottom:10px;">
                    <input type="radio" name="print_house" value="<?php echo esc_attr($house); ?>" required>
                    <?php echo esc_html($house); ?>
                </label>
            <?php endforeach; ?>

            <?php foreach ($print_data as $key => $value): ?>
                <?php if ($key !== 'print_houses'): ?>
                    <input type="hidden" name="<?php echo esc_attr($key); ?>" value="<?php echo esc_attr(is_array($value) ? json_encode($value) : $value); ?>">
                <?php endif; ?>
            <?php endforeach; ?>

            <button type="submit" class="button">Agregar al carrito</button>
        </form>
    <?php endif; ?>
</div>
