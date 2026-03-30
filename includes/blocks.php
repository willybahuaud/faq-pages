<?php
/**
 * Enregistrement des blocs dynamiques du plugin.
 *
 * Scanne le dossier blocks/ et enregistre chaque bloc
 * qui contient un block.json.
 *
 * @package FAQ_Pages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enregistre tous les blocs du plugin depuis le dossier blocks/.
 *
 * @return void
 */
function afp_register_blocks() {
	$blocks_dir = AFP_PATH . 'blocks/';

	if ( ! is_dir( $blocks_dir ) ) {
		return;
	}

	$block_dirs = glob( $blocks_dir . '*', GLOB_ONLYDIR );

	foreach ( $block_dirs as $block_dir ) {
		if ( file_exists( $block_dir . '/block.json' ) ) {
			register_block_type( $block_dir );
		}
	}
}
add_action( 'init', 'afp_register_blocks' );
