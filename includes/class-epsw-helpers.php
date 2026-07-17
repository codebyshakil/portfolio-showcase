<?php
/**
 * Shared static helper functions.
 *
 * @package PortfolioShowcase
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class EPSW_Helpers
 */
class EPSW_Helpers {

	/**
	 * Returns a fully assembled, escaped-on-output-ready data array for a
	 * single project post, used by both the admin list table and the
	 * frontend card template.
	 *
	 * @param int|WP_Post $post Project post object or ID.
	 * @return array|null
	 */
	public static function get_project_data( $post ) {
		$post = get_post( $post );

		if ( ! $post || 'epsw_project' !== $post->post_type ) {
			return null;
		}

		$categories   = wp_get_post_terms( $post->ID, 'epsw_category' );
		$technologies = wp_get_post_terms( $post->ID, 'epsw_technology' );

		$tech_list = array();
		if ( ! is_wp_error( $technologies ) ) {
			foreach ( $technologies as $tech ) {
				$tech_list[] = array(
					'id'       => $tech->term_id,
					'name'     => $tech->name,
					'slug'     => $tech->slug,
					'icon_url' => self::get_technology_icon_url( $tech->term_id ),
				);
			}
		}

		$cat_list = array();
		if ( ! is_wp_error( $categories ) ) {
			foreach ( $categories as $cat ) {
				$cat_list[] = array(
					'id'       => $cat->term_id,
					'name'     => $cat->name,
					'slug'     => $cat->slug,
					'icon_url' => self::get_technology_icon_url( $cat->term_id ),
				);
			}
		}

		return array(
			'id'           => $post->ID,
			'title'        => get_the_title( $post ),
			'description'  => get_post_meta( $post->ID, '_epsw_description', true ),
			'details_url'  => get_post_meta( $post->ID, '_epsw_details_url', true ),
			'demo_url'     => get_post_meta( $post->ID, '_epsw_demo_url', true ),
			'status'       => 'publish' === $post->post_status ? 'publish' : 'hidden',
			'order'        => (int) $post->menu_order,
			'featured'     => '1' === get_post_meta( $post->ID, '_epsw_featured', true ),
			'date'         => get_the_date( 'Y-m-d', $post ),
			'image_id'     => get_post_thumbnail_id( $post ),
			'image_url'    => get_the_post_thumbnail_url( $post, 'large' ),
			'categories'   => $cat_list,
			'technologies' => $tech_list,
		);
	}

	/**
	 * Returns the icon URL stored in term meta for a technology term.
	 *
	 * @param int $term_id Technology term ID.
	 * @return string
	 */
	public static function get_technology_icon_url( $term_id ) {
		$icon_id = get_term_meta( $term_id, 'epsw_icon_id', true );

		if ( ! $icon_id ) {
			return '';
		}

		$url = wp_get_attachment_url( $icon_id );

		return $url ? $url : '';
	}

	/**
	 * Whitelists the file types allowed for technology icon uploads.
	 *
	 * @return array<string,string> Extension => mime type.
	 */
	public static function allowed_icon_mimes() {
		return array(
			'svg'  => 'image/svg+xml',
			'png'  => 'image/png',
			'jpg'  => 'image/jpeg',
			'jpeg' => 'image/jpeg',
			'webp' => 'image/webp',
		);
	}

