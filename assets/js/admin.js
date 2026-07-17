/*!
 * Portfolio Showcase — Admin JS
 */
( function ( $ ) {
	'use strict';

	if ( typeof window.EPSW_Admin === 'undefined' ) {
		return;
	}

	var CONFIG = window.EPSW_Admin;

	$( function () {
		initProjectImagePicker();
		initTechnologyIconPicker();
		initShortcodeBuilder();
		initCopyButtons();
		initTermSorting();
	} );

	/**
	 * Wires the WordPress Media modal to the project image field.
	 */
	function initProjectImagePicker() {
		var $wrapper = $( '#epsw-project-image-picker' );
		if ( ! $wrapper.length || typeof wp === 'undefined' || ! wp.media ) {
			return;
		}

		var frame;
		var $input   = $( '#epsw-image-id' );
		var $preview = $( '#epsw-image-preview' );
		var $selectBtn = $wrapper.find( '.epsw-select-image-btn' );
		var $removeBtn = $wrapper.find( '.epsw-remove-image-btn' );

		$selectBtn.on( 'click', function ( e ) {
			e.preventDefault();

			if ( frame ) {
				frame.open();
				return;
			}

			frame = wp.media( {
				title: $selectBtn.data( 'title' ) || 'Select Project Image',
				multiple: false,
				library: { type: 'image' },
			} );

			frame.on( 'select', function () {
				var attachment = frame.state().get( 'selection' ).first().toJSON();
				$input.val( attachment.id );
				$preview.html( '<img src="' + attachment.url + '" alt="" />' );
				$selectBtn.text( 'Change Image' );
				$removeBtn.show();
			} );

			frame.open();
		} );

		$removeBtn.on( 'click', function ( e ) {
			e.preventDefault();
			$input.val( '' );
			$preview.empty();
			$selectBtn.text( 'Select Image' );
			$removeBtn.hide();
		} );
	}

	/**
	 * Wires the WordPress Media modal to the technology icon field.
	 */
	function initTechnologyIconPicker() {
		$( document ).on( 'click', '.epsw-media-upload-btn', function ( e ) {
			e.preventDefault();

			var $btn = $( this );
			var $input = $( '#' + $btn.data( 'target-id' ) );
			var $preview = $( '#' + $btn.data( 'preview-id' ) );

			if ( typeof wp === 'undefined' || ! wp.media ) {
				alert( 'WordPress media library is not available.' );
				return;
			}

			var frame = wp.media( {
				title: 'Select Technology Icon',
				multiple: false,
				library: {
					type: 'image'
				}
			} );

			frame.on( 'select', function () {
				var attachment = frame.state().get( 'selection' ).first().toJSON();
				$input.val( attachment.id );
				$preview.html( '<img src="' + attachment.url + '" alt="" style="max-width: 100px; max-height: 100px; display: block; margin-bottom: 10px;" />' );
				$btn.text( 'Change Icon' );
				
				// Show remove button if it exists
				var $removeBtn = $btn.siblings( '.epsw-media-remove-btn' );
				if ( $removeBtn.length ) {
					$removeBtn.show();
				} else {
					// Create remove button if it doesn't exist
					$btn.after( ' <button type="button" class="epsw-btn-admin epsw-btn-admin-ghost epsw-media-remove-btn" data-target-id="' + $btn.data( 'target-id' ) + '" data-preview-id="' + $btn.data( 'preview-id' ) + '" style="margin-left: 10px;">Remove Icon</button>' );
				}
			} );

			frame.open();
		} );

		// Handle remove button
		$( document ).on( 'click', '.epsw-media-remove-btn', function ( e ) {
			e.preventDefault();

			var $btn = $( this );
			var $input = $( '#' + $btn.data( 'target-id' ) );
			var $preview = $( '#' + $btn.data( 'preview-id' ) );
			var $uploadBtn = $btn.siblings( '.epsw-media-upload-btn' );

			$input.val( '' );
			$preview.empty();
			$uploadBtn.text( 'Upload Icon' );
			$btn.hide();
		} );
	}

	/**
	 * Keeps the live shortcode preview text in sync with checked
	 * category/technology checkboxes, and wires up the Preview button.
	 */
	function initShortcodeBuilder() {
		var $form = $( '#epsw-shortcode-form' );
		if ( ! $form.length ) {
			return;
		}

		var $liveText = $( '#epsw-live-shortcode' );
		var $previewBtn = $( '#epsw-preview-shortcode-btn' );
		var $previewResult = $( '#epsw-shortcode-preview-result' );

		function buildShortcodeText() {
			var cats = $form.find( '.epsw-sc-category:checked' ).map( function () {
				return this.value;
			} ).get();

			var techs = $form.find( '.epsw-sc-technology:checked' ).map( function () {
				return this.value;
			} ).get();

			var atts = [];
			if ( cats.length ) {
				atts.push( 'category="' + cats.join( ',' ) + '"' );
			}
			if ( techs.length ) {
				atts.push( 'technology="' + techs.join( ',' ) + '"' );
			}

			return atts.length ? '[estel_portfolio ' + atts.join( ' ' ) + ']' : '[estel_portfolio]';
		}

		$form.on( 'change', '.epsw-sc-category, .epsw-sc-technology', function () {
			$liveText.text( buildShortcodeText() );
		} );

		$previewBtn.on( 'click', function () {
			var cats = $form.find( '.epsw-sc-category:checked' ).map( function () {
				return this.value;
			} ).get();
			var techs = $form.find( '.epsw-sc-technology:checked' ).map( function () {
				return this.value;
			} ).get();

			$previewResult.html( '<p>Loading preview…</p>' );

			$.ajax( {
				url: CONFIG.ajax_url,
				type: 'POST',
				data: {
					action: 'epsw_preview_shortcode',
					nonce: CONFIG.nonce,
					categories: cats,
					technologies: techs,
				},
			} )
				.done( function ( response ) {
					if ( response && response.success ) {
						$previewResult.html( response.data.html );
					} else {
						$previewResult.html( '<p>Preview failed.</p>' );
					}
				} )
				.fail( function () {
					$previewResult.html( '<p>Preview failed.</p>' );
				} );
		} );
	}

	/**
	 * Wires "Copy" buttons on the Shortcodes page to the clipboard API.
	 */
	function initCopyButtons() {
		$( document ).on( 'click', '.epsw-copy-shortcode', function () {
			var $btn = $( this );
			var text = $btn.data( 'shortcode' );

			if ( navigator.clipboard && navigator.clipboard.writeText ) {
				navigator.clipboard.writeText( text ).then( function () {
					var original = $btn.text();
					$btn.text( CONFIG.i18n.copied );
					setTimeout( function () {
						$btn.text( original );
					}, 1500 );
				} );
			}
		} );
	}
	/**
	 * Enables drag-and-drop row reordering on the Categories and
	 * Technologies tables (jQuery UI Sortable, bundled with WordPress
	 * core) and saves the new order via AJAX after each drop.
	 */
	function initTermSorting() {
		$( '.epsw-sortable-rows' ).each( function () {
			var $tbody = $( this );
			var taxonomy = $tbody.closest( 'table' ).data( 'taxonomy' );

			if ( ! taxonomy || typeof $tbody.sortable !== 'function' ) {
				return;
			}

			$tbody.sortable( {
				items: 'tr',
				handle: '.epsw-drag-handle',
				axis: 'y',
				opacity: 0.7,
				helper: function ( e, tr ) {
					// Keep column widths consistent while dragging.
					var $originals = tr.children();
					var $helper = tr.clone();
					$helper.children().each( function ( index ) {
						$( this ).width( $originals.eq( index ).width() );
					} );
					return $helper;
				},
				update: function () {
					var order = $tbody.find( 'tr' ).map( function () {
						return $( this ).data( 'term-id' );
					} ).get();

					$.post( CONFIG.ajax_url, {
						action: 'epsw_reorder_terms',
						nonce: CONFIG.nonce,
						taxonomy: taxonomy,
						order: order,
					} );
				},
			} );
		} );
	}
} )( jQuery );
