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

// Cargar lógica tras plugins
add_action('plugins_loaded', function () {
    if (!function_exists('get_field')) {
        add_action('admin_notices', function () {
            echo '<div class="notice notice-error"><p><strong>PrintPrice Pro:</strong> ACF plugin is required.</p></div>';
        });
        return;
    }

    // Includes estructurados
    require_once PPP_PATH . 'includes/register-post-type.php';
    require_once PPP_PATH . 'includes/pdf/pdf-generator.php';
    require_once PPP_PATH . 'includes/api/create-order-endpoint.php';
    require_once PPP_PATH . 'includes/cart/cart-hooks.php';
    require_once PPP_PATH . 'includes/cart/add-to-cart.php';

    // Scripts & styles
    add_action('wp_enqueue_scripts', function () {
        if (is_page('seleccion-imprenta') || is_singular('print_order')) {
            wp_enqueue_script('ppp-frontend', PPP_URL . 'assets/js/frontend.js', ['jquery'], '1.0.0', true);
            wp_enqueue_style('ppp-style', PPP_URL . 'assets/css/frontend.css', [], '1.0.0');
        }
    });
});

require_once plugin_dir_path(__FILE__) . 'includes/admin/dashboard.php';