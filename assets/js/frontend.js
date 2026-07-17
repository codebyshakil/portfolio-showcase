/*!
 * Portfolio Showcase — Frontend JS (matching filter-design-v2.html)
 */
( function () {
	'use strict';

	if ( typeof window.EPSW_Frontend === 'undefined' ) {
		return;
	}

	var CONFIG = window.EPSW_Frontend;

	function init() {
		var instances = document.querySelectorAll( '.epsw-portfolio' );
		instances.forEach( setupInstance );
	}

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

		setupDropdowns( root, state, grid );
		setupPopularChips( root, state, grid );
		setupPopularMore( root );
		setupPopularToggle( root );
		setupFilterButtons( root, state, grid );
		setupMobileCollapse( root );

		if ( loadMoreBtn ) {
			loadMoreBtn.addEventListener( 'click', function () {
				handleLoadMore( root, loadMoreBtn, state, grid );
			} );
		}
	}

	function splitPreset( value ) {
		if ( ! value ) {
			return [];
		}
		return value.split( ',' ).filter( Boolean );
	}

	function setupDropdowns( root, state, grid ) {
		var dropdowns = root.querySelectorAll( '.epsw-filter-dropdown' );
		var backdrop = document.getElementById( 'epsw-backdrop' ) || createBackdrop();

		dropdowns.forEach( function ( dropdown ) {
			var select = dropdown.querySelector( '.epsw-filter-select' );
			var menu = dropdown.querySelector( '.epsw-filter-menu' );
			var searchInput = menu ? menu.querySelector( '.epsw-search-box input' ) : null;
			var optList = menu ? menu.querySelector( '.epsw-opt-list' ) : null;
			var miniClear = menu ? menu.querySelector( '.epsw-mini-clear' ) : null;
			var miniApply = menu ? menu.querySelector( '.epsw-mini-apply' ) : null;
			var sheetClose = menu ? menu.querySelector( '.epsw-sheet-header .sx' ) : null;

			if ( ! select || ! menu ) {
				return;
			}

			select.addEventListener( 'click', function ( e ) {
				e.stopPropagation();
				toggleDropdown( dropdown, backdrop );
			} );

			if ( searchInput && optList ) {
				searchInput.addEventListener( 'input', function () {
					filterOptions( optList, searchInput.value );
				} );
			}

			var options = menu.querySelectorAll( '.epsw-filter-option' );
			options.forEach( function ( option ) {
				option.addEventListener( 'click', function () {
					selectOption( dropdown, option, select, state );
					state.paged = 1;
					fetchProjects( root, state, grid, false );
					updateActiveChips( root, state );
					closeAllDropdowns( backdrop );
				} );
			} );

			if ( miniClear ) {
				miniClear.addEventListener( 'click', function ( e ) {
					e.stopPropagation();
					clearDropdown( dropdown, select, state );
				} );
			}

			if ( miniApply ) {
				miniApply.addEventListener( 'click', function ( e ) {
					e.stopPropagation();
					closeAllDropdowns( backdrop );
				} );
			}

			if ( sheetClose ) {
				sheetClose.addEventListener( 'click', function ( e ) {
					e.stopPropagation();
					closeAllDropdowns( backdrop );
				} );
			}
		} );

		backdrop.addEventListener( 'click', function () {
			closeAllDropdowns( backdrop );
		} );

		document.addEventListener( 'click', function ( e ) {
			if ( ! e.target.closest( '.epsw-filter-dropdown' ) ) {
				closeAllDropdowns( backdrop );
			}
		} );
	}

	function createBackdrop() {
		var backdrop = document.createElement( 'div' );
		backdrop.id = 'epsw-backdrop';
		backdrop.className = 'epsw-backdrop';
		document.body.appendChild( backdrop );
		return backdrop;
	}

	function toggleDropdown( dropdown, backdrop ) {
		var isOpen = dropdown.classList.contains( 'is-open' );
		var root = dropdown.closest( '.epsw-portfolio' );
		closeAllDropdowns( backdrop );
		if ( ! isOpen ) {
			dropdown.classList.add( 'is-open' );
			if ( dropdown.closest( '.epsw-filter-section' ) ) {
				dropdown.closest( '.epsw-filter-section' ).classList.add( 'is-mobile-panel-open' );
			}
			if ( root ) {
				root.classList.add( 'epsw-filter-has-open' );
				root.dataset.mobileFilterOpen = dropdown.dataset.filterType || '';
			}
		}
	}

	function closeAllDropdowns( backdrop ) {
		document.querySelectorAll( '.epsw-filter-dropdown' ).forEach( function ( d ) {
			d.classList.remove( 'is-open' );
		} );
		document.querySelectorAll( '.epsw-filter-section.is-mobile-panel-open' ).forEach( function ( section ) {
			section.classList.remove( 'is-mobile-panel-open' );
		} );
		document.querySelectorAll( '.epsw-portfolio' ).forEach( function ( root ) {
			root.classList.remove( 'epsw-filter-has-open' );
			delete root.dataset.mobileFilterOpen;
		} );
		if ( backdrop ) {
			backdrop.classList.remove( 'show' );
		}
	}

	function filterOptions( optList, searchTerm ) {
		var options = optList.querySelectorAll( '.epsw-filter-option' );
		var hasResults = false;
		var term = searchTerm.toLowerCase();

		options.forEach( function ( option ) {
			var text = option.textContent.toLowerCase();
			if ( text.includes( term ) ) {
				option.style.display = '';
				hasResults = true;
			} else {
				option.style.display = 'none';
			}
		} );

		var noResults = optList.querySelector( '.epsw-no-results' );
		if ( ! hasResults && ! noResults ) {
			var div = document.createElement( 'div' );
			div.className = 'epsw-no-results';
			div.textContent = CONFIG.i18n.noResults || 'No results found';
			optList.appendChild( div );
		} else if ( hasResults && noResults ) {
			noResults.remove();
		}
	}

	function selectOption( dropdown, option, select, state ) {
		var filterType = dropdown.dataset.filterType;
		var slug = option.dataset.slug;
		var name = option.dataset.name || option.textContent.trim();

		var options = dropdown.querySelectorAll( '.epsw-filter-option' );
		options.forEach( function ( opt ) {
			opt.classList.remove( 'is-active' );
		} );
		option.classList.add( 'is-active' );

		var selectText = select.querySelector( '.epsw-filter-select-text' );
		if ( selectText ) {
			selectText.textContent = name;
		}

		if ( 'category' === filterType ) {
			state.categories = slug ? [ slug ] : [];
		} else if ( 'technology' === filterType ) {
			state.technologies = slug ? [ slug ] : [];
		}
	}

	function clearDropdown( dropdown, select, state ) {
		var filterType = dropdown.dataset.filterType;
		var options = dropdown.querySelectorAll( '.epsw-filter-option' );
		var firstOption = options[0];

		options.forEach( function ( opt ) {
			opt.classList.remove( 'is-active' );
		} );

		if ( firstOption ) {
			firstOption.classList.add( 'is-active' );
			var selectText = select.querySelector( '.epsw-filter-select-text' );
			if ( selectText ) {
				selectText.textContent = firstOption.dataset.name || firstOption.textContent.trim();
			}
		}

		if ( 'category' === filterType ) {
			state.categories = [];
		} else if ( 'technology' === filterType ) {
			state.technologies = [];
		}
	}

	function setupPopularChips( root, state, grid ) {
		var chips = root.querySelectorAll( '.epsw-popular-chip' );

		chips.forEach( function ( chip ) {
			chip.addEventListener( 'click', function () {
				var filterType = chip.dataset.filterType;
				var slug = chip.dataset.slug;

				if ( ! filterType || ! slug ) {
					return;
				}

				if ( 'category' === filterType ) {
					state.categories = [ slug ];
				} else if ( 'technology' === filterType ) {
					state.technologies = [ slug ];
				}

				state.paged = 1;
				fetchProjects( root, state, grid, false );

				updateActiveChips( root, state );
				updateDropdownSelections( root, state );
			} );
		} );
	}

	function setupPopularMore( root ) {
		var rows = root.querySelectorAll( '.epsw-popular-grid' );
		var resizeTimer = null;

		function getRowParts( row ) {
			var moreButton = row.querySelector( '.epsw-popular-chip-more' );
			if ( ! moreButton ) {
				return null;
			}

			return {
				row: row,
				type: moreButton.dataset.moreType,
				moreButton: moreButton,
				chips: Array.prototype.slice.call( row.querySelectorAll( '.epsw-popular-chip[data-filter-type]' ) ),
			};
		}

		function showChip( chip ) {
			chip.classList.remove( 'is-hidden' );
			chip.style.display = '';
		}

		function hideChip( chip ) {
			chip.classList.add( 'is-hidden' );
			chip.style.display = 'none';
		}

		function setExpanded( parts, expanded ) {
			parts.row.classList.toggle( 'is-expanded', expanded );
			parts.chips.forEach( showChip );
			parts.moreButton.classList.toggle( 'is-expanded', expanded );
			parts.moreButton.setAttribute( 'aria-expanded', expanded ? 'true' : 'false' );
			parts.moreButton.style.display = expanded ? 'none' : '';

			if ( ! expanded ) {
				collapseRow( parts );
			}
		}

		function collapseRow( parts ) {
			if ( window.innerWidth <= 780 ) {
				parts.chips.forEach( showChip );
				parts.moreButton.style.display = 'none';
				return;
			}

			parts.row.classList.remove( 'is-expanded' );
			parts.chips.forEach( showChip );
			parts.moreButton.style.display = 'none';

			if ( parts.chips.length < 2 ) {
				return;
			}

			var firstTop = parts.chips[0].offsetTop;
			var hasOverflow = parts.chips.some( function ( chip ) {
				return chip.offsetTop > firstTop;
			} );

			if ( ! hasOverflow ) {
				return;
			}

			parts.moreButton.style.display = '';
			parts.moreButton.classList.remove( 'is-expanded' );
			parts.moreButton.setAttribute( 'aria-expanded', 'false' );

			for ( var i = parts.chips.length - 1; i >= 0; i-- ) {
				if ( parts.moreButton.offsetTop <= firstTop ) {
					break;
				}

				hideChip( parts.chips[i] );
			}

			for ( var j = 0; j < parts.chips.length; j++ ) {
				if ( parts.chips[j].offsetTop > firstTop ) {
					hideChip( parts.chips[j] );
				}
			}

			if ( parts.chips.every( function ( chip ) { return chip.classList.contains( 'is-hidden' ); } ) ) {
				showChip( parts.chips[0] );
			}
		}

		function layoutRows() {
			rows.forEach( function ( row ) {
				var parts = getRowParts( row );
				if ( ! parts ) {
					return;
				}

				if ( parts.moreButton.getAttribute( 'aria-expanded' ) === 'true' ) {
					setExpanded( parts, true );
				} else {
					collapseRow( parts );
				}
			} );
		}

		rows.forEach( function ( row ) {
			var parts = getRowParts( row );
			if ( ! parts ) {
				return;
			}

			row.appendChild( parts.moreButton );
			parts.chips.forEach( showChip );

			parts.moreButton.addEventListener( 'click', function () {
				setExpanded( parts, parts.moreButton.getAttribute( 'aria-expanded' ) !== 'true' );
			} );
		} );

		root.querySelectorAll( '.epsw-popular-view-all' ).forEach( function ( button ) {
			button.addEventListener( 'click', function () {
				var row = root.querySelector( '.epsw-popular-chip-more[data-more-type="' + button.dataset.type + '"]' );
				var parts = row ? getRowParts( row.closest( '.epsw-popular-grid' ) ) : null;
				if ( parts ) {
					setExpanded( parts, parts.moreButton.getAttribute( 'aria-expanded' ) !== 'true' );
				}
			} );
		} );

		window.addEventListener( 'resize', function () {
			clearTimeout( resizeTimer );
			resizeTimer = setTimeout( layoutRows, 120 );
		} );

		requestAnimationFrame( layoutRows );
	}

	function setupPopularToggle( root ) {
		var toggle = root.querySelector( '.epsw-popular-toggle' );
		var popularSections = root.querySelector( '.epsw-popular-sections' );

		if ( ! toggle || ! popularSections ) {
			return;
		}

		toggle.addEventListener( 'click', function () {
			var isExpanded = toggle.getAttribute( 'aria-expanded' ) === 'true';
			toggle.setAttribute( 'aria-expanded', isExpanded ? 'false' : 'true' );
			root.classList.toggle( 'epsw-popular-collapsed', isExpanded );
			if ( toggle.closest( '.epsw-filter-desktop' ) ) {
				toggle.closest( '.epsw-filter-desktop' ).classList.toggle( 'epsw-popular-collapsed', isExpanded );
			}
		} );
	}

	function updateActiveChips( root, state ) {
		var chips = root.querySelectorAll( '.epsw-popular-chip' );
		chips.forEach( function ( chip ) {
			var filterType = chip.dataset.filterType;
			var slug = chip.dataset.slug;
			var isActive = false;

			if ( 'category' === filterType && state.categories.includes( slug ) ) {
				isActive = true;
			} else if ( 'technology' === filterType && state.technologies.includes( slug ) ) {
				isActive = true;
			}

			if ( isActive ) {
				chip.classList.add( 'active' );
			} else {
				chip.classList.remove( 'active' );
			}
		} );
	}

	function updateDropdownSelections( root, state ) {
		root.querySelectorAll( '.epsw-filter-dropdown' ).forEach( function ( dropdown ) {
			var filterType = dropdown.dataset.filterType;
			var targetSlug = '';

			if ( 'category' === filterType && state.categories.length > 0 ) {
				targetSlug = state.categories[0];
			} else if ( 'technology' === filterType && state.technologies.length > 0 ) {
				targetSlug = state.technologies[0];
			}

			var options = dropdown.querySelectorAll( '.epsw-filter-option' );
			var select = dropdown.querySelector( '.epsw-filter-select' );
			var selectText = select ? select.querySelector( '.epsw-filter-select-text' ) : null;

			options.forEach( function ( option ) {
				if ( option.dataset.slug === targetSlug ) {
					option.classList.add( 'is-active' );
					if ( selectText ) {
						selectText.textContent = option.dataset.name || option.textContent.trim();
					}
				} else {
					option.classList.remove( 'is-active' );
				}
			} );
		} );
	}

	function setupFilterButtons( root, state, grid ) {
		var applyBtn = root.querySelector( '.epsw-filter-btn-apply' );
		var clearBtn = root.querySelector( '.epsw-filter-btn-clear' );

		if ( applyBtn ) {
			applyBtn.addEventListener( 'click', function () {
				state.paged = 1;
				fetchProjects( root, state, grid, false );
			} );
		}

		if ( clearBtn ) {
			clearBtn.addEventListener( 'click', function () {
				state.categories = [];
				state.technologies = [];
				state.paged = 1;

				root.querySelectorAll( '.epsw-filter-dropdown' ).forEach( function ( dropdown ) {
					var select = dropdown.querySelector( '.epsw-filter-select' );
					clearDropdown( dropdown, select, state );
				} );

				fetchProjects( root, state, grid, false );
				updateActiveChips( root, state );
			} );
		}
	}

	function setupMobileCollapse( root ) {
		var collapseBtn = root.querySelector( '.epsw-filter-mobile-toggle' );
		var filterCard = root.querySelector( '.epsw-filter-desktop' );
		var backdrop = document.getElementById( 'epsw-backdrop' );

		if ( collapseBtn && filterCard ) {
			collapseBtn.addEventListener( 'click', function () {
				if ( root.classList.contains( 'epsw-filter-has-open' ) ) {
					closeAllDropdowns( backdrop );
					return;
				}
				filterCard.classList.toggle( 'collapsed' );
			} );
		}
	}

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
