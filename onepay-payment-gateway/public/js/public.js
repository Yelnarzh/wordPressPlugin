jQuery(document).ready(function($) {
    $('#onepay-form, #onepay-subscription-form').on('submit', function(e) {
        e.preventDefault();
        
        var $form = $(this);
        var $errors = $form.find('.onepay-errors');
        var $button = $form.find('.onepay-submit-button');
        
        $errors.removeClass('show').text('');
        $button.prop('disabled', true).text('Processing...');
        
        var formData = {
            action: 'onepay_process_payment',
            nonce: onepay_params.nonce,
            amount: $form.find('input[name="amount"]').val(),
            currency: $form.find('input[name="currency"]').val(),
            name: $form.find('input[name="name"]').val(),
            email: $form.find('input[name="email"]').val(),
            description: $form.find('input[name="description"]').val()
        };
        
        $.ajax({
            url: onepay_params.ajax_url,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    if (response.data.redirect_url) {
                        window.location.href = response.data.redirect_url;
                    } else {
                        $errors.removeClass('show');
                        $('<div class="onepay-success show">Payment created! Redirecting...</div>').insertBefore($button);
                        $form[0].reset();
                    }
                } else {
                    $errors.addClass('show').text(response.data.message || 'An error occurred.');
                    $button.prop('disabled', false).text($button.data('original-text') || 'Pay Now');
                }
            },
            error: function() {
                $errors.addClass('show').text('An error occurred. Please try again.');
                $button.prop('disabled', false).text($button.data('original-text') || 'Pay Now');
            }
        });
    });
});
