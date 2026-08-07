<?php
/**
 * GitHub Automatic Plugin Updater Service.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WAH_Updater {

	private $file;
	private $plugin;
	private $basename;
	private $active;
	private $username;
	private $repository;
	private $github_response;

	public function __construct( $file ) {
		$this->file       = $file;
		$this->username   = 'halimurrosyid';
		$this->repository = 'wifi-aggregator-hub';
		$this->basename   = plugin_basename( $this->file );

		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'modify_transient' ) );
		add_filter( 'plugins_api', array( $this, 'plugin_popup' ), 10, 3 );
		add_filter( 'upgrader_post_install', array( $this, 'after_install' ), 10, 3 );
	}

	/**
	 * Get release data from GitHub API.
	 */
	private function get_repository_info() {
		if ( null !== $this->github_response ) {
			return;
		}

		$request_uri = sprintf( 'https://api.github.com/repos/%s/%s/releases/latest', $this->username, $this->repository );
		$response    = wp_remote_get(
			$request_uri,
			array(
				'timeout'    => 10,
				'user-agent' => 'WiFiAggregatorHub-Updater/1.0',
			)
		);

		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return;
		}

		$this->github_response = json_decode( wp_remote_retrieve_body( $response ), true );
	}

	/**
	 * Check for updates and modify WP transient.
	 */
	public function modify_transient( $transient ) {
		if ( empty( $transient->checked ) ) {
			return $transient;
		}

		$this->get_repository_info();

		if ( empty( $this->github_response ) || empty( $this->github_response['tag_name'] ) ) {
			return $transient;
		}

		$new_version = ltrim( $this->github_response['tag_name'], 'v' );

		if ( version_compare( WAH_VERSION, $new_version, '<' ) ) {
			$package = $this->github_response['zipball_url'] ?? '';
			if ( ! empty( $this->github_response['assets'][0]['browser_download_url'] ) ) {
				$package = $this->github_response['assets'][0]['browser_download_url'];
			}

			$plugin              = array(
				'slug'        => $this->basename,
				'new_version' => $new_version,
				'url'         => 'https://github.com/' . $this->username . '/' . $this->repository,
				'package'     => $package,
			);
			$transient->response[ $this->basename ] = (object) $plugin;
		}

		return $transient;
	}

	/**
	 * Render Plugin details modal popup in WP Admin.
	 */
	public function plugin_popup( $result, $action, $args ) {
		if ( 'plugin_information' !== $action || empty( $args->slug ) || $args->slug !== $this->basename ) {
			return $result;
		}

		$this->get_repository_info();

		if ( empty( $this->github_response ) ) {
			return $result;
		}

		$new_version = ltrim( $this->github_response['tag_name'], 'v' );
		$download_url= $this->github_response['zipball_url'] ?? '';
		if ( ! empty( $this->github_response['assets'][0]['browser_download_url'] ) ) {
			$download_url = $this->github_response['assets'][0]['browser_download_url'];
		}

		$plugin = array(
			'name'              => 'WiFi Aggregator Hub',
			'slug'              => $this->basename,
			'version'           => $new_version,
			'author'            => '<a href="https://indahweb.com">Mujaddid Halimurrosyid Ajid WP</a>',
			'homepage'          => 'https://github.com/halimurrosyid/wifi-aggregator-hub',
			'requires'          => '5.8',
			'tested'            => '6.6',
			'downloaded'        => 1000,
			'last_updated'      => $this->github_response['published_at'] ?? '',
			'sections'          => array(
				'description' => 'Mesin indeks dan agregator pencarian provider internet Indonesia dari berbagai feed website dengan pengelompokan wilayah & provider, deduplikasi otomatis, dan landing page SEO.',
				'changelog'   => $this->github_response['body'] ?? 'Pembaruan otomatis dari GitHub release.',
			),
			'download_link'     => $download_url,
		);

		return (object) $plugin;
	}

	/**
	 * Move unzipped folder to proper plugin directory name.
	 */
	public function after_install( $response, $hook_extra, $result ) {
		global $wp_filesystem;
		$install_directory = plugin_dir_path( $this->file );
		$wp_filesystem->move( $result['destination'], $install_directory );
		$result['destination'] = $install_directory;
		return $result;
	}
}
