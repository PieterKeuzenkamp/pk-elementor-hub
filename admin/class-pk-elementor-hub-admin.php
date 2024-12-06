<?php
/**
 * Admin functionality class
 */
class PK_Elementor_Hub_Admin {
    /**
     * Class instance.
     *
     * @var PK_Elementor_Hub_Admin
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
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
    }

    /**
     * Add admin menu items.
     */
    public function add_admin_menu() {
        add_menu_page(
            __('PK Extensions', 'pk-elementor-hub'),
            __('PK Extensions', 'pk-elementor-hub'),
            'manage_options',
            'pk-elementor-hub',
            [$this, 'render_dashboard_page'],
            'dashicons-admin-plugins',
            30
        );

        add_submenu_page(
            'pk-elementor-hub',
            __('Dashboard', 'pk-elementor-hub'),
            __('Dashboard', 'pk-elementor-hub'),
            'manage_options',
            'pk-elementor-hub',
            [$this, 'render_dashboard_page']
        );

        add_submenu_page(
            'pk-elementor-hub',
            __('Licenses', 'pk-elementor-hub'),
            __('Licenses', 'pk-elementor-hub'),
            'manage_options',
            'pk-elementor-hub-licenses',
            [$this, 'render_licenses_page']
        );

        add_submenu_page(
            'pk-elementor-hub',
            __('Settings', 'pk-elementor-hub'),
            __('Settings', 'pk-elementor-hub'),
            'manage_options',
            'pk-elementor-hub-settings',
            [$this, 'render_settings_page']
        );
    }

    /**
     * Register settings.
     */
    public function register_settings() {
        register_setting('pk_elementor_hub_settings', 'pk_elementor_hub_check_updates');
        register_setting('pk_elementor_hub_settings', 'pk_elementor_hub_auto_updates');
    }

    /**
     * Render dashboard page.
     */
    public function render_dashboard_page() {
        $extensions = PK_Elementor_Hub_Extensions::instance()->get_registered_extensions();
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('PK Elementor Extensions', 'pk-elementor-hub'); ?></h1>
            
            <div class="pk-extensions-grid">
                <?php foreach ($extensions as $slug => $extension) : 
                    $status = PK_Elementor_Hub_Extensions::instance()->get_extension_status($slug);
                    $update_status = PK_Elementor_Hub_Updater::instance()->get_extension_update_status($slug);
                    $license_status = PK_Elementor_Hub_License::instance()->check_license_status($slug);
                ?>
                    <div class="pk-extension-card">
                        <h2><?php echo esc_html($extension['name']); ?></h2>
                        <p><?php echo esc_html($extension['description']); ?></p>
                        
                        <div class="pk-extension-status">
                            <span class="status-indicator <?php echo $status['active'] ? 'active' : 'inactive'; ?>">
                                <?php echo $status['active'] ? esc_html__('Active', 'pk-elementor-hub') : esc_html__('Inactive', 'pk-elementor-hub'); ?>
                            </span>
                            
                            <?php if ($update_status['has_update']) : ?>
                                <span class="update-available">
                                    <?php printf(
                                        esc_html__('Update available: %s', 'pk-elementor-hub'),
                                        esc_html($update_status['new_version'])
                                    ); ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <div class="pk-extension-meta">
                            <span class="version">v<?php echo esc_html($extension['version']); ?></span>
                            <?php if ($extension['pro_available']) : ?>
                                <?php if ($license_status['status'] === 'valid') : ?>
                                    <span class="pro-badge active"><?php echo esc_html__('PRO', 'pk-elementor-hub'); ?></span>
                                <?php else : ?>
                                    <a href="<?php echo admin_url('admin.php?page=pk-elementor-hub-licenses'); ?>" class="button button-secondary">
                                        <?php echo esc_html__('Upgrade to PRO', 'pk-elementor-hub'); ?>
                                    </a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>

                        <div class="pk-extension-actions">
                            <?php if ($status['installed']) : ?>
                                <?php if ($status['active']) : ?>
                                    <button class="button pk-extension-toggle" data-extension="<?php echo esc_attr($slug); ?>" data-action="deactivate">
                                        <?php echo esc_html__('Deactivate', 'pk-elementor-hub'); ?>
                                    </button>
                                <?php else : ?>
                                    <button class="button button-primary pk-extension-toggle" data-extension="<?php echo esc_attr($slug); ?>" data-action="activate">
                                        <?php echo esc_html__('Activate', 'pk-elementor-hub'); ?>
                                    </button>
                                <?php endif; ?>
                                
                                <?php if ($update_status['has_update']) : ?>
                                    <button class="button pk-extension-update" data-extension="<?php echo esc_attr($slug); ?>">
                                        <?php echo esc_html__('Update Now', 'pk-elementor-hub'); ?>
                                    </button>
                                <?php endif; ?>
                            <?php else : ?>
                                <button class="button button-primary pk-extension-install" data-extension="<?php echo esc_attr($slug); ?>">
                                    <?php echo esc_html__('Install', 'pk-elementor-hub'); ?>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }

