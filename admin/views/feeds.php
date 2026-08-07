<?php
/**
 * Admin Feed Sources View.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$db    = WAH_DB::get_instance();
$message = '';

// Handle POST actions
if ( isset( $_POST['wah_save_feed'] ) && check_admin_referer( 'wah_feed_action', 'wah_feed_nonce' ) ) {
	$data = array(
		'website_name' => sanitize_text_field( $_POST['website_name'] ),
		'feed_url'     => esc_url_raw( $_POST['feed_url'] ),
		'sitemap_url'  => esc_url_raw( $_POST['sitemap_url'] ?? '' ),
		'priority'     => intval( $_POST['priority'] ?? 10 ),
		'status'       => sanitize_text_field( $_POST['status'] ?? 'active' ),
	);

	if ( ! empty( $_POST['feed_id'] ) ) {
		$db->update_feed( intval( $_POST['feed_id'] ), $data );
		$message = 'Feed berhasil diperbarui.';
	} else {
		$db->insert_feed( $data );
		$message = 'Feed baru berhasil ditambahkan.';
	}
}

if ( isset( $_GET['action'] ) && 'delete' === $_GET['action'] && ! empty( $_GET['id'] ) && check_admin_referer( 'wah_delete_feed' ) ) {
	$db->delete_feed( intval( $_GET['id'] ) );
	$message = 'Feed berhasil dihapus.';
}

$feeds = $db->get_feeds();
?>
<div class="wrap wah-admin-wrap">
	<h1 class="wah-title"><span class="dashicons dashicons-rss"></span> Kelola Feed Sources</h1>

	<?php if ( $message ) : ?>
		<div class="notice notice-success is-dismissible"><p><?php echo esc_html( $message ); ?></p></div>
	<?php endif; ?>

	<div class="wah-grid-layout">
		<!-- Form Column -->
		<div class="wah-card-panel">
			<h3><span class="dashicons dashicons-plus-alt"></span> Tambah / Edit Feed</h3>
			<form method="post" action="">
				<?php wp_nonce_field( 'wah_feed_action', 'wah_feed_nonce' ); ?>
				<input type="hidden" name="feed_id" id="feed_id" value="" />

				<div class="wah-form-group">
					<label>Nama Website Provider / Mitra:</label>
					<input type="text" name="website_name" id="website_name" class="regular-text" required placeholder="Contoh: Iconnet Official" />
				</div>

				<div class="wah-form-group">
					<label>URL Feed RSS/Atom:</label>
					<input type="url" name="feed_url" id="feed_url" class="regular-text" required placeholder="https://iconnet.biz.id/feed" />
				</div>

				<div class="wah-form-group">
					<label>URL Sitemap XML (Fallback):</label>
					<input type="url" name="sitemap_url" id="sitemap_url" class="regular-text" placeholder="https://iconnet.biz.id/sitemap.xml" />
				</div>

				<div class="wah-form-group">
					<label>Prioritas Domain (Skala 1 - 100):</label>
					<input type="number" name="priority" id="priority" value="10" min="1" max="100" class="small-text" />
					<p class="description">Prioritas lebih rendah = diutamakan saat deduplikasi.</p>
				</div>

				<div class="wah-form-group">
					<label>Status:</label>
					<select name="status" id="status">
						<option value="active">Aktif</option>
						<option value="disabled">Non-Aktif</option>
					</select>
				</div>

				<button type="submit" name="wah_save_feed" class="button button-primary">Simpan Feed Source</button>
			</form>
		</div>

		<!-- Table Column -->
		<div class="wah-card-panel">
			<h3>Daftar Feed Aggregator</h3>
			<table class="widefat fixed striped">
				<thead>
					<tr>
						<th>Website Name</th>
						<th>Feed / Sitemap URL</th>
						<th>Prioritas</th>
						<th>Status</th>
						<th>Terakhir Sync</th>
						<th>Aksi</th>
					</tr>
				</thead>
				<tbody>
					<?php if ( empty( $feeds ) ) : ?>
						<tr><td colspan="6">Belum ada feed source ditambahkan.</td></tr>
					<?php else : foreach ( $feeds as $feed ) : ?>
						<tr>
							<td><strong><?php echo esc_html( $feed['website_name'] ); ?></strong></td>
							<td>
								<small><a href="<?php echo esc_url( $feed['feed_url'] ); ?>" target="_blank"><?php echo esc_html( $feed['feed_url'] ); ?></a></small>
								<?php if ( ! empty( $feed['error_message'] ) ) : ?>
									<div class="wah-error-badge"><?php echo esc_html( $feed['error_message'] ); ?></div>
								<?php endif; ?>
							</td>
							<td><?php echo esc_html( $feed['priority'] ); ?></td>
							<td><span class="wah-badge <?php echo esc_attr( $feed['status'] ); ?>"><?php echo esc_html( ucfirst( $feed['status'] ) ); ?></span></td>
							<td><?php echo esc_html( $feed['last_synced'] ? $feed['last_synced'] : '-' ); ?></td>
							<td>
								<button class="button button-secondary wah-btn-sync-single" data-id="<?php echo esc_attr( $feed['id'] ); ?>">Sync</button>
								<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'action' => 'delete', 'id' => $feed['id'] ) ), 'wah_delete_feed' ) ); ?>" class="button button-link-delete" onclick="return confirm('Hapus feed ini?');">Hapus</a>
							</td>
						</tr>
					<?php endforeach; endif; ?>
				</tbody>
			</table>
		</div>
	</div>
</div>
