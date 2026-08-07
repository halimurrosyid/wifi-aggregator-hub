<?php
/**
 * Admin Activity Logs View.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$db      = WAH_DB::get_instance();
$message = '';

if ( isset( $_POST['wah_clear_logs'] ) && check_admin_referer( 'wah_clear_logs_action', 'wah_logs_nonce' ) ) {
	$db->clear_logs();
	$message = 'Semua aktivitas log berhasil dibersihkan.';
}

$logs = $db->get_logs( 100 );
?>
<div class="wrap wah-admin-wrap">
	<h1 class="wah-title"><span class="dashicons dashicons-list-view"></span> Log Aktivitas System</h1>

	<?php if ( $message ) : ?>
		<div class="notice notice-success is-dismissible"><p><?php echo esc_html( $message ); ?></p></div>
	<?php endif; ?>

	<div class="wah-card-panel">
		<div class="wah-panel-header">
			<h3>Histori Log Aktivitas Sync & Engine</h3>
			<form method="post" action="">
				<?php wp_nonce_field( 'wah_clear_logs_action', 'wah_logs_nonce' ); ?>
				<button type="submit" name="wah_clear_logs" class="button button-secondary" onclick="return confirm('Bersihkan semua log?');">Bersihkan Log</button>
			</form>
		</div>

		<table class="widefat fixed striped">
			<thead>
				<tr>
					<th style="width: 160px;">Waktu Log</th>
					<th style="width: 120px;">Tipe Log</th>
					<th>Pesan / Keterangan</th>
					<th>Detail JSON</th>
				</tr>
			</thead>
			<tbody>
				<?php if ( empty( $logs ) ) : ?>
					<tr><td colspan="4">Belum ada catatan log aktivitas.</td></tr>
				<?php else : foreach ( $logs as $log ) : ?>
					<tr>
						<td><small><?php echo esc_html( $log['created_at'] ); ?></small></td>
						<td><span class="wah-badge <?php echo esc_attr( $log['log_type'] ); ?>"><?php echo esc_html( strtoupper( $log['log_type'] ) ); ?></span></td>
						<td><strong><?php echo esc_html( $log['message'] ); ?></strong></td>
						<td><code><?php echo esc_html( mb_strimwidth( $log['details'], 0, 80, '...' ) ); ?></code></td>
					</tr>
				<?php endforeach; endif; ?>
			</tbody>
		</table>
	</div>
</div>
