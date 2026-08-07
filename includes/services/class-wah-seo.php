<?php
/**
 * Dynamic SEO Engine: Head tags, OpenGraph, Twitter Cards, Schema.org JSON-LD.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WAH_SEO {

	/**
	 * Output complete SEO tags into wp_head.
	 *
	 * @param string $type Landing type ('area' or 'provider').
	 * @param array $entity Area array or Provider array.
	 * @param array $articles List of associated active articles.
	 */
	public static function render_head_tags( $type, $entity, $articles = array() ) {
		$site_name = get_bloginfo( 'name' );
		$name      = $entity['name'] ?? '';
		$url       = self::get_canonical_url( $type, $entity );

		if ( 'area' === $type ) {
			$title = "Pasang WiFi Murah & Provider Internet di $name Terbaru " . date( 'Y' );
			$desc  = "Daftar rekomendasi provider internet wifi unlimited terbaik di $name. Bandingkan paket ICONNET, Indosat HiFi, CBN Fiber, Biznet, MyRepublic dan harga promo terpasang.";
		} else {
			$title = "Paket Internet WiFi $name Indonesia - Promo & Wilayah Jangkauan " . date( 'Y' );
			$desc  = "Cek daftar wilayah jangkauan, pilihan paket unlimited, dan cara daftar internet $name terbaru di seluruh Indonesia.";
		}

		echo "\n<!-- WiFi Aggregator Hub SEO Tags -->\n";
		echo '<title>' . esc_html( $title . ' - ' . $site_name ) . "</title>\n";
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
		if ( 'area' === $type ) {
			return $home . 'wifi-' . $entity['slug'] . '/';
		}
		return $home . 'provider/' . $entity['slug'] . '/';
	}

	/**
	 * Render Schema.org JSON-LD markup.
	 */
	private static function render_schema_json( $type, $entity, $title, $desc, $url, $articles ) {
		$site_name = get_bloginfo( 'name' );
		$home_url  = home_url( '/' );
		$name      = $entity['name'];

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

		// ItemList Schema
		$item_elements = array();
		$pos           = 1;
		foreach ( $articles as $art ) {
			$item_elements[] = array(
				'@type'    => 'ListItem',
				'position' => $pos++,
				'url'      => $art['url'],
				'name'     => $art['title'],
			);
		}

		$collection = array(
			'@context'    => 'https://schema.org',
			'@type'       => 'CollectionPage',
			'name'        => $title,
			'description' => $desc,
			'url'         => $url,
			'mainEntity'  => array(
				'@type'           => 'ItemList',
				'itemListElement' => $item_elements,
			),
		);

		// FAQPage Schema
		$faq = array(
			'@context'   => 'https://schema.org',
			'@type'      => 'FAQPage',
			'mainEntity' => array(
				array(
					'@type'          => 'Question',
					'name'           => "Apa provider internet WiFi terbaik di $name?",
					'acceptedAnswer' => array(
						'@type' => 'Answer',
						'text'  => "Beberapa pilihan provider internet wifi unlimited populer di $name antara lain ICONNET, Indosat HiFi, CBN Fiber, Biznet, dan MyRepublic tergantung jangkauan jaringan lokasi Anda.",
					),
				),
				array(
					'@type'          => 'Question',
					'name'           => "Bagaimana cara mengecek jangkauan wifi di $name?",
					'acceptedAnswer' => array(
						'@type' => 'Answer',
						'text'  => "Anda dapat memilih salah satu provider di atas dan menekan tombol 'Daftar Sekarang' atau 'Chat WhatsApp' untuk terhubung dengan sales provider terkait.",
					),
				),
			),
		);

		echo '<script type="application/ld+json">' . wp_json_encode( $breadcrumb ) . "</script>\n";
		echo '<script type="application/ld+json">' . wp_json_encode( $collection ) . "</script>\n";
		echo '<script type="application/ld+json">' . wp_json_encode( $faq ) . "</script>\n";
	}
}
