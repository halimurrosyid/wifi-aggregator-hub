<?php
/**
 * Provider Landing Page Template - High-Converting High-Impact Sales UI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

if ( ! isset( $provider ) && isset( $GLOBALS['wah_current_provider'] ) ) {
	$provider = $GLOBALS['wah_current_provider'];
}
if ( ! isset( $articles ) && isset( $GLOBALS['wah_current_articles'] ) ) {
	$articles = $GLOBALS['wah_current_articles'];
}

$db          = WAH_DB::get_instance();
$prov_name   = esc_html( $provider['name'] ?? 'Provider' );
$brand_col   = esc_attr( ( $provider['brand_color'] ?? '' ) ? $provider['brand_color'] : '#00a896' );
$all_areas   = $db->get_active_landing_areas();
$default_wa  = get_option( 'wah_default_wa_number', '' );
$default_cta = get_option( 'wah_default_cta_text', 'Daftar Sekarang' );
$custom_cta  = get_option( 'wah_custom_cta_url', '' );

$wa_url = ! empty( $default_wa )
	? 'https://wa.me/' . preg_replace( '/[^0-9]/', '', $default_wa ) . '?text=' . rawurlencode( "Halo Sales $prov_name, saya berminat daftar pasang baru internet $prov_name. Mohon bantu info promo & jangkauan lokasi saya." )
	: ( ! empty( $custom_cta ) ? $custom_cta : '#' );
?>

<div class="wah-public-container">
	<!-- Breadcrumbs -->
	<nav class="wah-breadcrumbs">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a> &rsaquo;
		<a href="<?php echo esc_url( home_url( '/#providers' ) ); ?>">Provider</a> &rsaquo;
		<span><?php echo $prov_name; ?></span>
	</nav>

	<!-- Glassmorphic Hero Banner -->
	<header class="wah-landing-hero" style="border-top: 6px solid <?php echo $brand_col; ?>;">
		<div style="display:flex; align-items:center; justify-content:center; gap:10px; flex-wrap:wrap; margin-bottom:16px;">
			<span class="wah-hero-badge" style="background-color: <?php echo $brand_col; ?>; color: #fff;">Direktori Provider ISP Resmi</span>
			<span style="display:inline-flex; align-items:center; gap:6px; background:#fef3c7; color:#92400e; padding:6px 16px; border-radius:30px; font-size:13px; font-weight:800; border:1px solid #fde68a;">⭐ <?php echo esc_html( get_option( 'wah_schema_rating_value', '4.8' ) ); ?> / 5.0 (<?php echo esc_html( get_option( 'wah_schema_review_count', '288' ) ); ?> Ulasan Pelanggan)</span>
		</div>
		<h1>Paket Internet WiFi <span class="highlight"><?php echo $prov_name; ?></span> Indonesia</h1>
		<p class="wah-hero-sub">Temukan area jangkauan coverage, daftar pilihan paket promo unlimited terbaru, dan informasi pendaftaran pasang baru <?php echo $prov_name; ?> secara resmi di kota Anda.</p>

		<!-- Direct WhatsApp Sales Order Button -->
		<div style="margin-top:20px;">
			<a href="<?php echo esc_url( $wa_url ); ?>" target="_blank" rel="noopener" class="wah-btn-pulse">
				<span>💬 HUBUNGI SALES <?php echo strtoupper( $prov_name ); ?> & CEK COVERAGE (FAST RESPONSE)</span>
			</a>
		</div>
	</header>

	<!-- Benefits Grid -->
	<section class="wah-benefits-grid">
		<div class="wah-benefit-card">
			<div class="wah-benefit-icon" style="background: <?php echo $brand_col; ?>15; color: <?php echo $brand_col; ?>;">⚡</div>
			<div class="wah-benefit-content">
				<h4>Jaringan Fiber Optic <?php echo $prov_name; ?></h4>
				<p>Koneksi kecepatan tinggi ultra stabil tanpa batasan kuota FUP.</p>
			</div>
		</div>
		<div class="wah-benefit-card">
			<div class="wah-benefit-icon" style="background: <?php echo $brand_col; ?>15; color: <?php echo $brand_col; ?>;">🎁</div>
			<div class="wah-benefit-content">
				<h4>Promo Pasang Baru</h4>
				<p>Dapatkan diskon langganan & bebas biaya sewa modem Wi-Fi.</p>
			</div>
		</div>
		<div class="wah-benefit-card">
			<div class="wah-benefit-icon" style="background: <?php echo $brand_col; ?>15; color: <?php echo $brand_col; ?>;">🚀</div>
			<div class="wah-benefit-content">
				<h4>Registrasi Cepat & Mudah</h4>
				<p>Proses registrasi online langsung via WhatsApp sales resmi.</p>
			</div>
		</div>
	</section>

	<!-- Speed Package Pricing Tiers Grid -->
	<section class="wah-pricing-section">
		<div class="wah-pricing-header">
			<h2>Pilihan Paket Promo Terfavorit <?php echo $prov_name; ?></h2>
			<p>Rekomendasi kecepatan bandwidth terbaik untuk rumah & tempat usaha</p>
		</div>

		<div class="wah-pricing-grid">
			<!-- Tier 1 -->
			<div class="wah-price-card">
				<h3>Paket Home 20 Mbps</h3>
				<div class="wah-price-val" style="color: <?php echo $brand_col; ?>;">Rp 150rb-an <span>/ bulan</span></div>
				<div class="wah-price-sub">Cocok untuk 1 - 3 Perangkat (HP / Laptop)</div>
				<ul class="wah-price-features">
					<li><span>✓</span> Unlimited Bandwidth No FUP</li>
					<li><span>✓</span> Gratis Router Wi-Fi Modem</li>
					<li><span>✓</span> Akses Cepat Medsos & Browsing</li>
				</ul>
				<a href="<?php echo esc_url( $wa_url ); ?>" target="_blank" rel="noopener" class="wah-btn wah-btn-whatsapp">
					<span>💬 Cek Promo <?php echo $prov_name; ?> 20M</span>
				</a>
			</div>

			<!-- Tier 2 Popular -->
			<div class="wah-price-card popular" style="border-color: <?php echo $brand_col; ?>;">
				<div class="wah-popular-badge" style="background: <?php echo $brand_col; ?>;">🔥 PALING LARIS</div>
				<h3>Paket Family 50 Mbps</h3>
				<div class="wah-price-val" style="color: <?php echo $brand_col; ?>;">Rp 200rb-an <span>/ bulan</span></div>
				<div class="wah-price-sub">Cocok untuk 4 - 7 Perangkat (Keluarga)</div>
				<ul class="wah-price-features">
					<li><span>✓</span> Unlimited High Speed Fiber Optic</li>
					<li><span>✓</span> Stream Video HD/4K Tanpa Buffering</li>
					<li><span>✓</span> Gratis Biaya Pemasangan Teknisi</li>
				</ul>
				<a href="<?php echo esc_url( $wa_url ); ?>" target="_blank" rel="noopener" class="wah-btn wah-btn-whatsapp">
					<span>💬 Daftar Paket <?php echo $prov_name; ?> 50M</span>
				</a>
			</div>

			<!-- Tier 3 -->
			<div class="wah-price-card">
				<h3>Paket Ultimate 100 Mbps</h3>
				<div class="wah-price-val" style="color: <?php echo $brand_col; ?>;">Rp 300rb-an <span>/ bulan</span></div>
				<div class="wah-price-sub">Cocok untuk 8+ Perangkat / Office / Gaming</div>
				<ul class="wah-price-features">
					<li><span>✓</span> Unlimited Ultra Bandwidth Speed</li>
					<li><span>✓</span> Ping Rendah Khusus Game Online</li>
					<li><span>✓</span> Prioritas Layanan Customer Support</li>
				</ul>
				<a href="<?php echo esc_url( $wa_url ); ?>" target="_blank" rel="noopener" class="wah-btn wah-btn-whatsapp">
					<span>💬 Daftar Paket <?php echo $prov_name; ?> 100M</span>
				</a>
			</div>
		</div>
	</section>

	<!-- Available Areas for this Provider -->
	<?php if ( ! empty( $all_areas ) ) : ?>
		<section class="wah-providers-strip">
			<h3>Wilayah & Kota Jangkauan <?php echo $prov_name; ?> Indonesia:</h3>
			<div class="wah-provider-tags">
				<?php foreach ( $all_areas as $ar ) : ?>
					<a href="<?php echo esc_url( home_url( '/wifi-' . $ar['slug'] . '/' ) ); ?>" class="wah-provider-tag" style="border-left: 4px solid <?php echo $brand_col; ?>;">
						📍 Pasang <?php echo $prov_name; ?> <?php echo esc_html( $ar['name'] ); ?>
					</a>
				<?php endforeach; ?>
			</div>
		</section>
	<?php endif; ?>

	<!-- Articles Section for Provider -->
	<section class="wah-articles-section">
		<h2>Katalog Promo & Artikel Terbaru <?php echo $prov_name; ?></h2>

		<?php if ( empty( $articles ) ) : ?>
			<div class="wah-card-panel text-center">
				<p>Belum ada rincian promo spesifik yang terindeks untuk provider ini. Silakan klik tombol Chat WhatsApp Sales di atas untuk berkonsultasi langsung mengenai paket terbaru <?php echo $prov_name; ?>.</p>
			</div>
		<?php else : ?>
			<div class="wah-card-grid">
				<?php foreach ( $articles as $art ) :
					$art_wa_url = ! empty( $art['whatsapp_number'] )
						? 'https://wa.me/' . preg_replace( '/[^0-9]/', '', $art['whatsapp_number'] ) . '?text=' . rawurlencode( "Halo, saya berminat daftar promo " . $art['title'] )
						: $wa_url;
				?>
					<article class="wah-card">
						<div class="wah-card-header">
							<span class="wah-card-provider" style="color: <?php echo esc_attr( $brand_col ); ?>;">
								<?php echo $prov_name; ?>
							</span>
							<span class="wah-card-domain"><?php echo esc_html( $art['website_name'] ? $art['website_name'] : $art['domain'] ); ?></span>
						</div>

						<?php if ( ! empty( $art['featured_image'] ) ) : ?>
							<div class="wah-card-image">
								<img src="<?php echo esc_url( $art['featured_image'] ); ?>" alt="<?php echo esc_attr( $art['title'] ); ?>" loading="lazy" />
							</div>
						<?php else : ?>
							<div class="wah-card-banner-fallback" style="background: linear-gradient(135deg, <?php echo esc_attr( $brand_col ); ?> 0%, #0f172a 100%);">
								<span><?php echo $prov_name; ?></span>
							</div>
						<?php endif; ?>

						<div class="wah-card-body">
							<h3 class="wah-card-title"><?php echo esc_html( $art['title'] ); ?></h3>
							<p class="wah-card-excerpt"><?php echo esc_html( wp_trim_words( $art['excerpt'], 20, '...' ) ); ?></p>
						</div>

						<div class="wah-card-actions">
							<a href="<?php echo esc_url( $art_wa_url ); ?>" target="_blank" rel="noopener" class="wah-btn wah-btn-whatsapp">
								💬 CHAT SALES <?php echo strtoupper( $prov_name ); ?>
							</a>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</section>
</div>

<?php
get_footer();
