<?php
/**
 * Area Landing Page Template.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$db             = WAH_DB::get_instance();
$area_name      = esc_html( $area['name'] );
$area_province  = esc_html( $area['province_name'] );
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
							<!-- Direct link to original article -->
							<a href="<?php echo esc_url( $art['url'] ); ?>" target="_blank" rel="nofollow noopener" class="wah-btn wah-btn-outline" data-article-id="<?php echo esc_attr( $art['id'] ); ?>">
								Lihat Detail &rarr;
							</a>

							<!-- CTA Link -->
							<a href="<?php echo esc_url( $art['url'] ); ?>" target="_blank" rel="nofollow noopener" class="wah-btn wah-btn-primary" style="background-color: <?php echo esc_attr( $brand_col ); ?>;" data-article-id="<?php echo esc_attr( $art['id'] ); ?>">
								<?php echo esc_html( $default_cta ); ?>
							</a>

							<?php if ( ! empty( $wa_target ) ) : ?>
								<a href="https://wa.me/<?php echo esc_attr( preg_replace( '/[^0-9]/', '', $wa_target ) ); ?>?text=Halo%20saya%20tertarik%20pasang%20wifi%20<?php echo urlencode( $prov_name . ' ' . $area_name ); ?>" target="_blank" rel="nofollow noopener" class="wah-btn wah-btn-whatsapp" data-article-id="<?php echo esc_attr( $art['id'] ); ?>">
									💬 Chat WhatsApp Direct
								</a>
							<?php endif; ?>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</main>

	<!-- Internal Linking Section: Related Areas -->
	<section class="wah-related-section">
		<h3>Wilayah Jangkauan Sekitar <?php echo $area_name; ?>:</h3>
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