	/**
	 * Strips potentially dangerous content (scripts, event handler
	 * attributes, external references) from an uploaded SVG string
	 * before it is written to disk.
	 *
	 * @param string $svg_content Raw SVG markup.
	 * @return string|false Sanitized SVG, or false if it looks invalid/unsafe.
	 */
	public static function sanitize_svg( $svg_content ) {
		if ( false !== stripos( $svg_content, '<script' ) ) {
			return false;
		}
		if ( false !== stripos( $svg_content, 'javascript:' ) ) {
			return false;
		}
		if ( false !== stripos( $svg_content, '<!ENTITY' ) ) {
			return false;
		}
		if ( false !== stripos( $svg_content, '<?php' ) ) {
			return false;
		}

		libxml_use_internal_errors( true );
		$dom = new DOMDocument();
		$loaded = $dom->loadXML( $svg_content, LIBXML_NONET | LIBXML_NOENT );
		libxml_clear_errors();

		if ( ! $loaded ) {
			return false;
		}

		if ( 'svg' !== strtolower( $dom->documentElement->nodeName ) ) {
			return false;
		}

		// Remove on* event handler attributes recursively.
		$xpath = new DOMXPath( $dom );
		$nodes = $xpath->query( '//@*' );
		foreach ( $nodes as $attr ) {
			if ( 0 === stripos( $attr->nodeName, 'on' ) ) {
				$attr->ownerElement->removeAttribute( $attr->nodeName );
			}
		}

		// Remove <script> and <foreignObject> nodes if any slipped through.
		foreach ( array( 'script', 'foreignObject' ) as $tag ) {
			$elements = $dom->getElementsByTagName( $tag );
			while ( $elements->length > 0 ) {
				$el = $elements->item( 0 );
				$el->parentNode->removeChild( $el );
			}
		}

		return $dom->saveXML( $dom->documentElement );
	}

	/**
	 * Ensures every term in a taxonomy has an `epsw_order` meta value,
	 * backfilling sequential values for any that don't. Called before
	 * any ordered fetch so drag-to-sort always has something reliable
	 * to sort against, even for terms created before this feature or
	 * imported from another site.
	 *
	 * @param string $taxonomy Taxonomy key.
	 */
	public static function ensure_term_order_meta( $taxonomy ) {
		$term_ids = get_terms(
			array(
				'taxonomy'   => $taxonomy,
				'hide_empty' => false,
				'fields'     => 'ids',
			)
		);

		if ( is_wp_error( $term_ids ) || empty( $term_ids ) ) {
			return;
		}

		$missing = array();
		$max     = 0;

		foreach ( $term_ids as $term_id ) {
			$value = get_term_meta( $term_id, 'epsw_order', true );
			if ( '' === $value ) {
				$missing[] = $term_id;
			} else {
				$max = max( $max, (int) $value );
			}
		}

		foreach ( $missing as $term_id ) {
			update_term_meta( $term_id, 'epsw_order', ++$max );
		}
	}

	/**
	 * Fetches terms for a taxonomy sorted by their manual drag-to-sort
	 * order (falling back to sequential backfill for any term that
	 * doesn't have an order value yet).
	 *
	 * @param string $taxonomy Taxonomy key.
	 * @param array  $args     Extra get_terms() args (e.g. 'search').
	 * @return WP_Term[]|WP_Error
	 */
	public static function get_ordered_terms( $taxonomy, array $args = array() ) {
		self::ensure_term_order_meta( $taxonomy );

		$args              = wp_parse_args( $args, array( 'hide_empty' => false ) );
		$args['taxonomy']  = $taxonomy;
		$args['meta_key']  = 'epsw_order'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		$args['orderby']   = 'meta_value_num';
		$args['order']     = 'ASC';

		return get_terms( $args );
	}

	/**
	 * Parses a comma separated slug list from a shortcode attribute into
	 * a clean array of sanitized slugs.
	 *
	 * @param string $value Raw attribute value, e.g. "wordpress, laravel".
	 * @return string[]
	 */
	public static function parse_slug_list( $value ) {
		if ( empty( $value ) ) {
			return array();
		}

		$parts = array_map( 'trim', explode( ',', (string) $value ) );
		$parts = array_map( 'sanitize_title', $parts );

		return array_values( array_filter( $parts ) );
	}

	/**
	 * Builds the [estel_portfolio ...] shortcode text from selected slugs.
	 *
	 * @param string[] $categories   Category slugs.
	 * @param string[] $technologies Technology slugs.
	 * @return string
	 */
	public static function build_shortcode_text( array $categories, array $technologies ) {
		$atts = array();

		if ( ! empty( $categories ) ) {
			$atts[] = 'category="' . implode( ',', $categories ) . '"';
		}

		if ( ! empty( $technologies ) ) {
			$atts[] = 'technology="' . implode( ',', $technologies ) . '"';
		}

		if ( empty( $atts ) ) {
			return '[estel_portfolio]';
		}

		return '[estel_portfolio ' . implode( ' ', $atts ) . ']';
	}
}
