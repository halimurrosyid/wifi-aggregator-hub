/**
 * WiFi Aggregator Hub Admin JS
 */
jQuery(document).ready(function($) {
	'use strict';

	// Sync All Feeds
	$('#wah-btn-sync-all').on('click', function(e) {
		e.preventDefault();
		var $btn = $(this);
		var $progress = $('#wah-sync-progress');
		var $progressBar = $progress.find('.wah-progress-bar');
		var $progressText = $progress.find('.wah-progress-text');

		$btn.prop('disabled', true).addClass('updating-message');
		$progress.removeClass('hidden');
		$progressBar.css('width', '20%');
		$progressText.text('Menghubungkan ke feed sources...');

		$.ajax({
			url: wahAdmin.ajax_url,
			type: 'POST',
			data: {
				action: 'wah_sync_now',
				nonce: wahAdmin.nonce
			},
			success: function(response) {
				$progressBar.css('width', '100%');
				if (response.success) {
					$progressText.text(response.data);
					setTimeout(function() {
						location.reload();
					}, 1500);
				} else {
					$progressText.text('Error: ' + response.data);
					$btn.prop('disabled', false).removeClass('updating-message');
				}
			},
			error: function() {
				$progressText.text('Terjadi kesalahan jaringan.');
				$btn.prop('disabled', false).removeClass('updating-message');
			}
		});
	});

	// Sync Single Feed
	$('.wah-btn-sync-single').on('click', function(e) {
		e.preventDefault();
		var $btn = $(this);
		var feedId = $btn.data('id');

		$btn.prop('disabled', true).text('Syncing...');

		$.ajax({
			url: wahAdmin.ajax_url,
			type: 'POST',
			data: {
				action: 'wah_sync_now',
				feed_id: feedId,
				nonce: wahAdmin.nonce
			},
			success: function(response) {
				if (response.success) {
					alert(response.data);
					location.reload();
				} else {
					alert('Gagal: ' + response.data);
					$btn.prop('disabled', false).text('Sync');
				}
			},
			error: function() {
				alert('Terjadi kesalahan koneksi.');
				$btn.prop('disabled', false).text('Sync');
			}
		});
	});

	// Check Broken Links
	$('#wah-btn-check-links').on('click', function(e) {
		e.preventDefault();
		var $btn = $(this);
		$btn.prop('disabled', true).text('Pemeriksaan Berjalan...');

		$.ajax({
			url: wahAdmin.ajax_url,
			type: 'POST',
			data: {
				action: 'wah_check_links',
				nonce: wahAdmin.nonce
			},
			success: function(response) {
				if (response.success) {
					alert(response.data);
					location.reload();
				} else {
					alert('Gagal: ' + response.data);
					$btn.prop('disabled', false).text('Cek Link Rusak / Noindex');
				}
			},
			error: function() {
				alert('Terjadi kesalahan koneksi.');
				$btn.prop('disabled', false).text('Cek Link Rusak / Noindex');
			}
		});
	});
});
