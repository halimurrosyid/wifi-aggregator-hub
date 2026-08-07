<?php
/**
 * Provider Landing Page Template.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$db            = WAH_DB::get_instance();
$prov_name     = esc_html( $provider['name'] );
$brand_col     = esc_attr( $provider['brand_color'] ? $provider['brand_color'] : '#00a896' );
$all_areas     = $db->get_areas();
$default_wa    = get_option( 'wah_default_wa_number', '' );
$default_cta   = get_option( 'wah_default_cta_text', 'Daftar Sekarang' );
?>

<div class="wah-public-container">
	<!-- Breadcrumbs -->
	<nav class="wah-breadcrumbs">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a> &rsaquo;
		<a href="<?php echo esc_url( home_url( '/#providers' ) ); ?>">Provider</a> &rsaquo;
		<span><?php echo $prov_name; ?></span>
	</nav>

	<!-- Hero Banner -->
	<header class="wah-landing-hero" style="border-top: 6px solid <?php echo $brand_col; ?>;">
		<span class="wah-hero-badge" style="background-color: <?php echo $brand_col; ?>; color: #fff;">Direktori Provider ISP</span>
		<h1>Paket Internet WiFi <?php echo $prov_name; ?> Indonesia</h1>
		<p class="wah-hero-sub">Temukan area jangkauan, daftar promo paket unlimited, dan informasi pendaftaran <?php echo $prov_name; ?> di berbagai kota.</p>
	</header>

	<!-- Available Areas for this Provider -->
	<section class="wah-providers-strip">
		<h3>Kota & Wilayah Jangkauan <?php echo $prov_name; ?>:</h3>
		<div class="wah-provider-tags">
			<?php foreach ( $all_areas as $area ) : ?>
				<a href="<?php echo esc_url( home_url( '/wifi-' . $area['slug'] . '/' ) ); ?>" class="wah-provider-tag">
					📍 <?php echo esc_html( $area['name'] ); ?>
				</a>
			<?php endforeach; ?>
		</div>
	</section>

	<!-- Articles Grid -->
	<main class="wah-articles-section">
		<h2>Daftar Artikel & Promo Terkini <?php echo $prov_name; ?></h2>

		<?php if ( empty( $articles ) ) : ?>
			<div class="wah-no-results">
				<p>Belum ada artikel terdaftar untuk provider <?php echo $prov_name; ?> saat ini.</p>
			</div>
		<?php else : ?>
			<div class="wah-card-grid">
				<?php foreach ( $articles as $art ) :
					$area_obj  = $art['area_id'] ? $db->get_area( $art['area_id'] ) : null;
					$area_label= $area_obj ? $area_obj['name'] : 'Indonesia';
					$wa_target = ! empty( $art['whatsapp_number'] ) ? $art['whatsapp_number'] : $default_wa;
				?>
					<article class="wah-card">
						<div class="wah-card-header" style="border-top: 4px solid <?php echo $brand_col; ?>;">
							<span class="wah-card-provider" style="color: <?php echo $brand_col; ?>;"><?php echo $prov_name; ?> - <?php echo esc_html( $area_label ); ?></span>
							<span class="wah-card-domain">via <?php echo esc_html( $art['website_name'] ? $art['website_name'] : $art['domain'] ); ?></span>
						</div>

						<?php if ( ! empty( $art['featured_image'] ) ) : ?>
							<div class="wah-card-image">
								<img src="<?php echo esc_url( $art['featured_image'] ); ?>" alt="<?php echo esc_attr( $art['title'] ); ?>" loading="lazy" />
							</div>
						<?php else : ?>
							<div class="wah-card-banner-fallback" style="background: linear-gradient(135deg, <?php echo $brand_col; ?> 0%, #0f172a 100%);">
								<span>⚡ <?php echo $prov_name; ?></span>
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
								$wa_text      = urlencode( 'Halo, saya tertarik pasang paket WiFi ' . $prov_name );
								$final_link   = 'https://wa.me/' . $wa_num_clean . '?text=' . $wa_text;
							} else {
								$final_link = 'https://wa.me/?text=' . urlencode( 'Halo, saya mau pesan paket WiFi ' . $prov_name );
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
</div>

<?php get_footer(); ?>
