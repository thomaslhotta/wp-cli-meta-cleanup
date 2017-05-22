<?php
/**
 * Adds a WP-CLI command to delete duplicate meta entry for posts, comments and terms.
 */
namespace WP_CLI_Meta_Cleanup;
use function foo\func;
use WP_CLI,
	WP_CLI_Meta_Cleanup\Commands;

if ( ! defined( 'WP_CLI' ) ) {
	return;
}

require_once __DIR__ . '/commands/clean-meta.php';
require_once __DIR__ . '/commands/clean-comment-meta.php';
require_once __DIR__ . '/commands/clean-post-meta.php';
require_once __DIR__ . '/commands/clean-term-meta.php';

WP_CLI::add_command( 'comment meta-cleanup', '\WP_CLI_Meta_Cleanup\Commands\Clean_Comment_Meta' );
WP_CLI::add_command( 'post meta-cleanup', '\WP_CLI_Meta_Cleanup\Commands\Clean_Post_Meta' );
WP_CLI::add_command( 'term meta-cleanup', '\WP_CLI_Meta_Cleanup\Commands\Clean_Term_Meta' );
