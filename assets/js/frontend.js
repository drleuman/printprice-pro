jQuery(document).ready(function ($) {
    $('#ppp-add-to-cart').on('click', function (e) {
        e.preventDefault();

        const orderId = $(this).data('order-id');
        const price = $(this).data('price');
        const productId = 989377; // ID de tu producto base "AI Print Order"

        if (!orderId || !price || !productId) {
            alert('❌ Missing order ID, price or product ID.');
            return;
        }

        $.ajax({
            url: '/wp-json/ppp/v1/add-to-cart',
            method: 'POST',
            data: JSON.stringify({
                print_order_id: orderId,
                price: parseFloat(price),
                product_id: productId
            }),
            contentType: 'application/json',
            success: function () {
                window.location.href = '/cart';
            },
            error: function () {
                alert('❌ Error adding to cart.');
            }
        });
    });
});
