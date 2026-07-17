<?php
/**
 * Builds the full-site export JSON and restores data from an imported
 * JSON file: Projects, Categories, Technologies, Settings and Shortcodes.
 *
 * @package PortfolioShowcase
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class EPSW_Import_Export
 */
class EPSW_Import_Export {

	/**
	 * Export format version. Bump if the JSON shape changes in a way
	 * that requires import-time compatibility handling.
	 */
	const FORMAT_VERSION = 1;

	/**
	 * Builds the complete export array (ready for wp_json_encode).
	 *
	 * @return array
	 */
	public static function export_array() {
		return array(
			'plugin'         => 'portfolio-showcase',
			'format_version' => self::FORMAT_VERSION,
			'exported_at'    => gmdate( 'c' ),
			'site_url'       => home_url(),
			'settings'       => self::export_settings(),
			'categories'     => self::export_terms( 'epsw_category', true ),
			'technologies'   => self::export_terms( 'epsw_technology', true ),
			'projects'       => self::export_projects(),
			'shortcodes'     => self::export_shortcodes(),
		);
	}

	/**
	 * @return array
	 */
	private static function export_settings() {
		return get_option( 'epsw_settings', array() );
	}

	/**
	 * Exports all terms for a taxonomy, optionally including icon URLs.
	 *
	 * @param string $taxonomy    Taxonomy key.
	 * @param bool   $with_icon   Whether to include an `icon_url` field.
	 * @return array[]
	 */
	private static function export_terms( $taxonomy, $with_icon = false ) {
		$terms = get_terms( array( 'taxonomy' => $taxonomy, 'hide_empty' => false ) );

		if ( is_wp_error( $terms ) ) {
			return array();
		}

		$out = array();
		foreach ( $terms as $term ) {
			$row = array(
				'name' => $term->name,
				'slug' => $term->slug,
			);

			if ( $with_icon ) {
				$icon_id       = get_term_meta( $term->term_id, 'epsw_icon_id', true );
				$row['icon_url'] = $icon_id ? wp_get_attachment_url( $icon_id ) : '';
			}

			$out[] = $row;
		}

		return $out;
	}

	/**
	 * @return array[]
	 */
	private static function export_projects() {
		$posts = get_posts(
			array(
				'post_type'      => 'epsw_project',
				'post_status'    => array( 'publish', 'draft' ),
				'numberposts'    => -1,
				'orderby'        => 'menu_order',
				'order'          => 'ASC',
			)
		);

		$out = array();
		foreach ( $posts as $post ) {
			$data = EPSW_Helpers::get_project_data( $post );

			$out[] = array(
				'title'        => $data['title'],
				'description'  => $data['description'],
				'details_url'  => $data['details_url'],
				'demo_url'     => $data['demo_url'],
				'status'       => $data['status'] === 'publish' ? 'publish' : 'draft',
				'order'        => $data['order'],
				'featured'     => $data['featured'],
				'image_url'    => $data['image_url'] ? $data['image_url'] : '',
				'categories'   => wp_list_pluck( $data['categories'], 'slug' ),
				'technologies' => wp_list_pluck( $data['technologies'], 'slug' ),
			);
		}

		return $out;
	}

	/**
	 * @return array[]
	 */
	private static function export_shortcodes() {
		$rows = EPSW_Shortcode_Manager::get_all();
		$out  = array();

		foreach ( $rows as $row ) {
			// The default [estel_portfolio] entry is re-seeded automatically
			// on the target site's activation; skip it on export to avoid
			// creating a duplicate protected row on import.
			if ( '1' === (string) $row['is_default'] ) {
				continue;
			}

			$out[] = array(
				'label'        => $row['label'],
				'shortcode'    => $row['shortcode_text'],
				'categories'   => array_values( array_filter( explode( ',', $row['categories'] ) ) ),
				'technologies' => array_values( array_filter( explode( ',', $row['technologies'] ) ) ),
			);
		}

		return $out;
	}

