<?php
/**
 * Dynamic SEO Engine: Head tags, OpenGraph, Twitter Cards, Schema.org JSON-LD.
 * Defensive PHP logic to prevent WordPress theme conflicts and duplicate titles.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WAH_SEO {

	private static $current_title = '';

	/**
	 * Setup document title filters for WordPress theme & core safely before header.
	 */
	public static function init_title_filter( $title_str ) {
		self::$current_title = $title_str;
		add_filter( 'pre_get_document_title', array( __CLASS__, 'override_document_title' ), 9999 );
		add_filter( 'document_title_parts', array( __CLASS__, 'override_title_parts' ), 9999 );
		add_filter( 'wp_title', array( __CLASS__, 'override_wp_title' ), 9999, 3 );
	}

	public static function override_document_title( $title ) {
		return ! empty( self::$current_title ) ? self::$current_title : $title;
	}

	public static function override_title_parts( $parts ) {
		if ( ! is_array( $parts ) ) {
			$parts = array();
		}
		if ( ! empty( self::$current_title ) ) {
			$parts['title'] = self::$current_title;
			unset( $parts['tagline'] );
		}
		return $parts;
	}

	public static function override_wp_title( $title, $sep = '', $seplocation = '' ) {
		return ! empty( self::$current_title ) ? self::$current_title : $title;
	}

	/**
	 * Generate dynamic title & description based on saved admin pattern.
	 */
	public static function generate_meta( $type, $entity ) {
		$site_name = get_bloginfo( 'name' );
		$name      = is_array( $entity ) && isset( $entity['name'] ) ? $entity['name'] : '';
		$year      = date( 'Y' );

		if ( 'area' === $type ) {
			$raw_title = get_option( 'wah_seo_title_pattern', 'Pasang WiFi Murah & Provider Internet di %area% Terbaru %year%' );
			$raw_desc  = get_option( 'wah_seo_desc_pattern', 'Daftar rekomendasi provider internet wifi unlimited terbaik di %area%. Bandingkan paket ICONNET, Indosat HiFi, CBN Fiber, Biznet, MyRepublic.' );
		} else {
			$raw_title = get_option( 'wah_seo_provider_title_pattern', 'Paket Internet WiFi %provider% Indonesia - Promo & Wilayah Jangkauan %year%' );
			$raw_desc  = get_option( 'wah_seo_provider_desc_pattern', 'Cek daftar wilayah jangkauan, pilihan paket unlimited, dan cara daftar internet %provider% terbaru di seluruh Indonesia.' );
		}

		$title = str_replace( array( '%area%', '%provider%', '%year%', '%site_name%' ), array( $name, $name, $year, $site_name ), $raw_title );
		$desc  = str_replace( array( '%area%', '%provider%', '%year%', '%site_name%' ), array( $name, $name, $year, $site_name ), $raw_desc );

		// Fallback for remaining unreplaced placeholders
		$title = str_replace( array( '%area%', '%provider%' ), array( $name, 'Internet ISP' ), $title );
		$desc  = str_replace( array( '%area%', '%provider%' ), array( $name, 'ICONNET & ISP' ), $desc );

		return array(
			'title' => trim( $title ),
			'desc'  => trim( $desc ),
		);
	}

	/**
	 * Output complete SEO tags into wp_head (without duplicate <title> tag).
	 */
	public static function render_head_tags( $type, $entity, $articles = array() ) {
		$meta      = self::generate_meta( $type, $entity );
		$site_name = get_bloginfo( 'name' );
		$title     = $meta['title'];
		$desc      = $meta['desc'];
		$url       = self::get_canonical_url( $type, $entity );

		echo "\n<!-- WiFi Aggregator Hub SEO Tags -->\n";
		echo '<meta name="description" content="' . esc_attr( $desc ) . '" />' . "\n";
		echo '<link rel="canonical" href="' . esc_url( $url ) . '" />' . "\n";

		// Open Graph
		echo '<meta property="og:locale" content="id_ID" />' . "\n";
		echo '<meta property="og:type" content="website" />' . "\n";
		echo '<meta property="og:title" content="' . esc_attr( $title ) . '" />' . "\n";
		echo '<meta property="og:description" content="' . esc_attr( $desc ) . '" />' . "\n";
		echo '<meta property="og:url" content="' . esc_url( $url ) . '" />' . "\n";
		echo '<meta property="og:site_name" content="' . esc_attr( $site_name ) . '" />' . "\n";

		// Twitter Cards
		echo '<meta name="twitter:card" content="summary_large_image" />' . "\n";
		echo '<meta name="twitter:title" content="' . esc_attr( $title ) . '" />' . "\n";
		echo '<meta name="twitter:description" content="' . esc_attr( $desc ) . '" />' . "\n";

		// JSON-LD Schemas
		self::render_schema_json( $type, $entity, $title, $desc, $url, $articles );
		echo "<!-- / WiFi Aggregator Hub SEO Tags -->\n\n";
	}

	/**
	 * Get Canonical URL for landing pages.
	 */
	public static function get_canonical_url( $type, $entity ) {
		$home = home_url( '/' );
		$slug = is_array( $entity ) && isset( $entity['slug'] ) ? $entity['slug'] : '';

		if ( 'area' === $type ) {
			return $home . 'wifi-' . $slug . '/';
		}
		return $home . 'provider/' . $slug . '/';
	}

	/**
	 * Render Schema.org JSON-LD markup.
	 */
	private static function render_schema_json( $type, $entity, $title, $desc, $url, $articles ) {
		$site_name = get_bloginfo( 'name' );
		$home_url  = home_url( '/' );
		$name      = is_array( $entity ) && isset( $entity['name'] ) ? $entity['name'] : '';

		// Breadcrumb Schema
		$breadcrumb = array(
			'@context'        => 'https://schema.org',
			'@type'           => 'BreadcrumbList',
			'itemListElement' => array(
				array(
					'@type'    => 'ListItem',
					'position' => 1,
					'name'     => 'Home',
					'item'     => $home_url,
				),
				array(
					'@type'    => 'ListItem',
					'position' => 2,
					'name'     => ( 'area' === $type ? 'Pencarian Area' : 'Daftar Provider' ),
					'item'     => $url,
				),
				array(
					'@type'    => 'ListItem',
					'position' => 3,
					'name'     => $name,
					'item'     => $url,
				),
			),
		);

		// CollectionPage / WebPage Schema
		$webpage = array(
			'@context'    => 'https://schema.org',
			'@type'       => 'CollectionPage',
			'name'        => $title,
			'description' => $desc,
			'url'         => $url,
			'publisher'   => array(
				'@type' => 'Organization',
				'name'  => $site_name,
				'url'   => $home_url,
			),
		);

		echo '<script type="application/ld+json">' . wp_json_encode( $breadcrumb, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) . "</script>\n";
		echo '<script type="application/ld+json">' . wp_json_encode( $webpage, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) . "</script>\n";
	}
}
