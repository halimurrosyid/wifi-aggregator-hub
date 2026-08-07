<?php
/**
 * Admin SEO & Sitemap View.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$message = '';
if ( isset( $_POST['wah_save_seo'] ) && check_admin_referer( 'wah_seo_action', 'wah_seo_nonce' ) ) {
	update_option( 'wah_seo_title_pattern', sanitize_text_field( $_POST['title_pattern'] ?? '' ) );
	update_option( 'wah_seo_desc_pattern', sanitize_textarea_field( $_POST['desc_pattern'] ?? '' ) );
	update_option( 'wah_seo_provider_title_pattern', sanitize_text_field( $_POST['provider_title_pattern'] ?? '' ) );
	update_option( 'wah_seo_provider_desc_pattern', sanitize_textarea_field( $_POST['provider_desc_pattern'] ?? '' ) );
	$message = 'Pengaturan Template SEO Meta & Pattern berhasil disimpan!';
}

$title_pattern          = get_option( 'wah_seo_title_pattern', 'Pasang WiFi Murah & Provider Internet di %area% Terbaru %year%' );
$desc_pattern           = get_option( 'wah_seo_desc_pattern', 'Daftar rekomendasi provider internet wifi unlimited terbaik di %area%. Bandingkan paket ICONNET, Indosat HiFi, CBN Fiber, Biznet, MyRepublic.' );
$provider_title_pattern = get_option( 'wah_seo_provider_title_pattern', 'Paket Internet WiFi %provider% Indonesia - Promo & Wilayah Jangkauan %year%' );
$provider_desc_pattern  = get_option( 'wah_seo_provider_desc_pattern', 'Cek daftar wilayah jangkauan, pilihan paket unlimited, dan cara daftar internet %provider% terbaru di seluruh Indonesia.' );
?>
<div class="wrap wah-admin-wrap">
	<h1 class="wah-title"><span class="dashicons dashicons-search"></span> SEO & XML Sitemap Manager</h1>

	<?php if ( $message ) : ?>
		<div class="notice notice-success is-dismissible"><p><?php echo esc_html( $message ); ?></p></div>
	<?php endif; ?>

	<div class="wah-card-panel">
		<h3>Meta SEO Pattern Customization (Template SEO Otomatis)</h3>
		<p style="color:#64748b;">Atur template meta title & description yang akan diterapkan secara otomatis ke seluruh Halaman Landing SEO Area & Provider.</p>

		<form method="post" action="">
			<?php wp_nonce_field( 'wah_seo_action', 'wah_seo_nonce' ); ?>
			<table class="form-table">
				<tr>
					<th scope="row"><label for="title_pattern">Format Meta Title Area Page:</label></th>
					<td>
						<input type="text" name="title_pattern" id="title_pattern" value="<?php echo esc_attr( $title_pattern ); ?>" class="large-text" style="width:100% !important; max-width:100% !important;" />
						<p class="description">Placeholder: <code>%area%</code>, <code>%provider%</code>, <code>%year%</code>, <code>%site_name%</code></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="desc_pattern">Format Meta Description Area Page:</label></th>
					<td>
						<textarea name="desc_pattern" id="desc_pattern" class="large-text" rows="3" style="width:100% !important; max-width:100% !important;"><?php echo esc_textarea( $desc_pattern ); ?></textarea>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="provider_title_pattern">Format Meta Title Provider Page:</label></th>
					<td>
						<input type="text" name="provider_title_pattern" id="provider_title_pattern" value="<?php echo esc_attr( $provider_title_pattern ); ?>" class="large-text" style="width:100% !important; max-width:100% !important;" />
						<p class="description">Placeholder: <code>%provider%</code>, <code>%year%</code>, <code>%site_name%</code></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="provider_desc_pattern">Format Meta Description Provider Page:</label></th>
					<td>
						<textarea name="provider_desc_pattern" id="provider_desc_pattern" class="large-text" rows="3" style="width:100% !important; max-width:100% !important;"><?php echo esc_textarea( $provider_desc_pattern ); ?></textarea>
					</td>
				</tr>
			</table>
			<p>
				<button type="submit" name="wah_save_seo" class="button button-primary" style="height:40px; padding:0 25px;">Simpan Template SEO</button>
			</p>
		</form>
	</div>

	<div class="wah-card-panel margin-top-20">
		<h3>Mandatory XML Sitemaps Generator</h3>
		<p>Sitemap khusus ini dikategorikan otomatis untuk membantu pengindeksan cepat Google Bot & Bing:</p>
		<ul class="wah-sitemap-list" style="list-style:none; padding:0; margin:0;">
			<li style="margin-bottom:10px;"><span class="dashicons dashicons-paperclip"></span> <strong>Landing Pages Sitemap:</strong> <a href="<?php echo esc_url( home_url( '/landing-sitemap.xml' ) ); ?>" target="_blank"><?php echo esc_url( home_url( '/landing-sitemap.xml' ) ); ?></a></li>
			<li style="margin-bottom:10px;"><span class="dashicons dashicons-paperclip"></span> <strong>Providers Sitemap:</strong> <a href="<?php echo esc_url( home_url( '/provider-sitemap.xml' ) ); ?>" target="_blank"><?php echo esc_url( home_url( '/provider-sitemap.xml' ) ); ?></a></li>
			<li style="margin-bottom:10px;"><span class="dashicons dashicons-paperclip"></span> <strong>Areas Sitemap:</strong> <a href="<?php echo esc_url( home_url( '/area-sitemap.xml' ) ); ?>" target="_blank"><?php echo esc_url( home_url( '/area-sitemap.xml' ) ); ?></a></li>
		</ul>
	</div>
</div>
