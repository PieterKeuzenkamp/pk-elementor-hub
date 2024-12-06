jQuery(document).ready(function($) {
    // Handle extension activation/deactivation
    $('.pk-extension-toggle').on('click', function(e) {
        e.preventDefault();
        
        var $button = $(this);
        var extension = $button.data('extension');
        var action = $button.data('action');

        $button.prop('disabled', true);

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'pk_elementor_hub_toggle_extension',
                extension: extension,
                toggle_action: action,
                nonce: pk_elementor_hub.nonce
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.data.message);
                }
            },
            error: function() {
                alert('An error occurred. Please try again.');
            },
            complete: function() {
                $button.prop('disabled', false);
            }
        });
    });

    // Handle license activation
    $('.pk-license-form').on('submit', function(e) {
        e.preventDefault();
        
        var $form = $(this);
        var $submit = $form.find('input[type="submit"]');

        $submit.prop('disabled', true);

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'pk_elementor_hub_activate_license',
                extension: $form.data('extension'),
                license_key: $form.find('input[name="license_key"]').val(),
                nonce: pk_elementor_hub.nonce
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.data.message);
                }
            },
            error: function() {
                alert('An error occurred. Please try again.');
            },
            complete: function() {
                $submit.prop('disabled', false);
            }
        });
    });
});
