<?php
/**
 * Plugin Name: PK Elementor Hub
 * Description: Central management dashboard for PK Elementor extensions.
 * Version: 1.0.0-alpha
 * Author: Pieter Keuzenkamp
 * Author URI: https://www.pieterkeuzenkamp.nl
 * Text Domain: pk-elementor-hub
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Plugin constants
define('PK_ELEMENTOR_HUB_VERSION', '1.0.0-alpha');
define('PK_ELEMENTOR_HUB_FILE', __FILE__);
define('PK_ELEMENTOR_HUB_PATH', plugin_dir_path(__FILE__));
define('PK_ELEMENTOR_HUB_URL', plugin_dir_url(__FILE__));

// Load the main plugin class
require_once PK_ELEMENTOR_HUB_PATH . 'includes/class-pk-elementor-hub.php';

/**
 * Initialize the main plugin
 */
function pk_elementor_hub() {
    return PK_Elementor_Hub::instance();
}

// Start the plugin
pk_elementor_hub();
