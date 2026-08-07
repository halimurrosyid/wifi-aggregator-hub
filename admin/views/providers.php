<?php
/**
 * Admin Provider Manager View.
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

$providers = $db->get_providers();
?>
<div class="wrap wah-admin-wrap">
	<h1 class="wah-title"><span class="dashicons dashicons-category"></span> Provider Manager</h1>

	<?php if ( $message ) : ?>
		<div class="notice notice-success is-dismissible"><p><?php echo esc_html( $message ); ?></p></div>
	<?php endif; ?>

	<div class="wah-grid-layout">
		<!-- Form -->
		<div class="wah-card-panel">
			<h3><span class="dashicons dashicons-plus-alt"></span> Tambah / Edit Provider</h3>
			<form method="post" action="">
				<?php wp_nonce_field( 'wah_provider_action', 'wah_provider_nonce' ); ?>
				<input type="hidden" name="provider_id" id="provider_id" value="" />

				<div class="wah-form-group">
					<label>Nama Provider:</label>
					<input type="text" name="name" id="name" class="regular-text" required placeholder="Contoh: ICONNET" />
				</div>

				<div class="wah-form-group">
					<label>Slug URL:</label>
					<input type="text" name="slug" id="slug" class="regular-text" placeholder="iconnet" />
				</div>

				<div class="wah-form-group">
					<label>Alias / Kata Kunci (Dipisahkan Koma):</label>
					<input type="text" name="aliases" id="aliases" class="regular-text" placeholder="Iconnet, Icon Plus, PLN Icon Plus" />
					<p class="description">Digunakan untuk mendeteksi provider dari judul/isi artikel secara otomatis.</p>
				</div>

				<div class="wah-form-group">
					<label>URL Logo Brand:</label>
					<input type="url" name="logo_url" id="logo_url" class="regular-text" placeholder="https://example.com/logo-iconnet.png" />
				</div>

				<div class="wah-form-group">
					<label>Warna Brand (HEX):</label>
					<input type="color" name="brand_color" id="brand_color" value="#00a896" />
				</div>

				<div class="wah-form-group">
					<label>Urutan Tampil:</label>
					<input type="number" name="display_order" id="display_order" value="0" class="small-text" />
				</div>

				<button type="submit" name="wah_save_provider" class="button button-primary">Simpan Provider</button>
			</form>
		</div>

		<!-- List -->
		<div class="wah-card-panel">
			<h3>Daftar Provider Terdaftar</h3>
			<table class="widefat fixed striped">
				<thead>
					<tr>
						<th>Logo & Nama</th>
						<th>Slug</th>
						<th>Alias Detection</th>
						<th>Warna</th>
						<th>Aksi</th>
					</tr>
				</thead>
				<tbody>
					<?php if ( empty( $providers ) ) : ?>
						<tr><td colspan="5">Belum ada provider ISP terdaftar.</td></tr>
					<?php else : foreach ( $providers as $prov ) : ?>
						<tr>
							<td>
								<span class="wah-color-badge" style="background-color: <?php echo esc_attr( $prov['brand_color'] ); ?>;"></span>
								<strong><?php echo esc_html( $prov['name'] ); ?></strong>
							</td>
							<td><code><?php echo esc_html( $prov['slug'] ); ?></code></td>
							<td><small><?php echo esc_html( $prov['aliases'] ); ?></small></td>
							<td><code><?php echo esc_html( $prov['brand_color'] ); ?></code></td>
							<td>
								<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'action' => 'delete', 'id' => $prov['id'] ) ), 'wah_delete_provider' ) ); ?>" class="button button-link-delete" onclick="return confirm('Hapus provider ini?');">Hapus</a>
							</td>
						</tr>
					<?php endforeach; endif; ?>
				</tbody>
			</table>
		</div>
	</div>
</div>
