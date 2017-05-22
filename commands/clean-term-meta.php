<?php
namespace WP_CLI_Meta_Cleanup\Commands;

use cli\Table;
use WP_CLI;

/**
 * Handles cleaning of term meta
 *
 * @package WP_CLI_Meta_Cleanup\Commands
 */
class Clean_Term_Meta extends Clean_Meta {

	protected function get_type() {
		return 'term';
	}

	protected function get_element_ids( array $assoc_args ) {
		$query_args = array(
			'meta_query'  => array(),
			'number'      => '',
			'fields'      => 'ids'
		);

		// If meta keys are given only find posts that actually have meta entries
		foreach ( $assoc_args['keys'] as $key ) {
			$query_args['meta_query'] = array(
				'key' => $key,
				'compare' => 'EXISTS',
			);
		}

		$query = new \WP_Term_Query(
			$query_args
		);

		return $query->get_terms();
	}

	protected function get_meta_table() {
		global $wpdb;
		return $wpdb->termmeta;
	}
}