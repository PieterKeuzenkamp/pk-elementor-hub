<?php
/**
 * Main plugin class
 */
class PK_Elementor_Hub {
    /**
     * Plugin instance.
     *
     * @var PK_Elementor_Hub
     */
    private static $_instance = null;

    /**
     * Get plugin instance.
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
        $this->logger = new WP_Error();
        $this->cache = [];
        add_action('plugins_loaded', [$this, 'init']);
    }

    /**
     * Initialize plugin.
     */
    public function init() {
        // Load translations
        load_plugin_textdomain('pk-elementor-hub', false, dirname(plugin_basename(PK_ELEMENTOR_HUB_FILE)) . '/languages');

        // Load dependencies
        $this->load_dependencies();

        // Initialize components
        $this->init_components();

        // Initialize admin
        if (is_admin()) {
            $this->init_admin();
        }

        // Register hooks
        $this->register_hooks();

        // Fire init action for extensions to register
        do_action('pk_elementor_hub_init');
    }

    /**
     * Load plugin dependencies.
     */
    private function load_dependencies() {
        require_once PK_ELEMENTOR_HUB_PATH . 'includes/class-pk-elementor-hub-extensions.php';
        require_once PK_ELEMENTOR_HUB_PATH . 'includes/class-pk-elementor-hub-updater.php';
        require_once PK_ELEMENTOR_HUB_PATH . 'includes/class-pk-elementor-hub-license.php';
        require_once PK_ELEMENTOR_HUB_PATH . 'admin/class-pk-elementor-hub-admin.php';
    }

    /**
     * Initialize components.
     */
    private function init_components() {
        // Initialize extensions manager
        PK_Elementor_Hub_Extensions::instance();

        // Initialize updater
        PK_Elementor_Hub_Updater::instance();

        // Initialize license manager
        PK_Elementor_Hub_License::instance();
    }

    /**
     * Initialize admin functionality.
     */
    private function init_admin() {
        PK_Elementor_Hub_Admin::instance();
    }

    /**
     * Register plugin hooks.
     */
    private function register_hooks() {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        add_action('wp_ajax_pk_elementor_hub_get_extension_status', [$this, 'ajax_get_extension_status']);
    }

    /**
     * Enqueue admin assets.
     */
    public function enqueue_admin_assets() {
        wp_enqueue_style(
            'pk-elementor-hub-admin',
            PK_ELEMENTOR_HUB_URL . 'assets/css/admin.css',
            [],
            PK_ELEMENTOR_HUB_VERSION
        );

        wp_enqueue_script(
            'pk-elementor-hub-admin',
            PK_ELEMENTOR_HUB_URL . 'assets/js/admin.js',
            ['jquery'],
            PK_ELEMENTOR_HUB_VERSION,
            true
        );

        wp_localize_script('pk-elementor-hub-admin', 'pk_elementor_hub', [
            'nonce' => wp_create_nonce('pk_elementor_hub_nonce'),
            'ajax_url' => admin_url('admin-ajax.php'),
        ]);
    }

    /**
     * Get extension status via AJAX.
     */
    public function ajax_get_extension_status() {
        check_ajax_referer('pk_elementor_hub_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            $this->log_error('pk_elementor_hub_ajax_error', __('You do not have permission to perform this action.', 'pk-elementor-hub'));
            wp_send_json_error(['message' => __('You do not have permission to perform this action.', 'pk-elementor-hub')]);
        }

        $extension = sanitize_text_field($_POST['extension']);
        
        $status = PK_Elementor_Hub_Extensions::instance()->get_extension_status($extension);
        $update_status = PK_Elementor_Hub_Updater::instance()->get_extension_update_status($extension);
        $license_status = PK_Elementor_Hub_License::instance()->check_license_status($extension);

        $cache_key = 'pk_elementor_hub_extension_status_' . $extension;
        $cached_status = $this->get_cache($cache_key);

        if ($cached_status) {
            wp_send_json_success($cached_status);
        } else {
            $status_data = [
                'status' => $status,
                'updates' => $update_status,
                'license' => $license_status,
            ];
            $this->set_cache($cache_key, $status_data);
            wp_send_json_success($status_data);
        }
    }

    /**
     * Log error message.
     */
    private function log_error($code, $message) {
        $this->logger->add($code, $message);
        error_log(sprintf('[PK Elementor Hub] %s: %s', $code, $message));
    }

    /**
     * Get cached value.
     */
    private function get_cache($key) {
        return isset($this->cache[$key]) ? $this->cache[$key] : false;
    }

    /**
     * Set cache value.
     */
    private function set_cache($key, $value, $expiry = 3600) {
        $this->cache[$key] = [
            'value' => $value,
            'expiry' => time() + $expiry
        ];
    }
}
