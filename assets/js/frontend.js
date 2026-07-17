/*!
 * Estel Portfolio Showcase — Frontend JS (vanilla, no dependencies)
 */
( function () {
	'use strict';

	if ( typeof window.EPSW_Frontend === 'undefined' ) {
		return;
	}

	var CONFIG = window.EPSW_Frontend;

	/**
	 * Initializes every [estel_portfolio] instance on the page.
	 */
	function init() {
		var instances = document.querySelectorAll( '.epsw-portfolio' );
		instances.forEach( setupInstance );
	}

	/**
	 * Wires up filter chips + load more button for one shortcode instance.
	 *
	 * @param {HTMLElement} root Root .epsw-portfolio element.
	 */
	function setupInstance( root ) {
		var state = {
			categories: splitPreset( root.dataset.presetCategories ),
			technologies: splitPreset( root.dataset.presetTechnologies ),
			columns: parseInt( root.dataset.columns, 10 ) || 3,
			perPage: parseInt( root.dataset.perPage, 10 ) || 9,
			paged: 1,
			maxPages: 1,
		};

		var grid = root.querySelector( '.epsw-grid' );
		var loadMoreBtn = root.querySelector( '.epsw-load-more' );

		if ( loadMoreBtn ) {
			state.maxPages = parseInt( loadMoreBtn.dataset.maxPages, 10 ) || 1;
			state.paged = parseInt( loadMoreBtn.dataset.page, 10 ) || 1;
		}

		root.querySelectorAll( '.epsw-filter-chip' ).forEach( function ( chip ) {
			chip.addEventListener( 'click', function () {
				handleChipClick( root, chip, state, grid );
			} );
		} );

		if ( loadMoreBtn ) {
			loadMoreBtn.addEventListener( 'click', function () {
				handleLoadMore( root, loadMoreBtn, state, grid );
			} );
		}
	}

	/**
	 * Splits a comma separated preset attribute into an array, ignoring
	 * empty strings.
	 *
	 * @param {string} value Raw dataset value.
	 * @return {string[]}
	 */
	function splitPreset( value ) {
		if ( ! value ) {
			return [];
		}
		return value.split( ',' ).filter( Boolean );
	}

	/**
	 * Handles a filter chip click: toggles active state within its group
	 * and triggers a fresh AJAX fetch of page 1.
	 *
	 * @param {HTMLElement} root  Instance root.
	 * @param {HTMLElement} chip  Clicked chip button.
	 * @param {Object}      state Instance state object.
	 * @param {HTMLElement} grid  Grid element to update.
	 */
	function handleChipClick( root, chip, state, grid ) {
		var group = chip.closest( '.epsw-filter-group' );
		var filterType = group.dataset.filterType;
		var slug = chip.dataset.slug;

		group.querySelectorAll( '.epsw-filter-chip' ).forEach( function ( el ) {
			el.classList.remove( 'is-active' );
		} );
		chip.classList.add( 'is-active' );

		if ( 'category' === filterType ) {
			state.categories = slug ? [ slug ] : [];
		} else if ( 'technology' === filterType ) {
			state.technologies = slug ? [ slug ] : [];
		}

		state.paged = 1;
		fetchProjects( root, state, grid, false );
	}

	/**
	 * Handles the Load More button: fetches the next page and appends
	 * results instead of replacing the grid.
	 *
	 * @param {HTMLElement} root Instance root.
	 * @param {HTMLElement} btn  Load more button.
	 * @param {Object}      state Instance state.
	 * @param {HTMLElement} grid Grid element.
	 */
	function handleLoadMore( root, btn, state, grid ) {
		if ( btn.classList.contains( 'is-loading' ) ) {
			return;
		}

		state.paged += 1;
		btn.classList.add( 'is-loading' );

		fetchProjects( root, state, grid, true, function () {
			btn.classList.remove( 'is-loading' );

			if ( state.paged >= state.maxPages ) {
				btn.parentElement.remove();
			} else {
				btn.dataset.page = String( state.paged );
			}
		} );
	}

	/**
	 * Performs the AJAX request to fetch filtered/paginated projects.
	 *
	 * @param {HTMLElement}  root   Instance root.
	 * @param {Object}       state  Instance state.
	 * @param {HTMLElement}  grid   Grid element to update.
	 * @param {boolean}      append Whether to append (Load More) or replace (filter change).
	 * @param {Function}     [done] Optional callback invoked after completion.
	 */
	function fetchProjects( root, state, grid, append, done ) {
		if ( ! append ) {
			grid.classList.add( 'is-filtering' );
		}

		var formData = new FormData();
		formData.append( 'action', 'epsw_filter_projects' );
		formData.append( 'nonce', CONFIG.nonce );
		formData.append( 'columns', state.columns );
		formData.append( 'per_page', state.perPage );
		formData.append( 'paged', state.paged );

		state.categories.forEach( function ( slug ) {
			formData.append( 'categories[]', slug );
		} );
		state.technologies.forEach( function ( slug ) {
			formData.append( 'technologies[]', slug );
		} );

		fetch( CONFIG.ajax_url, {
			method: 'POST',
			credentials: 'same-origin',
			body: formData,
		} )
			.then( function ( response ) {
				return response.json();
			} )
			.then( function ( response ) {
				if ( ! response || ! response.success ) {
					return;
				}

				var data = response.data;
				state.maxPages = data.max_pages;

				if ( append ) {
					grid.insertAdjacentHTML( 'beforeend', data.html );
				} else {
					grid.innerHTML = data.html || '<p class="epsw-no-results">' + CONFIG.i18n.noResults + '</p>';
				}

				grid.classList.remove( 'is-filtering' );

				if ( typeof done === 'function' ) {
					done();
				}
			} )
			.catch( function () {
				grid.classList.remove( 'is-filtering' );
				if ( typeof done === 'function' ) {
					done();
				}
			} );
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )();
