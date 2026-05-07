<?php
/**
 * Block Bindings — source "signature" pour les questions FAQ.
 *
 * Genere dynamiquement la ligne de signature :
 * "Reponse redigee par {auteur} ({metier}) — Publie/Modifie le {date}"
 *
 * @package FAQ_Pages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enregistre la source de block binding "faq-pages/signature".
 *
 * @return void
 */
function afp_register_block_bindings() {
	register_block_bindings_source(
		'faq-pages/signature',
		array(
			'label'              => __( 'Signature FAQ', 'faq-pages' ),
			'get_value_callback' => 'afp_get_signature_binding',
			'uses_context'       => array( 'postId', 'postType' ),
		)
	);
}
add_action( 'init', 'afp_register_block_bindings' );

/**
 * Callback du block binding "faq-pages/signature".
 *
 * Construit la signature complete a partir des donnees du post courant :
 * - Nom d'affichage de l'auteur
 * - Metier (champ ACF user, entre parentheses si renseigne)
 * - "Publie le" + date de publication, ou "Modifie le" + date de modification
 *   si le post a ete edite plus de 60 secondes apres sa publication
 * - Format de date : celui configure dans Reglages > General
 *
 * @param array    $source_args    Arguments de la source (non utilises).
 * @param WP_Block $block_instance Instance du bloc avec son contexte.
 * @param string   $attribute_name Attribut lie (ex: "content").
 * @return string La signature formatee.
 */
function afp_get_signature_binding( $source_args, $block_instance, $attribute_name ) {
	$post_id = $block_instance->context['postId'] ?? get_the_ID();

	if ( ! $post_id ) {
		return '';
	}

	$post = get_post( $post_id );

	if ( ! $post || 'faq_page' !== $post->post_type ) {
		return '';
	}

	// Auteur.
	$author_name = get_the_author_meta( 'display_name', $post->post_author );
	$job_title   = get_field( 'afp_user_job_title', 'user_' . $post->post_author );

	// Date : modification si plus de 60s apres la publication.
	$post_time     = strtotime( $post->post_date );
	$modified_time = strtotime( $post->post_modified );
	$is_modified   = ( $modified_time - $post_time ) > 60;
	$date_format   = get_option( 'date_format' );

	if ( $is_modified ) {
		$date_label = __( 'Modifié le', 'faq-pages' );
		$date       = get_the_modified_date( $date_format, $post );
	} else {
		$date_label = __( 'Publié le', 'faq-pages' );
		$date       = get_the_date( $date_format, $post );
	}

	// Construction de la signature.
	$signature = __( 'Réponse rédigée par', 'faq-pages' ) . ' ' . esc_html( $author_name );

	if ( $job_title ) {
		$signature .= ' (' . esc_html( $job_title ) . ')';
	}

	$signature .= ' — ' . $date_label . ' ' . $date;

	/**
	 * Filtre la signature finale affichee sous la question FAQ.
	 *
	 * @param string  $signature La signature formatee.
	 * @param WP_Post $post      Le post faq_page courant.
	 */
	return apply_filters( 'afp_signature', $signature, $post );
}
