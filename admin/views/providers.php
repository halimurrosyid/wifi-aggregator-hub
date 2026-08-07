<?php
/**
 * Admin Provider Manager View with Article Count Statistics & Pagination.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$db      = WAH_DB::get_instance();
$message = '';

if ( isset( $_POST['wah_save_provider'] ) && check_admin_referer( 'wah_provider_action', 'wah_provider_nonce' ) ) {
	$data = array(
		'name'          => sanitize_text_field( $_POST['name'] ),
		'slug'          => sanitize_title( $_POST['slug'] ?? $_POST['name'] ),
		'aliases'       => sanitize_text_field( $_POST['aliases'] ?? '' ),
		'logo_url'      => esc_url_raw( $_POST['logo_url'] ?? '' ),
		'brand_color'   => sanitize_hex_color( $_POST['brand_color'] ?? '#00a896' ),
		'display_order' => intval( $_POST['display_order'] ?? 0 ),
	);

	if ( ! empty( $_POST['provider_id'] ) ) {
		$db->update_provider( intval( $_POST['provider_id'] ), $data );
		$message = 'Provider ISP berhasil diperbarui.';
	} else {
		$db->insert_provider( $data );
		$message = 'Provider ISP baru berhasil ditambahkan.';
	}
}

if ( isset( $_GET['action'] ) && 'delete' === $_GET['action'] && ! empty( $_GET['id'] ) && check_admin_referer( 'wah_delete_provider' ) ) {
	$db->delete_provider( intval( $_GET['id'] ) );
	$message = 'Provider berhasil dihapus.';
}

$all_providers = $db->get_providers();

// Pagination setup
$current_page = isset( $_GET['paged'] ) ? max( 1, intval( $_GET['paged'] ) ) : 1;
$per_page     = 10;
$total_items  = count( $all_providers );
$total_pages  = ceil( $total_items / $per_page );
$offset       = ( $current_page - 1 ) * $per_page;

$providers = array_slice( $all_providers, $offset, $per_page );
?>
<div class="wrap wah-admin-wrap">
	<h1 class="wah-title"><span class="dashicons dashicons-category"></span> Provider Manager & Statistik Posting</h1>

	<?php if ( $message ) : ?>
		<div class="notice notice-success is-dismissible"><p><?php echo esc_html( $message ); ?></p></div>
	<?php endif; ?>

	<div class="wah-grid-layout" style="display:grid; grid-template-columns: 380px 1fr; gap:20px;">
		<!-- Form -->
		<div class="wah-card-panel">
			<h3><span class="dashicons dashicons-plus-alt"></span> Tambah / Edit Provider</h3>
			<form method="post" action="">
				<?php wp_nonce_field( 'wah_provider_action', 'wah_provider_nonce' ); ?>
				<input type="hidden" name="provider_id" id="provider_id" value="" />

				<div class="wah-form-group" style="margin-bottom:12px;">
					<label><strong>Nama Provider:</strong></label><br>
					<input type="text" name="name" id="name" class="regular-text" style="width:100% !important; max-width:100% !important;" required placeholder="Contoh: ICONNET" />
				</div>

				<div class="wah-form-group" style="margin-bottom:12px;">
					<label><strong>Slug URL:</strong></label><br>
					<input type="text" name="slug" id="slug" class="regular-text" style="width:100% !important; max-width:100% !important;" placeholder="iconnet" />
				</div>

				<div class="wah-form-group" style="margin-bottom:12px;">
					<label><strong>Alias / Kata Kunci (Dipisahkan Koma):</strong></label><br>
					<input type="text" name="aliases" id="aliases" class="regular-text" style="width:100% !important; max-width:100% !important;" placeholder="Iconnet, Icon Plus, PLN Icon Plus" />
					<p class="description">Digunakan untuk mendeteksi provider dari judul/isi artikel secara otomatis.</p>
				</div>

				<div class="wah-form-group" style="margin-bottom:12px;">
					<label><strong>URL Logo Brand:</strong></label><br>
					<input type="url" name="logo_url" id="logo_url" class="regular-text" style="width:100% !important; max-width:100% !important;" placeholder="https://example.com/logo-iconnet.png" />
				</div>

				<div class="wah-form-group" style="margin-bottom:12px;">
					<label><strong>Warna Brand (HEX):</strong></label><br>
					<input type="color" name="brand_color" id="brand_color" value="#00a896" />
				</div>

				<div class="wah-form-group" style="margin-bottom:15px;">
					<label><strong>Urutan Tampil:</strong></label><br>
					<input type="number" name="display_order" id="display_order" value="0" class="small-text" />
				</div>

				<button type="submit" name="wah_save_provider" class="button button-primary" style="width:100%; height:40px;">Simpan Provider</button>
			</form>
		</div>

		<!-- List -->
		<div class="wah-card-panel">
			<div class="wah-panel-header" style="display:flex; justify-content:space-between; align-items:center;">
				<h3>Daftar Provider Terdaftar (Halaman <?php echo esc_html( $current_page ); ?> dari <?php echo esc_html( max( 1, $total_pages ) ); ?>)</h3>
				<span class="wah-badge info">Total: <?php echo esc_html( $total_items ); ?> Provider</span>
			</div>
			<table class="widefat fixed striped">
				<thead>
					<tr>
						<th>Logo & Nama Provider</th>
						<th>Slug URL</th>
						<th>Jumlah Posting Terindeks</th>
						<th>Warna Brand</th>
						<th>Aksi</th>
					</tr>
				</thead>
				<tbody>
					<?php if ( empty( $providers ) ) : ?>
						<tr><td colspan="5">Belum ada provider terdaftar.</td></tr>
					<?php else : foreach ( $providers as $p ) :
						$art_count = count( $db->get_articles( array( 'provider_id' => $p['id'], 'status' => 'active' ) ) );
						$prov_url  = home_url( '/provider/' . $p['slug'] . '/' );
					?>
						<tr>
							<td>
								<span style="display:inline-block; width:12px; height:12px; border-radius:50%; background:<?php echo esc_attr( $p['brand_color'] ); ?>; margin-right:8px;"></span>
								<strong><?php echo esc_html( $p['name'] ); ?></strong>
							</td>
							<td><code>/provider/<?php echo esc_html( $p['slug'] ); ?>/</code></td>
							<td>
								<?php if ( $art_count > 0 ) : ?>
									<span class="wah-badge info" style="background:#e0f2fe; color:#0284c7; font-weight:700; border:1px solid #bae6fd;">
										<?php echo esc_html( number_format( $art_count ) ); ?> Artikel
									</span>
								<?php else : ?>
									<span class="wah-badge disabled">0 Artikel</span>
								<?php endif; ?>
							</td>
							<td><code><?php echo esc_html( $p['brand_color'] ); ?></code></td>
							<td>
								<a href="<?php echo esc_url( $prov_url ); ?>" target="_blank" class="button button-small">Buka Page &rarr;</a>
								<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=wah-providers&action=delete&id=' . $p['id'] ), 'wah_delete_provider' ) ); ?>" class="button button-small button-link-delete" onclick="return confirm('Hapus provider ini?');">Hapus</a>
							</td>
						</tr>
					<?php endforeach; endif; ?>
				</tbody>
			</table>

			<!-- Pagination Controls -->
			<?php if ( $total_pages > 1 ) : ?>
				<div style="display:flex; justify-content:space-between; align-items:center; margin-top:15px; padding-top:10px; border-top:1px solid #e2e8f0;">
					<div style="font-size:13px; color:#64748b;">
						Menampilkan <strong><?php echo esc_html( $offset + 1 ); ?></strong> - <strong><?php echo esc_html( min( $total_items, $offset + $per_page ) ); ?></strong> dari <strong><?php echo esc_html( $total_items ); ?></strong>
					</div>
					<div style="display:flex; gap:5px;">
						<?php if ( $current_page > 1 ) : ?>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=wah-providers&paged=' . ( $current_page - 1 ) ) ); ?>" class="button">&laquo; Sblmnya</a>
						<?php endif; ?>

						<?php for ( $i = 1; $i <= $total_pages; $i++ ) : 
							if ( $i == $current_page || $i == 1 || $i == $total_pages || ( $i >= $current_page - 2 && $i <= $current_page + 2 ) ) :
						?>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=wah-providers&paged=' . $i ) ); ?>" class="button <?php echo ( $i == $current_page ) ? 'button-primary' : ''; ?>"><?php echo esc_html( $i ); ?></a>
						<?php elseif ( $i == $current_page - 3 || $i == $current_page + 3 ) : ?>
							<span style="padding:4px 8px; color:#94a3b8;">...</span>
						<?php endif; endfor; ?>

						<?php if ( $current_page < $total_pages ) : ?>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=wah-providers&paged=' . ( $current_page + 1 ) ) ); ?>" class="button">Selanjutnya &raquo;</a>
						<?php endif; ?>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>
