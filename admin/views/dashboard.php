<?php
/**
 * Admin Dashboard View.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$db      = WAH_DB::get_instance();
$metrics = $db->get_dashboard_metrics();
$cron_next = wp_next_scheduled( 'wah_cron_sync_feeds' );
$cron_status = $cron_next ? 'Aktif (Jadwal Berikutnya: ' . date( 'd M Y H:i:s', $cron_next ) . ')' : 'Non-Aktif';
?>
<div class="wrap wah-admin-wrap">
	<h1 class="wah-title"><span class="dashicons dashicons-rss"></span> WiFi Aggregator Hub - Dashboard</h1>

	<div class="wah-dashboard-hero">
		<h2>Mesin Indeks Aggregator Provider Internet Indonesia</h2>
		<p>Mengindeks secara real-time dari website provider & mitra tanpa meng-copy konten asli.</p>
	</div>

	<!-- Stats Grid -->
	<div class="wah-stats-grid">
		<div class="wah-stat-card primary">
			<div class="stat-icon"><span class="dashicons dashicons-rss"></span></div>
			<div class="stat-content">
				<h3><?php echo esc_html( number_format_i18n( $metrics['total_feeds'] ) ); ?></h3>
				<p>Total Feed Sources</p>
			</div>
		</div>

		<div class="wah-stat-card success">
			<div class="stat-icon"><span class="dashicons dashicons-admin-post"></span></div>
			<div class="stat-content">
				<h3><?php echo esc_html( number_format_i18n( $metrics['total_articles'] ) ); ?></h3>
				<p>Total Artikel Aktif</p>
			</div>
		</div>

		<div class="wah-stat-card info">
			<div class="stat-icon"><span class="dashicons dashicons-category"></span></div>
			<div class="stat-content">
				<h3><?php echo esc_html( number_format_i18n( $metrics['total_providers'] ) ); ?></h3>
				<p>Total Provider ISP</p>
			</div>
		</div>

		<div class="wah-stat-card warning">
			<div class="stat-icon"><span class="dashicons dashicons-location-alt"></span></div>
			<div class="stat-content">
				<h3><?php echo esc_html( number_format_i18n( $metrics['total_areas'] ) ); ?></h3>
				<p>Total Kota & Wilayah</p>
			</div>
		</div>

		<div class="wah-stat-card accent">
			<div class="stat-icon"><span class="dashicons dashicons-plus-alt"></span></div>
			<div class="stat-content">
				<h3><?php echo esc_html( number_format_i18n( $metrics['total_new_24h'] ) ); ?></h3>
				<p>Artikel Baru (24h)</p>
			</div>
		</div>

		<div class="wah-stat-card danger">
			<div class="stat-icon"><span class="dashicons dashicons-warning"></span></div>
			<div class="stat-content">
				<h3><?php echo esc_html( number_format_i18n( $metrics['total_duplicate'] ) ); ?></h3>
				<p>Artikel Duplikat</p>
			</div>
		</div>
	</div>

	<!-- Cron & Quick Controls -->
	<div class="wah-card-panel margin-top-20">
		<div class="wah-panel-header">
			<h3><span class="dashicons dashicons-update"></span> Status Sinkronisasi Engine</h3>
			<button id="wah-btn-sync-all" class="button button-primary button-hero">
				<span class="dashicons dashicons-update-alt"></span> Sinkronkan Semua Feed Sekarang
			</button>
		</div>
		<div class="wah-panel-body">
			<table class="widefat fixed striped">
				<tr>
					<td><strong>Sinkronisasi Terakhir:</strong></td>
					<td><?php echo esc_html( $metrics['last_synced'] ); ?></td>
				</tr>
				<tr>
					<td><strong>Status WP-Cron:</strong></td>
					<td><span class="wah-badge active"><?php echo esc_html( $cron_status ); ?></span></td>
				</tr>
				<tr>
					<td><strong>Broken Links / Noindex:</strong></td>
					<td><?php echo esc_html( number_format_i18n( $metrics['total_broken'] ) ); ?> Terdeteksi</td>
				</tr>
			</table>
			<div id="wah-sync-progress" class="wah-progress-bar-container hidden">
				<div class="wah-progress-bar"></div>
				<p class="wah-progress-text">Memproses sinkronisasi feed data...</p>
			</div>
		</div>
	<!-- Recent Indexed Articles Table -->
	<div class="wah-card-panel margin-top-20">
		<div class="wah-panel-header">
			<h3><span class="dashicons dashicons-admin-post"></span> Daftar Artikel Terindeks Terbaru (Top 20)</h3>
		</div>
		<table class="widefat fixed striped">
			<thead>
				<tr>
					<th>Judul Artikel & Link Asli</th>
					<th>Provider ISP</th>
					<th>Wilayah / Kota</th>
					<th>Domain Sumber</th>
					<th>Status</th>
					<th>Tanggal Index</th>
				</tr>
			</thead>
			<tbody>
				<?php
				$recent_articles = $db->get_articles( array( 'limit' => 20 ) );
				if ( empty( $recent_articles ) ) :
				?>
					<tr><td colspan="6">Belum ada artikel terindeks. Silakan tambahkan feed lalu klik Sync.</td></tr>
				<?php else : foreach ( $recent_articles as $art ) :
					$prov = $art['provider_id'] ? $db->get_provider( $art['provider_id'] ) : null;
					$area = $art['area_id'] ? $db->get_area( $art['area_id'] ) : null;
				?>
					<tr>
						<td>
							<strong><a href="<?php echo esc_url( $art['url'] ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $art['title'] ); ?> &rarr;</a></strong>
						</td>
						<td>
							<?php if ( $prov ) : ?>
								<span class="wah-badge info" style="background-color: <?php echo esc_attr( $prov['brand_color'] ); ?>15; color: <?php echo esc_attr( $prov['brand_color'] ); ?>; border: 1px solid <?php echo esc_attr( $prov['brand_color'] ); ?>;">
									<?php echo esc_html( $prov['name'] ); ?>
								</span>
							<?php else : ?>
								<span class="wah-badge disabled">Unassigned</span>
							<?php endif; ?>
						</td>
						<td>
							<?php if ( $area ) : ?>
								<span class="wah-badge active">📍 <?php echo esc_html( $area['name'] ); ?></span>
							<?php else : ?>
								<span class="wah-badge disabled">Umum / Semua Kota</span>
							<?php endif; ?>
						</td>
						<td><code><?php echo esc_html( $art['website_name'] ? $art['website_name'] : $art['domain'] ); ?></code></td>
						<td><span class="wah-badge <?php echo esc_attr( $art['status'] ); ?>"><?php echo esc_html( ucfirst( $art['status'] ) ); ?></span></td>
						<td><small><?php echo esc_html( date( 'd M Y H:i', strtotime( $art['created_at'] ) ) ); ?></small></td>
					</tr>
				<?php endforeach; endif; ?>
			</tbody>
		</table>
	</div>
</div>
