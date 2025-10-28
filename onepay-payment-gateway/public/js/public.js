jQuery(document).ready(function($) {
    function formatCardNumber(value) {
        var v = value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
        var matches = v.match(/\d{4,16}/g);
        var match = matches && matches[0] || '';
        var parts = [];
        
        for (var i = 0, len = match.length; i < len; i += 4) {
            parts.push(match.substring(i, i + 4));
        }
        
        if (parts.length) {
            return parts.join(' ');
        } else {
            return value;
        }
    }
    
    function formatExpiry(value) {
        var v = value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
        
        if (v.length >= 2) {
            return v.substring(0, 2) + '/' + v.substring(2, 4);
        }
        
        return v;
    }
    
    $('#onepay-card-number, #onepay-sub-card-number').on('input', function() {
        $(this).val(formatCardNumber($(this).val()));
    });
    
    $('#onepay-expiry, #onepay-sub-expiry').on('input', function() {
        $(this).val(formatExpiry($(this).val()));
    });
    
    $('#onepay-cvc, #onepay-sub-cvc').on('input', function() {
        var v = $(this).val().replace(/\s+/g, '').replace(/[^0-9]/gi, '');
        $(this).val(v);
    });
    
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
                        $('<div class="onepay-success show">Payment successful!</div>').insertBefore($button);
                        $form[0].reset();
                    }
                } else {
                    $errors.addClass('show').text(response.data.message || 'An error occurred.');
                    $button.prop('disabled', false).text('Pay Now');
                }
            },
            error: function() {
                $errors.addClass('show').text('An error occurred. Please try again.');
                $button.prop('disabled', false).text('Pay Now');
            }
        });
    });
});
