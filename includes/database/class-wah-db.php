<?php
/**
 * Database abstraction layer using WPDB.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WAH_DB {

	/**
	 * Single instance.
	 */
	private static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	// -------------------------------------------------------------------------
	// FEEDS
	// -------------------------------------------------------------------------

	public function get_feeds( $status = null ) {
		global $wpdb;
		$table = $wpdb->prefix . 'wah_feeds';
		if ( $status ) {
			return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table WHERE status = %s ORDER BY priority ASC, id DESC", $status ), ARRAY_A );
		}
		return $wpdb->get_results( "SELECT * FROM $table ORDER BY priority ASC, id DESC", ARRAY_A );
	}

	public function get_feed( $id ) {
		global $wpdb;
		$table = $wpdb->prefix . 'wah_feeds';
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", $id ), ARRAY_A );
	}

	public function insert_feed( $data ) {
		global $wpdb;
		$table = $wpdb->prefix . 'wah_feeds';
		$wpdb->insert(
			$table,
			array(
				'website_name'  => sanitize_text_field( $data['website_name'] ),
				'feed_url'      => esc_url_raw( $data['feed_url'] ),
				'sitemap_url'   => ! empty( $data['sitemap_url'] ) ? esc_url_raw( $data['sitemap_url'] ) : '',
				'priority'      => isset( $data['priority'] ) ? intval( $data['priority'] ) : 10,
				'status'        => isset( $data['status'] ) ? sanitize_text_field( $data['status'] ) : 'active',
				'error_message' => '',
			)
		);
		return $wpdb->insert_id;
	}

	public function update_feed( $id, $data ) {
		global $wpdb;
		$table = $wpdb->prefix . 'wah_feeds';
		return $wpdb->update( $table, $data, array( 'id' => intval( $id ) ) );
	}

	public function delete_feed( $id ) {
		global $wpdb;
		$table = $wpdb->prefix . 'wah_feeds';
		return $wpdb->delete( $table, array( 'id' => intval( $id ) ) );
	}

	// -------------------------------------------------------------------------
	// PROVIDERS
	// -------------------------------------------------------------------------

	public function get_providers() {
		global $wpdb;
		$table = $wpdb->prefix . 'wah_providers';
		return $wpdb->get_results( "SELECT * FROM $table ORDER BY display_order ASC, name ASC", ARRAY_A );
	}

	public function get_provider( $id ) {
		global $wpdb;
		$table = $wpdb->prefix . 'wah_providers';
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", $id ), ARRAY_A );
	}

	public function get_provider_by_slug( $slug ) {
		global $wpdb;
		$table = $wpdb->prefix . 'wah_providers';
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE slug = %s", $slug ), ARRAY_A );
	}

	public function insert_provider( $data ) {
		global $wpdb;
		$table = $wpdb->prefix . 'wah_providers';
		$slug  = ! empty( $data['slug'] ) ? sanitize_title( $data['slug'] ) : sanitize_title( $data['name'] );
		$wpdb->insert(
			$table,
			array(
				'name'          => sanitize_text_field( $data['name'] ),
				'slug'          => $slug,
				'aliases'       => sanitize_text_field( $data['aliases'] ?? '' ),
				'logo_url'      => esc_url_raw( $data['logo_url'] ?? '' ),
				'brand_color'   => sanitize_hex_color( $data['brand_color'] ?? '#00a896' ),
				'display_order' => intval( $data['display_order'] ?? 0 ),
			)
		);
		return $wpdb->insert_id;
	}

	public function update_provider( $id, $data ) {
		global $wpdb;
		$table = $wpdb->prefix . 'wah_providers';
		return $wpdb->update( $table, $data, array( 'id' => intval( $id ) ) );
	}

	public function delete_provider( $id ) {
		global $wpdb;
		$table = $wpdb->prefix . 'wah_providers';
		return $wpdb->delete( $table, array( 'id' => intval( $id ) ) );
	}

	// -------------------------------------------------------------------------
	// AREAS
	// -------------------------------------------------------------------------

	public function get_areas( $type = null ) {
		global $wpdb;
		$table = $wpdb->prefix . 'wah_areas';
		if ( $type ) {
			return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table WHERE type = %s ORDER BY name ASC", $type ), ARRAY_A );
		}
		return $wpdb->get_results( "SELECT * FROM $table ORDER BY name ASC", ARRAY_A );
	}

	public function get_area( $id ) {
		global $wpdb;
		$table = $wpdb->prefix . 'wah_areas';
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", $id ), ARRAY_A );
	}

	public function get_area_by_slug( $slug ) {
		global $wpdb;
		$table = $wpdb->prefix . 'wah_areas';
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE slug = %s", $slug ), ARRAY_A );
	}

	/**
	 * Get ONLY areas that have at least 1 active indexed article.
	 */
	public function get_active_landing_areas() {
		global $wpdb;
		$t_areas    = $wpdb->prefix . 'wah_areas';
		$t_articles = $wpdb->prefix . 'wah_articles';

		return $wpdb->get_results(
			"SELECT DISTINCT a.* 
			 FROM $t_areas a 
			 INNER JOIN $t_articles ar ON a.id = ar.area_id 
			 WHERE ar.status = 'active' AND ar.area_id > 0 
			 ORDER BY a.name ASC",
			ARRAY_A
		);
	}

	/**
	 * Get sub-areas (Kecamatan & Desa) for a specific Kota / Kabupaten.
	 */
	public function get_sub_areas( $area ) {
		$area_name = is_array( $area ) ? $area['name'] : $area;
		$clean_name = trim( preg_replace( '/^(Kota|Kabupaten|Kab)\s+/i', '', $area_name ) );

		// Pre-loaded sub-district dictionary for Indonesian territories
		$sub_dict = array(
			'Labungkari' => array( 'Kecamatan Gu', 'Kecamatan Lakudo', 'Kecamatan Mawasangka', 'Kecamatan Mawasangka Timur', 'Kecamatan Mawasangka Tengah', 'Kecamatan Sangia Wambulu', 'Kecamatan Talaga Raya', 'Desa Bombonawulu', 'Desa Labungkari', 'Desa Lakudo', 'Kelurahan Mawasangka' ),
			'Bandung'    => array( 'Kecamatan Coblong', 'Kecamatan Cicendo', 'Kecamatan Lengkong', 'Kecamatan Sumur Bandung', 'Kecamatan Sukajadi', 'Kecamatan Cidadap', 'Kecamatan Buahbatu', 'Kecamatan Regol', 'Kecamatan Bojongloa Kaler' ),
			'Garut'      => array( 'Kecamatan Garut Kota', 'Kecamatan Tarogong Kaler', 'Kecamatan Tarogong Kidul', 'Kecamatan Karangpawitan', 'Kecamatan Wanaraja', 'Kecamatan Leles', 'Kecamatan Kadungora', 'Kecamatan Cilawu' ),
			'Tangerang'  => array( 'Kecamatan Tangerang', 'Kecamatan Karawaci', 'Kecamatan Cibodas', 'Kecamatan Ciledug', 'Kecamatan Cipondoh', 'Kecamatan Pinang', 'Kecamatan Serpong', 'Kecamatan BSD' ),
			'Bekasi'     => array( 'Kecamatan Bekasi Barat', 'Kecamatan Bekasi Timur', 'Kecamatan Bekasi Selatan', 'Kecamatan Bekasi Utara', 'Kecamatan Jatiasih', 'Kecamatan Pondok Gede' ),
			'Bogor'      => array( 'Kecamatan Bogor Tengah', 'Kecamatan Bogor Barat', 'Kecamatan Bogor Selatan', 'Kecamatan Bogor Utara', 'Kecamatan Bogor Timur', 'Kecamatan Tanah Sareal' ),
		);

		foreach ( $sub_dict as $key => $subs ) {
			if ( false !== stripos( $clean_name, $key ) ) {
				return $subs;
			}
		}

		// Fallback dynamic generator for any other city
		return array(
			'Kecamatan ' . $clean_name . ' Pusat',
			'Kecamatan ' . $clean_name . ' Barat',
			'Kecamatan ' . $clean_name . ' Timur',
			'Kecamatan ' . $clean_name . ' Utara',
			'Kecamatan ' . $clean_name . ' Selatan',
			'Desa / Kelurahan ' . $clean_name,
		);
	}

	public function search_areas( $keyword, $limit = 10 ) {
		global $wpdb;
		$table = $wpdb->prefix . 'wah_areas';
		$like  = '%' . $wpdb->esc_like( $keyword ) . '%';
		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM $table WHERE name LIKE %s OR aliases LIKE %s ORDER BY name ASC LIMIT %d",
				$like,
				$like,
				$limit
			),
			ARRAY_A
		);
	}

	public function insert_area( $data ) {
		global $wpdb;
		$table = $wpdb->prefix . 'wah_areas';
		$slug  = ! empty( $data['slug'] ) ? sanitize_title( $data['slug'] ) : sanitize_title( $data['name'] );
		$wpdb->insert(
			$table,
			array(
				'name'          => sanitize_text_field( $data['name'] ),
				'type'          => sanitize_text_field( $data['type'] ?? 'city' ),
				'province_name' => sanitize_text_field( $data['province_name'] ?? '' ),
				'slug'          => $slug,
				'aliases'       => sanitize_text_field( $data['aliases'] ?? '' ),
			)
		);
		return $wpdb->insert_id;
	}

	public function update_area( $id, $data ) {
		global $wpdb;
		$table = $wpdb->prefix . 'wah_areas';
		return $wpdb->update( $table, $data, array( 'id' => intval( $id ) ) );
	}

	public function delete_area( $id ) {
		global $wpdb;
		$table = $wpdb->prefix . 'wah_areas';
		return $wpdb->delete( $table, array( 'id' => intval( $id ) ) );
	}

	// -------------------------------------------------------------------------
	// ARTICLES
	// -------------------------------------------------------------------------

	public function get_articles( $args = array() ) {
		global $wpdb;
		$table  = $wpdb->prefix . 'wah_articles';
		$where  = array('1=1');
		$params = array();

		if ( ! empty( $args['status'] ) ) {
			$where[]  = 'status = %s';
			$params[] = $args['status'];
		}

		if ( ! empty( $args['area_id'] ) ) {
			if ( ! empty( $args['include_general'] ) ) {
				$where[]  = '(area_id = %d OR area_id = 0)';
				$params[] = intval( $args['area_id'] );
			} else {
				$where[]  = 'area_id = %d';
				$params[] = intval( $args['area_id'] );
			}
		}

		if ( ! empty( $args['provider_id'] ) ) {
			$where[]  = 'provider_id = %d';
			$params[] = intval( $args['provider_id'] );
		}

		if ( ! empty( $args['feed_id'] ) ) {
			$where[]  = 'feed_id = %d';
			$params[] = intval( $args['feed_id'] );
		}

		$where_sql = implode( ' AND ', $where );
		$limit_sql = '';
		if ( isset( $args['limit'] ) ) {
			$limit     = intval( $args['limit'] );
			$offset    = isset( $args['offset'] ) ? intval( $args['offset'] ) : 0;
			$limit_sql = " LIMIT $offset, $limit";
		}

		$query = "SELECT * FROM $table WHERE $where_sql ORDER BY publish_date DESC, id DESC $limit_sql";
		if ( ! empty( $params ) ) {
			return $wpdb->get_results( $wpdb->prepare( $query, $params ), ARRAY_A );
		}
		return $wpdb->get_results( $query, ARRAY_A );
	}

	public function get_article_by_url( $url ) {
		global $wpdb;
		$table = $wpdb->prefix . 'wah_articles';
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE url = %s", $url ), ARRAY_A );
	}

	public function insert_article( $data ) {
		global $wpdb;
		$table  = $wpdb->prefix . 'wah_articles';
		$domain = wp_parse_url( $data['url'], PHP_URL_HOST );
		if ( ! $domain ) {
			$domain = $data['website_name'] ?? '';
		}

		$wpdb->insert(
			$table,
			array(
				'feed_id'         => intval( $data['feed_id'] ?? 0 ),
				'provider_id'     => intval( $data['provider_id'] ?? 0 ),
				'area_id'         => intval( $data['area_id'] ?? 0 ),
				'title'           => sanitize_text_field( $data['title'] ),
				'url'             => esc_url_raw( $data['url'] ),
				'slug'            => sanitize_title( $data['slug'] ?? $data['title'] ),
				'publish_date'    => ! empty( $data['publish_date'] ) ? $data['publish_date'] : current_time( 'mysql' ),
				'update_date'     => ! empty( $data['update_date'] ) ? $data['update_date'] : current_time( 'mysql' ),
				'excerpt'         => wp_strip_all_tags( $data['excerpt'] ?? '' ),
				'ai_summary'      => sanitize_textarea_field( $data['ai_summary'] ?? '' ),
				'featured_image'  => esc_url_raw( $data['featured_image'] ?? '' ),
				'category'        => sanitize_text_field( $data['category'] ?? '' ),
				'tags'            => sanitize_text_field( $data['tags'] ?? '' ),
				'website_name'    => sanitize_text_field( $data['website_name'] ?? '' ),
				'cta_url'         => esc_url_raw( $data['cta_url'] ?? $data['url'] ),
				'whatsapp_number' => sanitize_text_field( $data['whatsapp_number'] ?? '' ),
				'domain'          => sanitize_text_field( $domain ),
				'word_count'      => intval( str_word_count( strip_tags( $data['excerpt'] ?? '' ) ) ),
				'status'          => sanitize_text_field( $data['status'] ?? 'active' ),
				'http_status'     => sanitize_text_field( $data['http_status'] ?? '200' ),
			)
		);
		return $wpdb->insert_id;
	}

	public function update_article( $id, $data ) {
		global $wpdb;
		$table = $wpdb->prefix . 'wah_articles';
		return $wpdb->update( $table, $data, array( 'id' => intval( $id ) ) );
	}

	public function delete_article( $id ) {
		global $wpdb;
		$table = $wpdb->prefix . 'wah_articles';
		return $wpdb->delete( $table, array( 'id' => intval( $id ) ) );
	}

	public function increment_article_clicks( $id ) {
		global $wpdb;
		$table = $wpdb->prefix . 'wah_articles';
		return $wpdb->query( $wpdb->prepare( "UPDATE $table SET word_count = word_count + 1 WHERE id = %d", $id ) );
	}

	public function increment_landing_views( $slug ) {
		global $wpdb;
		$table = $wpdb->prefix . 'wah_landings';
		return $wpdb->query( $wpdb->prepare( "UPDATE $table SET view_count = view_count + 1 WHERE slug = %s", $slug ) );
	}

	// -------------------------------------------------------------------------
	// LOGS
	// -------------------------------------------------------------------------

	public function log( $type, $message, $details = '' ) {
		global $wpdb;
		$table = $wpdb->prefix . 'wah_logs';
		$wpdb->insert(
			$table,
			array(
				'log_type' => sanitize_text_field( $type ),
				'message'  => sanitize_text_field( $message ),
				'details'  => is_array( $details ) || is_object( $details ) ? wp_json_encode( $details ) : sanitize_textarea_field( $details ),
			)
		);
	}

	public function get_logs( $limit = 50 ) {
		global $wpdb;
		$table = $wpdb->prefix . 'wah_logs';
		return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table ORDER BY id DESC LIMIT %d", $limit ), ARRAY_A );
	}

	public function clear_logs() {
		global $wpdb;
		$table = $wpdb->prefix . 'wah_logs';
		return $wpdb->query( "TRUNCATE TABLE $table" );
	}

	// -------------------------------------------------------------------------
	// METRICS
	// -------------------------------------------------------------------------

	public function get_dashboard_metrics() {
		global $wpdb;
		$t_feeds    = $wpdb->prefix . 'wah_feeds';
		$t_articles = $wpdb->prefix . 'wah_articles';
		$t_prov     = $wpdb->prefix . 'wah_providers';
		$t_areas    = $wpdb->prefix . 'wah_areas';

		$total_feeds     = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $t_feeds" );
		$total_articles  = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $t_articles WHERE status = 'active'" );
		$total_new_24h   = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $t_articles WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)" );
		$total_duplicate = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $t_articles WHERE status = 'duplicate'" );
		$total_broken    = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $t_articles WHERE status = 'broken' OR http_status = '404'" );
		$total_providers = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $t_prov" );
		$total_areas     = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $t_areas" );
		$last_sync       = $wpdb->get_var( "SELECT MAX(last_synced) FROM $t_feeds" );

		return array(
			'total_feeds'     => $total_feeds,
			'total_articles'  => $total_articles,
			'total_new_24h'   => $total_new_24h,
			'total_duplicate' => $total_duplicate,
			'total_broken'    => $total_broken,
			'total_providers' => $total_providers,
			'total_areas'     => $total_areas,
			'last_synced'     => $last_sync ? $last_sync : '-',
		);
	}
}
