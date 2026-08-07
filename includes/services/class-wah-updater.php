<?php
/**
 * Ultra-Reliable GitHub Automatic Plugin Updater Service.
 * Direct Raw Branch Version Checking & Force Refresh.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WAH_Updater {

	private $file;
	private $basename;
	private $username;
	private $repository;
	private $remote_version;
	private $download_url;

	public function __construct( $file ) {
		$this->file       = $file;
		$this->username   = 'halimurrosyid';
		$this->repository = 'wifi-aggregator-hub';
		$this->basename   = plugin_basename( $this->file );
		$this->download_url = sprintf( 'https://github.com/%s/%s/raw/main/wifi-aggregator-hub.zip', $this->username, $this->repository );

		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'modify_transient' ) );
		add_filter( 'plugins_api', array( $this, 'plugin_popup' ), 10, 3 );
		add_filter( 'plugin_action_links_' . $this->basename, array( $this, 'add_action_links' ) );
		add_action( 'admin_init', array( $this, 'handle_force_check' ) );
	}

	/**
	 * Fetch latest version header directly from GitHub main branch raw file.
	 */
	private function get_remote_version() {
		if ( null !== $this->remote_version ) {
			return;
		}

		$raw_url  = sprintf( 'https://raw.githubusercontent.com/%s/%s/main/wifi-aggregator-hub.php', $this->username, $this->repository );
		$response = wp_remote_get(
			$raw_url,
			array(
				'timeout'    => 10,
				'user-agent' => 'WiFiAggregatorHub-Updater/1.0',
			)
		);

		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return;
		}

		$body = wp_remote_retrieve_body( $response );
		if ( preg_match( '/Version:\s*([0-9\.]+)/i', $body, $matches ) ) {
			$this->remote_version = trim( $matches[1] );
		}
	}

	/**
	 * Modify WP plugin update transient.
	 */
	public function modify_transient( $transient ) {
		if ( ! is_object( $transient ) ) {
			$transient = new stdClass();
		}

		$this->get_remote_version();

		if ( ! empty( $this->remote_version ) && version_compare( WAH_VERSION, $this->remote_version, '<' ) ) {
			$plugin = array(
				'slug'        => $this->basename,
				'new_version' => $this->remote_version,
				'url'         => sprintf( 'https://github.com/%s/%s', $this->username, $this->repository ),
				'package'     => $this->download_url,
			);
			$transient->response[ $this->basename ] = (object) $plugin;
		}

		return $transient;
	}

	/**
	 * Render plugin info modal popup in WP Admin.
	 */
	public function plugin_popup( $result, $action, $args ) {
		if ( 'plugin_information' !== $action || empty( $args->slug ) || $args->slug !== $this->basename ) {
			return $result;
		}

		$this->get_remote_version();

		$plugin = array(
			'name'              => 'WiFi Aggregator Hub',
			'slug'              => $this->basename,
			'version'           => $this->remote_version ? $this->remote_version : WAH_VERSION,
			'author'            => '<a href="https://indahweb.com" target="_blank">Mujaddid Halimurrosyid Ajid WP</a>',
			'homepage'          => sprintf( 'https://github.com/%s/%s', $this->username, $this->repository ),
			'requires'          => '5.8',
			'tested'            => '6.6',
			'downloaded'        => 1000,
			'sections'          => array(
				'description' => 'Mesin indeks dan agregator pencarian provider internet Indonesia dari berbagai feed website dengan pengelompokan wilayah & provider, deduplikasi otomatis, dan landing page SEO.',
				'changelog'   => 'Versi terbaru tersedia dari repository GitHub.',
			),
			'download_link'     => $this->download_url,
		);

		return (object) $plugin;
	}

	/**
	 * Add "Cek Update" link to plugin list table.
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
			// Delete WP plugin update cache
			delete_site_transient( 'update_plugins' );
			wp_clean_plugins_cache();

			// Force fresh check
			wp_update_plugins();

			wp_safe_redirect( admin_url( 'plugins.php?wah_updated_checked=1' ) );
			exit;
		}
	}
}
