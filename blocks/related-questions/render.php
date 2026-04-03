<?php
/**
 * Rendu du bloc FAQ — Questions associees.
 *
 * Affiche la liste des questions associees configurees via le champ
 * ACF relationship du post courant.
 *
 * @package FAQ_Pages
 *
 * @param array    $block      Les donnees du bloc.
 * @param string   $content    Le contenu du bloc.
 * @param bool     $is_preview True si on est dans l'editeur.
 * @param int      $post_id    L'ID du post courant.
 * @param WP_Block $wp_block   L'instance WP_Block.
 */

$related_ids  = get_field( 'afp_related_questions', $post_id );
$current_id   = get_queried_object_id();
$is_automatic = empty( $related_ids );

if ( $is_automatic ) {
	$terms = get_the_terms( $current_id, 'faq_category' );

	if ( empty( $terms ) || is_wp_error( $terms ) ) {
		if ( $is_preview ) {
			echo '<p style="color:#999;font-style:italic;">' . esc_html__( 'Questions associées — aucune catégorie trouvée.', 'faq-pages' ) . '</p>';
		}
		return;
	}

	$term_ids = wp_list_pluck( $terms, 'term_id' );

	$query_args = array(
		'post_type'      => 'faq_page',
		'posts_per_page' => 3,
		'no_found_rows'  => true,
		'post__not_in'   => array( $current_id ),
		'orderby'        => 'rand',
		'tax_query'      => array(
			array(
				'taxonomy' => 'faq_category',
				'field'    => 'term_id',
				'terms'    => $term_ids,
			),
		),
	);
} else {
	$query_args = array(
		'post_type'      => 'faq_page',
		'post__in'       => $related_ids,
		'posts_per_page' => count( $related_ids ),
		'no_found_rows'  => true,
		'orderby'        => 'post__in',
	);
}

/**
 * Filtre les arguments de la requete WP_Query pour les questions associees.
 *
 * @param array $query_args Les arguments WP_Query.
 * @param int   $post_id    L'ID du post courant.
 */
$query_args = apply_filters( 'afp_related_questions_query_args', $query_args, $post_id );

$query = new WP_Query( $query_args );

if ( ! $query->have_posts() ) {
	return;
}

$wrapper_attributes = get_block_wrapper_attributes( array(
	'class' => 'afp-related-questions-block',
) );
?>
<div <?php echo $wrapper_attributes; ?>>
	<h2 class="afp-related-title"><?php esc_html_e( 'Questions associées', 'faq-pages' ); ?></h2>
	<ul class="afp-related-list">
		<?php while ( $query->have_posts() ) : $query->the_post(); ?>
			<li class="afp-related-item">
				<a href="<?php echo esc_url( get_permalink() ); ?>"><?php echo esc_html( get_the_title() ); ?></a>
			</li>
		<?php endwhile; ?>
	</ul>
</div>
<?php
wp_reset_postdata();
