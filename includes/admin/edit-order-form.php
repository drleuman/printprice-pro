<?php
// AJAX handler para actualizar campos ACF y recalcular precio
add_action('wp_ajax_cpd_update_print_order', 'cpd_update_print_order_ajax');
add_action('wp_ajax_nopriv_cpd_update_print_order', 'cpd_update_print_order_ajax');

function cpd_update_print_order_ajax() {
    if (!isset($_POST['post_id']) || !isset($_POST['fields']) || !is_array($_POST['fields'])) {
        wp_send_json_error(['message' => 'Invalid request.']);
    }

    $post_id = intval($_POST['post_id']);
    $fields = array_map('sanitize_text_field', $_POST['fields']);

    foreach ($fields as $key => $value) {
        update_field($key, $value, $post_id);
    }

    $data = cpd_load_print_house_data();
    $copies = intval($fields['copies'] ?? 0);
    $size = strtolower($fields['book_size'] ?? '');

    $compatible = array_filter($data, function ($row) use ($size) {
        return strtolower($row['Book Size'] ?? '') === $size;
    });

    if (empty($compatible)) {
        wp_send_json_error(['message' => 'No compatible print houses found.']);
    }

    $results = [];
    foreach ($compatible as $item) {
        $price_per_copy = floatval($item['Base Price (EUR)'] ?? 0);
        $multiplier = 1;
        if ($copies >= 1000) $multiplier = 0.85;
        elseif ($copies >= 500) $multiplier = 0.9;

        $total = round($price_per_copy * $copies * $multiplier, 2);
        $results[] = [
            'print_house' => $item['Print House'],
            'total_cost' => $total,
            'estimated_delivery_time' => $item['Estimated Delivery Time'] ?? 'Unknown'
        ];
    }

    update_field('print_houses', $results, $post_id);
    $selected = $results[0];
    update_field('selected_print_house', $selected['print_house'], $post_id);
    update_field('selected_print_house_total_cost', $selected['total_cost'], $post_id);
    update_field('selected_print_house_estimated_delivery_time', $selected['estimated_delivery_time'], $post_id);

    wp_send_json_success(['updated' => $selected]);
}

add_shortcode('edit_print_order_form', function ($atts) {
    if (!is_singular('print_order')) return '';
    global $post;
    $post_id = $post->ID;

    ob_start();
    ?>
    <div style="max-width:600px;margin:3em auto 4em;padding:2em;border:1px solid #ddd;border-radius:16px;background:#fdfdfd;box-shadow:0 0 10px rgba(0,0,0,0.05);">
        <h3 style="text-align:center;margin-bottom:1.5em;font-size:1.3em;color:#333;">✏️ Edit Your Print Order</h3>
        <form id="edit-print-order-form" method="post">
            <input type="hidden" name="post_id" value="<?php echo esc_attr($post_id); ?>">

            <div style="margin-bottom:1em;">
                <label>Copies:</label>
                <input type="number" name="fields[copies]" value="<?php echo esc_attr(get_field('copies', $post_id)); ?>" style="width:100%;padding:0.5em;border-radius:6px;border:1px solid #ccc;">
            </div>

            <div style="margin-bottom:1em;">
                <label>Book Size:</label>
                <input type="text" name="fields[book_size]" value="<?php echo esc_attr(get_field('book_size', $post_id)); ?>" style="width:100%;padding:0.5em;border-radius:6px;border:1px solid #ccc;">
            </div>

            <div style="margin-bottom:1em;">
                <label>Binding Method:</label>
                <input type="text" name="fields[binding_method]" value="<?php echo esc_attr(get_field('binding_method', $post_id)); ?>" style="width:100%;padding:0.5em;border-radius:6px;border:1px solid #ccc;">
            </div>

            <div style="margin-bottom:1.5em;">
                <label>Interior Print:</label>
                <input type="text" name="fields[interior_print]" value="<?php echo esc_attr(get_field('interior_print', $post_id)); ?>" style="width:100%;padding:0.5em;border-radius:6px;border:1px solid #ccc;">
            </div>

            <button type="submit" style="display:block;width:100%;background:#cc0000;color:white;padding:0.8em;border:none;border-radius:8px;font-weight:bold;cursor:pointer;">💾 Update Order</button>
        </form>
        <div id="edit-result" style="margin-top:1.5em;text-align:center;font-size:0.95em;"></div>
    </div>

    <script>
    document.getElementById("edit-print-order-form").addEventListener("submit", function (e) {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);
        formData.append("action", "cpd_update_print_order");

        fetch("<?php echo admin_url('admin-ajax.php'); ?>", {
            method: "POST",
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            const result = document.getElementById("edit-result");
            if (data.success) {
                result.innerHTML = `<p style=\"color:green\"><strong>✅ Updated:</strong> ${data.data.updated.print_house}, €${data.data.updated.total_cost}</p>`;
                location.reload();
            } else {
                result.innerHTML = `<p style=\"color:red\">❌ Error: ${data.data.message}</p>`;
            }
        })
        .catch(err => {
            document.getElementById("edit-result").innerHTML = `<p style=\"color:red\">❌ Request failed: ${err.message}</p>`;
        });
    });
    </script>
    <?php
    return ob_get_clean();
});
