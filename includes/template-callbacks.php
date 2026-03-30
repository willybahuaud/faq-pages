<?php
/**
 * Shortcodes de rendu pour les templates FAQ.
 *
 * @package FAQ_Pages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enregistre tous les shortcodes du plugin.
 *
 * @return void
 */
function afp_register_shortcodes() {
	add_shortcode( 'afp_search_form', 'afp_render_search_form' );
	add_shortcode( 'afp_top_questions', 'afp_render_top_questions' );
	add_shortcode( 'afp_questions_by_category', 'afp_render_questions_by_category' );
	add_shortcode( 'afp_cta', 'afp_render_cta' );
	add_shortcode( 'afp_related_questions', 'afp_render_related_questions' );
	add_shortcode( 'afp_search_results', 'afp_render_search_results' );
}
add_action( 'init', 'afp_register_shortcodes' );

/**
 * Affiche le formulaire de recherche FAQ avec le markup pour l'autocompletion.
 *
 * Le formulaire fonctionne nativement sans JS (submit classique).
 * Le JS d'autocompletion vient se greffer par-dessus.
 *
 * @return string Le HTML du formulaire.
 */
function afp_render_search_form() {
	$search_query = get_query_var( 's', '' );

	$html = '<form class="afp-search-form" action="' . esc_url( home_url( '/' ) ) . '" method="get" role="search">';
	$html .= '<input type="hidden" name="post_type" value="faq_page">';
	$html .= '<div class="afp-search-wrapper">';
	$html .= '<label for="afp-search-input" class="screen-reader-text">' . esc_html__( 'Rechercher dans la FAQ', 'faq-pages' ) . '</label>';
	$html .= '<input type="search" id="afp-search-input" name="s" class="afp-search-input"';
	$html .= ' placeholder="' . esc_attr__( 'Rechercher une question…', 'faq-pages' ) . '"';
	$html .= ' value="' . esc_attr( $search_query ) . '" autocomplete="off">';
	$html .= '<button type="submit" class="afp-search-submit">' . esc_html__( 'Rechercher', 'faq-pages' ) . '</button>';
	$html .= '<div class="afp-suggestions" role="listbox" aria-label="' . esc_attr__( 'Suggestions', 'faq-pages' ) . '" hidden></div>';
	$html .= '</div>';
	$html .= '</form>';

	/**
	 * Filtre le HTML du formulaire de recherche FAQ.
	 *
	 * @param string $html Le HTML du formulaire.
	 */
	return apply_filters( 'afp_search_form_html', $html );
}

/**
 * Affiche la liste des questions marquees "Top Question".
 *
 * @return string Le HTML de la liste.
 */
function afp_render_top_questions() {
	$query_args = array(
		'post_type'      => 'faq_page',
		'posts_per_page' => -1,
		'meta_query'     => array(
			array(
				'key'     => 'afp_top_question',
				'value'   => '1',
				'compare' => '=',
			),
		),
		'orderby'        => 'title',
		'order'          => 'ASC',
	);

	/**
	 * Filtre les arguments de la requete WP_Query pour les top questions.
	 *
	 * @param array $query_args Les arguments WP_Query.
	 */
	$query_args = apply_filters( 'afp_top_questions_query_args', $query_args );

	$query = new WP_Query( $query_args );

	if ( ! $query->have_posts() ) {
		return '';
	}

	$html = '';

	/**
	 * Se declenche avant le rendu des top questions.
	 */
	ob_start();
	do_action( 'afp_before_top_questions' );
	$html .= ob_get_clean();

	$html .= '<ul class="afp-top-questions">';
	while ( $query->have_posts() ) {
		$query->the_post();
		$html .= '<li class="afp-top-question-item">';
		$html .= '<a href="' . esc_url( get_permalink() ) . '">' . esc_html( get_the_title() ) . '</a>';
		$html .= '</li>';
	}
	$html .= '</ul>';
	wp_reset_postdata();

	/**
	 * Se declenche apres le rendu des top questions.
	 */
	ob_start();
	do_action( 'afp_after_top_questions' );
	$html .= ob_get_clean();

	return $html;
}

/**
 * Affiche toutes les questions regroupees par categorie faq_category.
 *
 * @return string Le HTML des questions par categorie.
 */
