<?php
/**
 * Fired during plugin deactivation.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WAH_Deactivator {

	/**
	 * Deactivate the plugin.
	 */
	public static function deactivate() {
		wp_clear_scheduled_hook( 'wah_cron_sync_feeds' );
		wp_clear_scheduled_hook( 'wah_cron_check_links' );
		flush_rewrite_rules();
	}
}
