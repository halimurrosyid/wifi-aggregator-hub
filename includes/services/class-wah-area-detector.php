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

		return 0;
	}
}
