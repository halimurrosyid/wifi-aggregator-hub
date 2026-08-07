<?php
/**
 * Detect Indonesian territory / area from title, excerpt, categories, tags.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WAH_Area_Detector {

	/**
	 * Detect area ID from title, excerpt, tags.
	 *
	 * @param string $text Title or content snippet.
	 * @return int Area ID or 0 if not found.
	 */
	public static function detect( $text ) {
		$db    = WAH_DB::get_instance();
		$areas = $db->get_areas();

		if ( empty( $areas ) ) {
			return 0;
		}

		$haystack = mb_strtolower( $text, 'UTF-8' );

		foreach ( $areas as $area ) {
			// Match exact area name
			$name = mb_strtolower( $area['name'], 'UTF-8' );
			if ( preg_match( '/\b' . preg_quote( $name, '/' ) . '\b/u', $haystack ) ) {
				return (int) $area['id'];
			}

			// Match aliases
			if ( ! empty( $area['aliases'] ) ) {
				$aliases = array_map( 'trim', explode( ',', $area['aliases'] ) );
				foreach ( $aliases as $alias ) {
					if ( empty( $alias ) ) {
						continue;
					}
					$alias_lower = mb_strtolower( $alias, 'UTF-8' );
					if ( preg_match( '/\b' . preg_quote( $alias_lower, '/' ) . '\b/u', $haystack ) ) {
						return (int) $area['id'];
					}
				}
			}
		}

		// If not detected in DB, check for dynamic location patterns (e.g. "Kabupaten X", "Kota X", "Kab X")
		$auto_area_id = self::auto_discover( $text );
		if ( $auto_area_id > 0 ) {
			return $auto_area_id;
		}

		return 0;
	}

	/**
	 * Dynamically auto-discover and create new Indonesian territory if pattern matches.
	 */
	private static function auto_discover( $text ) {
		if ( preg_match( '/\b(Kota|Kabupaten|Kab)\s+([A-Z][a-z]+(?:\s+[A-Z][a-z]+)?)\b/u', $text, $matches ) ) {
			$type_str = mb_strtolower( $matches[1] );
			$city_name= trim( $matches[2] );
			$type     = ( 'kota' === $type_str ) ? 'city' : 'regency';
			$full_name= ucfirst( $matches[1] ) . ' ' . $city_name;
			$slug     = sanitize_title( $city_name );

			$db = WAH_DB::get_instance();
			// Check if slug already exists
			$existing = $db->get_area_by_slug( $slug );
			if ( $existing ) {
				return (int) $existing['id'];
			}

			// Insert new auto-discovered area into DB
			$new_id = $db->insert_area(
				array(
					'name'          => $full_name,
					'type'          => $type,
					'province_name' => 'Indonesia',
					'slug'          => $slug,
					'aliases'       => $full_name . ', ' . $city_name . ', ' . $matches[1] . ' ' . $city_name,
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
