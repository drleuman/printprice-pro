<?php
defined('ABSPATH') || exit;
get_header();

if (have_posts()) : while (have_posts()) : the_post();
$post_id = get_the_ID();
?>

<div class="print-order-summary" style="max-width:800px;margin:2em auto;padding:2em;position:relative;background:#fff;border:1px solid #ddd;border-radius:12px;box-shadow:0 4px 12px rgba(0,0,0,0.05);">

    <!-- Botón redondo flotante -->
    <button id="toggle-editor" class="ppp-edit-icon" title="Edit Order">
            <i class="fas fa-edit"></i>
    </button>

    <!-- Notificación post-actualización -->
    <div id="edit-notification" style="display:none;margin-bottom:1em;padding:1em;border-radius:8px;font-size:0.95em;"></div>

    <!-- Título reducido -->
    <h2 style="margin-top:0;font-size:1.4em;color:#333;"><?php the_title(); ?></h2>

    <!-- Tabla de resumen -->
    <table style="width:100%;margin-top:1em;border-collapse:collapse;">
        <?php
        $fields = [
            'copies' => 'Number of Copies',
            'total_page_count' => 'Total Page Count',
            'book_size' => 'Book Size',
            'interior_print' => 'Interior Print',
            'cover_print' => 'Cover Print',
            'paper_weight_interior' => 'Paper Weight Interior',
            'paper_weight_cover' => 'Paper Weight Cover',
            'binding_method' => 'Binding Method',
            'finishing_options' => 'Finishing Options',
            'delivery_country' => 'Delivery Country',
            'selected_print_house' => 'Selected Print House',
            'selected_print_house_total_cost' => 'Total Cost',
            'selected_print_house_estimated_delivery_time' => 'Estimated Delivery Time',
        ];
        foreach ($fields as $key => $label) {
            $value = get_field($key);
            if ($value) {
                echo "<tr><th style='text-align:left;padding:8px 10px;border-bottom:1px solid #eee;width:40%;'>$label</th><td style='padding:8px 10px;border-bottom:1px solid #eee;'>$value</td></tr>";
            }
        }
        ?>
    </table>

    <!-- Botón para añadir al carrito -->
    <form method="post" action="">
        <input type="hidden" name="print_order_id" value="<?php echo esc_attr($post_id); ?>">
        <button type="submit" name="add_print_order_to_cart" class="button alt" style="margin-top:1.5em;">Add to Cart</button>
    </form>

</div>

<!-- Modal -->
<div id="edit-modal" class="ppp-modal">
  <div class="ppp-modal-content">
    <span id="close-editor" class="ppp-modal-close">&times;</span>
    <?php echo do_shortcode('[edit_print_order_form]'); ?>
  </div>
</div>

<!-- Estilos -->
<style>
.ppp-edit-icon {
    position: absolute;
    top: 16px;
    right: 16px;
    width: 42px;
    height: 42px;
    border-radius: 50%;
    background-color: #d4d4d4;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 2px 5px rgba(0,0,0,0.08);
    transition: background-color 0.3s, transform 0.2s;
    z-index: 1001;
}
.ppp-edit-icon:hover {
    background-color: #c0c0c0;
    transform: scale(1.05);
}
.ppp-edit-icon i {
    color: white;
    font-size: 18px;
}

/* Modal base */
.ppp-modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0; top: 0;
    width: 100%; height: 100%;
    background-color: rgba(0,0,0,0.4);
    justify-content: center;
    align-items: center;
}
/* Modal contenido */
.ppp-modal-content {
    background-color: #fff;
    padding: 2em;
    border-radius: 12px;
    width: 90%;
    max-width: 600px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
    position: relative;
}

/* Botón cerrar */
.ppp-modal-close {
    position: absolute;
    top: 12px;
    right: 16px;
    font-size: 24px;
    font-weight: bold;
    color: #888;
    cursor: pointer;
}
.ppp-modal-close:hover {
    color: #333;
}
</style>

<!-- Scripts -->
<script>
document.getElementById("toggle-editor").addEventListener("click", function () {
    document.getElementById("edit-modal").style.display = "flex";
});
document.getElementById("close-editor").addEventListener("click", function () {
    document.getElementById("edit-modal").style.display = "none";
});
window.addEventListener("click", function (event) {
    const modal = document.getElementById("edit-modal");
    if (event.target === modal) {
        modal.style.display = "none";
    }
});

document.addEventListener('DOMContentLoaded', () => {
    const hash = window.location.hash;
    if (hash === '#updated') {
        const box = document.getElementById('edit-notification');
        box.innerHTML = '<strong>✅ Order updated successfully.</strong>';
        box.style.background = '#e6ffed';
        box.style.border = '1px solid #b2e2b2';
        box.style.color = '#256029';
        box.style.display = 'block';
    }
});
</script>

<?php
endwhile; endif;
get_footer();
