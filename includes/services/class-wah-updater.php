<?php
/**
 * Ultra-Reliable GitHub Automatic Plugin Updater Service.
 * Standardized WordPress Transient Format & Direct Raw Branch Verification.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WAH_Updater {

	private $file;
	private $basename;
	private $slug;
	private $username;
	private $repository;
	private $remote_version;
	private $download_url;

	public function __construct( $file ) {
		$this->file         = $file;
		$this->username     = 'halimurrosyid';
		$this->repository   = 'wifi-aggregator-hub';
		$this->basename     = plugin_basename( $this->file );
		$this->slug         = dirname( $this->basename );
		$this->download_url = sprintf( 'https://github.com/%s/%s/raw/main/wifi-aggregator-hub.zip', $this->username, $this->repository );

		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'modify_transient' ) );
		add_filter( 'plugins_api', array( $this, 'plugin_popup' ), 10, 3 );
		add_filter( 'plugin_action_links_' . $this->basename, array( $this, 'add_action_links' ) );
		add_action( 'admin_init', array( $this, 'handle_force_check' ) );
		add_action( 'admin_notices', array( $this, 'show_admin_notices' ) );
	}

	/**
	 * Fetch latest version header directly from GitHub main branch raw file.
	 */
	private function get_remote_version() {
		if ( null !== $this->remote_version ) {
			return $this->remote_version;
		}

		$raw_url  = sprintf( 'https://raw.githubusercontent.com/%s/%s/main/wifi-aggregator-hub.php?nocache=' . time(), $this->username, $this->repository );
		$response = wp_remote_get(
			$raw_url,
			array(
				'timeout'    => 10,
				'user-agent' => 'WiFiAggregatorHub-Updater/1.0',
			)
		);

		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			$this->remote_version = false;
			return false;
		}

		$body = wp_remote_retrieve_body( $response );
		if ( preg_match( '/Version:\s*([0-9\.]+)/i', $body, $matches ) ) {
			$this->remote_version = trim( $matches[1] );
		} else {
			$this->remote_version = false;
		}

		return $this->remote_version;
	}

	/**
	 * Modify WP plugin update transient adhering strictly to WP Core standard.
	 */
	public function modify_transient( $transient ) {
		if ( ! is_object( $transient ) ) {
			$transient = new stdClass();
		}

		$remote_ver = $this->get_remote_version();

		if ( ! empty( $remote_ver ) && version_compare( WAH_VERSION, $remote_ver, '<' ) ) {
			$obj              = new stdClass();
			$obj->slug        = $this->slug;
			$obj->plugin      = $this->basename;
			$obj->new_version = $remote_ver;
			$obj->url         = sprintf( 'https://github.com/%s/%s', $this->username, $this->repository );
			$obj->package     = $this->download_url;
			$obj->icons       = array();

			$transient->response[ $this->basename ] = $obj;
		}

		return $transient;
	}

	/**
	 * Render plugin info modal popup in WP Admin.
	 */
	public function plugin_popup( $result, $action, $args ) {
		if ( 'plugin_information' !== $action || empty( $args->slug ) || ( $args->slug !== $this->slug && $args->slug !== $this->basename ) ) {
			return $result;
		}

		$remote_ver = $this->get_remote_version();

		$plugin = array(
			'name'              => 'WiFi Aggregator Hub',
			'slug'              => $this->slug,
			'plugin_name'       => 'WiFi Aggregator Hub',
			'version'           => $remote_ver ? $remote_ver : WAH_VERSION,
			'author'            => '<a href="https://indahweb.com" target="_blank">Mujaddid Halimurrosyid Ajid WP</a>',
			'homepage'          => sprintf( 'https://github.com/%s/%s', $this->username, $this->repository ),
			'requires'          => '5.8',
			'tested'            => '6.6',
			'downloaded'        => 1000,
			'sections'          => array(
				'description' => 'Mesin indeks dan agregator pencarian provider internet Indonesia dari berbagai feed website dengan pengelompokan wilayah & provider, deduplikasi otomatis, dan landing page SEO.',
				'changelog'   => 'Versi 1.0.3 tersedia dari repository GitHub.',
			),
			'download_link'     => $this->download_url,
		);

		return (object) $plugin;
	}

	/**
	 * Add "Cek Update Sekarang" link to plugin list table.
	 */
	public function add_action_links( $links ) {
		$check_url = wp_nonce_url( admin_url( 'plugins.php?wah_check_update=1' ), 'wah_check_update_nonce' );
		$check_link = '<a href="' . esc_url( $check_url ) . '" style="color:#0284c7; font-weight:bold;">🔄 Cek Update Sekarang</a>';
		array_unshift( $links, $check_link );
		return $links;
	}

	/**
	 * Handle manual force check click to clear WP update transient cache immediately.
	 */
	public function handle_force_check() {
		if ( isset( $_GET['wah_check_update'] ) && check_admin_referer( 'wah_check_update_nonce' ) ) {
			// Clear transient cache
			delete_site_transient( 'update_plugins' );
			wp_clean_plugins_cache();

			// Force remote check
			$this->remote_version = null;
			$remote_ver           = $this->get_remote_version();

			$transient = get_site_transient( 'update_plugins' );
			$transient = $this->modify_transient( $transient );
			set_site_transient( 'update_plugins', $transient );

			$status = ( $remote_ver && version_compare( WAH_VERSION, $remote_ver, '<' ) ) ? 'found' : 'latest';
			wp_safe_redirect( admin_url( 'plugins.php?wah_check_result=' . $status . '&remote_ver=' . urlencode( $remote_ver ) ) );
			exit;
		}
	}

	/**
	 * Show feedback admin notice after checking for updates.
	 */
	public function show_admin_notices() {
		if ( isset( $_GET['wah_check_result'] ) ) {
			$status     = sanitize_text_field( $_GET['wah_check_result'] );
			$remote_ver = isset( $_GET['remote_ver'] ) ? sanitize_text_field( $_GET['remote_ver'] ) : '';

			if ( 'found' === $status ) {
				echo '<div class="notice notice-warning is-dismissible"><p><strong>🚀 Pembaruan Terdeteksi!</strong> Versi terbaru <strong>' . esc_html( $remote_ver ) . '</strong> tersedia di GitHub. Silakan lihat baris plugin di bawah dan klik <strong>"update now / perbarui sekarang"</strong>.</p></div>';
			} else {
				echo '<div class="notice notice-info is-dismissible"><p><strong>✅ Plugin Versi Terbaru!</strong> Saat ini Anda sudah menggunakan versi terbaru (Versi ' . esc_html( WAH_VERSION ) . ').</p></div>';
			}
		}
	}
}
