<?php
/**
 * Template: Edit Print Order
 */

defined('ABSPATH') || exit;

if (!is_user_logged_in()) {
    wp_redirect(wp_login_url());
    exit;
}

get_header();

$post_id = get_the_ID();

if (!get_post_type($post_id) === 'print_order') {
    echo '<p>Invalid print order.</p>';
    get_footer();
    exit;
}

$fields = [
    'copies' => 'Number of Copies',
    'book_size' => 'Book Size',
    'interior_print' => 'Interior Print',
    'cover_print' => 'Cover Print',
    'paper_weight_interior' => 'Paper Weight Interior',
    'paper_weight_cover' => 'Paper Weight Cover',
    'binding_method' => 'Binding Method',
    'finishing_options' => 'Finishing Options',
    'delivery_country' => 'Delivery Country'
];

?>
<div class="ppp-edit-order" style="max-width: 720px; margin: auto; padding: 2em;">
    <h1>Edit Your Print Order</h1>
    <form id="edit-print-order-form">
        <input type="hidden" name="print_order_id" value="<?php echo esc_attr($post_id); ?>">

        <?php foreach ($fields as $key => $label) :
            $value = get_field($key, $post_id);
        ?>
            <p>
                <label for="<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></label><br>
                <input type="text" name="<?php echo esc_attr($key); ?>" value="<?php echo esc_attr($value); ?>">
            </p>
        <?php endforeach; ?>

        <button type="submit">Recalculate and Save</button>
    </form>
    <div id="edit-order-response" style="margin-top: 1em;"></div>
</div>

<script>
jQuery(function ($) {
    $('#edit-print-order-form').on('submit', function (e) {
        e.preventDefault();
        let form = $(this);
        let data = form.serialize();

        $.post('<?php echo admin_url('admin-ajax.php'); ?>', {
            action: 'ppp_update_print_order',
            data: data
        }, function (response) {
            $('#edit-order-response').html('<strong>' + response.message + '</strong>');
        }, 'json');
    });
});
</script>
<?php
get_footer();