    /**
     * Render licenses page.
     */
    public function render_licenses_page() {
        $extensions = PK_Elementor_Hub_Extensions::instance()->get_registered_extensions();
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('PK Extensions Licenses', 'pk-elementor-hub'); ?></h1>
            
            <div class="pk-licenses-grid">
                <?php foreach ($extensions as $slug => $extension) :
                    if (!$extension['pro_available']) continue;
                    
                    $license_status = PK_Elementor_Hub_License::instance()->check_license_status($slug);
                ?>
                    <div class="pk-license-card">
                        <h2><?php echo esc_html($extension['name']); ?></h2>
                        
                        <?php if ($license_status['status'] === 'valid') : ?>
                            <div class="pk-license-info">
                                <p class="status valid"><?php echo esc_html__('License Active', 'pk-elementor-hub'); ?></p>
                                <?php if ($license_status['expiry']) : ?>
                                    <p class="expiry">
                                        <?php printf(
                                            esc_html__('Expires: %s', 'pk-elementor-hub'),
                                            date_i18n(get_option('date_format'), strtotime($license_status['expiry']))
                                        ); ?>
                                    </p>
                                <?php endif; ?>
                                <button class="button pk-license-deactivate" data-extension="<?php echo esc_attr($slug); ?>">
                                    <?php echo esc_html__('Deactivate License', 'pk-elementor-hub'); ?>
                                </button>
                            </div>
                        <?php else : ?>
                            <div class="pk-license-form">
                                <input type="text" class="regular-text" placeholder="<?php echo esc_attr__('Enter License Key', 'pk-elementor-hub'); ?>">
                                <button class="button button-primary pk-license-activate" data-extension="<?php echo esc_attr($slug); ?>">
                                    <?php echo esc_html__('Activate License', 'pk-elementor-hub'); ?>
                                </button>
                                <?php if ($license_status['message']) : ?>
                                    <p class="status-message"><?php echo esc_html($license_status['message']); ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }

    /**
     * Render settings page.
     */
    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('PK Extensions Settings', 'pk-elementor-hub'); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('pk_elementor_hub_settings');
                do_settings_sections('pk_elementor_hub_settings');
                ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php echo esc_html__('Update Checking', 'pk-elementor-hub'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="pk_elementor_hub_check_updates" value="1" <?php checked(get_option('pk_elementor_hub_check_updates', true)); ?>>
                                <?php echo esc_html__('Automatically check for updates', 'pk-elementor-hub'); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo esc_html__('Automatic Updates', 'pk-elementor-hub'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="pk_elementor_hub_auto_updates" value="1" <?php checked(get_option('pk_elementor_hub_auto_updates', false)); ?>>
                                <?php echo esc_html__('Enable automatic updates for extensions', 'pk-elementor-hub'); ?>
                            </label>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
}
