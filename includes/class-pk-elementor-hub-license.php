<?php
/**
 * License management class
 */
class PK_Elementor_Hub_License {
    /**
     * Class instance.
     *
     * @var PK_Elementor_Hub_License
     */
    private static $_instance = null;

    /**
     * License API endpoint.
     *
     * @var string
     */
    private $api_url = 'https://www.pieterkeuzenkamp.nl/wp-json/pk-elementor/v1/license';

    /**
     * Get class instance.
     */
    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Constructor.
     */
    public function __construct() {
        add_action('wp_ajax_pk_elementor_hub_activate_license', [$this, 'ajax_activate_license']);
        add_action('wp_ajax_pk_elementor_hub_deactivate_license', [$this, 'ajax_deactivate_license']);
    }

    /**
     * Activate license via AJAX.
     */
    public function ajax_activate_license() {
        check_ajax_referer('pk_elementor_hub_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('You do not have permission to perform this action.', 'pk-elementor-hub')]);
        }

        $extension = sanitize_text_field($_POST['extension']);
        $license_key = sanitize_text_field($_POST['license_key']);

        $result = $this->activate_license($extension, $license_key);

        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }

        wp_send_json_success(['message' => __('License activated successfully.', 'pk-elementor-hub')]);
    }

    /**
     * Deactivate license via AJAX.
     */
    public function ajax_deactivate_license() {
        check_ajax_referer('pk_elementor_hub_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('You do not have permission to perform this action.', 'pk-elementor-hub')]);
        }

        $extension = sanitize_text_field($_POST['extension']);

        $result = $this->deactivate_license($extension);

        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }

        wp_send_json_success(['message' => __('License deactivated successfully.', 'pk-elementor-hub')]);
    }

    /**
     * Activate a license.
     */
    public function activate_license($extension, $license_key) {
        $response = wp_remote_post($this->api_url . '/activate', [
            'body' => [
                'extension' => $extension,
                'license_key' => $license_key,
                'site_url' => home_url(),
            ]
        ]);

        if (is_wp_error($response)) {
            return $response;
        }

        $result = json_decode(wp_remote_retrieve_body($response));

        if (!$result->success) {
            return new WP_Error('license_error', $result->message);
        }

        update_option('pk_elementor_' . $extension . '_license_key', $license_key);
        update_option('pk_elementor_' . $extension . '_license_status', 'valid');
        update_option('pk_elementor_' . $extension . '_license_expiry', $result->expiry);

        return true;
    }

    /**
     * Deactivate a license.
     */
    public function deactivate_license($extension) {
        $license_key = get_option('pk_elementor_' . $extension . '_license_key');

        if (!$license_key) {
            return new WP_Error('license_error', __('No license key found.', 'pk-elementor-hub'));
        }

        $response = wp_remote_post($this->api_url . '/deactivate', [
            'body' => [
                'extension' => $extension,
                'license_key' => $license_key,
                'site_url' => home_url(),
            ]
        ]);

        if (is_wp_error($response)) {
            return $response;
        }

        $result = json_decode(wp_remote_retrieve_body($response));

        if (!$result->success) {
            return new WP_Error('license_error', $result->message);
        }

        delete_option('pk_elementor_' . $extension . '_license_key');
        delete_option('pk_elementor_' . $extension . '_license_status');
        delete_option('pk_elementor_' . $extension . '_license_expiry');

        return true;
    }

    /**
     * Check license status.
     */
    public function check_license_status($extension) {
        $license_key = get_option('pk_elementor_' . $extension . '_license_key');

        if (!$license_key) {
            return [
                'status' => 'invalid',
                'message' => __('No license key found.', 'pk-elementor-hub'),
                'expiry' => null,
            ];
        }

        $response = wp_remote_post($this->api_url . '/check', [
            'body' => [
                'extension' => $extension,
                'license_key' => $license_key,
                'site_url' => home_url(),
            ]
        ]);

        if (is_wp_error($response)) {
            return [
                'status' => 'error',
                'message' => $response->get_error_message(),
                'expiry' => null,
            ];
        }

        $result = json_decode(wp_remote_retrieve_body($response));

        return [
            'status' => $result->status,
            'message' => $result->message,
            'expiry' => $result->expiry ?? null,
        ];
    }
}
