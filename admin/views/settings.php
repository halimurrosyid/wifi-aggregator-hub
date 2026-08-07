<?php
/**
 * Admin Settings View.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$message = '';
if ( isset( $_POST['wah_save_settings'] ) && check_admin_referer( 'wah_settings_action', 'wah_settings_nonce' ) ) {
	update_option( 'wah_default_wa_number', sanitize_text_field( $_POST['default_wa_number'] ?? '' ) );
	update_option( 'wah_default_cta_text', sanitize_text_field( $_POST['default_cta_text'] ?? 'Daftar Sekarang' ) );
	update_option( 'wah_auto_hide_broken', isset( $_POST['auto_hide_broken'] ) ? '1' : '0' );
	$message = 'Pengaturan umum berhasil disimpan.';
}

$wa_num           = get_option( 'wah_default_wa_number', '' );
$cta_text         = get_option( 'wah_default_cta_text', 'Daftar Sekarang' );
$auto_hide_broken = get_option( 'wah_auto_hide_broken', '1' );
?>
<div class="wrap wah-admin-wrap">
	<h1 class="wah-title"><span class="dashicons dashicons-admin-generic"></span> General Settings</h1>

	<?php if ( $message ) : ?>
		<div class="notice notice-success is-dismissible"><p><?php echo esc_html( $message ); ?></p></div>
	<?php endif; ?>

	<div class="wah-card-panel">
		<h3>Pengaturan Umum Aggregator</h3>
		<form method="post" action="">
			<?php wp_nonce_field( 'wah_settings_action', 'wah_settings_nonce' ); ?>
			<table class="form-table">
				<tr>
					<th scope="row"><label for="default_wa_number">Nomor WhatsApp Direct Sales (Default):</label></th>
					<td>
						<input type="text" name="default_wa_number" id="default_wa_number" value="<?php echo esc_attr( $wa_num ); ?>" class="regular-text" placeholder="6281234567890" />
						<p class="description">Nomor WhatsApp penerima prospek jika feed artikel tidak menyediakan kontak langsung.</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="default_cta_text">Teks Tombol CTA Default:</label></th>
					<td>
						<input type="text" name="default_cta_text" id="default_cta_text" value="<?php echo esc_attr( $cta_text ); ?>" class="regular-text" />
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="auto_hide_broken">Otomatis Sembunyikan Link Rusak (404/Noindex):</label></th>
					<td>
						<label><input type="checkbox" name="auto_hide_broken" id="auto_hide_broken" value="1" <?php checked( $auto_hide_broken, '1' ); ?> /> Ya, otomatis sembunyikan artikel dari landing page jika status HTTP 404 atau noindex.</label>
					</td>
				</tr>
			</table>
			<button type="submit" name="wah_save_settings" class="button button-primary">Simpan Pengaturan</button>
		</form>
	</div>
</div>
