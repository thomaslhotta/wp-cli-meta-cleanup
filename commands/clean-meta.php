<?php
namespace WP_CLI_Meta_Cleanup\Commands;

use cli\Table;
use WP_CLI;

/**
 * Handles cleaning of post meta
 *
 * @package WP_CLI_Meta_Cleanup\Commands
 */
abstract class Clean_Meta extends \WP_CLI_Command {
	/**
	 * Cleans duplicate meta entries that have the same value.
	 *
	 * [--keys=<keys>]
	 * : Meta keys to find the duplictes for. If ommited all meta entries will be searched
	 *
	 * [--dry-run]
	 * : Don't actually perform any changes.
	 *
	 * @subcommand delete-duplicates
	 */
	public function delte_duplicates( $args, $assoc_args ) {
		$assoc_args = $this->sanitize_delete_arguments( $assoc_args );

		$elements = $this->get_element_ids( $assoc_args );

		// Create progress bar
		$progress = \WP_CLI\Utils\make_progress_bar( 'Processing', count( $elements ) );
		$duplicates = array();

		foreach ( $elements as $id ) {
			$meta_keys = $assoc_args['keys'];
			if ( empty( $meta_keys ) ) {
				$meta_keys = $this->get_all_meta_keys( $id );
			}

			// Iteratate over all meta keys
			foreach ( $meta_keys as $key ) {
				$meta = get_metadata( $this->get_type(), $id, $key, false );
				// Skip posts that to not have this meta
				if ( empty( $meta ) ) {
					continue;
				}

				$dups = count( $meta );
				$meta = array_unique( $meta );
				$dups -= count( $meta );

				// Do nothing if no duplicates were found
				if ( 0 === $dups ) {
					continue;
				}

				// Set up counter
				if ( ! isset( $duplicates[ $key ] ) ) {
					$duplicates[ $key ] = 0;
				}

				$duplicates[ $key ] += $dups;

				// Stop here if we are performing a dry run
				if ( $assoc_args['dry-run'] ) {
					continue;
				}

				// Actually delete and recreate meta
				delete_metadata( $this->get_type(), $id, $key );
				foreach ( $meta as $value ) {
					add_metadata( $this->get_type(), $id, $key, $value );
				}
			}

			$progress->tick();
		}
		$progress->finish();

		WP_CLI::line();
		WP_CLI::success( sprintf( '%d duplicates removed', array_sum( $duplicates ) ) );

		if ( 0 === array_sum( $duplicates ) ) {
			return;
		}

		// Show statistics
		$table = new Table();
		$table->setHeaders(
			array(
				'key'   => 'Meta key',
				'count' => 'Count',
			)
		);

		foreach ( $duplicates as $key => $count ) {
			$table->addRow(
				array(
					'key'   => $key,
					'count' => $count
				)
			);
		}
		$table->display();
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

	/**
	 * Returns all meta keys for a give post
	 *
	 * @param $post_id
	 *
	 * @return array
	 */
	protected function get_all_meta_keys( $post_id ) {
		global $wpdb;

		$table = $this->get_meta_table();
		$id_col = $this->get_type() . '_id';

		return $wpdb->get_col(
			$wpdb->prepare(
				"SELECT DISTINCT( meta_key ) FROM $table WHERE $id_col = %d",
				$post_id
			)
		);
	}

	/**
	 * Returns the element ids
	 *
	 * @param array $args
	 *
	 * @return int[]
	 */
	protected abstract function get_element_ids( array $args );

	/**
	 * Returns the meta type
	 *
	 * @return string
	 */
	protected abstract function get_type();

	/**
	 * Returns the meta table name
	 *
	 * @return string
	 */
	protected abstract function get_meta_table();
}
