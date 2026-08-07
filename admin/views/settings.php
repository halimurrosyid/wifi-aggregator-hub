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
	update_option( 'wah_custom_cta_url', esc_url_raw( $_POST['custom_cta_url'] ?? '' ) );
	update_option( 'wah_default_cta_text', sanitize_text_field( $_POST['default_cta_text'] ?? '💬 Chat WhatsApp Direct' ) );
	update_option( 'wah_auto_hide_broken', isset( $_POST['auto_hide_broken'] ) ? '1' : '0' );
	$message = 'Pengaturan umum berhasil disimpan.';
}

$wa_num           = get_option( 'wah_default_wa_number', '' );
$custom_cta_url   = get_option( 'wah_custom_cta_url', '' );
$cta_text         = get_option( 'wah_default_cta_text', '💬 Chat WhatsApp Direct' );
$auto_hide_broken = get_option( 'wah_auto_hide_broken', '1' );
?>
<div class="wrap wah-admin-wrap">
	<h1 class="wah-title"><span class="dashicons dashicons-admin-generic"></span> General Settings</h1>

	<?php if ( $message ) : ?>
		<div class="notice notice-success is-dismissible"><p><?php echo esc_html( $message ); ?></p></div>
	<?php endif; ?>

	<div class="wah-card-panel">
		<h3>Pengaturan Tombol CTA Custom Aggregator</h3>
		<form method="post" action="">
			<?php wp_nonce_field( 'wah_settings_action', 'wah_settings_nonce' ); ?>
			<table class="form-table">
				<tr>
					<th scope="row"><label for="default_wa_number">Nomor WhatsApp Direct Sales:</label></th>
					<td>
						<input type="text" name="default_wa_number" id="default_wa_number" value="<?php echo esc_attr( $wa_num ); ?>" class="regular-text" style="width:100% !important; max-width:100% !important;" placeholder="6281234567890" />
						<p class="description">Nomor WhatsApp penerima order/pesanan pelanggan saat pengunjung menekan tombol CTA.</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="custom_cta_url">URL Link Custom CTA (Opsional):</label></th>
					<td>
						<input type="url" name="custom_cta_url" id="custom_cta_url" value="<?php echo esc_attr( $custom_cta_url ); ?>" class="regular-text" style="width:100% !important; max-width:100% !important;" placeholder="https://website-anda.com/order/" />
						<p class="description">Kosongkan jika ingin menggunakan link WhatsApp Direct otomatis.</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="default_cta_text">Teks Tombol CTA Custom:</label></th>
					<td>
						<input type="text" name="default_cta_text" id="default_cta_text" value="<?php echo esc_attr( $cta_text ); ?>" class="regular-text" style="width:100% !important; max-width:100% !important;" placeholder="💬 Chat WhatsApp Direct" />
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="auto_hide_broken">Otomatis Sembunyikan Link Rusak (404/Noindex):</label></th>
					<td>
						<label><input type="checkbox" name="auto_hide_broken" id="auto_hide_broken" value="1" <?php checked( $auto_hide_broken, '1' ); ?> /> Ya, otomatis sembunyikan artikel jika status HTTP 404 atau noindex.</label>
					</td>
				</tr>
			</table>
			<p>
				<button type="submit" name="wah_save_settings" class="button button-primary" style="height:40px; padding:0 25px;">Simpan Pengaturan</button>
			</p>
		</form>
	</div>
</div>
