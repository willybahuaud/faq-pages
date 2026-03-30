<?php
/**
 * Filtrage de la recherche WordPress pour le CPT faq_page.
 *
 * Quand la recherche est filtree sur post_type=faq_page, on force le
 * type de post dans la query et on oriente vers le template de recherche FAQ.
 *
 * @package FAQ_Pages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Force le post_type a faq_page quand la recherche est filtree.
 *
 * @param WP_Query $query L'objet WP_Query.
 * @return void
 */
function afp_filter_search_query( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	if ( ! $query->is_search() ) {
		return;
	}

	$post_type = isset( $_GET['post_type'] ) ? sanitize_text_field( wp_unslash( $_GET['post_type'] ) ) : '';

	if ( 'faq_page' !== $post_type ) {
		return;
	}

	$query->set( 'post_type', 'faq_page' );
}
add_action( 'pre_get_posts', 'afp_filter_search_query' );

/**
 * Insere le template search-faq_page en tete de la hierarchie quand
 * la recherche est filtree sur le CPT faq_page.
 *
 * Fonctionne en classic theme et en block theme (FSE).
 * En block theme, le filtre `search_template_hierarchy` alimente aussi
 * la resolution des block templates depuis WP 6.1.
 *
 * @param string[] $templates La liste des templates candidats.
 * @return string[] La liste modifiee.
 */
function afp_search_template_hierarchy( $templates ) {
	$post_type = isset( $_GET['post_type'] ) ? sanitize_text_field( wp_unslash( $_GET['post_type'] ) ) : '';

	if ( 'faq_page' !== $post_type ) {
		return $templates;
	}

	array_unshift( $templates, 'search-faq_page' );

	return $templates;
}
add_filter( 'search_template_hierarchy', 'afp_search_template_hierarchy' );
