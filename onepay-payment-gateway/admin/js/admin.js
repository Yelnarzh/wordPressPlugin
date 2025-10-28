jQuery(document).ready(function($) {
    $('.cancel-subscription').on('click', function(e) {
        e.preventDefault();
        
        var subscriptionId = $(this).data('subscription-id');
        var $button = $(this);
        
        if (!confirm('Are you sure you want to cancel this subscription?')) {
            return;
        }
        
        $button.prop('disabled', true).text('Canceling...');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'onepay_cancel_subscription',
                subscription_id: subscriptionId,
                nonce: onepay_admin_params.nonce
            },
            success: function(response) {
                if (response.success) {
                    alert('Subscription canceled successfully.');
                    location.reload();
                } else {
                    alert('Error: ' + response.data.message);
                    $button.prop('disabled', false).text('Cancel');
                }
            },
            error: function() {
                alert('An error occurred. Please try again.');
                $button.prop('disabled', false).text('Cancel');
            }
        });
    });
});
