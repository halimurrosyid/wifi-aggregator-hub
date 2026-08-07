<?php
/**
 * WiFi Aggregator Hub - SEO Landing Pages View with 10-item Pagination
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$db    = WAH_DB::get_instance();
$all_active_areas = $db->get_active_landing_areas();

// Pagination setup
$current_page = isset( $_GET['paged'] ) ? max( 1, intval( $_GET['paged'] ) ) : 1;
$per_page     = 10;
$total_items  = count( $all_active_areas );
$total_pages  = ceil( $total_items / $per_page );
$offset       = ( $current_page - 1 ) * $per_page;

$areas = array_slice( $all_active_areas, $offset, $per_page );
?>
<div class="wrap wah-admin-wrap">
	<h1 class="wah-title"><span class="dashicons dashicons-admin-multisite"></span> SEO Landing Pages Otomatis</h1>

	<div class="wah-card-panel">
		<div class="wah-panel-header" style="display:flex; justify-content:space-between; align-items:center;">
			<div>
				<h3>Daftar Landing Page Area Berjalan (Halaman <?php echo esc_html( $current_page ); ?> dari <?php echo esc_html( max( 1, $total_pages ) ); ?>)</h3>
				<p style="margin:0; color:#64748b; font-size:13px;">Hanya wilayah yang memiliki artikel terindeks yang diaktifkan sebagai Halaman SEO.</p>
			</div>
			<span class="wah-badge active">Per Page View: 10 Items</span>
		</div>

		<table class="widefat fixed striped">
			<thead>
				<tr>
					<th>Nama Kota / Wilayah</th>
					<th>URL Halaman SEO</th>
					<th>Jumlah Artikel Terindeks</th>
					<th>Lihat Halaman</th>
				</tr>
			</thead>
			<tbody>
				<?php if ( empty( $areas ) ) : ?>
					<tr><td colspan="4">Belum ada wilayah yang memiliki artikel terindeks. Silakan tambahkan feed artikel spesifik kota.</td></tr>
				<?php else : foreach ( $areas as $area ) :
					$count = count( $db->get_articles( array( 'area_id' => $area['id'], 'status' => 'active' ) ) );
					$url   = home_url( '/wifi-' . $area['slug'] . '/' );
				?>
					<tr>
						<td><strong>📍 <?php echo esc_html( $area['name'] ); ?></strong></td>
						<td><code>/wifi-<?php echo esc_html( $area['slug'] ); ?>/</code></td>
						<td><span class="wah-badge info"><?php echo esc_html( $count ); ?> PROVIDER / ARTIKEL</span></td>
						<td><a href="<?php echo esc_url( $url ); ?>" target="_blank" class="button button-small">Buka Halaman &rarr;</a></td>
					</tr>
				<?php endforeach; endif; ?>
			</tbody>
		</table>

		<!-- Pagination Controls -->
		<?php if ( $total_pages > 1 ) : ?>
			<div style="display:flex; justify-content:space-between; align-items:center; margin-top:15px; padding-top:10px; border-top:1px solid #e2e8f0;">
				<div style="font-size:13px; color:#64748b;">
					Menampilkan wilayah <strong><?php echo esc_html( $offset + 1 ); ?></strong> - <strong><?php echo esc_html( min( $total_items, $offset + $per_page ) ); ?></strong> dari total <strong><?php echo esc_html( $total_items ); ?></strong>
				</div>
				<div style="display:flex; gap:5px;">
					<?php if ( $current_page > 1 ) : ?>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=wah-landings&paged=' . ( $current_page - 1 ) ) ); ?>" class="button">&laquo; Sblmnya</a>
					<?php endif; ?>

					<?php for ( $i = 1; $i <= $total_pages; $i++ ) : 
						if ( $i == $current_page || $i == 1 || $i == $total_pages || ( $i >= $current_page - 2 && $i <= $current_page + 2 ) ) :
					?>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=wah-landings&paged=' . $i ) ); ?>" class="button <?php echo ( $i == $current_page ) ? 'button-primary' : ''; ?>"><?php echo esc_html( $i ); ?></a>
					<?php elseif ( $i == $current_page - 3 || $i == $current_page + 3 ) : ?>
						<span style="padding:4px 8px; color:#94a3b8;">...</span>
					<?php endif; endfor; ?>

					<?php if ( $current_page < $total_pages ) : ?>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=wah-landings&paged=' . ( $current_page + 1 ) ) ); ?>" class="button">Selanjutnya &raquo;</a>
					<?php endif; ?>
				</div>
			</div>
		<?php endif; ?>
	</div>
</div>
