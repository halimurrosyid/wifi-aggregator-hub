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
	update_option( 'wah_schema_rating_value', sanitize_text_field( $_POST['rating_value'] ?? '4.8' ) );
	update_option( 'wah_schema_review_count', sanitize_text_field( $_POST['review_count'] ?? '288' ) );
	$message = 'Pengaturan Template SEO Meta, Pattern & Schema Rating berhasil disimpan!';
}

$title_pattern          = get_option( 'wah_seo_title_pattern', 'Pasang WiFi Murah & Provider Internet di %area% Terbaru %year%' );
$desc_pattern           = get_option( 'wah_seo_desc_pattern', 'Daftar rekomendasi provider internet wifi unlimited terbaik di %area%. Bandingkan paket ICONNET, Indosat HiFi, CBN Fiber, Biznet, MyRepublic.' );
$provider_title_pattern = get_option( 'wah_seo_provider_title_pattern', 'Paket Internet WiFi %provider% Indonesia - Promo & Wilayah Jangkauan %year%' );
$provider_desc_pattern  = get_option( 'wah_seo_provider_desc_pattern', 'Cek daftar wilayah jangkauan, pilihan paket unlimited, dan cara daftar internet %provider% terbaru di seluruh Indonesia.' );
$rating_value           = get_option( 'wah_schema_rating_value', '4.8' );
$review_count           = get_option( 'wah_schema_review_count', '288' );
?>
<div class="wrap wah-admin-wrap">
	<h1 class="wah-title"><span class="dashicons dashicons-search"></span> SEO & XML Sitemap Manager</h1>

	<?php if ( $message ) : ?>
		<div class="notice notice-success is-dismissible"><p><?php echo esc_html( $message ); ?></p></div>
	<?php endif; ?>

	<div class="wah-card-panel">
		<h3>Meta SEO Pattern & Rating Star Schema Customization</h3>
		<p style="color:#64748b;">Atur template meta title & description serta nilai Bintang Rating (Schema AggregateRating) yang akan tampil di hasil pencarian Google.</p>

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
				<tr>
					<th scope="row"><label for="rating_value">⭐ Default Rating Value (Bintang Google):</label></th>
					<td>
						<input type="text" name="rating_value" id="rating_value" value="<?php echo esc_attr( $rating_value ); ?>" class="regular-text" placeholder="4.8" />
						<p class="description">Nilai rating bintang yang akan diproses oleh Google Search (Contoh: <code>4.8</code>).</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="review_count">💬 Default Review Count (Jumlah Ulasan):</label></th>
					<td>
						<input type="text" name="review_count" id="review_count" value="<?php echo esc_attr( $review_count ); ?>" class="regular-text" placeholder="288" />
						<p class="description">Jumlah total ulasan pelanggan untuk Google Rich Snippet (Contoh: <code>288</code>).</p>
					</td>
				</tr>
			</table>
			<p>
				<button type="submit" name="wah_save_seo" class="button button-primary" style="height:40px; padding:0 25px;">Simpan Pengaturan SEO & Rating Schema</button>
			</p>
		</form>
	</div>

	<!-- Google Search Console Sitemap Submission Box -->
	<div class="wah-card-panel margin-top-20" style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:12px; padding:24px;">
		<div style="display:flex; align-items:center; gap:10px; margin-bottom:12px;">
			<span class="dashicons dashicons-google" style="font-size:28px; width:28px; height:28px; color:#16a34a;"></span>
			<h3 style="margin:0; color:#14532d; font-size:18px; font-weight:700;">Google Search Console - Master XML Sitemap Submission</h3>
		</div>
		<p style="color:#166534; margin:0 0 16px 0; font-size:14px; line-height:1.5;">
			Copy & paste URL <strong>Master Sitemap Index</strong> berikut ke menu <code>Peta Situs / Sitemaps</code> di <strong>Google Search Console</strong> Anda. Cukup kirimkan 1 URL ini saja, dan Googlebot akan otomatis mengindeks seluruh 6.167+ artikel, kota, & provider!
		</p>

		<div style="display:flex; gap:10px; align-items:center; background:#ffffff; padding:12px 16px; border-radius:8px; border:1px solid #86efac; margin-bottom:16px;">
			<span class="dashicons dashicons-admin-links" style="color:#16a34a;"></span>
			<input type="text" readonly value="<?php echo esc_url( home_url( '/wifi-sitemap.xml' ) ); ?>" style="flex:1; border:none; background:transparent; font-family:monospace; font-size:14px; font-weight:700; color:#14532d;" id="wah-master-sitemap-url" />
			<a href="<?php echo esc_url( home_url( '/wifi-sitemap.xml' ) ); ?>" target="_blank" class="button" style="display:inline-flex; align-items:center; gap:4px;"><span class="dashicons dashicons-external" style="margin-top:2px;"></span> Buka XML</a>
		</div>

		<h4 style="margin:16px 0 10px 0; color:#14532d; font-size:14px;">Sub-Sitemaps Terkait (Otomatis Dikirim oleh Master Sitemap):</h4>
		<ul class="wah-sitemap-list" style="list-style:none; padding:0; margin:0; display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:10px;">
			<li style="background:#ffffff; padding:10px 14px; border-radius:6px; border:1px solid #dcfce7; font-size:13px;">
				<strong>📍 Areas Sitemap:</strong><br />
				<a href="<?php echo esc_url( home_url( '/area-sitemap.xml' ) ); ?>" target="_blank" style="color:#15803d; font-weight:600; text-decoration:underline; font-family:monospace;"><?php echo esc_url( home_url( '/area-sitemap.xml' ) ); ?></a>
			</li>
			<li style="background:#ffffff; padding:10px 14px; border-radius:6px; border:1px solid #dcfce7; font-size:13px;">
				<strong>🏢 Providers Sitemap:</strong><br />
				<a href="<?php echo esc_url( home_url( '/provider-sitemap.xml' ) ); ?>" target="_blank" style="color:#15803d; font-weight:600; text-decoration:underline; font-family:monospace;"><?php echo esc_url( home_url( '/provider-sitemap.xml' ) ); ?></a>
			</li>
			<li style="background:#ffffff; padding:10px 14px; border-radius:6px; border:1px solid #dcfce7; font-size:13px;">
				<strong>🚀 Main Landings Sitemap:</strong><br />
				<a href="<?php echo esc_url( home_url( '/landing-sitemap.xml' ) ); ?>" target="_blank" style="color:#15803d; font-weight:600; text-decoration:underline; font-family:monospace;"><?php echo esc_url( home_url( '/landing-sitemap.xml' ) ); ?></a>
			</li>
		</ul>
	</div>
</div>
