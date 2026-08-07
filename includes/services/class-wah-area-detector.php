<?php
/**
 * Smart Indonesian Area / Territory Detector Service.
 * Automatic Fuzzy Matching & Dynamic Auto-Discovery of Indonesian Regencies/Cities.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WAH_Area_Detector {

	/**
	 * Detect area ID from text (title + excerpt + tags).
	 */
	public static function detect( $text ) {
		if ( empty( $text ) ) {
			return 0;
		}

		$db    = WAH_DB::get_instance();
		$areas = $db->get_areas();

		$haystack = mb_strtolower( $text, 'UTF-8' );

		// 1. Check existing areas in DB
		foreach ( $areas as $area ) {
			$name = mb_strtolower( $area['name'], 'UTF-8' );
			if ( false !== mb_strpos( $haystack, $name, 0, 'UTF-8' ) ) {
				return (int) $area['id'];
			}

			if ( ! empty( $area['aliases'] ) ) {
				$aliases = array_map( 'trim', explode( ',', $area['aliases'] ) );
				foreach ( $aliases as $alias ) {
					$alias_lower = mb_strtolower( $alias, 'UTF-8' );
					if ( false !== mb_strpos( $haystack, $alias_lower, 0, 'UTF-8' ) ) {
						return (int) $area['id'];
					}
				}
			}
		}

		// 2. Dynamic Auto-Discovery for new location names in title
		$auto_area_id = self::auto_discover( $text );
		if ( $auto_area_id > 0 ) {
			return $auto_area_id;
		}

		return 0;
	}

	/**
	 * Dynamically auto-discover and create new Indonesian territory from text patterns.
	 */
	public static function auto_discover( $text ) {
		$city_name = '';

		// Pattern A: "Kota X" or "Kabupaten X" or "Kab X"
		if ( preg_match( '/\b(Kota|Kabupaten|Kab)\s+([A-Z][a-zA-Z\s]{2,20})\b/u', $text, $matches ) ) {
			$city_name = trim( $matches[2] );
		}
		// Pattern B: "di [NamaKota]" e.g. "di Labungkari", "di Panyabungan"
		elseif ( preg_match( '/\bdi\s+([A-Z][a-zA-Z]{2,20}(?:\s+[A-Z][a-zA-Z]{2,20})?)\b/u', $text, $matches ) ) {
			$candidate = trim( $matches[1] );
			// Exclude common non-location words
			if ( ! in_self_stop_words( $candidate ) ) {
				$city_name = $candidate;
			}
		}
		// Pattern C: "IconNet [NamaKota]" or "WiFi [NamaKota]" at end of title e.g. "IconNet Sintang", "IconNet Waikabubak"
		elseif ( preg_match( '/\b(?:IconNet|WiFi|Wifi|Pasang|Paket|Harga)\s+([A-Z][a-zA-Z]{2,20}(?:\s+[A-Z][a-zA-Z]{2,20})?)\b/u', $text, $matches ) ) {
			$candidate = trim( $matches[1] );
			if ( ! in_self_stop_words( $candidate ) ) {
				$city_name = $candidate;
			}
		}

		if ( ! empty( $city_name ) ) {
			$slug      = sanitize_title( $city_name );
			$full_name = 'Kota ' . ucfirst( $city_name );

			$db       = WAH_DB::get_instance();
			$existing = $db->get_area_by_slug( $slug );

			if ( $existing ) {
				return (int) $existing['id'];
			}

			// Insert new area into DB
			$new_id = $db->insert_area(
				array(
					'name'          => $full_name,
					'type'          => 'city',
					'province_name' => 'Indonesia',
					'slug'          => $slug,
					'aliases'       => $full_name . ', ' . $city_name . ', Kabupaten ' . $city_name,
				)
			);

			if ( $new_id ) {
				$db->log( 'area', "Wilayah baru terdeteksi dari feed dan otomatis ditambahkan: $full_name" );
				return (int) $new_id;
			}
		}

		return 0;
	}
}

/**
 * Filter non-location stop words for auto discovery.
 */
function in_self_stop_words( $word ) {
	$stop = array( 'rumah', 'indonesia', 'murah', 'terbaru', 'terbaik', 'cepat', 'unlimited', 'fiber', 'wifi', 'iconnet', 'indihome', 'biznet', 'hifi', 'cbn', 'lengkap', 'perbandingan' );
	return in_array( strtolower( trim( $word ) ), $stop, true );
}
