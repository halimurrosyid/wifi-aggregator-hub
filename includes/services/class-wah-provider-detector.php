<?php
/**
 * Detect ISP Provider from text content, title, tags, or domain using DB aliases.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WAH_Provider_Detector {

	/**
	 * Detect provider ID from title, excerpt, tags, domain.
	 *
	 * @param string $text Title, excerpt, or combined text.
	 * @param string $domain Domain name.
	 * @return int Provider ID or 0 if not detected.
	 */
	public static function detect( $text, $domain = '' ) {
		$db        = WAH_DB::get_instance();
		$providers = $db->get_providers();

		if ( empty( $providers ) ) {
			return 0;
		}

		$haystack = mb_strtolower( $text . ' ' . $domain, 'UTF-8' );

		foreach ( $providers as $prov ) {
			// Check provider primary name
			$name = mb_strtolower( $prov['name'], 'UTF-8' );
			if ( false !== mb_strpos( $haystack, $name, 0, 'UTF-8' ) ) {
				return (int) $prov['id'];
			}

			// Check aliases
			if ( ! empty( $prov['aliases'] ) ) {
				$aliases = array_map( 'trim', explode( ',', $prov['aliases'] ) );
				foreach ( $aliases as $alias ) {
					if ( empty( $alias ) ) {
						continue;
					}
					$alias_lower = mb_strtolower( $alias, 'UTF-8' );
					if ( false !== mb_strpos( $haystack, $alias_lower, 0, 'UTF-8' ) ) {
						return (int) $prov['id'];
					}
				}
			}
		}

		return 0;
	}
}
