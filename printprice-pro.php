<?php
/**
 * Plugin Name: PrintPrice Pro
 * Description: Unified plugin for AI-driven print order generation, print house selection, WooCommerce integration and PDF export.
 * Version: 1.0.0
 * Author: Manuel Enrique Morales
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

defined('ABSPATH') || exit;

define('PPP_PATH', plugin_dir_path(__FILE__));
define('PPP_URL', plugin_dir_url(__FILE__));

// Wait until all plugins are loaded
add_action('plugins_loaded', function () {
    // Require ACF
    if (!function_exists('get_field')) {
        add_action('admin_notices', function () {
            echo '<div class="notice notice-error"><p><strong>PrintPrice Pro:</strong> ACF plugin is required.</p></div>';
        });
        return;
    }

    // 1. Helpers and utilities
    require_once PPP_PATH . 'includes/api/functions.php';

    // 2. Register custom post type
    require_once PPP_PATH . 'includes/register-post-type.php';

    // 3. PDF generation
    require_once PPP_PATH . 'includes/pdf/pdf-generator.php';

    // 4. API Endpoints
    require_once PPP_PATH . 'includes/api/create-order-endpoint.php';
    require_once PPP_PATH . 'includes/api/api-endpoint.php';

    // 5. WooCommerce integration
    require_once PPP_PATH . 'includes/cart/cart-hooks.php';
    require_once PPP_PATH . 'includes/cart/add-to-cart.php';

    // 6. Admin dashboard and print order editor
    require_once PPP_PATH . 'includes/admin/dashboard.php';
    require_once PPP_PATH . 'includes/api/edit-order-form-selects.php';

    // 7. Frontend assets
    add_action('wp_enqueue_scripts', function () {
        if (is_singular('print_order')) {
            wp_enqueue_script('ppp-frontend', PPP_URL . 'assets/js/frontend.js', ['jquery'], '1.0.0', true);
            wp_enqueue_style('ppp-style', PPP_URL . 'assets/css/frontend.css', [], '1.0.0');
        }
    });
});
if (class_exists('WooCommerce')) {
    // Aplicar precio personalizado desde el campo 'custom_price'
    add_filter('woocommerce_before_calculate_totals', function ($cart) {
        if (is_admin() && !defined('DOING_AJAX')) return;

        foreach ($cart->get_cart() as $cart_item) {
            if (isset($cart_item['custom_price'])) {
                $cart_item['data']->set_price(floatval($cart_item['custom_price']));
            }
        }
    });

    // Mostrar el nombre personalizado del pedido en el carrito
    add_filter('woocommerce_cart_item_name', function ($name, $cart_item) {
        if (isset($cart_item['print_order_id'])) {
            $post_title = get_the_title($cart_item['print_order_id']);
            return $post_title . ' (Custom Order)';
        }
        return $name;
    }, 10, 2);
}

