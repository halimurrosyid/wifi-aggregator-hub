<?php
/**
 * Admin Duplicate Manager View.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$db      = WAH_DB::get_instance();
$message = '';

if ( isset( $_POST['wah_save_dedup'] ) && check_admin_referer( 'wah_dedup_settings', 'wah_dedup_nonce' ) ) {
	$strategy = sanitize_text_field( $_POST['dedup_strategy'] );
	update_option( 'wah_dedup_strategy', $strategy );

	// Run manual deduplication pass
	$res     = WAH_Deduplicator::run();
	$message = "Strategi disimpan. Pemasangan deduplication ulang selesai ({$res['duplicates']} duplikat ditemukan).";
}

$current_strategy = get_option( 'wah_dedup_strategy', 'domain_priority' );
$duplicates       = $db->get_articles( array( 'status' => 'duplicate', 'limit' => 50 ) );
?>
<div class="wrap wah-admin-wrap">
	<h1 class="wah-title"><span class="dashicons dashicons-warning"></span> Duplicate Manager</h1>

	<?php if ( $message ) : ?>
		<div class="notice notice-success is-dismissible"><p><?php echo esc_html( $message ); ?></p></div>
	<?php endif; ?>

	<div class="wah-card-panel">
		<h3>Strategi Penanganan Artikel Duplikat</h3>
		<p>Jika terdapat lebih dari satu artikel dari provider & kota yang sama, sistem hanya menampilkan SATU artikel utama untuk menghindari duplicate content.</p>

		<form method="post" action="">
			<?php wp_nonce_field( 'wah_dedup_settings', 'wah_dedup_nonce' ); ?>
			<table class="form-table">
				<tr>
					<th scope="row"><label for="dedup_strategy">Metode Pemilihan Artikel Utama:</label></th>
					<td>
						<select name="dedup_strategy" id="dedup_strategy">
							<option value="domain_priority" <?php selected( $current_strategy, 'domain_priority' ); ?>>Prioritas Domain Feed Source (Rekomendasi)</option>
							<option value="latest" <?php selected( $current_strategy, 'latest' ); ?>>Artikel Terbaru (Tanggal Publish/Update)</option>
							<option value="longest" <?php selected( $current_strategy, 'longest' ); ?>>Artikel Terpanjang (Jumlah Kata / Excerpt)</option>
							<option value="manual" <?php selected( $current_strategy, 'manual' ); ?>>Manual (Pilihan Admin)</option>
						</select>
					</td>
				</tr>
			</table>
			<button type="submit" name="wah_save_dedup" class="button button-primary">Simpan & Jalankan Deduplikasi</button>
		</form>
	</div>

	<div class="wah-card-panel margin-top-20">
		<h3>Daftar Artikel Yang Ditandai Sebagai Duplikat (Sembunyi)</h3>
		<table class="widefat fixed striped">
			<thead>
				<tr>
					<th>Judul Artikel</th>
					<th>Domain Asli</th>
					<th>Provider / Wilayah</th>
					<th>Tanggal Index</th>
					<th>Status</th>
				</tr>
			</thead>
			<tbody>
				<?php if ( empty( $duplicates ) ) : ?>
					<tr><td colspan="5">Tidak ada artikel duplikat terdeteksi. Semua unik!</td></tr>
				<?php else : foreach ( $duplicates as $art ) : ?>
					<tr>
						<td><strong><a href="<?php echo esc_url( $art['url'] ); ?>" target="_blank"><?php echo esc_html( $art['title'] ); ?></a></strong></td>
						<td><code><?php echo esc_html( $art['domain'] ); ?></code></td>
						<td>
							<?php
							$prov = $art['provider_id'] ? $db->get_provider( $art['provider_id'] ) : null;
							$area = $art['area_id'] ? $db->get_area( $art['area_id'] ) : null;
							echo esc_html( ( $prov ? $prov['name'] : 'N/A' ) . ' - ' . ( $area ? $area['name'] : 'N/A' ) );
							?>
						</td>
						<td><?php echo esc_html( $art['created_at'] ); ?></td>
						<td><span class="wah-badge warning">Duplicate</span></td>
					</tr>
				<?php endforeach; endif; ?>
			</tbody>
		</table>
	</div>
</div>
