<?php
/**
 * Feed & XML Sitemap Fetcher and Parser.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WAH_Fetcher {

	/**
	 * Fetch articles from feed URL or sitemap URL fallback.
	 *
	 * @param array $feed_row Feed record array from DB.
	 * @return array Array of extracted article data arrays or WP_Error.
	 */
	public static function fetch( $feed_row ) {
		$articles = array();
		$feed_url = $feed_row['feed_url'];

		// Attempt RSS/Atom parsing first
		$rss_items = self::fetch_rss( $feed_url );

		if ( ! is_wp_error( $rss_items ) && ! empty( $rss_items ) ) {
			foreach ( $rss_items as $item ) {
				$article = self::format_item( $item, $feed_row );
				if ( $article ) {
					$articles[] = $article;
				}
			}
			return $articles;
		}

		// Fallback to XML Sitemap if available
		if ( ! empty( $feed_row['sitemap_url'] ) ) {
			$sitemap_items = self::fetch_sitemap( $feed_row['sitemap_url'] );
			if ( ! is_wp_error( $sitemap_items ) && ! empty( $sitemap_items ) ) {
				foreach ( $sitemap_items as $item ) {
					$article = self::format_item( $item, $feed_row );
					if ( $article ) {
						$articles[] = $article;
					}
				}
				return $articles;
			}
		}

		if ( is_wp_error( $rss_items ) ) {
			return $rss_items;
		}

		return new WP_Error( 'feed_empty', __( 'Tidak ada artikel yang ditemukan dari feed/sitemap ini.', 'wifi-aggregator-hub' ) );
	}

	/**
	 * Parse RSS/Atom feed using SimplePie / fetch_feed or wp_remote_get.
	 */
	private static function fetch_rss( $url ) {
		if ( ! function_exists( 'fetch_feed' ) ) {
			require_once ABSPATH . WPINC . '/feed.php';
		}

		$rss = fetch_feed( $url );

		if ( is_wp_error( $rss ) ) {
			// Try manual fallback with wp_remote_get
			return self::fetch_rss_fallback( $url );
		}

		$maxitems  = $rss->get_item_quantity( 50 );
		$rss_items = $rss->get_items( 0, $maxitems );

		$results = array();
		foreach ( $rss_items as $item ) {
			$title   = $item->get_title();
			$link    = $item->get_permalink();
			$date    = $item->get_date( 'Y-m-d H:i:s' );
			$excerpt = $item->get_description();

			// Enclosure / media image extraction
			$image = '';
			if ( $enclosure = $item->get_enclosure() ) {
				$image = $enclosure->get_link();
			}

			// Categories / tags
			$cats       = array();
			$categories = $item->get_categories();
			if ( is_array( $categories ) ) {
				foreach ( $categories as $cat ) {
					$cats[] = $cat->get_label();
				}
			}

			$results[] = array(
				'title'          => $title,
				'url'            => $link,
				'publish_date'   => $date ? $date : current_time( 'mysql' ),
				'excerpt'        => $excerpt,
				'featured_image' => $image,
				'category'       => implode( ', ', $cats ),
				'tags'           => implode( ', ', $cats ),
			);
		}

		return $results;
	}

	/**
	 * Manual RSS fallback using wp_remote_get & SimpleXMLElement.
	 */
	private static function fetch_rss_fallback( $url ) {
		$response = wp_remote_get(
			$url,
			array(
				'timeout'    => 15,
				'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) WiFiAggregatorHub/1.0',
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = wp_remote_retrieve_body( $response );
		if ( empty( $body ) ) {
			return new WP_Error( 'http_empty', 'Response body is empty.' );
		}

		libxml_use_internal_errors( true );
		$xml = simplexml_load_string( $body );
		if ( ! $xml ) {
			return new WP_Error( 'xml_invalid', 'Gagal memparsing XML feed.' );
		}

		$results = array();
		// RSS 2.0
		if ( isset( $xml->channel->item ) ) {
			foreach ( $xml->channel->item as $item ) {
				$results[] = array(
					'title'          => (string) $item->title,
					'url'            => (string) $item->link,
					'publish_date'   => isset( $item->pubDate ) ? date( 'Y-m-d H:i:s', strtotime( (string) $item->pubDate ) ) : current_time( 'mysql' ),
					'excerpt'        => (string) ($item->description ?? ''),
					'featured_image' => '',
					'category'       => '',
					'tags'           => '',
				);
			}
		}

		return $results;
	}

	/**
	 * Sitemap XML parser fallback.
	 */
	private static function fetch_sitemap( $url ) {
		$response = wp_remote_get(
			$url,
			array(
				'timeout'    => 15,
				'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) WiFiAggregatorHub/1.0',
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = wp_remote_retrieve_body( $response );
		if ( empty( $body ) ) {
			return new WP_Error( 'sitemap_empty', 'Sitemap body empty.' );
		}

		libxml_use_internal_errors( true );
		$xml = simplexml_load_string( $body );
		if ( ! $xml ) {
			return new WP_Error( 'sitemap_invalid', 'Gagal memparsing Sitemap XML.' );
		}

		$results = array();
		if ( isset( $xml->url ) ) {
			foreach ( $xml->url as $url_node ) {
				$loc     = (string) $url_node->loc;
				$lastmod = isset( $url_node->lastmod ) ? date( 'Y-m-d H:i:s', strtotime( (string) $url_node->lastmod ) ) : current_time( 'mysql' );

				// Clean title from URL slug as fallback
				$slug       = basename( parse_url( $loc, PHP_URL_PATH ) );
				$clean_name = ucwords( str_replace( array( '-', '_' ), ' ', $slug ) );

				$results[] = array(
					'title'          => $clean_name,
					'url'            => $loc,
					'publish_date'   => $lastmod,
					'excerpt'        => $clean_name,
					'featured_image' => '',
					'category'       => '',
					'tags'           => '',
				);
			}
		}

		return $results;
	}

	/**
	 * Format raw parsed item into standard article data model.
	 */
	private static function format_item( $raw, $feed_row ) {
		if ( empty( $raw['url'] ) || empty( $raw['title'] ) ) {
			return null;
		}

		$domain = wp_parse_url( $raw['url'], PHP_URL_HOST );

		// Detect Provider & Area
		$full_text   = $raw['title'] . ' ' . $raw['excerpt'] . ' ' . $raw['tags'];
		$provider_id = WAH_Provider_Detector::detect( $full_text, $domain );
		$area_id     = WAH_Area_Detector::detect( $full_text );

		// Generate WhatsApp / CTA URL if domain offers parameter
		$cta_url  = $raw['url'];
		$wa_num   = '';
		if ( preg_match( '/(?:wa\.me|api\.whatsapp\.com\/send\?phone=)(\d+)/i', $raw['excerpt'], $matches ) ) {
			$wa_num = $matches[1];
		}

		// Clean excerpt to max 250 characters
		$clean_excerpt = wp_strip_all_tags( $raw['excerpt'] );
		if ( mb_strlen( $clean_excerpt ) > 250 ) {
			$clean_excerpt = mb_substr( $clean_excerpt, 0, 247 ) . '...';
		}

		return array(
			'feed_id'         => $feed_row['id'],
			'provider_id'     => $provider_id,
			'area_id'         => $area_id,
			'title'           => sanitize_text_field( $raw['title'] ),
			'url'             => esc_url_raw( $raw['url'] ),
			'slug'            => sanitize_title( $raw['title'] ),
			'publish_date'    => $raw['publish_date'],
			'update_date'     => current_time( 'mysql' ),
			'excerpt'         => $clean_excerpt,
			'ai_summary'      => WAH_AI_Summarizer::generate( $raw['title'], $clean_excerpt, $provider_id, $area_id ),
			'featured_image'  => esc_url_raw( $raw['featured_image'] ),
			'category'        => sanitize_text_field( $raw['category'] ),
			'tags'            => sanitize_text_field( $raw['tags'] ),
			'website_name'    => sanitize_text_field( $feed_row['website_name'] ),
			'cta_url'         => $cta_url,
			'whatsapp_number' => $wa_num,
			'domain'          => $domain,
			'status'          => 'active',
			'http_status'     => '200',
		);
	}
}
