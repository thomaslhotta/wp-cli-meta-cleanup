<?php
namespace WP_CLI_Meta_Cleanup\Commands;

use cli\Table;
use WP_CLI;

/**
 * Handles cleaning of post meta
 *
 * @package WP_CLI_Meta_Cleanup\Commands
 */
class Clean_Post_Meta extends Clean_Meta {

	protected function get_type() {
		return 'post';
	}

	/**
	 * Sanitizes arguments
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	protected function sanitize_delete_arguments( array $args ) {
		$args = wp_parse_args(
			$args,
			array(
				'keys' => array(),
				'dry-run' => false,
			)
		);

		if ( is_string( $args['keys'] ) ) {
			$args['keys'] = explode( ',', $args['keys'] );
		}

		return $args;
	}

	protected function get_element_ids( array $assoc_args ) {
		$query_args = array(
			'meta_query' => array(),
			'posts_per_page' => -1,
			'post_type' => 'any',
			'post_status' => 'any',
			'fields' => 'ids'
		);

		// If meta keys are given only find posts that actually have meta entries
		foreach ( $assoc_args['keys'] as $key ) {
			$query_args['meta_query'] = array(
				'key' => $key,
				'compare' => 'EXISTS',
			);
		}

		$query = new \WP_Query(
			$query_args
		);

		return $query->get_posts();
	}

	protected function get_meta_table() {
		global $wpdb;
		return $wpdb->postmeta;
	}
}