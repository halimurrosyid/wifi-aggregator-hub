/**
 * WiFi Aggregator Hub Admin JS
 */
jQuery(document).ready(function($) {
	'use strict';

	// Sync All Feeds (Dashboard Button + Sync View Button)
	$(document).on('click', '#wah-btn-sync-all, .wah-sync-btn', function(e) {
		e.preventDefault();
		var $btn = $(this);
		var originalHtml = $btn.html();
		var $progress = $('#wah-sync-progress');
		var $progressBar = $progress.find('.wah-progress-bar');
		var $progressText = $progress.find('.wah-progress-text');

		$btn.prop('disabled', true).addClass('updating-message').html('<span class="dashicons dashicons-update spin" style="margin-top:4px;"></span> Menyinkronkan...');
		if ($progress.length) {
			$progress.removeClass('hidden');
			$progressBar.css('width', '20%');
			$progressText.text('Menghubungkan ke feed sources & sitemaps...');
		}

		$.ajax({
			url: wahAdmin.ajax_url,
			type: 'POST',
			data: {
				action: 'wah_sync_now',
				nonce: wahAdmin.nonce
			},
			success: function(response) {
				if ($progressBar.length) {
					$progressBar.css('width', '100%');
					$progressText.text(response.data);
				}
				if (response.success) {
					alert(response.data || 'Sinkronisasi feed berhasil diselesaikan!');
					location.reload();
				} else {
					alert('Gagal Sync: ' + response.data);
					$btn.prop('disabled', false).removeClass('updating-message').html(originalHtml);
				}
			},
			error: function() {
				alert('Terjadi kesalahan jaringan saat melakukan sinkronisasi.');
				$btn.prop('disabled', false).removeClass('updating-message').html(originalHtml);
			}
		});
	});

	// Sync Single Feed
	$(document).on('click', '.wah-btn-sync-single', function(e) {
		e.preventDefault();
		var $btn = $(this);
		var feedId = $btn.data('id');
		var originalText = $btn.text();

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
					$btn.prop('disabled', false).text(originalText);
				}
			},
			error: function() {
				alert('Terjadi kesalahan koneksi.');
				$btn.prop('disabled', false).text(originalText);
			}
		});
	});

	// Check Broken Links
	$(document).on('click', '#wah-btn-check-links', function(e) {
		e.preventDefault();
		var $btn = $(this);
		var originalText = $btn.text();
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
					$btn.prop('disabled', false).text(originalText);
				}
			},
			error: function() {
				alert('Terjadi kesalahan koneksi.');
				$btn.prop('disabled', false).text(originalText);
			}
		});
	});
});
