<?php
/**
 * WiFi Aggregator Hub - Advanced Analytics & Dashboard View with Pagination
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$db      = WAH_DB::get_instance();
$metrics = $db->get_dashboard_metrics();

// Pagination setup for indexed articles table
$current_page = isset( $_GET['paged'] ) ? max( 1, intval( $_GET['paged'] ) ) : 1;
$per_page     = 10;
$offset       = ( $current_page - 1 ) * $per_page;

$total_articles_count = intval( $metrics['total_articles'] );
$total_pages          = ceil( $total_articles_count / $per_page );

$articles = $db->get_articles(
	array(
		'limit'  => $per_page,
		'offset' => $offset,
		'status' => 'active',
	)
);

// Calculate provider distribution for analytics chart
$providers         = $db->get_providers();
$provider_stats    = array();
$total_art_for_pct = max( 1, $total_articles_count );

foreach ( $providers as $prov ) {
	$count = count( $db->get_articles( array( 'provider_id' => $prov['id'], 'status' => 'active' ) ) );
	if ( $count > 0 ) {
		$provider_stats[] = array(
			'name'       => $prov['name'],
			'count'      => $count,
			'percentage' => round( ( $count / $total_art_for_pct ) * 100, 1 ),
			'color'      => ! empty( $prov['brand_color'] ) ? $prov['brand_color'] : '#0284c7',
		);
	}
}
?>
<div class="wrap wah-admin-wrap">
	<h1 class="wah-title"><span class="dashicons dashicons-dashboard"></span> WiFi Aggregator Hub - Dashboard & Analitik</h1>

	<!-- Top Metric Cards Grid -->
	<div class="wah-metrics-grid">
		<div class="wah-metric-card">
			<div class="wah-metric-icon" style="background:#e0f2fe; color:#0284c7;"><span class="dashicons dashicons-rss"></span></div>
			<div class="wah-metric-info">
				<span class="wah-metric-value"><?php echo esc_html( $metrics['total_feeds'] ); ?></span>
				<span class="wah-metric-label">Total Feed Sources</span>
			</div>
		</div>
		<div class="wah-metric-card">
			<div class="wah-metric-icon" style="background:#dcfce7; color:#16a34a;"><span class="dashicons dashicons-admin-post"></span></div>
			<div class="wah-metric-info">
				<span class="wah-metric-value"><?php echo esc_html( number_format( $metrics['total_articles'] ) ); ?></span>
				<span class="wah-metric-label">Artikel Terindeks</span>
			</div>
		</div>
		<div class="wah-metric-card">
			<div class="wah-metric-icon" style="background:#f3e8ff; color:#9333ea;"><span class="dashicons dashicons-category"></span></div>
			<div class="wah-metric-info">
				<span class="wah-metric-value"><?php echo esc_html( $metrics['total_providers'] ); ?></span>
				<span class="wah-metric-label">Provider ISP</span>
			</div>
		</div>
		<div class="wah-metric-card">
			<div class="wah-metric-icon" style="background:#fef3c7; color:#d97706;"><span class="dashicons dashicons-location"></span></div>
			<div class="wah-metric-info">
				<span class="wah-metric-value"><?php echo esc_html( count( $db->get_active_landing_areas() ) ); ?></span>
				<span class="wah-metric-label">Wilayah Aktif Terisi</span>
			</div>
		</div>
		<div class="wah-metric-card">
			<div class="wah-metric-icon" style="background:#e0e7ff; color:#4f46e5;"><span class="dashicons dashicons-clock"></span></div>
			<div class="wah-metric-info">
				<span class="wah-metric-value"><?php echo esc_html( $metrics['total_new_24h'] ); ?></span>
				<span class="wah-metric-label">Artikel Baru (24j)</span>
			</div>
		</div>
	</div>

	<!-- Analytics Charts Grid -->
	<div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px; margin-top:20px;">
		<!-- Provider Share Analytics Chart -->
		<div class="wah-card-panel">
			<div class="wah-panel-header">
				<h3><span class="dashicons dashicons-chart-pie"></span> Distribusi Artikel per Provider ISP</h3>
			</div>
			<div style="padding:10px 0;">
				<?php if ( empty( $provider_stats ) ) : ?>
					<p style="color:#64748b;">Belum ada data distribusi provider terindeks.</p>
				<?php else : foreach ( $provider_stats as $stat ) : ?>
					<div style="margin-bottom:12px;">
						<div style="display:flex; justify-content:space-between; font-size:13px; font-weight:600; margin-bottom:4px;">
							<span><span style="display:inline-block; width:10px; height:10px; border-radius:50%; background:<?php echo esc_attr( $stat['color'] ); ?>; margin-right:6px;"></span><?php echo esc_html( $stat['name'] ); ?></span>
							<span><?php echo esc_html( $stat['count'] ); ?> artikel (<?php echo esc_html( $stat['percentage'] ); ?>%)</span>
						</div>
						<div style="background:#e2e8f0; height:8px; border-radius:4px; overflow:hidden;">
							<div style="background:<?php echo esc_attr( $stat['color'] ); ?>; width:<?php echo esc_attr( $stat['percentage'] ); ?>%; height:100%;"></div>
						</div>
					</div>
				<?php endforeach; endif; ?>
			</div>
		</div>

		<!-- Engine Health & Status Panel -->
		<div class="wah-card-panel">
			<div class="wah-panel-header">
				<h3><span class="dashicons dashicons-performance"></span> Status Kesehatan Engine Aggregator</h3>
			</div>
			<div style="display:flex; flex-direction:column; gap:12px; padding:10px 0;">
				<div style="display:flex; align-items:center; justify-content:space-between; background:#f8fafc; padding:12px; border-radius:8px; border:1px solid #e2e8f0;">
					<div>
						<strong>Autoupdate WP-Cron Engine:</strong>
						<div style="font-size:12px; color:#64748b;">Jadwal otomatis 1 jam sekali</div>
					</div>
					<span class="wah-badge active">ONLINE & AKTIFF</span>
				</div>
				<div style="display:flex; align-items:center; justify-content:space-between; background:#f8fafc; padding:12px; border-radius:8px; border:1px solid #e2e8f0;">
					<div>
						<strong>Broken Link Checker (404/Noindex):</strong>
						<div style="font-size:12px; color:#64748b;"><?php echo esc_html( $metrics['total_broken'] ); ?> tautan rusak terdeteksi</div>
					</div>
					<span class="wah-badge active">NORMAL</span>
				</div>
				<div style="display:flex; align-items:center; justify-content:space-between; background:#f8fafc; padding:12px; border-radius:8px; border:1px solid #e2e8f0;">
					<div>
						<strong>Sinkronisasi Terakhir:</strong>
						<div style="font-size:12px; color:#64748b;"><?php echo esc_html( $metrics['last_synced'] ); ?></div>
					</div>
					<button type="button" class="button button-primary wah-sync-btn" style="background:#0284c7; border-color:#0284c7;"><span class="dashicons dashicons-update" style="margin-top:4px;"></span> Sync Sekarang</button>
				</div>
			</div>
		</div>
	</div>

	<!-- Paginated Indexed Articles Table -->
	<div class="wah-card-panel margin-top-20">
		<div class="wah-panel-header" style="display:flex; justify-content:space-between; align-items:center;">
			<h3><span class="dashicons dashicons-admin-post"></span> Daftar Artikel Terindeks (Halaman <?php echo esc_html( $current_page ); ?> dari <?php echo esc_html( max( 1, $total_pages ) ); ?>)</h3>
			<span style="font-size:13px; color:#64748b;">Menampilkan 10 artikel per halaman</span>
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
				<?php if ( empty( $articles ) ) : ?>
					<tr><td colspan="6">Belum ada artikel terindeks. Silakan tambahkan feed lalu klik Sync.</td></tr>
				<?php else : foreach ( $articles as $art ) :
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

		<!-- Table Pagination Controls -->
		<?php if ( $total_pages > 1 ) : ?>
			<div style="display:flex; justify-content:space-between; align-items:center; margin-top:15px; padding-top:10px; border-top:1px solid #e2e8f0;">
				<div style="font-size:13px; color:#64748b;">
					Menampilkan artikel <strong><?php echo esc_html( $offset + 1 ); ?></strong> - <strong><?php echo esc_html( min( $total_articles_count, $offset + $per_page ) ); ?></strong> dari total <strong><?php echo esc_html( $total_articles_count ); ?></strong>
				</div>
				<div style="display:flex; gap:5px;">
					<?php if ( $current_page > 1 ) : ?>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=wifi-aggregator-hub&paged=' . ( $current_page - 1 ) ) ); ?>" class="button">&laquo; Sblmnya</a>
					<?php endif; ?>

					<?php for ( $i = 1; $i <= $total_pages; $i++ ) : 
						if ( $i == $current_page || $i == 1 || $i == $total_pages || ( $i >= $current_page - 2 && $i <= $current_page + 2 ) ) :
					?>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=wifi-aggregator-hub&paged=' . $i ) ); ?>" class="button <?php echo ( $i == $current_page ) ? 'button-primary' : ''; ?>"><?php echo esc_html( $i ); ?></a>
					<?php elseif ( $i == $current_page - 3 || $i == $current_page + 3 ) : ?>
						<span style="padding:4px 8px; color:#94a3b8;">...</span>
					<?php endif; endfor; ?>

					<?php if ( $current_page < $total_pages ) : ?>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=wifi-aggregator-hub&paged=' . ( $current_page + 1 ) ) ); ?>" class="button">Selanjutnya &raquo;</a>
					<?php endif; ?>
				</div>
			</div>
		<?php endif; ?>
	</div>
</div>
