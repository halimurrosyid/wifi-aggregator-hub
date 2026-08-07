<?php
/**
 * Frontend Public Controller.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WAH_Public {

	/**
	 * Enqueue frontend scripts & styles.
	 */
	public function enqueue_styles_and_scripts() {
		wp_enqueue_style( 'wah-public-css', WAH_PLUGIN_URL . 'public/assets/css/public.css', array(), WAH_VERSION );
		wp_enqueue_script( 'wah-public-search-js', WAH_PLUGIN_URL . 'public/assets/js/public-search.js', array( 'jquery' ), WAH_VERSION, true );

		wp_localize_script(
			'wah-public-search-js',
			'wahPublic',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'site_url' => home_url( '/' ),
			)
		);
	}

	/**
	 * AJAX Autocomplete Search Endpoint.
	 */
	public function ajax_search_autocomplete() {
		$term = isset( $_GET['term'] ) ? sanitize_text_field( $_GET['term'] ) : '';

		if ( mb_strlen( $term ) < 2 ) {
			wp_send_json_success( array() );
		}

		$db    = WAH_DB::get_instance();
		$areas = $db->search_areas( $term, 10 );

		$results = array();
		foreach ( $areas as $area ) {
			$results[] = array(
				'type'  => 'area',
				'label' => $area['name'] . ( $area['province_name'] ? ' (' . $area['province_name'] . ')' : '' ),
				'url'   => home_url( '/wifi-' . $area['slug'] . '/' ),
			);
		}

		// Also search providers
		$providers = $db->get_providers();
		foreach ( $providers as $prov ) {
			if ( false !== mb_strpos( mb_strtolower( $prov['name'] ), mb_strtolower( $term ) ) ) {
				$results[] = array(
					'type'  => 'provider',
					'label' => 'Provider: ' . $prov['name'],
					'url'   => home_url( '/provider/' . $prov['slug'] . '/' ),
				);
			}
		}

		wp_send_json_success( $results );
	}

	/**
	 * Render [wifi_search_box] shortcode.
	 */
	public function render_search_shortcode() {
		ob_start();
		?>
		<div class="wah-search-widget">
			<div class="wah-search-box-wrapper">
				<span class="wah-search-icon">🔍</span>
				<input type="text" id="wah-search-input" placeholder="Cari kota atau provider internet (Contoh: Bandung, ICONNET)..." autocomplete="off" />
				<div id="wah-search-dropdown" class="wah-search-results-dropdown hidden"></div>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render [wifi_area_grid] shortcode.
	 */
	public function render_area_grid_shortcode( $atts ) {
		$atts  = shortcode_atts( array( 'limit' => 12 ), $atts );
		$db    = WAH_DB::get_instance();
		$areas = array_slice( $db->get_active_landing_areas(), 0, intval( $atts['limit'] ) );

		ob_start();
		?>
		<div class="wah-area-grid-widget">
			<div class="wah-related-links">
				<?php foreach ( $areas as $area ) : ?>
					<a href="<?php echo esc_url( home_url( '/wifi-' . $area['slug'] . '/' ) ); ?>" class="wah-related-chip">
						📍 Pasang WiFi <?php echo esc_html( $area['name'] ); ?>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render [wifi_provider_grid] shortcode.
	 */
	public function render_provider_grid_shortcode( $atts ) {
		$db        = WAH_DB::get_instance();
		$providers = $db->get_providers();

		ob_start();
		?>
		<div class="wah-provider-grid-widget">
			<div class="wah-provider-tags">
				<?php foreach ( $providers as $prov ) : ?>
					<a href="<?php echo esc_url( home_url( '/provider/' . $prov['slug'] . '/' ) ); ?>" class="wah-provider-tag" style="border-left: 4px solid <?php echo esc_attr( $prov['brand_color'] ); ?>;">
						⚡ <?php echo esc_html( $prov['name'] ); ?>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * AJAX endpoint to track lead clicks.
	 */
	public function ajax_track_click() {
		$article_id = isset( $_POST['article_id'] ) ? intval( $_POST['article_id'] ) : 0;
		if ( $article_id > 0 ) {
			$db = WAH_DB::get_instance();
			$db->increment_article_clicks( $article_id );
			wp_send_json_success( 'Click logged.' );
		}
		wp_send_json_error( 'Invalid ID.' );
	}
}
