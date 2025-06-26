<?php

function register_print_order_post_type() {
    register_post_type('print_order', [
        'labels' => [
            'name' => 'Print Orders',
            'singular_name' => 'Print Order',
        ],
        'public' => true,
        'has_archive' => true,
        'rewrite' => ['slug' => 'print_order'], // 👈 este slug es crucial
        'supports' => ['title', 'editor', 'custom-fields'],
        'show_in_rest' => true,
    ]);
}
add_action('init', 'register_print_order_post_type');
