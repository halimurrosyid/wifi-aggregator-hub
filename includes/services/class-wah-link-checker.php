<?php
/**
 * Broken Link & Noindex Checker Service.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WAH_Link_Checker {

	/**
	 * Run link checker over active articles.
	 *
	 * @param int $limit Number of links to check per batch.
	 * @return array Results summary.
	 */
	public static function check_all( $limit = 50 ) {
		$db       = WAH_DB::get_instance();
		$articles = $db->get_articles(
			array(
				'status' => 'active',
				'limit'  => $limit,
			)
		);

		$checked = 0;
		$broken  = 0;

		foreach ( $articles as $art ) {
			$res = self::check_url( $art['url'] );
			$checked++;

			if ( $res['is_broken'] ) {
				$broken++;
				$db->update_article(
					$art['id'],
					array(
						'status'      => 'broken',
						'http_status' => $res['http_code'],
					)
				);
				$db->log( 'broken_link', "Artikel ID {$art['id']} ({$art['url']}) ditandai rusak. HTTP Status: {$res['http_code']}" );
			} else {
				$db->update_article(
					$art['id'],
					array(
						'http_status' => $res['http_code'],
					)
				);
			}
		}

		return array(
			'checked' => $checked,
			'broken'  => $broken,
		);
	}

	/**
	 * Check single URL HTTP response & meta noindex directive.
	 */
	public static function check_url( $url ) {
		$response = wp_remote_head(
			$url,
			array(
				'timeout'     => 10,
				'redirection' => 5,
				'user-agent'  => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) WiFiAggregatorHub/1.0',
			)
		);

		if ( is_wp_error( $response ) ) {
			// Try GET if HEAD fails
			$response = wp_remote_get(
				$url,
				array(
					'timeout'     => 10,
					'redirection' => 5,
					'user-agent'  => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) WiFiAggregatorHub/1.0',
				)
			);
		}

		if ( is_wp_error( $response ) ) {
			return array(
				'http_code' => '404',
				'is_broken' => true,
			);
		}

		$http_code = (string) wp_remote_retrieve_response_code( $response );
		$is_broken = ( (int) $http_code >= 400 );

		// Check body for noindex if 200 OK
		if ( ! $is_broken ) {
			$body = wp_remote_retrieve_body( $response );
			if ( ! empty( $body ) && preg_match( '/<meta[^>]+name=[\'"]robots[\'"][^>]+content=[\'"][^\'"]*noindex/i', $body ) ) {
				$is_broken = true;
				$http_code = 'noindex';
			}
		}

		return array(
			'http_code' => $http_code,
			'is_broken' => $is_broken,
		);
	}
}
