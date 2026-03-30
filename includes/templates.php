<?php
/**
 * Enregistrement des block templates FSE.
 *
 * Les templates sont surchargeables par le theme :
 * il suffit de placer un fichier du meme nom dans templates/.
 *
 * @package FAQ_Pages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enregistre les block templates du plugin via wp_register_block_template().
 *
 * @return void
 */
function afp_register_block_templates() {
	$templates = array(
		'archive-faq_page' => array(
			'title'       => __( 'Archive FAQ', 'faq-pages' ),
			'description' => __( 'Page d\'archive listant toutes les questions FAQ.', 'faq-pages' ),
			'post_types'  => array( 'faq_page' ),
		),
		'single-faq_page'  => array(
			'title'       => __( 'Question FAQ', 'faq-pages' ),
			'description' => __( 'Page de detail d\'une question FAQ.', 'faq-pages' ),
			'post_types'  => array( 'faq_page' ),
		),
		'search-faq_page'  => array(
			'title'       => __( 'Recherche FAQ', 'faq-pages' ),
			'description' => __( 'Résultats de recherche filtrés sur la FAQ.', 'faq-pages' ),
		),
	);

	foreach ( $templates as $slug => $args ) {
		$template_file = AFP_PATH . 'templates/' . $slug . '.html';
		if ( file_exists( $template_file ) ) {
			$args['content'] = file_get_contents( $template_file );
		}

		wp_register_block_template( 'faq-pages//' . $slug, $args );
	}
}
add_action( 'init', 'afp_register_block_templates' );