function afp_render_questions_by_category() {
	$terms = get_terms( array(
		'taxonomy'   => 'faq_category',
		'hide_empty' => true,
		'orderby'    => 'name',
		'order'      => 'ASC',
	) );

	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return '';
	}

	$html = '';

	/**
	 * Se declenche avant le rendu des questions par categorie.
	 */
	ob_start();
	do_action( 'afp_before_questions_by_category' );
	$html .= ob_get_clean();

	foreach ( $terms as $term ) {
		$query_args = array(
			'post_type'      => 'faq_page',
			'posts_per_page' => -1,
			'tax_query'      => array(
				array(
					'taxonomy' => 'faq_category',
					'field'    => 'term_id',
					'terms'    => $term->term_id,
				),
			),
			'orderby'        => 'title',
			'order'          => 'ASC',
		);

		$query = new WP_Query( $query_args );

		if ( ! $query->have_posts() ) {
			continue;
		}

		$html .= '<div class="afp-category-group">';
		$html .= '<h3 class="afp-category-title">' . esc_html( $term->name ) . '</h3>';
		$html .= '<ul class="afp-category-questions">';
		while ( $query->have_posts() ) {
			$query->the_post();
			$html .= '<li class="afp-question-item">';
			$html .= '<a href="' . esc_url( get_permalink() ) . '">' . esc_html( get_the_title() ) . '</a>';
			$html .= '</li>';
		}
		$html .= '</ul>';
		$html .= '</div>';
		wp_reset_postdata();
	}

	/**
	 * Se declenche apres le rendu des questions par categorie.
	 */
	ob_start();
	do_action( 'afp_after_questions_by_category' );
	$html .= ob_get_clean();

	return $html;
}

/**
 * Affiche le CTA configurable depuis les champs ACF du post courant.
 *
 * @return string Le HTML du CTA, ou vide si non configure.
 */
function afp_render_cta() {
	$post_id  = get_the_ID();
	$cta_text = get_field( 'afp_cta_text', $post_id );
	$cta_url  = get_field( 'afp_cta_url', $post_id );

	if ( empty( $cta_text ) || empty( $cta_url ) ) {
		return '';
	}

	$html = '<div class="afp-cta">';
	$html .= '<a href="' . esc_url( $cta_url ) . '" class="afp-cta-link">' . esc_html( $cta_text ) . '</a>';
	$html .= '</div>';

	/**
	 * Filtre le HTML du bloc CTA.
	 *
	 * @param string $html    Le HTML du CTA.
	 * @param string $cta_text Le texte du CTA.
	 * @param string $cta_url  L'URL du CTA.
	 * @param int    $post_id  L'ID du post courant.
	 */
	return apply_filters( 'afp_cta_html', $html, $cta_text, $cta_url, $post_id );
}

/**
 * Affiche la liste des questions associees (champ ACF relationship).
 *
 * @return string Le HTML de la liste des questions associees.
 */
function afp_render_related_questions() {
	$post_id      = get_the_ID();
	$related_ids  = get_field( 'afp_related_questions', $post_id );

	if ( empty( $related_ids ) ) {
		return '';
	}

	$query_args = array(
		'post_type'      => 'faq_page',
		'post__in'       => $related_ids,
		'posts_per_page' => count( $related_ids ),
		'orderby'        => 'post__in',
	);

	/**
	 * Filtre les arguments de la requete WP_Query pour les questions associees.
	 *
	 * @param array $query_args Les arguments WP_Query.
	 * @param int   $post_id    L'ID du post courant.
	 */
	$query_args = apply_filters( 'afp_related_questions_query_args', $query_args, $post_id );

	$query = new WP_Query( $query_args );

	if ( ! $query->have_posts() ) {
		return '';
	}

	$html  = '<div class="afp-related-questions">';
	$html .= '<h2 class="afp-related-title">' . esc_html__( 'Questions associées', 'faq-pages' ) . '</h2>';
	$html .= '<ul class="afp-related-list">';
	while ( $query->have_posts() ) {
		$query->the_post();
		$html .= '<li class="afp-related-item">';
		$html .= '<a href="' . esc_url( get_permalink() ) . '">' . esc_html( get_the_title() ) . '</a>';
		$html .= '</li>';
	}
	$html .= '</ul>';
	$html .= '</div>';
	wp_reset_postdata();

	return $html;
}

/**
 * Affiche la liste des resultats de recherche filtres sur le CPT faq_page.
 *
 * @return string Le HTML des resultats de recherche.
 */
function afp_render_search_results() {
	$search_query = get_query_var( 's', '' );

	if ( empty( $search_query ) ) {
		return '<p class="afp-search-empty">' . esc_html__( 'Saisissez un terme pour lancer la recherche.', 'faq-pages' ) . '</p>';
	}

	$query = new WP_Query( array(
		'post_type'      => 'faq_page',
		's'              => $search_query,
		'posts_per_page' => -1,
	) );

	if ( ! $query->have_posts() ) {
		return '<p class="afp-no-results">' . esc_html__( 'Aucune question ne correspond à votre recherche.', 'faq-pages' ) . '</p>';
	}

	$html  = '<ul class="afp-search-results">';
	while ( $query->have_posts() ) {
		$query->the_post();
		$html .= '<li class="afp-search-result-item">';
		$html .= '<a href="' . esc_url( get_permalink() ) . '">' . esc_html( get_the_title() ) . '</a>';
		$html .= '</li>';
	}
	$html .= '</ul>';
	wp_reset_postdata();

	return $html;
}
