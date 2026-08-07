<?php
/**
 * Plugin Name: WiFi Aggregator Hub
 * Plugin URI: https://github.com/halimurrosyid/wifi-aggregator-hub
 * Description: Mesin indeks dan agregator pencarian provider internet Indonesia dari berbagai feed website (RSS/Atom/Sitemap) dengan pengelompokan wilayah & provider, deduplikasi otomatis, dan landing page SEO.
 * Version: 1.0.6
 * Author: Mujaddid Halimurrosyid Ajid WP
 * Author URI: https://indahweb.com
 * Text Domain: wifi-aggregator-hub
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * GitHub Plugin URI: halimurrosyid/wifi-aggregator-hub
 * Primary Branch: main
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WAH_VERSION', '1.0.6' );
define( 'WAH_PLUGIN_FILE', __FILE__ );
define( 'WAH_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WAH_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WAH_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * The code that runs during plugin activation.
 */
function activate_wifi_aggregator_hub() {
	require_once WAH_PLUGIN_DIR . 'includes/class-wah-activator.php';
	WAH_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_wifi_aggregator_hub() {
	require_once WAH_PLUGIN_DIR . 'includes/class-wah-deactivator.php';
	WAH_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_wifi_aggregator_hub' );
register_deactivation_hook( __FILE__, 'deactivate_wifi_aggregator_hub' );

/**
 * Include the core class responsibility file.
 */
require_once WAH_PLUGIN_DIR . 'includes/class-wah-main.php';

/**
 * Begins execution of the plugin.
 */
function run_wifi_aggregator_hub() {
	$plugin = WAH_Main::get_instance();
	$plugin->run();
}
run_wifi_aggregator_hub();
