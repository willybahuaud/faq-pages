<?php
/**
 * Rendu du bloc FAQ — CTA.
 *
 * Affiche le bouton d'appel a l'action configure via les champs ACF
 * du post courant. Ne rend rien si les champs sont vides.
 *
 * @package FAQ_Pages
 *
 * @param array    $block      Les donnees du bloc.
 * @param string   $content    Le contenu du bloc.
 * @param bool     $is_preview True si on est dans l'editeur.
 * @param int      $post_id    L'ID du post courant.
 * @param WP_Block $wp_block   L'instance WP_Block.
 */

$cta_text = get_field( 'afp_cta_text', $post_id );
$cta_url  = get_field( 'afp_cta_url', $post_id );

if ( empty( $cta_text ) || empty( $cta_url ) ) {
	if ( $is_preview ) {
		echo '<p style="color:#999;font-style:italic;">' . esc_html__( 'CTA — remplir les champs texte et URL.', 'faq-pages' ) . '</p>';
	}
	return;
}

$wrapper_attributes = get_block_wrapper_attributes( array(
	'class' => 'afp-cta-block',
) );

$html = '<div ' . $wrapper_attributes . '>';
$html .= '<a href="' . esc_url( $cta_url ) . '" class="afp-cta-link">' . esc_html( $cta_text ) . '</a>';
$html .= '</div>';

/**
 * Filtre le HTML du bloc CTA.
 *
 * @param string $html     Le HTML du CTA.
 * @param string $cta_text Le texte du CTA.
 * @param string $cta_url  L'URL du CTA.
 * @param int    $post_id  L'ID du post courant.
 */
echo apply_filters( 'afp_cta_html', $html, $cta_text, $cta_url, $post_id );
