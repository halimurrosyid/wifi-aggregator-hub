<?php
/**
 * Main plugin container class.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WAH_Main {

	private static $instance = null;
	protected $loader;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		$this->load_dependencies();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->define_cron_hooks();
	}

	private function load_dependencies() {
		require_once WAH_PLUGIN_DIR . 'includes/class-wah-loader.php';
		require_once WAH_PLUGIN_DIR . 'includes/database/class-wah-db.php';
		require_once WAH_PLUGIN_DIR . 'includes/services/class-wah-provider-detector.php';
		require_once WAH_PLUGIN_DIR . 'includes/services/class-wah-area-detector.php';
		require_once WAH_PLUGIN_DIR . 'includes/services/class-wah-ai-summarizer.php';
		require_once WAH_PLUGIN_DIR . 'includes/services/class-wah-deduplicator.php';
		require_once WAH_PLUGIN_DIR . 'includes/services/class-wah-fetcher.php';
		require_once WAH_PLUGIN_DIR . 'includes/services/class-wah-synchronizer.php';
		require_once WAH_PLUGIN_DIR . 'includes/services/class-wah-link-checker.php';
		require_once WAH_PLUGIN_DIR . 'includes/services/class-wah-seo.php';
		require_once WAH_PLUGIN_DIR . 'includes/services/class-wah-sitemap.php';
		require_once WAH_PLUGIN_DIR . 'includes/services/class-wah-updater.php';
		require_once WAH_PLUGIN_DIR . 'includes/routers/class-wah-router.php';

		// Instantiate Automatic GitHub Updater
		new WAH_Updater( WAH_PLUGIN_FILE );

		if ( is_admin() ) {
			require_once WAH_PLUGIN_DIR . 'admin/class-wah-admin.php';
		}

		require_once WAH_PLUGIN_DIR . 'public/class-wah-public.php';

		$this->loader = new WAH_Loader();
	}

	private function define_admin_hooks() {
		if ( is_admin() ) {
			$admin = new WAH_Admin();
			$this->loader->add_action( 'admin_menu', $admin, 'add_admin_menu' );
			$this->loader->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_styles_and_scripts' );
			$this->loader->add_action( 'wp_ajax_wah_sync_now', $admin, 'ajax_sync_now' );
			$this->loader->add_action( 'wp_ajax_wah_check_links', $admin, 'ajax_check_links' );
		}
	}

	private function define_public_hooks() {
		$public = new WAH_Public();
		$this->loader->add_filter( 'query_vars', 'WAH_Router', 'register_query_vars' );
		$this->loader->add_action( 'template_redirect', 'WAH_Router', 'template_redirect' );
		$this->loader->add_action( 'wp_enqueue_scripts', $public, 'enqueue_styles_and_scripts' );
		$this->loader->add_action( 'wp_ajax_wah_search_autocomplete', $public, 'ajax_search_autocomplete' );
		$this->loader->add_action( 'wp_ajax_nopriv_wah_search_autocomplete', $public, 'ajax_search_autocomplete' );

		// Register Shortcodes
		add_shortcode( 'wifi_search_box', array( $public, 'render_search_shortcode' ) );
		add_shortcode( 'wifi_area_grid', array( $public, 'render_area_grid_shortcode' ) );
		add_shortcode( 'wifi_provider_grid', array( $public, 'render_provider_grid_shortcode' ) );

		$this->loader->add_action( 'wp_ajax_wah_track_click', $public, 'ajax_track_click' );
		$this->loader->add_action( 'wp_ajax_nopriv_wah_track_click', $public, 'ajax_track_click' );
	}

	private function define_cron_hooks() {
		$this->loader->add_action( 'wah_cron_sync_feeds', 'WAH_Synchronizer', 'sync_all' );
		$this->loader->add_action( 'wah_cron_check_links', 'WAH_Link_Checker', 'check_all' );
	}

	public function run() {
		$this->loader->run();
	}
}
