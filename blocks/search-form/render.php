<?php
/**
 * Rendu du bloc FAQ — Recherche.
 *
 * Affiche le formulaire de recherche avec le markup pour l'autocompletion JS.
 * Fonctionne nativement sans JS (submit classique).
 *
 * @package FAQ_Pages
 *
 * @param array    $block      Les donnees du bloc.
 * @param string   $content    Le contenu du bloc.
 * @param bool     $is_preview True si on est dans l'editeur.
 * @param int      $post_id    L'ID du post courant.
 * @param WP_Block $wp_block   L'instance WP_Block.
 */

$search_query     = get_query_var( 's', '' );
$placeholder      = get_field( 'afp_search_placeholder' );
if ( ! $placeholder ) {
	$placeholder = __( 'Rechercher une question…', 'faq-pages' );
}
$wrapper_attributes = get_block_wrapper_attributes( array(
	'class' => 'afp-search-block',
) );

// Enqueue le JS d'autocompletion uniquement quand le bloc est rendu.
// Le CSS est gere via la propriete "style" du block.json.
if ( ! $is_preview ) {
	wp_enqueue_script( 'faq-autocomplete' );
}
?>
<div <?php echo $wrapper_attributes; ?>>
	<form class="afp-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>" method="get" role="search">
		<input type="hidden" name="post_type" value="faq_page">
		<div class="afp-search-wrapper">
			<label for="afp-search-input" class="screen-reader-text"><?php echo esc_html( $placeholder ); ?></label>
			<input
				type="search"
				id="afp-search-input"
				name="s"
				class="afp-search-input"
				placeholder="<?php echo esc_attr( $placeholder ); ?>"
				value="<?php echo esc_attr( $search_query ); ?>"
				autocomplete="off"
				required
			>
			<button type="submit" class="afp-search-submit"><?php esc_html_e( 'Rechercher', 'faq-pages' ); ?></button>
			<div class="afp-suggestions" role="listbox" aria-label="<?php esc_attr_e( 'Suggestions', 'faq-pages' ); ?>" hidden></div>
		</div>
	</form>
</div>
