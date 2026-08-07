<?php
/**
 * Area Landing Page Template.
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
?>

<div class="wah-public-container">
	<!-- Breadcrumbs -->
	<nav class="wah-breadcrumbs">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a> &rsaquo;
		<a href="<?php echo esc_url( home_url( '/#search' ) ); ?>">Wilayah</a> &rsaquo;
		<span><?php echo $area_name; ?></span>
	</nav>

	<!-- Hero Banner -->
	<header class="wah-landing-hero">
		<span class="wah-hero-badge">Portal Pencarian Provider Internet</span>
		<h1>Pasang WiFi & Provider Internet Terbaik di <?php echo $area_name; ?></h1>
		<p class="wah-hero-sub">Bandingkan paket internet fiber optic unlimited murah dari berbagai penyedia resmi di <?php echo $area_name; ?> (<?php echo $area_province; ?>).</p>

		<!-- Search Bar Component -->
		<div class="wah-hero-search">
			<?php echo do_shortcode( '[wifi_search_box]' ); ?>
		</div>
	</header>

	<!-- AI Summary Box -->
	<section class="wah-ai-summary-box">
		<h2><span class="wah-icon">✨</span> Ringkasan Layanan WiFi <?php echo $area_name; ?></h2>
		<p><?php echo esc_html( WAH_AI_Summarizer::generate( "Pasang WiFi $area_name", "", 0, $area['id'] ) ); ?></p>
	</section>

	<!-- Available Providers Badges -->
	<section class="wah-providers-strip">
		<h3>Provider ISP Tersedia di <?php echo $area_name; ?>:</h3>
		<div class="wah-provider-tags">
			<?php foreach ( $all_providers as $prov ) : ?>
				<a href="<?php echo esc_url( home_url( '/provider/' . $prov['slug'] . '/' ) ); ?>" class="wah-provider-tag" style="border-left: 4px solid <?php echo esc_attr( $prov['brand_color'] ); ?>;">
					<?php echo esc_html( $prov['name'] ); ?>
				</a>
			<?php endforeach; ?>
		</div>
	</section>

	<!-- Articles / Offers Grid -->
	<main class="wah-articles-section">
		<h2>Pilihan Paket & Informasi Terkini di <?php echo $area_name; ?></h2>

		<?php if ( empty( $articles ) ) : ?>
			<div class="wah-no-results">
				<p>Belum ada artikel terdaftar untuk area <?php echo $area_name; ?>. Silakan cek area terdekat atau gunakan fitur pencarian.</p>
			</div>
		<?php else : ?>
			<div class="wah-card-grid">
				<?php foreach ( $articles as $art ) :
					$prov      = $art['provider_id'] ? $db->get_provider( $art['provider_id'] ) : null;
					$brand_col = $prov ? $prov['brand_color'] : '#00a896';
					$prov_name = $prov ? $prov['name'] : 'Internet Provider';
					$wa_target = ! empty( $art['whatsapp_number'] ) ? $art['whatsapp_number'] : $default_wa;
				?>
					<article class="wah-card">
						<div class="wah-card-header" style="border-top: 4px solid <?php echo esc_attr( $brand_col ); ?>;">
							<span class="wah-card-provider" style="color: <?php echo esc_attr( $brand_col ); ?>;"><?php echo esc_html( $prov_name ); ?></span>
							<span class="wah-card-domain">via <?php echo esc_html( $art['website_name'] ? $art['website_name'] : $art['domain'] ); ?></span>
						</div>

						<?php if ( ! empty( $art['featured_image'] ) ) : ?>
							<div class="wah-card-image">
								<img src="<?php echo esc_url( $art['featured_image'] ); ?>" alt="<?php echo esc_attr( $art['title'] ); ?>" loading="lazy" />
							</div>
						<?php else : ?>
							<div class="wah-card-banner-fallback" style="background: linear-gradient(135deg, <?php echo esc_attr( $brand_col ); ?> 0%, #0f172a 100%);">
								<span>⚡ <?php echo esc_html( $prov_name ); ?></span>
							</div>
						<?php endif; ?>

						<div class="wah-card-body">
							<h3 class="wah-card-title"><?php echo esc_html( $art['title'] ); ?></h3>
							<p class="wah-card-excerpt"><?php echo esc_html( $art['excerpt'] ); ?></p>
						</div>

						<div class="wah-card-actions">
							<?php
							// Custom CTA Link Resolution
							$global_wa  = get_option( 'wah_default_wa_number', '' );
							$custom_url = get_option( 'wah_custom_cta_url', '' );
							$cta_label  = get_option( 'wah_default_cta_text', '💬 Chat WhatsApp Direct' );
							$target_wa  = ! empty( $art['whatsapp_number'] ) ? $art['whatsapp_number'] : $global_wa;

							if ( ! empty( $custom_url ) ) {
								$final_link = $custom_url;
							} elseif ( ! empty( $target_wa ) ) {
								$wa_num_clean = preg_replace( '/[^0-9]/', '', $target_wa );
								$wa_text      = urlencode( 'Halo, saya tertarik pasang paket WiFi ' . $prov_name . ' di ' . $area_name );
								$final_link   = 'https://wa.me/' . $wa_num_clean . '?text=' . $wa_text;
							} else {
								$final_link = 'https://wa.me/?text=' . urlencode( 'Halo, saya mau pesan paket WiFi ' . $prov_name . ' ' . $area_name );
							}
							?>
							<a href="<?php echo esc_url( $final_link ); ?>" target="_blank" rel="nofollow noopener" class="wah-btn wah-btn-primary" style="background-color: <?php echo esc_attr( $brand_col ); ?>; width:100%; text-align:center; justify-content:center; font-weight:700; font-size:15px; padding:12px 18px; border-radius:8px; display:inline-flex; align-items:center; gap:8px;" data-article-id="<?php echo esc_attr( $art['id'] ); ?>">
								<?php echo esc_html( $cta_label ); ?>
							</a>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</main>

	<!-- Sub-Area & Kecamatan Coverage Section -->
	<div class="wah-card-panel margin-top-30" style="background:#fff; border-radius:12px; padding:20px; box-shadow:0 2px 10px rgba(0,0,0,0.05); margin-top:25px;">
		<h3 style="font-size:18px; font-weight:700; color:#0f172a; margin-top:0; margin-bottom:8px;">📍 Wilayah Jangkauan Sekitar <?php echo esc_html( $area_name ); ?>:</h3>
		<p style="color:#64748b; font-size:14px; margin-bottom:15px;">Layanan pemasangan internet WiFi super cepat melayani seluruh area kecamatan, desa, dan kelurahan di sekitar <?php echo esc_html( $area_name ); ?>:</p>
		<div style="display:flex; flex-wrap:wrap; gap:8px;">
			<?php 
			$sub_areas = $db->get_sub_areas( $area );
			foreach ( $sub_areas as $sub ) : 
			?>
				<span style="background:#f1f5f9; color:#334155; padding:6px 12px; border-radius:20px; font-size:13px; font-weight:500; border:1px solid #e2e8f0; display:inline-flex; align-items:center; gap:5px;">
					📌 <?php echo esc_html( $sub ); ?>
				</span>
			<?php endforeach; ?>
		</div>
	</div>

	<!-- Internal Linking Section: Related Areas -->
	<section class="wah-related-section">
		<h3>Kota & Kabupaten Lainnya:</h3>
		<div class="wah-related-links">
			<?php foreach ( $related_areas as $rel ) : ?>
				<a href="<?php echo esc_url( home_url( '/wifi-' . $rel['slug'] . '/' ) ); ?>" class="wah-related-chip">
					📍 Pasang WiFi <?php echo esc_html( $rel['name'] ); ?>
				</a>
			<?php endforeach; ?>
		</div>
	</section>

	<!-- FAQ Section -->
	<section class="wah-faq-section">
		<h2>Pertanyaan Sering Diajukan (FAQ) - WiFi <?php echo $area_name; ?></h2>
		<div class="wah-faq-item">
			<h4>Apa provider wifi internet unlimited murah di <?php echo $area_name; ?>?</h4>
			<p>Di wilayah <?php echo $area_name; ?>, terdapat beberapa pilihan provider serat optik seperti ICONNET, Indosat HiFi, CBN Fiber, Biznet, dan MyRepublic dengan kecepatan mulai 20 Mbps hingga 100 Mbps+.</p>
		</div>
		<div class="wah-faq-item">
			<h4>Bagaimana cara cek jangkauan jaringan lokasi rumah di <?php echo $area_name; ?>?</h4>
			<p>Anda dapat mengklik tombol "Lihat Detail" atau "WhatsApp" pada kartu provider di atas untuk berkonsultasi langsung dengan sales representatif resmi.</p>
		</div>
	</section>
</div>

<?php get_footer(); ?>
