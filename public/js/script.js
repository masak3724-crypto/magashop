document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.minus-btn').forEach(function (btn) {
        if (btn.closest('.cart-qty-form')) {
            return;
        }

        btn.addEventListener('click', function () {
            var input = this.parentElement.querySelector('.quantity-input');
            var val = parseInt(input.value, 10) || 1;
            if (val > 1) {
                input.value = val - 1;
            }
        });
    });

    document.querySelectorAll('.plus-btn').forEach(function (btn) {
        if (btn.closest('.cart-qty-form')) {
            return;
        }

        btn.addEventListener('click', function () {
            var input = this.parentElement.querySelector('.quantity-input');
            var val = parseInt(input.value, 10) || 1;
            var max = parseInt(input.getAttribute('max'), 10) || 100;
            if (val < max) {
                input.value = val + 1;
            }
        });
    });
});
