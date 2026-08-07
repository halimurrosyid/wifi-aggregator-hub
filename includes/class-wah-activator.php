<?php
/**
 * Fired during plugin activation.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WAH_Activator {

	/**
	 * Activate the plugin.
	 */
	public static function activate() {
		self::create_tables();
		self::seed_default_data();
		self::schedule_cron();
		self::add_rewrite_rules();
		flush_rewrite_rules();
	}

	/**
	 * Create custom DB tables using dbDelta.
	 */
	private static function create_tables() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();

		$table_feeds     = $wpdb->prefix . 'wah_feeds';
		$table_providers = $wpdb->prefix . 'wah_providers';
		$table_areas     = $wpdb->prefix . 'wah_areas';
		$table_articles  = $wpdb->prefix . 'wah_articles';
		$table_landings  = $wpdb->prefix . 'wah_landings';
		$table_logs      = $wpdb->prefix . 'wah_logs';

		$sql = "
		CREATE TABLE $table_feeds (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			website_name varchar(255) NOT NULL,
			feed_url text NOT NULL,
			sitemap_url text DEFAULT '',
			priority int(11) DEFAULT 10,
			status varchar(50) DEFAULT 'active',
			last_synced datetime DEFAULT NULL,
			error_message text DEFAULT '',
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id)
		) $charset_collate;

		CREATE TABLE $table_providers (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			name varchar(255) NOT NULL,
			slug varchar(255) NOT NULL,
			aliases text DEFAULT '',
			logo_url text DEFAULT '',
			brand_color varchar(50) DEFAULT '#00a896',
			display_order int(11) DEFAULT 0,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			UNIQUE KEY slug (slug)
		) $charset_collate;

		CREATE TABLE $table_areas (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			name varchar(255) NOT NULL,
			type varchar(50) DEFAULT 'city',
			province_name varchar(255) DEFAULT '',
			slug varchar(255) NOT NULL,
			aliases text DEFAULT '',
			parent_id bigint(20) DEFAULT 0,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			UNIQUE KEY slug (slug)
		) $charset_collate;

		CREATE TABLE $table_articles (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			feed_id bigint(20) NOT NULL,
			provider_id bigint(20) DEFAULT 0,
			area_id bigint(20) DEFAULT 0,
			title text NOT NULL,
			url text NOT NULL,
			slug varchar(255) DEFAULT '',
			publish_date datetime DEFAULT NULL,
			update_date datetime DEFAULT NULL,
			excerpt text DEFAULT '',
			ai_summary text DEFAULT '',
			featured_image text DEFAULT '',
			category varchar(255) DEFAULT '',
			tags text DEFAULT '',
			website_name varchar(255) DEFAULT '',
			cta_url text DEFAULT '',
			whatsapp_number varchar(100) DEFAULT '',
			domain varchar(255) DEFAULT '',
			word_count int(11) DEFAULT 0,
			status varchar(50) DEFAULT 'active',
			http_status varchar(20) DEFAULT '200',
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY provider_id (provider_id),
			KEY area_id (area_id),
			KEY feed_id (feed_id),
			KEY status (status)
		) $charset_collate;

		CREATE TABLE $table_landings (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			area_id bigint(20) DEFAULT 0,
			provider_id bigint(20) DEFAULT 0,
			slug varchar(255) NOT NULL,
			custom_title text DEFAULT '',
			custom_description text DEFAULT '',
			custom_ai_summary text DEFAULT '',
			view_count bigint(20) DEFAULT 0,
			updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			UNIQUE KEY slug (slug)
		) $charset_collate;

		CREATE TABLE $table_logs (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			log_type varchar(50) NOT NULL,
			message text NOT NULL,
			details text DEFAULT '',
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id)
		) $charset_collate;
		";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * Seed initial default Providers and Indonesian Areas if empty.
	 */
	private static function seed_default_data() {
		global $wpdb;

		// Seed Providers
		$table_providers = $wpdb->prefix . 'wah_providers';
		$provider_count  = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $table_providers" );
		if ( 0 === $provider_count ) {
			$json_file = WAH_PLUGIN_DIR . 'data/default-providers.json';
			if ( file_exists( $json_file ) ) {
				$providers = json_decode( file_get_contents( $json_file ), true );
				if ( is_array( $providers ) ) {
					foreach ( $providers as $prov ) {
						$wpdb->insert(
							$table_providers,
							array(
								'name'          => sanitize_text_field( $prov['name'] ),
								'slug'          => sanitize_title( $prov['slug'] ),
								'aliases'       => is_array( $prov['aliases'] ) ? implode( ', ', array_map( 'sanitize_text_field', $prov['aliases'] ) ) : sanitize_text_field( $prov['aliases'] ),
								'brand_color'   => sanitize_hex_color( $prov['brand_color'] ?? '#00a896' ),
								'display_order' => intval( $prov['display_order'] ?? 0 ),
							)
						);
					}
				}
			}
		}

		// Seed Areas
		$table_areas = $wpdb->prefix . 'wah_areas';
		$area_count  = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $table_areas" );
		if ( 0 === $area_count ) {
			$json_file = WAH_PLUGIN_DIR . 'data/indonesian-areas.json';
			if ( file_exists( $json_file ) ) {
				$areas = json_decode( file_get_contents( $json_file ), true );
				if ( is_array( $areas ) ) {
					foreach ( $areas as $area ) {
						$wpdb->insert(
							$table_areas,
							array(
								'name'          => sanitize_text_field( $area['name'] ),
								'type'          => sanitize_text_field( $area['type'] ?? 'city' ),
								'province_name' => sanitize_text_field( $area['province'] ?? '' ),
								'slug'          => sanitize_title( $area['slug'] ),
								'aliases'       => is_array( $area['aliases'] ) ? implode( ', ', array_map( 'sanitize_text_field', $area['aliases'] ) ) : sanitize_text_field( $area['aliases'] ),
							)
						);
					}
				}
			}
		}
	}

	/**
	 * Schedule WP Cron tasks.
	 */
	public static function schedule_cron() {
		if ( ! wp_next_scheduled( 'wah_cron_sync_feeds' ) ) {
			wp_schedule_event( time(), 'hourly', 'wah_cron_sync_feeds' );
		}
		if ( ! wp_next_scheduled( 'wah_cron_check_links' ) ) {
			wp_schedule_event( time(), 'daily', 'wah_cron_check_links' );
		}
	}

	/**
	 * Add rewrite rules for virtual aggregate landing pages & sitemaps.
	 */
	public static function add_rewrite_rules() {
		add_rewrite_rule( '^wifi-([a-zA-Z0-9-]+)/?$', 'index.php?wah_area=$matches[1]', 'top' );
		add_rewrite_rule( '^provider/([a-zA-Z0-9-]+)/?$', 'index.php?wah_provider=$matches[1]', 'top' );
		add_rewrite_rule( '^landing-sitemap\.xml$', 'index.php?wah_sitemap=landing', 'top' );
		add_rewrite_rule( '^provider-sitemap\.xml$', 'index.php?wah_sitemap=provider', 'top' );
		add_rewrite_rule( '^area-sitemap\.xml$', 'index.php?wah_sitemap=area', 'top' );
	}
}
