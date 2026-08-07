<?php
/**
 * Feed Synchronization Service & WP Cron Orchestrator.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WAH_Synchronizer {

	/**
	 * Run synchronization across all active feeds.
	 *
	 * @return array Sync stats.
	 */
	public static function sync_all() {
		$db         = WAH_DB::get_instance();
		$feeds      = $db->get_feeds( 'active' );
		$new_count  = 0;
		$upd_count  = 0;
		$err_count  = 0;
		$start_time = microtime( true );

		if ( empty( $feeds ) ) {
			return array(
				'success'  => false,
				'message'  => 'Tidak ada feed aktif untuk disinkronkan.',
				'new'      => 0,
				'updated'  => 0,
				'errors'   => 0,
			);
		}

		foreach ( $feeds as $feed ) {
			$res = self::sync_feed( $feed['id'] );
			if ( is_wp_error( $res ) ) {
				$err_count++;
			} else {
				$new_count += $res['new'];
				$upd_count += $res['updated'];
			}
		}

		// Run deduplication after sync
		$dedup_res = WAH_Deduplicator::run();

		// Clear transients / cache
		delete_transient( 'wah_dashboard_metrics' );

		$duration = round( microtime( true ) - $start_time, 2 );
		$msg      = sprintf( 'Sinkronisasi selesai (%ss). Artikel Baru: %d, Diperbarui: %d, Duplikat: %d, Error: %d', $duration, $new_count, $upd_count, $dedup_res['duplicates'], $err_count );
		$db->log( 'sync', $msg );

		return array(
			'success'    => true,
			'message'    => $msg,
			'new'        => $new_count,
			'updated'    => $upd_count,
			'duplicates' => $dedup_res['duplicates'],
			'errors'     => $err_count,
		);
	}

	/**
	 * Sync single feed by feed ID.
	 */
	public static function sync_feed( $feed_id ) {
		$db   = WAH_DB::get_instance();
		$feed = $db->get_feed( $feed_id );

		if ( ! $feed ) {
			return new WP_Error( 'feed_not_found', 'Feed tidak ditemukan.' );
		}

		$fetched_articles = WAH_Fetcher::fetch( $feed );

		if ( is_wp_error( $fetched_articles ) ) {
			$err_msg = $fetched_articles->get_error_message();
			$db->update_feed(
				$feed_id,
				array(
					'error_message' => $err_msg,
					'last_synced'   => current_time( 'mysql' ),
				)
			);
			$db->log( 'error', "Gagal membaca feed [{$feed['website_name']}]: $err_msg" );
			return $fetched_articles;
		}

		$new_count = 0;
		$upd_count = 0;

		foreach ( $fetched_articles as $art_data ) {
			$existing = $db->get_article_by_url( $art_data['url'] );
			if ( $existing ) {
				$detected_area = $art_data['area_id'] ? $art_data['area_id'] : WAH_Area_Detector::detect( $art_data['title'] . ' ' . $art_data['excerpt'] );
				$db->update_article(
					$existing['id'],
					array(
						'title'       => $art_data['title'],
						'excerpt'     => $art_data['excerpt'],
						'update_date' => current_time( 'mysql' ),
						'provider_id' => $art_data['provider_id'] ? $art_data['provider_id'] : $existing['provider_id'],
						'area_id'     => $detected_area ? $detected_area : $existing['area_id'],
					)
				);
				$upd_count++;
			} else {
				// Insert new article
				$db->insert_article( $art_data );
				$new_count++;
			}
		}

		// Update feed status
		$db->update_feed(
			$feed_id,
			array(
				'last_synced'   => current_time( 'mysql' ),
				'error_message' => '',
			)
		);

		return array(
			'new'     => $new_count,
			'updated' => $upd_count,
		);
	}
}
