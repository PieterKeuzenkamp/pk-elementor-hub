<?php
/**
 * Extensions management class
 */
class PK_Elementor_Hub_Extensions {
    /**
     * Class instance.
     *
     * @var PK_Elementor_Hub_Extensions
     */
    private static $_instance = null;

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
        add_action('init', [$this, 'init']);
    }

    /**
     * Initialize class.
     */
    public function init() {
        // Register known extensions
        $this->register_known_extensions();
    }

    /**
     * Register known PK Elementor extensions.
     */
    private function register_known_extensions() {
        $extensions = [
            'pk-elementor-service-box' => [
                'name' => 'PK Elementor Service Box',
                'description' => __('Create beautiful service boxes with icons and descriptions.', 'pk-elementor-hub'),
                'version' => '2.0.0',
                'requires' => '5.0',
                'tested' => '6.4',
                'author' => 'Pieter Keuzenkamp',
                'author_uri' => 'https://www.pieterkeuzenkamp.nl',
                'pro_available' => false,
            ],
            'pk-elementor-ai' => [
                'name' => 'PK Elementor AI',
                'description' => __('AI-powered content generation for Elementor.', 'pk-elementor-hub'),
                'version' => '2.0.0',
                'requires' => '5.0',
                'tested' => '6.4',
                'author' => 'Pieter Keuzenkamp',
                'author_uri' => 'https://www.pieterkeuzenkamp.nl',
                'pro_available' => true,
            ],
        ];

        foreach ($extensions as $slug => $data) {
            pk_elementor_hub()->register_extension($data);
        }
    }

    /**
     * Get extension status.
     */
    public function get_extension_status($slug) {
        $status = [
            'installed' => false,
            'active' => false,
            'version' => null,
            'update_available' => false,
            'license_status' => 'free',
        ];

        if (file_exists(WP_PLUGIN_DIR . '/' . $slug)) {
            $status['installed'] = true;
            
            if (is_plugin_active($slug . '/' . $slug . '.php')) {
                $status['active'] = true;
            }

            $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $slug . '/' . $slug . '.php');
            $status['version'] = $plugin_data['Version'];
        }

        return $status;
    }
}
