<?php
/**
 * Area Landing Page Template - High-Converting High-Impact Sales UI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

if ( ! isset( $area ) && isset( $GLOBALS['wah_current_area'] ) ) {
	$area = $GLOBALS['wah_current_area'];
}
if ( ! isset( $articles ) && isset( $GLOBALS['wah_current_articles'] ) ) {
	$articles = $GLOBALS['wah_current_articles'];
}

$db             = WAH_DB::get_instance();
$area_name      = esc_html( $area['name'] ?? 'Kota' );
$area_province  = esc_html( $area['province_name'] ?? 'Indonesia' );
$all_providers  = $db->get_providers();
$all_areas      = $db->get_active_landing_areas();

// Filter related areas in the same province for internal linking
$related_areas = array_filter(
	$all_areas,
	function( $a ) use ( $area ) {
		return ( $a['id'] !== $area['id'] );
	}
);
$related_areas = array_slice( $related_areas, 0, 6 );

// Get default WhatsApp & CTA settings
$default_wa  = get_option( 'wah_default_wa_number', '' );
$default_cta = get_option( 'wah_default_cta_text', 'Daftar Sekarang' );
$custom_cta  = get_option( 'wah_custom_cta_url', '' );

$wa_url = ! empty( $default_wa )
	? 'https://wa.me/' . preg_replace( '/[^0-9]/', '', $default_wa ) . '?text=' . rawurlencode( "Halo Sales, saya ingin konsultasi & pasang WiFi murah di $area_name. Mohon bantu cek coverage lokasi saya." )
	: ( ! empty( $custom_cta ) ? $custom_cta : '#' );
?>

<div class="wah-public-container">
	<!-- Breadcrumbs -->
	<nav class="wah-breadcrumbs">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a> &rsaquo;
		<a href="<?php echo esc_url( home_url( '/#search' ) ); ?>">Wilayah</a> &rsaquo;
		<span><?php echo $area_name; ?></span>
	</nav>

	<!-- High-Impact Glassmorphic Hero Banner -->
	<header class="wah-landing-hero">
		<div style="display:flex; align-items:center; justify-content:center; gap:10px; flex-wrap:wrap; margin-bottom:16px;">
			<span class="wah-hero-badge">⚡ Coverage 100% Fiber Optic</span>
			<span style="display:inline-flex; align-items:center; gap:6px; background:#fef3c7; color:#92400e; padding:6px 16px; border-radius:30px; font-size:13px; font-weight:800; border:1px solid #fde68a;">⭐ <?php echo esc_html( get_option( 'wah_schema_rating_value', '4.8' ) ); ?> / 5.0 (<?php echo esc_html( get_option( 'wah_schema_review_count', '288' ) ); ?> Ulasan Pelanggan)</span>
		</div>
		<h1>Pasang WiFi & Internet Unlimited Murah di <span class="highlight"><?php echo $area_name; ?></span></h1>
		<p class="wah-hero-sub">Bandingkan promo harga paket internet rumah fiber optic unlimited tercepat & terjangkau di <?php echo $area_name; ?> (<?php echo $area_province; ?>). Gratis sewa modem & bebas biaya pemasangan!</p>

		<!-- Live Search Autocomplete Box -->
		<div class="wah-hero-search" style="margin-bottom:24px;">
			<?php echo do_shortcode( '[wifi_search_box]' ); ?>
		</div>

		<!-- Direct WhatsApp Sales Order Button -->
		<div>
			<a href="<?php echo esc_url( $wa_url ); ?>" target="_blank" rel="noopener" class="wah-btn-pulse">
				<span>💬 CHAT SALES & CEK COVERAGE LOKASI (FAST RESPONSE)</span>
			</a>
		</div>
	</header>

	<!-- Benefits Grid -->
	<section class="wah-benefits-grid">
		<div class="wah-benefit-card">
			<div class="wah-benefit-icon">⚡</div>
			<div class="wah-benefit-content">
				<h4>100% Fiber Optic</h4>
				<p>Kecepatan internet stabil tanpa terpengaruh cuaca buruk atau hujan.</p>
			</div>
		</div>
		<div class="wah-benefit-card">
			<div class="wah-benefit-icon">🎁</div>
			<div class="wah-benefit-content">
				<h4>Gratis Pemasangan</h4>
				<p>Bebas biaya instalasi awal & gratis pinjam modem Wi-Fi Dual Band.</p>
			</div>
		</div>
		<div class="wah-benefit-card">
			<div class="wah-benefit-icon">🚀</div>
			<div class="wah-benefit-content">
				<h4>Proses 1x24 Jam</h4>
				<p>Tim teknisi lokal <?php echo $area_name; ?> siap survei & aktifkan jaringan cepat.</p>
			</div>
		</div>
	</section>

	<!-- High-Converting Speed Package Pricing Cards Grid -->
	<section class="wah-pricing-section">
		<div class="wah-pricing-header">
			<h2>Pilihan Paket Internet WiFi Terpopuler di <?php echo $area_name; ?></h2>
			<p>Pilih kecepatan bandwidth yang sesuai dengan kebutuhan keluarga & usaha Anda</p>
		</div>

		<div class="wah-pricing-grid">
			<!-- Tier 1: 20 Mbps -->
			<div class="wah-price-card">
				<h3>Paket Hemat 20 Mbps</h3>
				<div class="wah-price-val">Rp 150rb-an <span>/ bulan</span></div>
				<div class="wah-price-sub">Cocok untuk 1 - 3 Perangkat (HP / Laptop)</div>
				<ul class="wah-price-features">
					<li><span>✓</span> Unlimited No FUP / Tanpa Kuota</li>
					<li><span>✓</span> Kecepatan Download/Upload 1:1</li>
					<li><span>✓</span> Gratis Sewa Modem Wi-Fi</li>
					<li><span>✓</span> Ideal untuk Browsing & Medsos</li>
				</ul>
				<a href="<?php echo esc_url( $wa_url ); ?>" target="_blank" rel="noopener" class="wah-btn wah-btn-whatsapp">
					<span>💬 Cek Promo 20 Mbps</span>
				</a>
			</div>

			<!-- Tier 2: 50 Mbps (Popular) -->
			<div class="wah-price-card popular">
				<div class="wah-popular-badge">🔥 PALING LARIS</div>
				<h3>Paket Favorit 50 Mbps</h3>
				<div class="wah-price-val">Rp 200rb-an <span>/ bulan</span></div>
				<div class="wah-price-sub">Cocok untuk 4 - 7 Perangkat (Keluarga)</div>
				<ul class="wah-price-features">
					<li><span>✓</span> Unlimited No FUP / Tanpa Kuota</li>
					<li><span>✓</span> Stream Video HD/4K Tanpa Buffering</li>
					<li><span>✓</span> Gratis Biaya Pasang & Instalasi</li>
					<li><span>✓</span> Prioritas Layanan Customer Support</li>
				</ul>
				<a href="<?php echo esc_url( $wa_url ); ?>" target="_blank" rel="noopener" class="wah-btn wah-btn-whatsapp">
					<span>💬 Pesan Paket 50 Mbps</span>
				</a>
			</div>

			<!-- Tier 3: 100 Mbps -->
			<div class="wah-price-card">
				<h3>Paket Sultan 100 Mbps</h3>
				<div class="wah-price-val">Rp 300rb-an <span>/ bulan</span></div>
				<div class="wah-price-sub">Cocok untuk 8+ Perangkat / Gaming / Office</div>
				<ul class="wah-price-features">
					<li><span>✓</span> Unlimited Bandwidth Ultra Fast</li>
					<li><span>✓</span> Ping Rendah Khusus Online Gaming</li>
					<li><span>✓</span> Router Wi-Fi Dual Band High Speed</li>
					<li><span>✓</span> Teknisi On-Site Prioritas 24/7</li>
				</ul>
				<a href="<?php echo esc_url( $wa_url ); ?>" target="_blank" rel="noopener" class="wah-btn wah-btn-whatsapp">
					<span>💬 Pesan Paket 100 Mbps</span>
				</a>
			</div>
		</div>
	</section>

	<!-- AI Summary Box -->
	<section class="wah-ai-summary-box">
		<h2><span class="wah-icon">✨</span> Panduan Jangkauan Layanan WiFi <?php echo $area_name; ?></h2>
		<p><?php echo esc_html( WAH_AI_Summarizer::generate( "Pasang WiFi $area_name", "", 0, $area['id'] ) ); ?></p>
	</section>

	<!-- Available Providers Badges -->
	<section class="wah-providers-strip">
		<h3>Daftar Provider ISP Resmi Tersedia di <?php echo $area_name; ?>:</h3>
		<div class="wah-provider-tags">
			<?php foreach ( $all_providers as $prov ) : ?>
				<a href="<?php echo esc_url( home_url( '/provider/' . $prov['slug'] . '/' ) ); ?>" class="wah-provider-tag" style="border-left: 4px solid <?php echo esc_attr( $prov['brand_color'] ); ?>;">
					<?php echo esc_html( $prov['name'] ); ?>
				</a>
			<?php endforeach; ?>
		</div>
	</section>

	<!-- Filtered Articles Grid Section -->
	<section class="wah-articles-section">
		<h2>Daftar Promo Paket Internet Terbaru di <?php echo $area_name; ?></h2>

		<?php if ( empty( $articles ) ) : ?>
			<div class="wah-card-panel text-center">
				<p>Belum ada rincian artikel spesifik untuk wilayah ini. Silakan klik tombol Chat WhatsApp di atas untuk berkonsultasi langsung dengan Sales resmi <?php echo $area_name; ?>.</p>
			</div>
		<?php else : ?>
			<div class="wah-card-grid">
				<?php foreach ( $articles as $art ) :
					$art_prov   = $art['provider_id'] ? $db->get_provider( $art['provider_id'] ) : null;
					$prov_color = $art_prov ? $art_prov['brand_color'] : '#0284c7';
					$art_wa_url = ! empty( $art['whatsapp_number'] )
						? 'https://wa.me/' . preg_replace( '/[^0-9]/', '', $art['whatsapp_number'] ) . '?text=' . rawurlencode( "Halo, saya berminat daftar promo " . $art['title'] . " di $area_name." )
						: $wa_url;
				?>
					<article class="wah-card">
						<div class="wah-card-header">
							<span class="wah-card-provider" style="color: <?php echo esc_attr( $prov_color ); ?>;">
								<?php echo esc_html( $art_prov ? $art_prov['name'] : 'Internet WiFi' ); ?>
							</span>
							<span class="wah-card-domain"><?php echo esc_html( $art['website_name'] ? $art['website_name'] : $art['domain'] ); ?></span>
						</div>

						<?php if ( ! empty( $art['featured_image'] ) ) : ?>
							<div class="wah-card-image">
								<img src="<?php echo esc_url( $art['featured_image'] ); ?>" alt="<?php echo esc_attr( $art['title'] ); ?>" loading="lazy" />
							</div>
						<?php else : ?>
							<div class="wah-card-banner-fallback" style="background: linear-gradient(135deg, <?php echo esc_attr( $prov_color ); ?> 0%, #0f172a 100%);">
								<span><?php echo esc_html( $art_prov ? $art_prov['name'] : 'Internet WiFi' ); ?></span>
							</div>
						<?php endif; ?>

						<div class="wah-card-body">
							<h3 class="wah-card-title"><?php echo esc_html( $art['title'] ); ?></h3>
							<p class="wah-card-excerpt"><?php echo esc_html( wp_trim_words( $art['excerpt'], 20, '...' ) ); ?></p>
						</div>

						<div class="wah-card-actions">
							<a href="<?php echo esc_url( $art_wa_url ); ?>" target="_blank" rel="noopener" class="wah-btn wah-btn-whatsapp">
								💬 HUBUNGI SALES & DAFTAR
							</a>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</section>

	<!-- Customer Testimonials Section (Social Proof) -->
	<section class="wah-reviews-section">
		<h2>Ulasan Pelanggan Terpasang di <?php echo $area_name; ?></h2>
		<div class="wah-reviews-grid">
			<div class="wah-review-card">
				<div class="wah-review-stars">⭐⭐⭐⭐⭐</div>
				<p class="wah-review-text">"Proses pasangnya cepat banget! Kemarin pesan siang via WhatsApp, besok paginya teknisi sudah datang pasang jaringan di rumah saya di <?php echo $area_name; ?>. Internetnya lancar jaya!"</p>
				<div class="wah-review-author">Bpk. Rahmat S. — <span>Pelanggan Terverifikasi (<?php echo $area_name; ?>)</span></div>
			</div>
			<div class="wah-review-card">
				<div class="wah-review-stars">⭐⭐⭐⭐⭐</div>
				<p class="wah-review-text">"Sangat terbantu ada direktori perbandingan WiFi ini. Jadi bisa pilih paket promo yang paling hemat untuk anak-anak sekolah online dan WFH."</p>
				<div class="wah-review-author">Ibu Diah P. — <span>Pelanggan Terverifikasi (<?php echo $area_name; ?>)</span></div>
			</div>
			<div class="wah-review-card">
				<div class="wah-review-stars">⭐⭐⭐⭐⭐</div>
				<p class="wah-review-text">"Harganya transparan dan tanpa biaya admin tersembunyi. Kecepatan 50 Mbps stabil buat game online & nonton streaming Netflix 4K tanpa buffering."</p>
				<div class="wah-review-author">Mas Hendra K. — <span>Pelanggan Terverifikasi (<?php echo $area_name; ?>)</span></div>
			</div>
		</div>
	</section>

	<!-- Related Areas Interlinking Mesh -->
	<?php if ( ! empty( $related_areas ) ) : ?>
		<section class="wah-related-section">
			<h3>📍 Jangkauan Wilayah & Kota Terdekat di <?php echo $area_province; ?>:</h3>
			<div class="wah-related-links">
				<?php foreach ( $related_areas as $rel ) : ?>
					<a href="<?php echo esc_url( home_url( '/wifi-' . $rel['slug'] . '/' ) ); ?>" class="wah-related-chip">
						Pasang WiFi <?php echo esc_html( $rel['name'] ); ?>
					</a>
				<?php endforeach; ?>
			</div>
		</section>
	<?php endif; ?>

	<!-- Rich FAQ Section -->
	<section class="wah-faq-section">
		<h2>Pertanyaan Sering Diajukan (FAQ) - WiFi <?php echo $area_name; ?></h2>
		<div class="wah-faq-item">
			<h4>Apa provider internet WiFi murah terbaik di <?php echo $area_name; ?>?</h4>
			<p>Di wilayah <?php echo $area_name; ?>, tersedia berbagai pilihan provider internet unlimited seperti ICONNET, Indosat HiFi, Biznet, CBN, dan MyRepublic dengan kisaran harga mulai Rp 150rb-an per bulan.</p>
		</div>
		<div class="wah-faq-item">
			<h4>Berapa lama proses pemasangan baru WiFi di lokasi saya?</h4>
			<p>Setelah pengajuan pendaftaran diproses oleh Sales resmi, jadwal survei & instalasi teknisi di <?php echo $area_name; ?> umumnya membutuhkan waktu 1x24 jam hingga maksimal 2 hari kerja.</p>
		</div>
		<div class="wah-faq-item">
			<h4>Apakah ada batas kuota (FUP) pada paket yang ditawarkan?</h4>
			<p>Hampir seluruh paket fiber optic yang terdaftar menggunakan skema True Unlimited tanpa batasan kuota FUP bulanan.</p>
		</div>
	</section>
</div>

<?php
get_footer();
