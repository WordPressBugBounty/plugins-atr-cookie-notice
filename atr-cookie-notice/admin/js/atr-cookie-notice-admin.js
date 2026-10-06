(function( $ ) {
	'use strict';
	// Settings screen tabbed navigation behavior
	$(function(){
		var headings = $('.settings-container > h2, .settings-container > h3');
		var paragraphs = $('.settings-container > p');
		var tables = $('.settings-container > table');
		var triggers = $('.settings-tabs a');

		triggers.each(function(i){
			triggers.eq(i).on('click', function(e){
				e.preventDefault();
				triggers.removeClass('nav-tab-active');
				headings.hide();
				paragraphs.hide();
				tables.hide();

				triggers.eq(i).addClass('nav-tab-active');
				headings.eq(i).show();
				paragraphs.eq(i).show();
				tables.eq(i).show();
			});
		});

		if (triggers.length) {
			triggers.eq(0).trigger('click');
		}
	});

	// Docs page tabs behavior
	$(function(){
		var docLinks = $('.atr-scb-tab-link');
		var docContents = $('.atr-scb-tab-content');
		if (!docLinks.length) return;

		docLinks.on('click', function(e){
			e.preventDefault();
			docLinks.removeClass('active');
			docContents.removeClass('active');
			$(this).addClass('active');
			var target = $(this).attr('href');
			$(target).addClass('active');
		});

		// Ensure first tab is active on load
		docLinks.eq(0).trigger('click');
	});

	// Handle dismiss of the admin notice using localized data
	$(document).on('click', '#atr-cookie-notice-admin-notice .notice-dismiss', function(){
		if (typeof atrCookieNoticeAdmin !== 'undefined') {
			$.post(atrCookieNoticeAdmin.ajaxUrl, {
				action: 'atr_cookie_notice_dismiss_notice',
				nonce: atrCookieNoticeAdmin.dismissNonce
			});
		}
	});

	// Confirm before Reset Styling to defaults to prevent accidental reset
	$(document).on('click', '.atr-scb-reset-style-btn', function(e){
		var msg = (typeof atrCookieNoticeAdmin !== 'undefined' && atrCookieNoticeAdmin.resetStyleConfirm) ? atrCookieNoticeAdmin.resetStyleConfirm : 'Are you sure you want to reset all Styling & Appearance settings to their defaults? This cannot be undone.';
		if (!confirm(msg)) {
			e.preventDefault();
			return false;
		}
	});

	// Live preview for Styling & Appearance
	function atrScbCollectValue(id) {
		var el = document.getElementById(id);
		return el ? el.value : '';
	}
	function atrScbIsChecked(id) {
		var el = document.getElementById(id);
		return el ? el.checked : false;
	}
	function atrScbToPx(val, fallback) {
		var n = parseInt(val, 10);
		if (isNaN(n)) { return fallback; }
		return n + 'px';
	}
	function atrScbBuildPreviewVars() {
		// Colors
		var primary   = atrScbCollectValue('primary_color');
		var secondary = atrScbCollectValue('secondary_color');
		var text      = atrScbCollectValue('text_color');
		var bg        = atrScbCollectValue('background_color');
		var link      = atrScbCollectValue('link_color');
		// Buttons
		var pBtnBg    = atrScbCollectValue('primary_button_bg_color');
		var pBtnText  = atrScbCollectValue('primary_button_text_color');
		var sBtnBg    = atrScbCollectValue('secondary_button_bg_color');
		var sBtnText  = atrScbCollectValue('secondary_button_text_color');
		// Typography
		var fontFam   = atrScbCollectValue('font_family');
		var baseSize  = atrScbCollectValue('base_font_size');
		var fw        = atrScbCollectValue('font_weight');
		var btnFw     = atrScbCollectValue('button_font_weight');
		var upper     = atrScbIsChecked('uppercase_buttons');
		// Layout
		var radius    = atrScbCollectValue('border_radius');
		var maxW      = atrScbCollectValue('modal_max_width');
		var padY      = atrScbCollectValue('container_padding_y');
		var padX      = atrScbCollectValue('container_padding_x');
		var gap       = atrScbCollectValue('gap_between_elements');
		var dir       = atrScbCollectValue('layout_direction') || 'column';
		var align     = atrScbCollectValue('content_align') || 'left';

		var vars = [];
		if (primary)   vars.push('--scb-primary:' + primary);
		if (secondary) vars.push('--scb-secondary:' + secondary);
		if (text)      vars.push('--scb-text:' + text);
		if (bg)        vars.push('--scb-bg:' + bg);
		if (link)      vars.push('--scb-link:' + link);
		if (fontFam)   vars.push('--scb-font-family:' + fontFam);
		if (baseSize)  vars.push('--scb-font-size:' + parseInt(baseSize,10) + 'px');
		if (fw)        vars.push('--scb-font-weight:' + fw);
		if (btnFw)     vars.push('--scb-btn-weight:' + btnFw);
		if (upper)     vars.push('--scb-btn-transform:uppercase');
		if (radius)    vars.push('--scb-radius:' + atrScbToPx(radius, '8px'));
		if (maxW)      vars.push('--scb-modal-max:' + atrScbToPx(maxW, '420px'));
		if (padY)      vars.push('--scb-pad-y:' + atrScbToPx(padY, '16px'));
		if (padX)      vars.push('--scb-pad-x:' + atrScbToPx(padX, '20px'));
		if (gap)       vars.push('--scb-gap:' + atrScbToPx(gap, '12px'));
		if (dir)       vars.push('--scb-direction:' + dir);
		if (align)     vars.push('--scb-align:' + align);
		if (pBtnBg)    vars.push('--scb-primary-btn-bg:' + pBtnBg);
		if (pBtnText)  vars.push('--scb-primary-btn-text:' + pBtnText);
		if (sBtnBg)    vars.push('--scb-secondary-btn-bg:' + sBtnBg);
		if (sBtnText)  vars.push('--scb-secondary-btn-text:' + sBtnText);

		// Return both serialized and an array map for direct setProperty
		return {
			cssText: vars.join(';'),
			map: (function(){
				var m = {};
				vars.forEach(function(pair){
					var parts = pair.split(':');
					if (parts.length >= 2) {
						var key = parts.shift();
						var val = parts.join(':');
						m[key] = val;
					}
				});
				return m;
			})()
		};
	}
	function atrScbApplyPreview() {
		var styleEl = document.getElementById('atr-scb-preview-style');
		if (!styleEl) return;
		var result = atrScbBuildPreviewVars();
		var cssVars = result.cssText;
		var css = '';
		if (cssVars) {
			css += '.atr-scb-preview .scb-banner{' + cssVars + ';}';
		}
		styleEl.textContent = css;

		// Force override class
		var force = atrScbIsChecked('force_override_theme_styles');
		var banner = document.querySelector('.atr-scb-preview .scb-banner');
		if (banner) {
			banner.classList.toggle('scb-force', !!force);
			// Also set variables directly on the element style for immediate effect
			var map = result.map || {};
			Object.keys(map).forEach(function(k){
				try { banner.style.setProperty(k, map[k]); } catch(e) {}
			});
		}
	}
	$(function(){
		var previewBlock = $('.atr-scb-live-preview');
		var stickyClass = 'atr-scb-preview-sticky';

		function setPreviewSticky(isStylingTab) {
			if (isStylingTab) {
				previewBlock.addClass(stickyClass);
			} else {
				previewBlock.removeClass(stickyClass);
			}
		}

		function isStylingTabActive() {
			var href = $('.settings-tabs a.nav-tab-active').attr('href') || '';
			return href.indexOf('#styling') !== -1;
		}

		// When Styling tab is selected: make preview stick to top; otherwise normal flow
		$('.settings-tabs').on('click', 'a', function(){
			var targetHref = $(this).attr('href') || '';
			setTimeout(function(){
				setPreviewSticky(targetHref.indexOf('#styling') !== -1);
			}, 0);
		});
		// Initial state (default tab is first, so not Styling)
		setPreviewSticky(isStylingTabActive());

		// Apply initial
		atrScbApplyPreview();

		// Listen to changes in the settings form
		$('.settings-container').on('input change keyup paste', 'input, select, textarea', function(){
			atrScbApplyPreview();
		});

		// Design presets (populate fields on change)
		$('#design_preset').on('change', function(){
			var preset = $(this).val();
			function setVal(id, val){ var el = document.getElementById(id); if (el) { el.value = val; } }
			function setCheck(id, on){ var el = document.getElementById(id); if (el) { el.checked = !!on; } }
			if (!preset) { return; }
			switch (preset) {
				case 'light':
					setVal('background_color', '#ffffff');
					setVal('text_color', '#333333');
					setVal('primary_color', '#0b74de');
					setVal('link_color', '#0b74de');
					setVal('secondary_color', '#666666');
					setVal('primary_button_bg_color', '#0b74de');
					setVal('primary_button_text_color', '#ffffff');
					setVal('secondary_button_bg_color', '#f7f7f7');
					setVal('secondary_button_text_color', '#333333');
					setVal('overlay_color', '#000000');
					setVal('overlay_opacity', '0.35');
					setVal('shadow_preset', 'md');
					setCheck('uppercase_buttons', false);
					break;
				case 'dark':
					setVal('background_color', '#1f2937');
					setVal('text_color', '#f3f4f6');
					setVal('primary_color', '#2563eb');
					setVal('link_color', '#93c5fd');
					setVal('secondary_color', '#9ca3af');
					setVal('primary_button_bg_color', '#2563eb');
					setVal('primary_button_text_color', '#ffffff');
					setVal('secondary_button_bg_color', '#374151');
					setVal('secondary_button_text_color', '#f3f4f6');
					setVal('overlay_color', '#000000');
					setVal('overlay_opacity', '0.5');
					setVal('shadow_preset', 'lg');
					setCheck('uppercase_buttons', false);
					break;
				case 'minimal':
					setVal('background_color', '#ffffff');
					setVal('text_color', '#222222');
					setVal('primary_color', '#111827');
					setVal('link_color', '#111827');
					setVal('secondary_color', '#666666');
					setVal('primary_button_bg_color', '#ffffff');
					setVal('primary_button_text_color', '#111827');
					setVal('secondary_button_bg_color', '#ffffff');
					setVal('secondary_button_text_color', '#111827');
					setVal('overlay_color', '#000000');
					setVal('overlay_opacity', '0.2');
					setVal('shadow_preset', 'sm');
					setCheck('uppercase_buttons', false);
					break;
				case 'contrast':
					setVal('background_color', '#000000');
					setVal('text_color', '#ffffff');
					setVal('primary_color', '#ffcc00');
					setVal('link_color', '#ffcc00');
					setVal('secondary_color', '#ffffff');
					setVal('primary_button_bg_color', '#ffcc00');
					setVal('primary_button_text_color', '#000000');
					setVal('secondary_button_bg_color', '#000000');
					setVal('secondary_button_text_color', '#ffffff');
					setVal('overlay_color', '#000000');
					setVal('overlay_opacity', '0.6');
					setVal('shadow_preset', 'none');
					setCheck('uppercase_buttons', true);
					break;
			}
			// Trigger input event so any listeners update immediately
			$('#background_color,#text_color,#primary_color,#link_color,#secondary_color,#primary_button_bg_color,#primary_button_text_color,#secondary_button_bg_color,#secondary_button_text_color,#overlay_color').trigger('input');
			atrScbApplyPreview();
			// no reposition
		});

		// Initialize WP color picker on color inputs
		if ($.fn.wpColorPicker) {
			$('.color-picker').wpColorPicker({
				change: function(event, ui) {
					// Reflect value to the input and update preview
					$(event.target).val(ui.color.toString());
					$(event.target).trigger('input');
					atrScbApplyPreview();
					// no reposition
				},
				clear: function() {
					$(this).trigger('input');
					atrScbApplyPreview();
					// no reposition
				}
			});
		}
	});

})( jQuery );