	/**
	 * Validates and imports a decoded export array. Runs entirely inside
	 * a best-effort loop — a single bad row is skipped rather than
	 * aborting the whole import.
	 *
	 * @param array $data Decoded JSON (must already be an array, not raw string).
	 * @return array{
	 *     categories: int, technologies: int, projects: int,
	 *     shortcodes: int, settings: bool, errors: string[]
	 * }
	 */
	public static function import_array( array $data ) {
		$report = array(
			'categories'   => 0,
			'technologies' => 0,
			'projects'     => 0,
			'shortcodes'   => 0,
			'settings'     => false,
			'errors'       => array(),
		);

		if ( empty( $data['plugin'] ) || ! in_array( $data['plugin'], array( 'portfolio-showcase', 'estel-portfolio-showcase' ), true ) ) {
			$report['errors'][] = __( 'This file does not look like a Portfolio Showcase export.', 'portfolio-showcase' );
			return $report;
		}

		// Settings (merged, only known keys, never overwrites with junk).
		if ( ! empty( $data['settings'] ) && is_array( $data['settings'] ) ) {
			$current = get_option( 'epsw_settings', array() );
			$allowed = array( 'items_per_page', 'load_more_enabled', 'remove_data_on_uninstall' );

			foreach ( $allowed as $key ) {
				if ( isset( $data['settings'][ $key ] ) ) {
					$current[ $key ] = is_numeric( $data['settings'][ $key ] ) ? (int) $data['settings'][ $key ] : $data['settings'][ $key ];
				}
			}

			update_option( 'epsw_settings', $current );
			$report['settings'] = true;
		}

		// Categories (with optional icon sideload from icon_url).
		$category_slug_map = array();
		if ( ! empty( $data['categories'] ) && is_array( $data['categories'] ) ) {
			foreach ( $data['categories'] as $row ) {
				$term_id = self::import_term( $row, 'epsw_category' );
				if ( $term_id ) {
					if ( ! empty( $row['icon_url'] ) && ! get_term_meta( $term_id, 'epsw_icon_id', true ) ) {
						$attachment_id = self::sideload_image( $row['icon_url'] );
						if ( $attachment_id ) {
							update_term_meta( $term_id, 'epsw_icon_id', $attachment_id );
						}
					}
					$category_slug_map[ sanitize_title( $row['slug'] ?? $row['name'] ?? '' ) ] = $term_id;
					++$report['categories'];
				}
			}
		}

		// Technologies (with optional icon sideload from icon_url).
		$technology_slug_map = array();
		if ( ! empty( $data['technologies'] ) && is_array( $data['technologies'] ) ) {
			foreach ( $data['technologies'] as $row ) {
				$term_id = self::import_term( $row, 'epsw_technology' );
				if ( $term_id ) {
					if ( ! empty( $row['icon_url'] ) && ! get_term_meta( $term_id, 'epsw_icon_id', true ) ) {
						$attachment_id = self::sideload_image( $row['icon_url'] );
						if ( $attachment_id ) {
							update_term_meta( $term_id, 'epsw_icon_id', $attachment_id );
						}
					}
					$technology_slug_map[ sanitize_title( $row['slug'] ?? $row['name'] ?? '' ) ] = $term_id;
					++$report['technologies'];
				}
			}
		}

		// Projects.
		if ( ! empty( $data['projects'] ) && is_array( $data['projects'] ) ) {
			foreach ( $data['projects'] as $row ) {
				if ( self::import_project( $row ) ) {
					++$report['projects'];
				}
			}
		}

		// Saved shortcodes (skip exact duplicates already present).
		if ( ! empty( $data['shortcodes'] ) && is_array( $data['shortcodes'] ) ) {
			$existing = wp_list_pluck( EPSW_Shortcode_Manager::get_all(), 'shortcode_text' );

			foreach ( $data['shortcodes'] as $row ) {
				if ( empty( $row['shortcode'] ) || in_array( $row['shortcode'], $existing, true ) ) {
					continue;
				}

				$label = ! empty( $row['label'] ) ? sanitize_text_field( $row['label'] ) : __( 'Imported Shortcode', 'portfolio-showcase' );
				$cats  = isset( $row['categories'] ) ? array_map( 'sanitize_title', (array) $row['categories'] ) : array();
				$techs = isset( $row['technologies'] ) ? array_map( 'sanitize_title', (array) $row['technologies'] ) : array();

				if ( EPSW_Shortcode_Manager::create( $label, $cats, $techs ) ) {
					++$report['shortcodes'];
				}
			}
		}

		return $report;
	}

