<?php
/**
 * WiFi Aggregator Hub - System Logs View with 15-item Pagination
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$db = WAH_DB::get_instance();

// Clear Logs Action
if ( isset( $_POST['wah_clear_logs'] ) && check_admin_referer( 'wah_clear_logs_nonce' ) ) {
	$db->clear_logs();
	echo '<div class="notice notice-success is-dismissible"><p>Log aktivitas berhasil dibersihkan!</p></div>';
}

$all_logs     = $db->get_logs( 200 );
$current_page = isset( $_GET['paged'] ) ? max( 1, intval( $_GET['paged'] ) ) : 1;
$per_page     = 15;
$total_items  = count( $all_logs );
$total_pages  = ceil( $total_items / $per_page );
$offset       = ( $current_page - 1 ) * $per_page;

$logs = array_slice( $all_logs, $offset, $per_page );
?>
<div class="wrap wah-admin-wrap">
	<h1 class="wah-title"><span class="dashicons dashicons-list-view"></span> System Logs & Log Aktivitas Engine</h1>

	<div class="wah-card-panel">
		<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
			<div>
				<h3 style="margin:0;">Riwayat Aktivitas Engine (Halaman <?php echo esc_html( $current_page ); ?> dari <?php echo esc_html( max( 1, $total_pages ) ); ?>)</h3>
				<span style="font-size:13px; color:#64748b;">Log aktivitas sinkronisasi feed, deteksi provider, dan update sistem.</span>
			</div>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=wah-logs' ) ); ?>">
				<?php wp_nonce_field( 'wah_clear_logs_nonce' ); ?>
				<button type="submit" name="wah_clear_logs" class="button button-secondary" onclick="return confirm('Kosongkan seluruh log aktivitas?');">Bersihkan Logs</button>
			</form>
		</div>

		<table class="widefat fixed striped">
			<thead>
				<tr>
					<th style="width:70px;">ID</th>
					<th style="width:120px;">Tipe Log</th>
					<th>Pesan Aktivitas</th>
					<th>Detail Tambahan</th>
					<th style="width:160px;">Waktu</th>
				</tr>
			</thead>
			<tbody>
				<?php if ( empty( $logs ) ) : ?>
					<tr><td colspan="5">Belum ada riwayat log aktivitas.</td></tr>
				<?php else : foreach ( $logs as $log ) : ?>
					<tr>
						<td>#<?php echo esc_html( $log['id'] ); ?></td>
						<td><span class="wah-badge info"><?php echo esc_html( strtoupper( $log['log_type'] ) ); ?></span></td>
						<td><strong><?php echo esc_html( $log['message'] ); ?></strong></td>
						<td><code><?php echo esc_html( $log['details'] ? ( mb_strlen( $log['details'] ) > 80 ? mb_substr( $log['details'], 0, 77 ) . '...' : $log['details'] ) : '-' ); ?></code></td>
						<td><small><?php echo esc_html( date( 'd M Y H:i:s', strtotime( $log['created_at'] ) ) ); ?></small></td>
					</tr>
				<?php endforeach; endif; ?>
			</tbody>
		</table>

		<!-- Pagination Controls -->
		<?php if ( $total_pages > 1 ) : ?>
			<div style="display:flex; justify-content:space-between; align-items:center; margin-top:15px; padding-top:10px; border-top:1px solid #e2e8f0;">
				<div style="font-size:13px; color:#64748b;">
					Menampilkan log <strong><?php echo esc_html( $offset + 1 ); ?></strong> - <strong><?php echo esc_html( min( $total_items, $offset + $per_page ) ); ?></strong> dari <strong><?php echo esc_html( $total_items ); ?></strong>
				</div>
				<div style="display:flex; gap:5px;">
					<?php if ( $current_page > 1 ) : ?>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=wah-logs&paged=' . ( $current_page - 1 ) ) ); ?>" class="button">&laquo; Sblmnya</a>
					<?php endif; ?>

					<?php for ( $i = 1; $i <= $total_pages; $i++ ) : 
						if ( $i == $current_page || $i == 1 || $i == $total_pages || ( $i >= $current_page - 2 && $i <= $current_page + 2 ) ) :
					?>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=wah-logs&paged=' . $i ) ); ?>" class="button <?php echo ( $i == $current_page ) ? 'button-primary' : ''; ?>"><?php echo esc_html( $i ); ?></a>
					<?php elseif ( $i == $current_page - 3 || $i == $current_page + 3 ) : ?>
						<span style="padding:4px 8px; color:#94a3b8;">...</span>
					<?php endif; endfor; ?>

					<?php if ( $current_page < $total_pages ) : ?>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=wah-logs&paged=' . ( $current_page + 1 ) ) ); ?>" class="button">Selanjutnya &raquo;</a>
					<?php endif; ?>
				</div>
			</div>
		<?php endif; ?>
	</div>
</div>
