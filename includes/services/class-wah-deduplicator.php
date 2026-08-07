<?php
/**
 * Deduplicate articles based on Provider + Area grouping.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WAH_Deduplicator {

	/**
	 * Run deduplication process over all articles or for a specific area/provider pair.
	 *
	 * @return array Summary of processed and duplicate count.
	 */
	public static function run() {
		global $wpdb;
		$db         = WAH_DB::get_instance();
		$t_articles = $wpdb->prefix . 'wah_articles';
		$strategy   = get_option( 'wah_dedup_strategy', 'domain_priority' );

		// Find groups with more than 1 active article having valid provider and area
		$groups = $wpdb->get_results(
			"SELECT provider_id, area_id, COUNT(*) as count 
			 FROM $t_articles 
			 WHERE status = 'active' AND provider_id > 0 AND area_id > 0 
			 GROUP BY provider_id, area_id 
			 HAVING count > 1",
			ARRAY_A
		);

		$duplicates_found = 0;

		foreach ( $groups as $group ) {
			$provider_id = intval( $group['provider_id'] );
			$area_id     = intval( $group['area_id'] );

			// Get all active articles for this (provider_id, area_id)
			$articles = $db->get_articles(
				array(
					'provider_id' => $provider_id,
					'area_id'     => $area_id,
					'status'      => 'active',
				)
			);

			if ( count( $articles ) <= 1 ) {
				continue;
			}

			// Sort articles to pick the winner based on selected strategy
			$winner = self::pick_winner( $articles, $strategy );

			// Mark all others as 'duplicate'
			foreach ( $articles as $art ) {
				if ( (int) $art['id'] !== (int) $winner['id'] ) {
					$db->update_article( $art['id'], array( 'status' => 'duplicate' ) );
					$duplicates_found++;
				}
			}
		}

		if ( $duplicates_found > 0 ) {
			$db->log( 'duplicate', "Proses deduplikasi selesai. $duplicates_found artikel ditandai sebagai duplikat (Strategi: $strategy)." );
		}

		return array(
			'duplicates' => $duplicates_found,
			'strategy'   => $strategy,
		);
	}

	/**
	 * Pick winning article from a list of duplicates based on strategy.
	 */
	private static function pick_winner( $articles, $strategy ) {
		$db = WAH_DB::get_instance();

		if ( 'latest' === $strategy ) {
			usort(
				$articles,
				function( $a, $b ) {
					return strtotime( $b['publish_date'] ) - strtotime( $a['publish_date'] );
				}
			);
			return $articles[0];
		}

		if ( 'longest' === $strategy ) {
			usort(
				$articles,
				function( $a, $b ) {
					return $b['word_count'] - $a['word_count'];
				}
			);
			return $articles[0];
		}

		// Default: domain_priority
		// Fetch feeds to get feed priority (lower priority number = higher priority)
		$feeds         = $db->get_feeds();
		$feed_priorities = array();
		foreach ( $feeds as $f ) {
			$feed_priorities[ $f['id'] ] = (int) $f['priority'];
		}

		usort(
			$articles,
			function( $a, $b ) use ( $feed_priorities ) {
				$p_a = $feed_priorities[ $a['feed_id'] ] ?? 99;
				$p_b = $feed_priorities[ $b['feed_id'] ] ?? 99;
				if ( $p_a === $p_b ) {
					return strtotime( $b['publish_date'] ) - strtotime( $a['publish_date'] );
				}
				return $p_a - $p_b;
			}
		);

		return $articles[0];
	}
}
