<?php
/**
 * Admin Synchronization View.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$message = '';
if ( isset( $_POST['wah_save_sync_settings'] ) && check_admin_referer( 'wah_sync_settings', 'wah_sync_nonce' ) ) {
	$schedule = sanitize_text_field( $_POST['cron_schedule'] ?? 'hourly' );
	update_option( 'wah_cron_schedule', $schedule );

	// Reschedule WP Cron
	wp_clear_scheduled_hook( 'wah_cron_sync_feeds' );
	wp_schedule_event( time(), $schedule, 'wah_cron_sync_feeds' );

	$message = 'Pengaturan jadwal sinkronisasi WP-Cron berhasil diperbarui.';
}

$current_schedule = get_option( 'wah_cron_schedule', 'hourly' );
$db               = WAH_DB::get_instance();
$logs             = $db->get_logs( 15 );
?>
<div class="wrap wah-admin-wrap">
	<h1 class="wah-title"><span class="dashicons dashicons-update"></span> Pengaturan Sinkronisasi</h1>

	<?php if ( $message ) : ?>
		<div class="notice notice-success is-dismissible"><p><?php echo esc_html( $message ); ?></p></div>
	<?php endif; ?>

	<div class="wah-card-panel">
		<h3>Jadwal Otomatis WP-Cron</h3>
		<form method="post" action="">
			<?php wp_nonce_field( 'wah_sync_settings', 'wah_sync_nonce' ); ?>
			<table class="form-table">
				<tr>
					<th scope="row"><label for="cron_schedule">Frekuensi Sinkronisasi:</label></th>
					<td>
						<select name="cron_schedule" id="cron_schedule">
							<option value="hourly" <?php selected( $current_schedule, 'hourly' ); ?>>Setiap Jam</option>
							<option value="twicedaily" <?php selected( $current_schedule, 'twicedaily' ); ?>>Setiap 12 Jam</option>
							<option value="daily" <?php selected( $current_schedule, 'daily' ); ?>>Harian (Daily)</option>
						</select>
						<p class="description">Proses sinkronisasi akan berjalan di latar belakang tanpa memberatkan pengunjung.</p>
					</td>
				</tr>
			</table>
			<button type="submit" name="wah_save_sync_settings" class="button button-primary">Simpan Jadwal</button>
		</form>
	</div>

	<div class="wah-card-panel margin-top-20">
		<h3>Prosedur Sinkronisasi Engine</h3>
		<ol class="wah-steps-list">
			<li><strong>1. Download Feed:</strong> Membaca data XML/RSS atau Sitemap XML terbaru dari domain terdaftar.</li>
			<li><strong>2. Validasi & Parsing:</strong> Mengekstrak judul, URL asli, thumbnail, tanggal update, dan kata kunci.</li>
			<li><strong>3. Provider & Area Matching:</strong> Mendeteksi otomatis jenis ISP dan wilayah Indonesia.</li>
			<li><strong>4. Local DB Indexing:</strong> Menyimpan data summary ke tabel khusus agregator.</li>
			<li><strong>5. Deduplikasi Content:</strong> Memfilter artikel duplikat dari domain berbeda sesuai strategi.</li>
			<li><strong>6. Update Landing Page & Sitemap:</strong> Memperbarui meta SEO & sitemap XML otomatis.</li>
		</ol>

		<div class="wah-actions-row">
			<button id="wah-btn-sync-all" class="button button-primary button-hero">Jalankan Sinkronisasi Manual</button>
			<button id="wah-btn-check-links" class="button button-secondary button-hero">Cek Link Rusak / Noindex</button>
		</div>

		<div id="wah-sync-progress" class="wah-progress-bar-container hidden">
			<div class="wah-progress-bar"></div>
			<p class="wah-progress-text">Sedang memproses...</p>
		</div>
	</div>
</div>
