<?php
/**
 * Admin Panel Controller & Navigation Manager.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WAH_Admin {

	/**
	 * Register main menu and submenus.
	 */
	public function add_admin_menu() {
		add_menu_page(
			__( 'WiFi Aggregator Hub', 'wifi-aggregator-hub' ),
			__( 'WiFi Aggregator', 'wifi-aggregator-hub' ),
			'manage_options',
			'wifi-aggregator-hub',
			array( $this, 'render_dashboard' ),
			'dashicons-rss',
			30
		);

		add_submenu_page(
			'wifi-aggregator-hub',
			__( 'Dashboard', 'wifi-aggregator-hub' ),
			__( 'Dashboard', 'wifi-aggregator-hub' ),
			'manage_options',
			'wifi-aggregator-hub',
			array( $this, 'render_dashboard' )
		);

		add_submenu_page(
			'wifi-aggregator-hub',
			__( 'Feed Sources', 'wifi-aggregator-hub' ),
			__( 'Feed Sources', 'wifi-aggregator-hub' ),
			'manage_options',
			'wah-feeds',
			array( $this, 'render_feeds' )
		);

		add_submenu_page(
			'wifi-aggregator-hub',
			__( 'Synchronization', 'wifi-aggregator-hub' ),
			__( 'Synchronization', 'wifi-aggregator-hub' ),
			'manage_options',
			'wah-sync',
			array( $this, 'render_sync' )
		);

		add_submenu_page(
			'wifi-aggregator-hub',
			__( 'Provider Manager', 'wifi-aggregator-hub' ),
			__( 'Provider Manager', 'wifi-aggregator-hub' ),
			'manage_options',
			'wah-providers',
			array( $this, 'render_providers' )
		);

		add_submenu_page(
			'wifi-aggregator-hub',
			__( 'Area Manager', 'wifi-aggregator-hub' ),
			__( 'Area Manager', 'wifi-aggregator-hub' ),
			'manage_options',
			'wah-areas',
			array( $this, 'render_areas' )
		);

		add_submenu_page(
			'wifi-aggregator-hub',
			__( 'Duplicate Manager', 'wifi-aggregator-hub' ),
			__( 'Duplicate Manager', 'wifi-aggregator-hub' ),
			'manage_options',
			'wah-duplicate',
			array( $this, 'render_duplicate' )
		);

		add_submenu_page(
			'wifi-aggregator-hub',
			__( 'Landing Pages', 'wifi-aggregator-hub' ),
			__( 'Landing Pages', 'wifi-aggregator-hub' ),
			'manage_options',
			'wah-landings',
			array( $this, 'render_landings' )
		);

		add_submenu_page(
			'wifi-aggregator-hub',
			__( 'SEO Manager', 'wifi-aggregator-hub' ),
			__( 'SEO', 'wifi-aggregator-hub' ),
			'manage_options',
			'wah-seo',
			array( $this, 'render_seo' )
		);

		add_submenu_page(
			'wifi-aggregator-hub',
			__( 'System Logs', 'wifi-aggregator-hub' ),
			__( 'Logs', 'wifi-aggregator-hub' ),
			'manage_options',
			'wah-logs',
			array( $this, 'render_logs' )
		);

		add_submenu_page(
			'wifi-aggregator-hub',
			__( 'Settings', 'wifi-aggregator-hub' ),
			__( 'Settings', 'wifi-aggregator-hub' ),
			'manage_options',
			'wah-settings',
			array( $this, 'render_settings' )
		);
	}

	/**
	 * Enqueue admin scripts & styles.
	 */
	public function enqueue_styles_and_scripts( $hook ) {
		if ( false === strpos( $hook, 'wah-' ) && false === strpos( $hook, 'wifi-aggregator-hub' ) ) {
			return;
		}

		wp_enqueue_style( 'wah-admin-css', WAH_PLUGIN_URL . 'admin/assets/css/admin.css', array(), WAH_VERSION );
		wp_enqueue_script( 'wah-admin-js', WAH_PLUGIN_URL . 'admin/assets/js/admin.js', array( 'jquery' ), WAH_VERSION, true );

		wp_localize_script(
			'wah-admin-js',
			'wahAdmin',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'wah_admin_nonce' ),
			)
		);
	}

	// -------------------------------------------------------------------------
	// Render View Callbacks
	// -------------------------------------------------------------------------

	public function render_dashboard() {
		require_once WAH_PLUGIN_DIR . 'admin/views/dashboard.php';
	}

	public function render_feeds() {
		require_once WAH_PLUGIN_DIR . 'admin/views/feeds.php';
	}

	public function render_sync() {
		require_once WAH_PLUGIN_DIR . 'admin/views/sync.php';
	}

	public function render_providers() {
		require_once WAH_PLUGIN_DIR . 'admin/views/providers.php';
	}

	public function render_areas() {
		require_once WAH_PLUGIN_DIR . 'admin/views/areas.php';
	}

	public function render_duplicate() {
		require_once WAH_PLUGIN_DIR . 'admin/views/duplicate.php';
	}

	public function render_landings() {
		require_once WAH_PLUGIN_DIR . 'admin/views/landings.php';
	}

	public function render_seo() {
		require_once WAH_PLUGIN_DIR . 'admin/views/seo.php';
	}

	public function render_logs() {
		require_once WAH_PLUGIN_DIR . 'admin/views/logs.php';
	}

	public function render_settings() {
		require_once WAH_PLUGIN_DIR . 'admin/views/settings.php';
	}

	// -------------------------------------------------------------------------
	// AJAX Handlers
	// -------------------------------------------------------------------------

	public function ajax_sync_now() {
		check_ajax_referer( 'wah_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Permission denied.' );
		}

		$feed_id = isset( $_POST['feed_id'] ) ? intval( $_POST['feed_id'] ) : 0;

		if ( $feed_id > 0 ) {
			$res = WAH_Synchronizer::sync_feed( $feed_id );
			if ( is_wp_error( $res ) ) {
				wp_send_json_error( $res->get_error_message() );
			}
			wp_send_json_success( "Sync feed ID $feed_id berhasil. Artikel baru: {$res['new']}, diperbarui: {$res['updated']}" );
		} else {
			$res = WAH_Synchronizer::sync_all();
			wp_send_json_success( $res['message'] );
		}
	}

	public function ajax_check_links() {
		check_ajax_referer( 'wah_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Permission denied.' );
		}

		$res = WAH_Link_Checker::check_all( 30 );
		wp_send_json_success( "Link checking selesai. Diperiksa: {$res['checked']}, Broken/Noindex: {$res['broken']}" );
	}
}
