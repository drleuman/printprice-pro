<?php
defined('ABSPATH') || exit;
get_header();

if (have_posts()) : while (have_posts()) : the_post();
$post_id = get_the_ID();
$total_cost = get_field('selected_print_house_total_cost', $post_id);
?>

<div class="print-order-summary" style="max-width:800px;margin:2em auto;padding:2em;position:relative;background:#fff;border:1px solid #ddd;border-radius:12px;box-shadow:0 4px 12px rgba(0,0,0,0.05);">

    <!-- Botón flotante de edición con icono Font Awesome -->
    <button id="edit-order-btn" style="position:absolute;top:15px;right:15px;background:#000;color:#fff;border:none;padding:10px 12px;border-radius:50%;cursor:pointer;font-size:18px;" title="Edit Order">
        <i class="fas fa-edit"></i>
    </button>

    <h2 style="margin-top:0;font-size:1.4em;color:#333;"><?php the_title(); ?></h2>

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

    <!-- Modal -->
    <div id="edit-modal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.6);z-index:9999;">
        <div style="max-width:700px;margin:5% auto;background:#fff;padding:2em;border-radius:10px;position:relative;">
            <span id="close-edit-modal" style="position:absolute;top:15px;right:20px;cursor:pointer;font-weight:bold;font-size:1.2em;">&times;</span>
            <?php echo do_shortcode('[edit_print_order_form]'); ?>
        </div>
    </div>

    <button id="ppp-add-to-cart"
        data-order-id="<?php echo esc_attr($post_id); ?>"
        data-price="<?php echo esc_attr($total_cost); ?>"
        data-product-id="989377"
        style="margin-top:20px;padding:10px 20px;background:#d10000;color:white;border:none;border-radius:4px;cursor:pointer;">
    Add to Cart
</button>

</div>

<script>
document.getElementById("edit-order-btn").addEventListener("click", function () {
    document.getElementById("edit-modal").style.display = "block";
});
document.getElementById("close-edit-modal").addEventListener("click", function () {
    document.getElementById("edit-modal").style.display = "none";
});
window.addEventListener("click", function (event) {
    const modal = document.getElementById("edit-modal");
    if (event.target === modal) {
        modal.style.display = "none";
    }
});
</script>

<?php
endwhile; endif;
get_footer();
