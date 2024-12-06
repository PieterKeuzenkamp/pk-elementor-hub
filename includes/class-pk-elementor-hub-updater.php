<?php
/**
 * Extension updater class
 */
class PK_Elementor_Hub_Updater {
    /**
     * Class instance.
     *
     * @var PK_Elementor_Hub_Updater
     */
    private static $_instance = null;

    /**
     * Update API endpoint.
     *
     * @var string
     */
    private $api_url = 'https://www.pieterkeuzenkamp.nl/wp-json/pk-elementor/v1/updates';

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
        add_filter('pre_set_site_transient_update_plugins', [$this, 'check_for_updates']);
        add_filter('plugins_api', [$this, 'plugin_info'], 10, 3);
        add_action('admin_init', [$this, 'schedule_update_checks']);
    }

    /**
     * Schedule daily update checks.
     */
    public function schedule_update_checks() {
        if (!wp_next_scheduled('pk_elementor_hub_check_updates')) {
            wp_schedule_event(time(), 'daily', 'pk_elementor_hub_check_updates');
        }
    }

    /**
     * Check for updates.
     */
    public function check_for_updates($transient) {
        if (empty($transient->checked)) {
            return $transient;
        }

        $extensions = PK_Elementor_Hub_Extensions::instance()->get_registered_extensions();

        foreach ($extensions as $slug => $extension) {
            $response = wp_remote_post($this->api_url . '/check', [
                'body' => [
                    'slug' => $slug,
                    'version' => $extension['version'],
                    'license_key' => get_option('pk_elementor_' . $slug . '_license_key'),
                ]
            ]);

            if (is_wp_error($response)) {
                continue;
            }

            $update_info = json_decode(wp_remote_retrieve_body($response));

            if (!empty($update_info) && version_compare($extension['version'], $update_info->new_version, '<')) {
                $transient->response[$slug . '/' . $slug . '.php'] = (object) [
                    'slug' => $slug,
                    'new_version' => $update_info->new_version,
                    'url' => $update_info->url,
                    'package' => $update_info->package,
                    'tested' => $update_info->tested,
                    'requires' => $update_info->requires,
                    'requires_php' => $update_info->requires_php,
                ];
            }
        }

        return $transient;
    }

    /**
     * Get plugin information for the updates screen.
     */
    public function plugin_info($result, $action, $args) {
        if ($action !== 'plugin_information') {
            return $result;
        }

        $extensions = PK_Elementor_Hub_Extensions::instance()->get_registered_extensions();

        if (!isset($extensions[$args->slug])) {
            return $result;
        }

        $response = wp_remote_post($this->api_url . '/info', [
            'body' => [
                'slug' => $args->slug,
                'license_key' => get_option('pk_elementor_' . $args->slug . '_license_key'),
            ]
        ]);

        if (is_wp_error($response)) {
            return $result;
        }

        $info = json_decode(wp_remote_retrieve_body($response));

        if (!empty($info)) {
            return (object) [
                'name' => $info->name,
                'slug' => $args->slug,
                'version' => $info->version,
                'author' => $info->author,
                'author_profile' => $info->author_profile,
                'requires' => $info->requires,
                'tested' => $info->tested,
                'requires_php' => $info->requires_php,
                'sections' => [
                    'description' => $info->sections->description,
                    'changelog' => $info->sections->changelog,
                ],
                'banners' => $info->banners,
                'download_link' => $info->download_link,
            ];
        }

        return $result;
    }

    /**
     * Get update status for an extension.
     */
    public function get_extension_update_status($slug) {
        $status = [
            'has_update' => false,
            'current_version' => '',
            'new_version' => '',
            'package_url' => '',
            'last_checked' => get_option('pk_elementor_' . $slug . '_last_update_check'),
        ];

        $updates = get_site_transient('update_plugins');
        if (isset($updates->response[$slug . '/' . $slug . '.php'])) {
            $update = $updates->response[$slug . '/' . $slug . '.php'];
            $status['has_update'] = true;
            $status['new_version'] = $update->new_version;
            $status['package_url'] = $update->package;
        }

        return $status;
    }
}
