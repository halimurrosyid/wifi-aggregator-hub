/**
 * WiFi Aggregator Hub Frontend Live Search JS
 */
jQuery(document).ready(function($) {
	'use strict';

	var $input = $('#wah-search-input');
	var $dropdown = $('#wah-search-dropdown');
	var searchTimer = null;

	if (!$input.length) {
		return;
	}

	$input.on('keyup input', function() {
		var term = $(this).val().trim();

		clearTimeout(searchTimer);

		if (term.length < 2) {
			$dropdown.addClass('hidden').empty();
			return;
		}

		searchTimer = setTimeout(function() {
			$.ajax({
				url: wahPublic.ajax_url,
				type: 'GET',
				data: {
					action: 'wah_search_autocomplete',
					term: term
				},
				success: function(response) {
					$dropdown.empty();
					if (response.success && response.data.length > 0) {
						$.each(response.data, function(idx, item) {
							var $a = $('<a></a>')
								.addClass('wah-search-item')
								.attr('href', item.url)
								.text(item.label);
							$dropdown.append($a);
						});
						$dropdown.removeClass('hidden');
					} else {
						$dropdown.append('<div class="wah-search-item">Tidak ada wilayah / provider ditemukan.</div>').removeClass('hidden');
					}
				}
			});
		}, 250);
	});

	// Close dropdown when clicking outside
	$(document).on('click', function(e) {
		if (!$(e.target).closest('.wah-search-widget').length) {
			$dropdown.addClass('hidden');
		}
	});

	// Track Lead Clicks
	$(document).on('click', '.wah-btn', function() {
		var articleId = $(this).data('article-id');
		if (articleId) {
			$.post(wahPublic.ajax_url, {
				action: 'wah_track_click',
				article_id: articleId
			});
		}
	});
});
