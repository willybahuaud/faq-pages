<?php
/**
 * Enqueue des assets front-end (JS autocompletion, CSS).
 *
 * @package FAQ_Pages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue le JS d'autocompletion et le CSS sur les pages FAQ.
 *
 * Charge les assets uniquement sur l'archive, le single et la recherche FAQ.
 *
 * @return void
 */
function afp_enqueue_frontend_assets() {
	if ( ! afp_should_load_assets() ) {
		return;
	}

	wp_enqueue_style(
		'faq-pages',
		AFP_URL . 'assets/css/faq-pages.css',
		array(),
		AFP_VERSION
	);

	wp_enqueue_script(
		'faq-autocomplete',
		AFP_URL . 'assets/js/faq-autocomplete.js',
		array(),
		AFP_VERSION,
		true
	);

	$script_data = array(
		'restUrl'   => rest_url( 'wp/v2/faq-pages' ),
		'nonce'     => wp_create_nonce( 'wp_rest' ),
		'noResults' => __( 'Aucun résultat', 'faq-pages' ),
		'loading'   => __( 'Recherche en cours…', 'faq-pages' ),
		'error'     => __( 'Erreur de recherche', 'faq-pages' ),
	);

	/**
	 * Filtre les donnees localisees du script d'autocompletion.
	 *
	 * @param array $script_data Les donnees localisees.
	 */
	$script_data = apply_filters( 'afp_autocomplete_script_data', $script_data );

	wp_localize_script( 'faq-autocomplete', 'afpAutocomplete', $script_data );
}
add_action( 'wp_enqueue_scripts', 'afp_enqueue_frontend_assets' );

/**
 * Determine si les assets FAQ doivent etre charges.
 *
 * @return bool True si on est sur une page FAQ.
 */
function afp_should_load_assets() {
	if ( is_post_type_archive( 'faq_page' ) ) {
		return true;
	}

	if ( is_singular( 'faq_page' ) ) {
		return true;
	}

	if ( is_search() ) {
		$post_type = isset( $_GET['post_type'] ) ? sanitize_text_field( wp_unslash( $_GET['post_type'] ) ) : '';
		if ( 'faq_page' === $post_type ) {
			return true;
		}
	}

	return false;
}
