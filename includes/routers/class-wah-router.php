<?php
/**
 * Router handling virtual URL rewrites for Landing Pages & Sitemaps.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WAH_Router {

	/**
	 * Register query variables.
	 */
	public static function register_query_vars( $vars ) {
		$vars[] = 'wah_area';
		$vars[] = 'wah_provider';
		$vars[] = 'wah_sitemap';
		return $vars;
	}

	/**
	 * Intercept template load for virtual routes.
	 */
	public static function template_redirect() {
		$sitemap  = get_query_var( 'wah_sitemap' );
		$area_slug = get_query_var( 'wah_area' );
		$prov_slug = get_query_var( 'wah_provider' );

		// Handle Sitemap XML
		if ( ! empty( $sitemap ) ) {
			WAH_Sitemap::render( $sitemap );
			exit;
		}

		// Handle Area Landing Page
		if ( ! empty( $area_slug ) ) {
			$db   = WAH_DB::get_instance();
			$area = $db->get_area_by_slug( sanitize_title( $area_slug ) );

			if ( $area ) {
				$articles = $db->get_articles(
					array(
						'area_id' => $area['id'],
						'status'  => 'active',
					)
				);

				// Output SEO tags
				add_action(
					'wp_head',
					function() use ( $area, $articles ) {
						WAH_SEO::render_head_tags( 'area', $area, $articles );
					}
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

				// Output SEO tags
				add_action(
					'wp_head',
					function() use ( $provider, $articles ) {
						WAH_SEO::render_head_tags( 'provider', $provider, $articles );
					}
				);

				status_header( 200 );
				include WAH_PLUGIN_DIR . 'public/views/landing-provider.php';
				exit;
			}
		}
	}
}
