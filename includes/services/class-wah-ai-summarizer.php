<?php
/**
 * Smart AI Summarizer Service for ISP Landing Pages & Articles.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WAH_AI_Summarizer {

	/**
	 * Generate a 100-150 word summary.
	 *
	 * @param string $title Article title.
	 * @param string $excerpt Article excerpt.
	 * @param int $provider_id Provider ID.
	 * @param int $area_id Area ID.
	 * @return string Summarized text.
	 */
	public static function generate( $title, $excerpt, $provider_id = 0, $area_id = 0 ) {
		$db            = WAH_DB::get_instance();
		$provider_name = 'Provider Internet';
		$area_name     = 'Indonesia';

		if ( $provider_id ) {
			$prov = $db->get_provider( $provider_id );
			if ( $prov ) {
				$provider_name = $prov['name'];
			}
		}

		if ( $area_id ) {
			$area = $db->get_area( $area_id );
			if ( $area ) {
				$area_name = $area['name'];
			}
		}

		$clean_excerpt = wp_strip_all_tags( $excerpt );
		if ( empty( $clean_excerpt ) ) {
			$clean_excerpt = "Informasi lengkap pendaftaran dan pemasangan layanan $provider_name untuk wilayah $area_name.";
		}

		$template = "Layanan internet wifi dari $provider_name kini hadir melayani kebutuhan koneksi cepat dan stabil di wilayah $area_name. "
			. "Berdasarkan ulasan informasi '$title', calon pelanggan di $area_name dapat menikmati paket kuota unlimited dengan kecepatan tinggi serta fasilitas instalasi resmi. "
			. "$clean_excerpt "
			. "Untuk mendapatkan informasi harga promo terbaru, syarat registrasi, dan bantuan cek jangkauan jaringan lokasi rumah atau kantor Anda di $area_name, silakan akses halaman resmi atau hubungi kontak layanan yang tersedia.";

		// Trim to ~120-140 words
		$words = explode( ' ', $template );
		if ( count( $words ) > 150 ) {
			$words = array_slice( $words, 0, 140 );
			return implode( ' ', $words ) . '...';
		}

		return implode( ' ', $words );
	}
}
