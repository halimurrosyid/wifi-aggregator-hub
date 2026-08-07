<?php
/**
 * Router Class for virtual pages and XML Sitemaps.
 * Universal Template Hijacker & Auto-Healing Router for 100% Theme Compatibility.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WAH_Router {

	public static function init() {
		add_action( 'init', array( __CLASS__, 'add_rewrite_rules_and_auto_flush' ) );
		add_filter( 'query_vars', array( __CLASS__, 'add_query_vars' ) );
		add_filter( 'request', array( __CLASS__, 'filter_request' ), 1 );
		add_action( 'after_switch_theme', array( __CLASS__, 'flush_rules_on_theme_switch' ) );
		add_filter( 'template_include', array( __CLASS__, 'override_template' ), 9999 );
		add_action( 'template_redirect', array( __CLASS__, 'template_redirect' ), 1 );
	}

	public static function add_rewrite_rules() {
		add_rewrite_rule( '^wifi-([a-z0-9-]+)/?$', 'index.php?wah_area=$matches[1]', 'top' );
		add_rewrite_rule( '^provider/([a-z0-9-]+)/?$', 'index.php?wah_provider=$matches[1]', 'top' );
		add_rewrite_rule( '^(landing-sitemap|provider-sitemap|area-sitemap)\.xml$', 'index.php?wah_sitemap=$matches[1]', 'top' );
	}

	public static function add_rewrite_rules_and_auto_flush() {
		self::add_rewrite_rules();

		// Auto heal rewrite rules if missing from WordPress database option
		$rules = get_option( 'rewrite_rules' );
		if ( is_array( $rules ) && ! isset( $rules['^wifi-([a-z0-9-]+)/?$'] ) ) {
			flush_rewrite_rules( false );
		}
	}

	public static function flush_rules_on_theme_switch() {
		self::add_rewrite_rules();
		flush_rewrite_rules();
	}

	public static function add_query_vars( $vars ) {
		$vars[] = 'wah_area';
		$vars[] = 'wah_provider';
		$vars[] = 'wah_sitemap';
		return $vars;
	}

	public static function register_query_vars( $vars ) {
		return self::add_query_vars( $vars );
	}

	/**
	 * Intercept request array before 404 determination.
	 */
	public static function filter_request( $query_vars ) {
		$req_uri = isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '';
		$path    = trim( wp_parse_url( $req_uri, PHP_URL_PATH ), '/' );

		if ( preg_match( '/^wifi-([a-z0-9-]+)$/i', $path, $m ) ) {
			$query_vars['wah_area'] = $m[1];
		} elseif ( preg_match( '/^provider\/([a-z0-9-]+)$/i', $path, $m ) ) {
			$query_vars['wah_provider'] = $m[1];
		} elseif ( preg_match( '/^(landing-sitemap|provider-sitemap|area-sitemap)\.xml$/i', $path, $m ) ) {
			$query_vars['wah_sitemap'] = $m[1];
		}

		return $query_vars;
	}

	/**
	 * Universal Template Hijacker for Block Themes (FSE) & Classic Themes.
	 */
	public static function override_template( $template ) {
		global $wp_query;

		$area_slug = get_query_var( 'wah_area' );
		$prov_slug = get_query_var( 'wah_provider' );
		$sitemap   = get_query_var( 'wah_sitemap' );

		// Fallback check directly from REQUEST_URI if query_var was lost during theme switch
		if ( empty( $area_slug ) && empty( $prov_slug ) && empty( $sitemap ) ) {
			$req_uri = isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '';
			$path    = trim( wp_parse_url( $req_uri, PHP_URL_PATH ), '/' );

			if ( preg_match( '/^wifi-([a-z0-9-]+)$/i', $path, $m ) ) {
				$area_slug = $m[1];
			} elseif ( preg_match( '/^provider\/([a-z0-9-]+)$/i', $path, $m ) ) {
				$prov_slug = $m[1];
			} elseif ( preg_match( '/^(landing-sitemap|provider-sitemap|area-sitemap)\.xml$/i', $path, $m ) ) {
				$sitemap = $m[1];
			}
		}

		// Handle Sitemap XML
		if ( ! empty( $sitemap ) ) {
			WAH_Sitemap::render( $sitemap );
			exit;
		}

		if ( ! empty( $area_slug ) || ! empty( $prov_slug ) ) {
			$wp_query->is_404  = false;
			$wp_query->is_page = true;
			status_header( 200 );

			if ( ! empty( $area_slug ) ) {
				self::setup_area_globals( $area_slug );
				return WAH_PLUGIN_DIR . 'public/views/landing-area.php';
			}

			if ( ! empty( $prov_slug ) ) {
				self::setup_provider_globals( $prov_slug );
				return WAH_PLUGIN_DIR . 'public/views/landing-provider.php';
			}
		}

		return $template;
	}

	/**
	 * Setup Area globals & SEO metadata.
	 */
	public static function setup_area_globals( $area_slug ) {
		$clean_slug = sanitize_title( $area_slug );
		$db         = WAH_DB::get_instance();
		$area       = $db->get_area_by_slug( $clean_slug );

		if ( ! $area ) {
			$raw_name = ucwords( str_replace( '-', ' ', $clean_slug ) );
			$area_id  = WAH_Area_Detector::auto_discover( 'Pasang WiFi ' . $raw_name );
			if ( $area_id ) {
				$area = $db->get_area( $area_id );
			}
		}

		if ( ! $area ) {
			$raw_name = ucwords( str_replace( '-', ' ', $clean_slug ) );
			$area     = array(
				'id'            => 0,
				'name'          => 'Kota ' . $raw_name,
				'province_name' => 'Indonesia',
				'slug'          => $clean_slug,
				'type'          => 'city',
				'aliases'       => $raw_name,
			);
		}

		$articles = $db->get_articles(
			array(
				'area_id' => $area['id'],
				'status'  => 'active',
			)
		);

		// Expose variables for template scope
		$GLOBALS['wah_current_area']     = $area;
		$GLOBALS['wah_current_articles'] = $articles;

		$meta = WAH_SEO::generate_meta( 'area', $area );
		WAH_SEO::init_title_filter( $meta['title'] );

		add_action(
			'wp_head',
			function() use ( $area, $articles ) {
				WAH_SEO::render_head_tags( 'area', $area, $articles );
			},
			1
		);
	}

	/**
	 * Setup Provider globals & SEO metadata.
	 */
	public static function setup_provider_globals( $prov_slug ) {
		$clean_slug = sanitize_title( $prov_slug );
		$db         = WAH_DB::get_instance();
		$provider   = $db->get_provider_by_slug( $clean_slug );

		if ( $provider ) {
			$articles = $db->get_articles(
				array(
					'provider_id' => $provider['id'],
					'status'      => 'active',
				)
			);

			$GLOBALS['wah_current_provider'] = $provider;
			$GLOBALS['wah_current_articles'] = $articles;

			$meta = WAH_SEO::generate_meta( 'provider', $provider );
			WAH_SEO::init_title_filter( $meta['title'] );

			add_action(
				'wp_head',
				function() use ( $provider, $articles ) {
					WAH_SEO::render_head_tags( 'provider', $provider, $articles );
				},
				1
			);
		}
	}

	public static function template_redirect() {
		// Handled via template_include filter
	}
}
