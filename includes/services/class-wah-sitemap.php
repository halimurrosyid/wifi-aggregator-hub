<?php
/**
 * XML Sitemap Generator Service for Google Search Console.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WAH_Sitemap {

	/**
	 * Render XML sitemap based on query type.
	 *
	 * @param string $type Sitemap type: 'wifi-sitemap', 'sitemap-index', 'landing', 'provider', or 'area'.
	 */
	public static function render( $type ) {
		$home = home_url( '/' );
		$date = current_time( 'Y-m-d' );
		$db   = WAH_DB::get_instance();

		// Handle Master Sitemap Index XML for Google Search Console
		if ( in_array( $type, array( 'wifi-sitemap', 'sitemap-index' ), true ) ) {
			header( 'Content-Type: application/xml; charset=utf-8' );
			echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
			echo '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

			$sitemaps = array(
				$home . 'area-sitemap.xml',
				$home . 'provider-sitemap.xml',
				$home . 'landing-sitemap.xml',
			);

			foreach ( $sitemaps as $url ) {
				echo "  <sitemap>\n";
				echo '    <loc>' . esc_url( $url ) . "</loc>\n";
				echo '    <lastmod>' . esc_html( $date ) . "</lastmod>\n";
				echo "  </sitemap>\n";
			}

			echo '</sitemapindex>';
			exit;
		}

		if ( ! in_array( $type, array( 'landing', 'provider', 'area' ), true ) ) {
			return;
		}

		header( 'Content-Type: application/xml; charset=utf-8' );
		echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
		echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

		if ( 'area' === $type ) {
			$areas = $db->get_active_landing_areas();
			foreach ( $areas as $area ) {
				$loc     = $home . 'wifi-' . $area['slug'] . '/';
				echo "  <url>\n";
				echo '    <loc>' . esc_url( $loc ) . "</loc>\n";
				echo '    <lastmod>' . esc_html( $date ) . "</lastmod>\n";
				echo "    <changefreq>daily</changefreq>\n";
				echo "    <priority>0.9</priority>\n";
				echo "  </url>\n";
			}
		} elseif ( 'provider' === $type ) {
			$providers = $db->get_providers();
			foreach ( $providers as $prov ) {
				$loc     = $home . 'provider/' . $prov['slug'] . '/';
				echo "  <url>\n";
				echo '    <loc>' . esc_url( $loc ) . "</loc>\n";
				echo '    <lastmod>' . esc_html( $date ) . "</lastmod>\n";
				echo "    <changefreq>weekly</changefreq>\n";
				echo "    <priority>0.8</priority>\n";
				echo "  </url>\n";
			}
		} elseif ( 'landing' === $type ) {
			$areas = $db->get_areas();
			foreach ( $areas as $area ) {
				$loc     = $home . 'wifi-' . $area['slug'] . '/';
				echo "  <url>\n";
				echo '    <loc>' . esc_url( $loc ) . "</loc>\n";
				echo '    <lastmod>' . esc_html( $date ) . "</lastmod>\n";
				echo "    <changefreq>daily</changefreq>\n";
				echo "    <priority>0.9</priority>\n";
				echo "  </url>\n";
			}
		}

		echo '</urlset>';
		exit;
	}
}
