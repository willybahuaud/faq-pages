<?php
/**
 * Rendu du bloc FAQ — Questions par categorie.
 *
 * Affiche toutes les questions regroupees par terme de la taxonomie faq_category.
 *
 * @package FAQ_Pages
 *
 * @param array    $block      Les donnees du bloc.
 * @param string   $content    Le contenu du bloc.
 * @param bool     $is_preview True si on est dans l'editeur.
 * @param int      $post_id    L'ID du post courant.
 * @param WP_Block $wp_block   L'instance WP_Block.
 */

$terms = get_terms( array(
	'taxonomy'   => 'faq_category',
	'hide_empty' => true,
	'orderby'    => 'name',
	'order'      => 'ASC',
) );

if ( is_wp_error( $terms ) || empty( $terms ) ) {
	return;
}

$category_gap = (int) get_field( 'afp_category_gap' );
if ( $category_gap < 0 ) {
	$category_gap = 8;
}

$wrapper_attributes = get_block_wrapper_attributes( array(
	'class' => 'afp-questions-by-category-block',
	'style' => '--afp-list-gap:' . $category_gap . 'px',
) );
?>
<div <?php echo $wrapper_attributes; ?>>
	<?php
	/**
	 * Se declenche avant le rendu des questions par categorie.
	 */
	do_action( 'afp_before_questions_by_category' );

	foreach ( $terms as $term ) :
		$query = new WP_Query( array(
			'post_type'      => 'faq_page',
			'posts_per_page' => -1,
			'no_found_rows'  => true,
			'tax_query'      => array(
				array(
					'taxonomy' => 'faq_category',
					'field'    => 'term_id',
					'terms'    => $term->term_id,
				),
			),
			'orderby'        => 'title',
			'order'          => 'ASC',
		) );

		if ( ! $query->have_posts() ) :
			continue;
		endif;
		?>
		<div class="afp-category-group">
			<h3 class="afp-category-title"><?php echo esc_html( $term->name ); ?></h3>
			<ul class="afp-category-questions">
				<?php while ( $query->have_posts() ) : $query->the_post(); ?>
					<li class="afp-question-item">
						<a href="<?php echo esc_url( get_permalink() ); ?>"><?php echo esc_html( get_the_title() ); ?></a>
					</li>
				<?php endwhile; ?>
			</ul>
		</div>
		<?php
		wp_reset_postdata();
	endforeach;

	/**
	 * Se declenche apres le rendu des questions par categorie.
	 */
	do_action( 'afp_after_questions_by_category' );
	?>
</div>
