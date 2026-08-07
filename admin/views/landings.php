<?php
/**
 * Admin Landing Pages Manager View.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$db    = WAH_DB::get_instance();
$areas = $db->get_areas();
?>
<div class="wrap wah-admin-wrap">
	<h1 class="wah-title"><span class="dashicons dashicons-admin-multisite"></span> SEO Landing Pages Otomatis</h1>

	<div class="wah-card-panel">
		<h3>Daftar Landing Page Area Berjalan</h3>
		<p>Setiap wilayah yang memiliki artikel terindeks secara otomatis membentuk Halaman SEO khusus.</p>

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
					<tr><td colspan="4">Belum ada landing page area yang dibuat.</td></tr>
				<?php else : foreach ( $areas as $area ) :
					$count = count( $db->get_articles( array( 'area_id' => $area['id'], 'status' => 'active' ) ) );
					$url   = home_url( '/wifi-' . $area['slug'] . '/' );
				?>
					<tr>
						<td><strong><?php echo esc_html( $area['name'] ); ?></strong></td>
						<td><code>/wifi-<?php echo esc_html( $area['slug'] ); ?>/</code></td>
						<td><span class="wah-badge info"><?php echo esc_html( $count ); ?> Provider / Artikel</span></td>
						<td><a href="<?php echo esc_url( $url ); ?>" target="_blank" class="button button-small button-secondary">Buka Halaman &rarr;</a></td>
					</tr>
				<?php endforeach; endif; ?>
			</tbody>
		</table>
	</div>
</div>
