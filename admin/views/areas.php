<?php
/**
 * WiFi Aggregator Hub - Area Manager View with 10-item Pagination
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$db = WAH_DB::get_instance();

// Handle Form Submission
if ( isset( $_POST['wah_save_area'] ) && check_admin_referer( 'wah_save_area_nonce' ) ) {
	$area_id = isset( $_POST['area_id'] ) ? intval( $_POST['area_id'] ) : 0;
	$data    = array(
		'name'          => sanitize_text_field( $_POST['name'] ),
		'type'          => sanitize_text_field( $_POST['type'] ),
		'province_name' => sanitize_text_field( $_POST['province_name'] ),
		'slug'          => sanitize_title( $_POST['slug'] ),
		'aliases'       => sanitize_textarea_field( $_POST['aliases'] ),
	);

	if ( $area_id > 0 ) {
		$db->update_area( $area_id, $data );
		echo '<div class="notice notice-success is-dismissible"><p>Data wilayah berhasil diperbarui!</p></div>';
	} else {
		$db->insert_area( $data );
		echo '<div class="notice notice-success is-dismissible"><p>Wilayah baru berhasil ditambahkan!</p></div>';
	}
}

// Handle Delete Action
if ( isset( $_GET['action'] ) && 'delete' === $_GET['action'] && isset( $_GET['id'] ) && check_admin_referer( 'wah_delete_area' ) ) {
	$db->delete_area( intval( $_GET['id'] ) );
	echo '<div class="notice notice-success is-dismissible"><p>Wilayah berhasil dihapus!</p></div>';
}

$edit_area = null;
if ( isset( $_GET['action'] ) && 'edit' === $_GET['action'] && isset( $_GET['id'] ) ) {
	$edit_area = $db->get_area( intval( $_GET['id'] ) );
}

$all_areas   = $db->get_areas();
$current_page = isset( $_GET['paged'] ) ? max( 1, intval( $_GET['paged'] ) ) : 1;
$per_page     = 10;
$total_items  = count( $all_areas );
$total_pages  = ceil( $total_items / $per_page );
$offset       = ( $current_page - 1 ) * $per_page;

$areas = array_slice( $all_areas, $offset, $per_page );
?>
<div class="wrap wah-admin-wrap">
	<h1 class="wah-title"><span class="dashicons dashicons-location"></span> Kelola Wilayah & Target Area</h1>

	<div class="wah-grid-layout" style="display:grid; grid-template-columns: 380px 1fr; gap: 20px;">
		<!-- Form Left Panel -->
		<div class="wah-card-panel">
			<h3><?php echo $edit_area ? 'Edit Wilayah' : 'Tambah Wilayah Baru'; ?></h3>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=wah-areas' ) ); ?>">
				<?php wp_nonce_field( 'wah_save_area_nonce' ); ?>
				<input type="hidden" name="area_id" value="<?php echo $edit_area ? esc_attr( $edit_area['id'] ) : 0; ?>">

				<p>
					<label><strong>Nama Wilayah / Kota:</strong></label><br>
					<input type="text" name="name" class="regular-text" style="width:100% !important; max-width:100% !important; box-sizing:border-box !important;" required placeholder="Contoh: Kota Bandung" value="<?php echo $edit_area ? esc_attr( $edit_area['name'] ) : ''; ?>">
				</p>

				<p>
					<label><strong>Tipe Wilayah:</strong></label><br>
					<select name="type" style="width:100%;">
						<option value="city" <?php selected( $edit_area ? $edit_area['type'] : '', 'city' ); ?>>Kota</option>
						<option value="regency" <?php selected( $edit_area ? $edit_area['type'] : '', 'regency' ); ?>>Kabupaten</option>
						<option value="district" <?php selected( $edit_area ? $edit_area['type'] : '', 'district' ); ?>>Kecamatan</option>
						<option value="province" <?php selected( $edit_area ? $edit_area['type'] : '', 'province' ); ?>>Provinsi</option>
					</select>
				</p>

				<p>
					<label><strong>Nama Provinsi:</strong></label><br>
					<input type="text" name="province_name" class="regular-text" style="width:100% !important; max-width:100% !important; box-sizing:border-box !important;" placeholder="Contoh: Jawa Barat" value="<?php echo $edit_area ? esc_attr( $edit_area['province_name'] ) : 'Indonesia'; ?>">
				</p>

				<p>
					<label><strong>Slug URL:</strong></label><br>
					<input type="text" name="slug" class="regular-text" style="width:100% !important; max-width:100% !important; box-sizing:border-box !important;" placeholder="Contoh: bandung" value="<?php echo $edit_area ? esc_attr( $edit_area['slug'] ) : ''; ?>">
				</p>

				<p>
					<label><strong>Alias / Kata Kunci Deteksi (Pisahkan Koma):</strong></label><br>
					<textarea name="aliases" rows="3" class="large-text" style="width:100% !important; max-width:100% !important; box-sizing:border-box !important;" placeholder="Contoh: Bandung, Kota Bandung, Bandung City"><?php echo $edit_area ? esc_textarea( $edit_area['aliases'] ) : ''; ?></textarea>
				</p>

				<p>
					<button type="submit" name="wah_save_area" class="button button-primary" style="width:100%; height:40px;">Simpan Wilayah</button>
				</p>
			</form>
		</div>

		<!-- Table Right Panel -->
		<div class="wah-card-panel">
			<div class="wah-panel-header" style="display:flex; justify-content:space-between; align-items:center;">
				<h3>Daftar Wilayah (Halaman <?php echo esc_html( $current_page ); ?> dari <?php echo esc_html( max( 1, $total_pages ) ); ?>)</h3>
				<span class="wah-badge info">Total: <?php echo esc_html( $total_items ); ?> Wilayah</span>
			</div>
			<table class="widefat fixed striped">
				<thead>
					<tr>
						<th>Wilayah</th>
						<th>Tipe</th>
						<th>Slug URL</th>
						<th>Aksi</th>
					</tr>
				</thead>
				<tbody>
					<?php if ( empty( $areas ) ) : ?>
						<tr><td colspan="4">Belum ada data wilayah.</td></tr>
					<?php else : foreach ( $areas as $area ) : ?>
						<tr>
							<td><strong>📍 <?php echo esc_html( $area['name'] ); ?></strong></td>
							<td><span class="wah-badge active"><?php echo esc_html( ucfirst( $area['type'] ) ); ?></span></td>
							<td><code>/wifi-<?php echo esc_html( $area['slug'] ); ?>/</code></td>
							<td>
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=wah-areas&action=edit&id=' . $area['id'] ) ); ?>" class="button button-small">Edit</a>
								<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=wah-areas&action=delete&id=' . $area['id'] ), 'wah_delete_area' ) ); ?>" class="button button-small button-link-delete" onclick="return confirm('Hapus wilayah ini?');">Hapus</a>
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
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=wah-areas&paged=' . ( $current_page - 1 ) ) ); ?>" class="button">&laquo; Sblmnya</a>
						<?php endif; ?>

						<?php for ( $i = 1; $i <= $total_pages; $i++ ) : 
							if ( $i == $current_page || $i == 1 || $i == $total_pages || ( $i >= $current_page - 2 && $i <= $current_page + 2 ) ) :
						?>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=wah-areas&paged=' . $i ) ); ?>" class="button <?php echo ( $i == $current_page ) ? 'button-primary' : ''; ?>"><?php echo esc_html( $i ); ?></a>
						<?php elseif ( $i == $current_page - 3 || $i == $current_page + 3 ) : ?>
							<span style="padding:4px 8px; color:#94a3b8;">...</span>
						<?php endif; endfor; ?>

						<?php if ( $current_page < $total_pages ) : ?>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=wah-areas&paged=' . ( $current_page + 1 ) ) ); ?>" class="button">Selanjutnya &raquo;</a>
						<?php endif; ?>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>
