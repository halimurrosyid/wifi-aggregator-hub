<?php
/**
 * Admin Area Manager View.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$db      = WAH_DB::get_instance();
$message = '';

if ( isset( $_POST['wah_save_area'] ) && check_admin_referer( 'wah_area_action', 'wah_area_nonce' ) ) {
	$data = array(
		'name'          => sanitize_text_field( $_POST['name'] ),
		'type'          => sanitize_text_field( $_POST['type'] ?? 'city' ),
		'province_name' => sanitize_text_field( $_POST['province_name'] ?? '' ),
		'slug'          => sanitize_title( $_POST['slug'] ?? $_POST['name'] ),
		'aliases'       => sanitize_text_field( $_POST['aliases'] ?? '' ),
	);

	if ( ! empty( $_POST['area_id'] ) ) {
		$db->update_area( intval( $_POST['area_id'] ), $data );
		$message = 'Wilayah berhasil diperbarui.';
	} else {
		$db->insert_area( $data );
		$message = 'Wilayah baru berhasil ditambahkan.';
	}
}

if ( isset( $_GET['action'] ) && 'delete' === $_GET['action'] && ! empty( $_GET['id'] ) && check_admin_referer( 'wah_delete_area' ) ) {
	$db->delete_area( intval( $_GET['id'] ) );
	$message = 'Wilayah berhasil dihapus.';
}

$search_keyword = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '';
if ( ! empty( $search_keyword ) ) {
	$areas = $db->search_areas( $search_keyword, 50 );
} else {
	$areas = $db->get_areas();
}
?>
<div class="wrap wah-admin-wrap">
	<h1 class="wah-title"><span class="dashicons dashicons-location-alt"></span> Area Manager (Wilayah Indonesia)</h1>

	<?php if ( $message ) : ?>
		<div class="notice notice-success is-dismissible"><p><?php echo esc_html( $message ); ?></p></div>
	<?php endif; ?>

	<div class="wah-grid-layout">
		<!-- Form -->
		<div class="wah-card-panel">
			<h3><span class="dashicons dashicons-plus-alt"></span> Tambah Wilayah / Kota</h3>
			<form method="post" action="">
				<?php wp_nonce_field( 'wah_area_action', 'wah_area_nonce' ); ?>
				<input type="hidden" name="area_id" id="area_id" value="" />

				<div class="wah-form-group">
					<label>Nama Wilayah / Kota:</label>
					<input type="text" name="name" id="name" class="regular-text" required placeholder="Contoh: Kabupaten Garut" />
				</div>

				<div class="wah-form-group">
					<label>Jenis Wilayah:</label>
					<select name="type" id="type">
						<option value="city">Kota</option>
						<option value="regency">Kabupaten</option>
						<option value="province">Provinsi</option>
					</select>
				</div>

				<div class="wah-form-group">
					<label>Nama Provinsi:</label>
					<input type="text" name="province_name" id="province_name" class="regular-text" placeholder="Jawa Barat" />
				</div>

				<div class="wah-form-group">
					<label>Slug URL Landing Page:</label>
					<input type="text" name="slug" id="slug" class="regular-text" placeholder="garut" />
				</div>

				<div class="wah-form-group">
					<label>Alias Detection (Dipisahkan Koma):</label>
					<input type="text" name="aliases" id="aliases" class="regular-text" placeholder="Garut, Kab Garut, Kabupaten Garut" />
				</div>

				<button type="submit" name="wah_save_area" class="button button-primary">Simpan Wilayah</button>
			</form>
		</div>

		<!-- Table -->
		<div class="wah-card-panel">
			<div class="wah-panel-header">
				<h3>Daftar Wilayah (Total: <?php echo count( $areas ); ?>)</h3>
				<form method="get" action="" class="wah-search-inline">
					<input type="hidden" name="page" value="wah-areas" />
					<input type="search" name="s" value="<?php echo esc_attr( $search_keyword ); ?>" placeholder="Cari kota / kabupaten..." />
					<button type="submit" class="button">Cari</button>
				</form>
			</div>

			<table class="widefat fixed striped">
				<thead>
					<tr>
						<th>Nama Wilayah</th>
						<th>Jenis</th>
						<th>Provinsi</th>
						<th>Landing URL</th>
						<th>Aksi</th>
					</tr>
				</thead>
				<tbody>
					<?php if ( empty( $areas ) ) : ?>
						<tr><td colspan="5">Belum ada data wilayah.</td></tr>
					<?php else : foreach ( $areas as $area ) : ?>
						<tr>
							<td><strong><?php echo esc_html( $area['name'] ); ?></strong></td>
							<td><span class="wah-badge info"><?php echo esc_html( ucfirst( $area['type'] ) ); ?></span></td>
							<td><?php echo esc_html( $area['province_name'] ); ?></td>
							<td>
								<a href="<?php echo esc_url( home_url( '/wifi-' . $area['slug'] . '/' ) ); ?>" target="_blank">
									<code>/wifi-<?php echo esc_html( $area['slug'] ); ?>/</code>
								</a>
							</td>
							<td>
								<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'action' => 'delete', 'id' => $area['id'] ) ), 'wah_delete_area' ) ); ?>" class="button button-link-delete" onclick="return confirm('Hapus area ini?');">Hapus</a>
							</td>
						</tr>
					<?php endforeach; endif; ?>
				</tbody>
			</table>
		</div>
	</div>
</div>