	/**
	 * Finds an existing term by slug or creates a new one.
	 *
	 * @param array  $row      Term row from the import file.
	 * @param string $taxonomy Taxonomy key.
	 * @return int Term ID, or 0 on failure.
	 */
	private static function import_term( $row, $taxonomy ) {
		if ( empty( $row['name'] ) ) {
			return 0;
		}

		$name = sanitize_text_field( $row['name'] );
		$slug = ! empty( $row['slug'] ) ? sanitize_title( $row['slug'] ) : sanitize_title( $name );

		$existing = get_term_by( 'slug', $slug, $taxonomy );
		if ( $existing && ! is_wp_error( $existing ) ) {
			return (int) $existing->term_id;
		}

		$result = wp_insert_term( $name, $taxonomy, array( 'slug' => $slug ) );

		if ( is_wp_error( $result ) ) {
			return 0;
		}

		return (int) $result['term_id'];
	}

	/**
	 * Creates a project post from an imported row.
	 *
	 * @param array $row Project row from the import file.
	 * @return bool
	 */
	private static function import_project( $row ) {
		if ( empty( $row['title'] ) ) {
			return false;
		}

		$post_id = wp_insert_post(
			array(
				'post_type'   => 'epsw_project',
				'post_title'  => sanitize_text_field( $row['title'] ),
				'post_status' => 'publish' === ( $row['status'] ?? 'draft' ) ? 'publish' : 'draft',
				'menu_order'  => isset( $row['order'] ) ? intval( $row['order'] ) : 0,
			),
			true
		);

		if ( is_wp_error( $post_id ) ) {
			return false;
		}

		update_post_meta( $post_id, '_epsw_description', isset( $row['description'] ) ? sanitize_textarea_field( $row['description'] ) : '' );
		update_post_meta( $post_id, '_epsw_details_url', isset( $row['details_url'] ) ? esc_url_raw( $row['details_url'] ) : '' );
		update_post_meta( $post_id, '_epsw_demo_url', isset( $row['demo_url'] ) ? esc_url_raw( $row['demo_url'] ) : '' );
		update_post_meta( $post_id, '_epsw_featured', ! empty( $row['featured'] ) ? '1' : '0' );

		if ( ! empty( $row['image_url'] ) ) {
			$attachment_id = self::sideload_image( $row['image_url'] );
			if ( $attachment_id ) {
				set_post_thumbnail( $post_id, $attachment_id );
			}
		}

		if ( ! empty( $row['categories'] ) && is_array( $row['categories'] ) ) {
			$term_ids = array();
			foreach ( $row['categories'] as $slug ) {
				$term = get_term_by( 'slug', sanitize_title( $slug ), 'epsw_category' );
				if ( $term && ! is_wp_error( $term ) ) {
					$term_ids[] = $term->term_id;
				}
			}
			wp_set_object_terms( $post_id, $term_ids, 'epsw_category', false );
		}

		if ( ! empty( $row['technologies'] ) && is_array( $row['technologies'] ) ) {
			$term_ids = array();
			foreach ( $row['technologies'] as $slug ) {
				$term = get_term_by( 'slug', sanitize_title( $slug ), 'epsw_technology' );
				if ( $term && ! is_wp_error( $term ) ) {
					$term_ids[] = $term->term_id;
				}
			}
			wp_set_object_terms( $post_id, $term_ids, 'epsw_technology', false );
		}

		return true;
	}

	/**
	 * Downloads a remote image URL (from the export file) into the media
	 * library using WordPress' own sideloading APIs. Only ever called
	 * from an admin-triggered, capability-checked import action.
	 *
	 * @param string $url Remote image URL.
	 * @return int Attachment ID, or 0 on failure.
	 */
	private static function sideload_image( $url ) {
		if ( empty( $url ) || ! wp_http_validate_url( $url ) ) {
			return 0;
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		$attachment_id = media_sideload_image( $url, 0, null, 'id' );

		if ( is_wp_error( $attachment_id ) ) {
			return 0;
		}

		return (int) $attachment_id;
	}
}
