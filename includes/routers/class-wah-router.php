<?php
/**
 * Router Class for virtual pages and XML Sitemaps.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WAH_Router {

	public static function init() {
		add_action( 'init', array( __CLASS__, 'add_rewrite_rules' ) );
		add_filter( 'query_vars', array( __CLASS__, 'add_query_vars' ) );
		add_action( 'template_redirect', array( __CLASS__, 'template_redirect' ), 1 );
	}

	public static function add_rewrite_rules() {
		add_rewrite_rule( '^wifi-([a-z0-9-]+)/?$', 'index.php?wah_area=$matches[1]', 'top' );
		add_rewrite_rule( '^provider/([a-z0-9-]+)/?$', 'index.php?wah_provider=$matches[1]', 'top' );
		add_rewrite_rule( '^(landing-sitemap|provider-sitemap|area-sitemap)\.xml$', 'index.php?wah_sitemap=$matches[1]', 'top' );
	}

	public static function add_query_vars( $vars ) {
		$vars[] = 'wah_area';
		$vars[] = 'wah_provider';
		$vars[] = 'wah_sitemap';
		return $vars;
	}

	/**
	 * Intercept template load for virtual routes.
	 */
	public static function template_redirect() {
		$sitemap   = get_query_var( 'wah_sitemap' );
		$area_slug = get_query_var( 'wah_area' );
		$prov_slug = get_query_var( 'wah_provider' );

		// Handle Sitemap XML
		if ( ! empty( $sitemap ) ) {
			WAH_Sitemap::render( $sitemap );
			exit;
		}

		// Handle Area Landing Page
		if ( ! empty( $area_slug ) ) {
			$clean_slug = sanitize_title( $area_slug );
			$db         = WAH_DB::get_instance();
			$area       = $db->get_area_by_slug( $clean_slug );

			// Auto-discover / register area if missing
			if ( ! $area ) {
				$raw_name = ucwords( str_replace( '-', ' ', $clean_slug ) );
				$area_id  = WAH_Area_Detector::auto_discover( 'Pasang WiFi ' . $raw_name );
				if ( $area_id ) {
					$area = $db->get_area( $area_id );
				}
			}

			if ( $area ) {
				$articles = $db->get_articles(
					array(
						'area_id' => $area['id'],
						'status'  => 'active',
					)
				);

				// Immediately generate meta title and initialize title filters for theme & WP head
				$meta = WAH_SEO::generate_meta( 'area', $area );
				WAH_SEO::init_title_filter( $meta['title'] );

				// Output SEO tags into wp_head
				add_action(
					'wp_head',
					function() use ( $area, $articles ) {
						WAH_SEO::render_head_tags( 'area', $area, $articles );
					},
					1
				);

				status_header( 200 );
				include WAH_PLUGIN_DIR . 'public/views/landing-area.php';
				exit;
			}
		}

		// Handle Provider Landing Page
		if ( ! empty( $prov_slug ) ) {
			$db       = WAH_DB::get_instance();
			$provider = $db->get_provider_by_slug( sanitize_title( $prov_slug ) );

			if ( $provider ) {
				$articles = $db->get_articles(
					array(
						'provider_id' => $provider['id'],
						'status'      => 'active',
					)
				);

				// Immediately generate meta title and initialize title filters
				$meta = WAH_SEO::generate_meta( 'provider', $provider );
				WAH_SEO::init_title_filter( $meta['title'] );

				// Output SEO tags into wp_head
				add_action(
					'wp_head',
					function() use ( $provider, $articles ) {
						WAH_SEO::render_head_tags( 'provider', $provider, $articles );
					},
					1
				);

				status_header( 200 );
				include WAH_PLUGIN_DIR . 'public/views/landing-provider.php';
				exit;
			}
		}
	}
}
