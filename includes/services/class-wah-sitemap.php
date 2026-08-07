<?php
/**
 * XML Sitemap Generator Service.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WAH_Sitemap {

	/**
	 * Render XML sitemap based on query type.
	 *
	 * @param string $type Sitemap type: 'landing', 'provider', or 'area'.
	 */
	public static function render( $type ) {
		if ( ! in_array( $type, array( 'landing', 'provider', 'area' ), true ) ) {
			return;
		}

		header( 'Content-Type: application/xml; charset=utf-8' );
		echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
		echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

		$db   = WAH_DB::get_instance();
		$home = home_url( '/' );

		if ( 'area' === $type ) {
			$areas = $db->get_active_landing_areas();
			foreach ( $areas as $area ) {
				$loc     = $home . 'wifi-' . $area['slug'] . '/';
				$lastmod = current_time( 'Y-m-d' );
				echo "  <url>\n";
				echo '    <loc>' . esc_url( $loc ) . "</loc>\n";
				echo '    <lastmod>' . esc_html( $lastmod ) . "</lastmod>\n";
				echo "    <changefreq>daily</changefreq>\n";
				echo "    <priority>0.8</priority>\n";
				echo "  </url>\n";
			}
		} elseif ( 'provider' === $type ) {
			$providers = $db->get_providers();
			foreach ( $providers as $prov ) {
				$loc     = $home . 'provider/' . $prov['slug'] . '/';
				$lastmod = current_time( 'Y-m-d' );
				echo "  <url>\n";
				echo '    <loc>' . esc_url( $loc ) . "</loc>\n";
				echo '    <lastmod>' . esc_html( $lastmod ) . "</lastmod>\n";
				echo "    <changefreq>weekly</changefreq>\n";
				echo "    <priority>0.8</priority>\n";
				echo "  </url>\n";
			}
		} elseif ( 'landing' === $type ) {
			// Combination of active area landings
			$areas = $db->get_areas();
			foreach ( $areas as $area ) {
				$loc     = $home . 'wifi-' . $area['slug'] . '/';
				$lastmod = current_time( 'Y-m-d' );
				echo "  <url>\n";
				echo '    <loc>' . esc_url( $loc ) . "</loc>\n";
				echo '    <lastmod>' . esc_html( $lastmod ) . "</lastmod>\n";
				echo "    <changefreq>daily</changefreq>\n";
				echo "    <priority>0.9</priority>\n";
				echo "  </url>\n";
			}
		}

		echo '</urlset>';
		exit;
	}
}
