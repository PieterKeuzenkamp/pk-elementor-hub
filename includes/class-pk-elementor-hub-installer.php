<?php
/**
 * Extension installer class
 */
class PK_Elementor_Hub_Installer {
    /**
     * Class instance.
     *
     * @var PK_Elementor_Hub_Installer
     */
    private static $_instance = null;

    /**
     * Installation API endpoint.
     *
     * @var string
     */
    private $api_url = 'https://www.pieterkeuzenkamp.nl/wp-json/pk-elementor/v1/download';

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
        add_action('wp_ajax_pk_elementor_hub_install_extension', [$this, 'ajax_install_extension']);
        add_action('wp_ajax_pk_elementor_hub_activate_extension', [$this, 'ajax_activate_extension']);
        add_action('wp_ajax_pk_elementor_hub_deactivate_extension', [$this, 'ajax_deactivate_extension']);
    }

    /**
     * Install extension via AJAX.
     */
    public function ajax_install_extension() {
        check_ajax_referer('pk_elementor_hub_nonce', 'nonce');

        if (!current_user_can('install_plugins')) {
            wp_send_json_error(['message' => __('You do not have permission to install plugins.', 'pk-elementor-hub')]);
        }

        $extension = sanitize_text_field($_POST['extension']);
        $license_key = sanitize_text_field($_POST['license_key'] ?? '');

        $result = $this->install_extension($extension, $license_key);

        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }

        wp_send_json_success(['message' => __('Extension installed successfully.', 'pk-elementor-hub')]);
    }

    /**
     * Activate extension via AJAX.
     */
    public function ajax_activate_extension() {
        check_ajax_referer('pk_elementor_hub_nonce', 'nonce');

        if (!current_user_can('activate_plugins')) {
            wp_send_json_error(['message' => __('You do not have permission to activate plugins.', 'pk-elementor-hub')]);
        }

        $extension = sanitize_text_field($_POST['extension']);
        $result = $this->activate_extension($extension);

        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }

        wp_send_json_success(['message' => __('Extension activated successfully.', 'pk-elementor-hub')]);
    }

    /**
     * Deactivate extension via AJAX.
     */
    public function ajax_deactivate_extension() {
        check_ajax_referer('pk_elementor_hub_nonce', 'nonce');

        if (!current_user_can('activate_plugins')) {
            wp_send_json_error(['message' => __('You do not have permission to deactivate plugins.', 'pk-elementor-hub')]);
        }

        $extension = sanitize_text_field($_POST['extension']);
        $result = $this->deactivate_extension($extension);

        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }

        wp_send_json_success(['message' => __('Extension deactivated successfully.', 'pk-elementor-hub')]);
    }

    /**
     * Install an extension.
     */
    private function install_extension($extension, $license_key = '') {
        require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        require_once ABSPATH . 'wp-admin/includes/plugin-install.php';

        // Get the download URL from our API
        $response = wp_remote_post($this->api_url, [
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
            return new WP_Error('download_failed', $result->message);
        }

        // Prepare the upgrader
        $skin = new WP_Ajax_Upgrader_Skin();
        $upgrader = new Plugin_Upgrader($skin);

        // Install the plugin
        $install_result = $upgrader->install($result->download_url);

        if (is_wp_error($install_result)) {
            return $install_result;
        }

        if (!$install_result) {
            return new WP_Error('install_failed', __('Plugin installation failed.', 'pk-elementor-hub'));
        }

        // Clean up temporary files
        $this->cleanup_temporary_files();

        return true;
    }

    /**
     * Activate an extension.
     */
    private function activate_extension($extension) {
        $plugin_file = $extension . '/' . $extension . '.php';
        
        if (!file_exists(WP_PLUGIN_DIR . '/' . $plugin_file)) {
            return new WP_Error('plugin_not_found', __('Plugin file not found.', 'pk-elementor-hub'));
        }

        $result = activate_plugin($plugin_file);

        if (is_wp_error($result)) {
            return $result;
        }

        return true;
    }

    /**
     * Deactivate an extension.
     */
    private function deactivate_extension($extension) {
        $plugin_file = $extension . '/' . $extension . '.php';
        
        if (!file_exists(WP_PLUGIN_DIR . '/' . $plugin_file)) {
            return new WP_Error('plugin_not_found', __('Plugin file not found.', 'pk-elementor-hub'));
        }

        deactivate_plugins($plugin_file);

        return true;
    }

    /**
     * Clean up temporary files after installation.
     */
    private function cleanup_temporary_files() {
        global $wp_filesystem;

        if (!$wp_filesystem) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            WP_Filesystem();
        }

        $temp_dir = get_temp_dir();
        $files = glob($temp_dir . 'pk-elementor-*');
        
        foreach ($files as $file) {
            $wp_filesystem->delete($file, true);
        }
    }
}
