<?php
/**
 * Enregistrement des assets front-end (JS autocompletion, CSS).
 *
 * Les assets sont enregistres globalement mais enqueues uniquement
 * par le bloc search-form quand il est rendu.
 *
 * @package FAQ_Pages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enregistre le JS d'autocompletion et le CSS.
 *
 * L'enqueue effectif se fait dans le render.php du bloc search-form.
 *
 * @return void
 */
function afp_register_frontend_assets() {
	wp_register_style(
		'faq-pages',
		AFP_URL . 'assets/css/faq-pages.css',
		array(),
		AFP_VERSION
	);

	wp_register_script(
		'faq-autocomplete',
		AFP_URL . 'assets/js/faq-autocomplete.js',
		array(),
		AFP_VERSION,
		true
	);

	$script_data = array(
		'restUrl'   => rest_url( 'wp/v2/faq-pages' ),
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
add_action( 'wp_enqueue_scripts', 'afp_register_frontend_assets' );
