<?php
/**
 * Advanced Multi-Page Feed & Recursive XML Sitemap Index Fetcher.
 * Capable of extracting hundreds to thousands of articles per domain.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WAH_Fetcher {

	/**
	 * Fetch ALL available articles from RSS feed (multi-page) and XML Sitemaps.
	 */
	public static function fetch( $feed_row ) {
		$raw_items = array();
		$feed_url  = $feed_row['feed_url'];
		$seen_urls = array();

		// 1. Fetch Deep Paged RSS (Pages 1 to 25 = up to 250+ articles)
		$rss_items = self::fetch_rss_paged( $feed_url, 25 );
		if ( ! is_wp_error( $rss_items ) && ! empty( $rss_items ) ) {
			foreach ( $rss_items as $item ) {
				if ( ! empty( $item['url'] ) && ! isset( $seen_urls[ $item['url'] ] ) ) {
					$seen_urls[ $item['url'] ] = true;
					$raw_items[]               = $item;
				}
			}
		}

		// 2. Fetch Deep XML Sitemap & Sitemap Index if provided or probe /sitemap.xml
		$sitemap_url = ! empty( $feed_row['sitemap_url'] ) ? $feed_row['sitemap_url'] : self::probe_sitemap_url( $feed_url );
		if ( ! empty( $sitemap_url ) ) {
			$sitemap_items = self::fetch_sitemap( $sitemap_url );
			if ( ! is_wp_error( $sitemap_items ) && ! empty( $sitemap_items ) ) {
				foreach ( $sitemap_items as $item ) {
					if ( ! empty( $item['url'] ) && ! isset( $seen_urls[ $item['url'] ] ) ) {
						$seen_urls[ $item['url'] ] = true;
						$raw_items[]               = $item;
					}
				}
			}
		}

		if ( empty( $raw_items ) ) {
			return new WP_Error( 'feed_empty', __( 'Tidak ada artikel yang ditemukan dari feed/sitemap ini.', 'wifi-aggregator-hub' ) );
		}

		// 3. Format raw items into standardized articles
		$articles = array();
		foreach ( $raw_items as $raw ) {
			$formatted = self::format_item( $raw, $feed_row );
			if ( $formatted ) {
				$articles[] = $formatted;
			}
		}

		return $articles;
	}

	/**
	 * Probe sitemap URL if not explicitly defined.
	 */
	private static function probe_sitemap_url( $feed_url ) {
		$host = wp_parse_url( $feed_url, PHP_URL_SCHEME ) . '://' . wp_parse_url( $feed_url, PHP_URL_HOST );
		return $host . '/sitemap_index.xml';
	}

	/**
	 * Fetch multi-page RSS feed (/feed/?paged=1..N).
	 */
	private static function fetch_rss_paged( $base_url, $max_pages = 25 ) {
		if ( ! function_exists( 'fetch_feed' ) ) {
			require_once ABSPATH . WPINC . '/feed.php';
		}

		$all_items = array();
		$clean_url = preg_replace( '/\?.*$/', '', $base_url );
		$clean_url = rtrim( $clean_url, '/' );

		for ( $page = 1; $page <= $max_pages; $page++ ) {
			$page_url = ( 1 === $page ) ? $base_url : $clean_url . '/?paged=' . $page;
			$rss      = fetch_feed( $page_url );

			if ( is_wp_error( $rss ) ) {
				break;
			}

			$maxitems  = $rss->get_item_quantity( 50 );
			$rss_items = $rss->get_items( 0, $maxitems );

			if ( empty( $rss_items ) ) {
				break;
			}

			$page_count = 0;
			foreach ( $rss_items as $item ) {
				$title   = $item->get_title();
				$link    = $item->get_permalink();
				$date    = $item->get_date( 'Y-m-d H:i:s' );
				$excerpt = $item->get_description();

				$image = '';
				if ( $enclosure = $item->get_enclosure() ) {
					$image = $enclosure->get_link();
				}

				$cats       = array();
				$categories = $item->get_categories();
				if ( is_array( $categories ) ) {
					foreach ( $categories as $cat ) {
						$cats[] = $cat->get_label();
					}
				}

				$all_items[] = array(
					'title'          => $title,
					'url'            => $link,
					'publish_date'   => $date ? $date : current_time( 'mysql' ),
					'excerpt'        => $excerpt,
					'featured_image' => $image,
					'category'       => implode( ', ', $cats ),
					'tags'           => implode( ', ', $cats ),
				);
				$page_count++;
			}

			if ( $page_count < 5 ) {
				break; // Stop if less than 5 items returned
			}
		}

		return $all_items;
	}

	/**
	 * Recursive Sitemap XML & Sitemap Index parser.
	 */
	private static function fetch_sitemap( $url, $depth = 0 ) {
		if ( $depth > 3 ) {
			return array();
		}

		$response = wp_remote_get(
			$url,
			array(
				'timeout'    => 15,
				'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) WiFiAggregatorHub/1.0',
			)
		);

		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return array();
		}

		$body = wp_remote_retrieve_body( $response );
		if ( empty( $body ) ) {
			return array();
		}

		libxml_use_internal_errors( true );
		$xml = simplexml_load_string( $body );
		if ( ! $xml ) {
			return array();
		}

		$results = array();

		// Handle Sitemap Index (<sitemapindex><sitemap><loc>...</loc></sitemap></sitemapindex>)
		if ( isset( $xml->sitemap ) ) {
			foreach ( $xml->sitemap as $sub_sitemap ) {
				$sub_loc = (string) $sub_sitemap->loc;
				// Exclude media/author/category sub-sitemaps
				if ( ! preg_match( '/(author|category|post_tag|attachment|media|elementor)-sitemap/i', $sub_loc ) ) {
					$sub_results = self::fetch_sitemap( $sub_loc, $depth + 1 );
					$results     = array_merge( $results, $sub_results );
				}
			}
		}

		// Handle URL entries (<urlset><url><loc>...</loc></url></urlset>)
		if ( isset( $xml->url ) ) {
			foreach ( $xml->url as $url_node ) {
				$loc     = (string) $url_node->loc;
				$lastmod = isset( $url_node->lastmod ) ? date( 'Y-m-d H:i:s', strtotime( (string) $url_node->lastmod ) ) : current_time( 'mysql' );

				// Derive title from URL path slug
				$slug       = basename( parse_url( $loc, PHP_URL_PATH ) );
				$clean_name = ucwords( str_replace( array( '-', '_' ), ' ', $slug ) );

				if ( ! empty( $loc ) && strlen( $slug ) > 3 ) {
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
		$cta_url = $raw['url'];
		$wa_num  = '';
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
