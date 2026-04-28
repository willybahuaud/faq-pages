<?php
/**
 * Rendu du bloc FAQ — Top Questions.
 *
 * Utilise la liste ordonnee de la page d'options en priorite (respect du drag & drop).
 * Fallback sur la meta query si la page d'options n'est pas configuree.
 *
 * @package FAQ_Pages
 *
 * @param array    $block      Les donnees du bloc.
 * @param string   $content    Le contenu du bloc.
 * @param bool     $is_preview True si on est dans l'editeur.
 * @param int      $post_id    L'ID du post courant.
 * @param WP_Block $wp_block   L'instance WP_Block.
 */

$top_ids = (array) get_field( 'afp_top_questions_list', 'option' );
$top_ids = array_filter( $top_ids );

// Liste ordonnee depuis la page d'options.
if ( ! empty( $top_ids ) ) {
	$query_args = array(
		'post_type'      => 'faq_page',
		'post__in'       => $top_ids,
		'posts_per_page' => count( $top_ids ),
		'no_found_rows'  => true,
		'orderby'        => 'post__in',
	);
} else {
	// Fallback : meta query sur le toggle individuel.
	$query_args = array(
		'post_type'      => 'faq_page',
		'posts_per_page' => -1,
		'no_found_rows'  => true,
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
}

/**
 * Filtre les arguments de la requete WP_Query pour les top questions.
 *
 * @param array $query_args Les arguments WP_Query.
 */
$query_args = apply_filters( 'afp_top_questions_query_args', $query_args );

$query = new WP_Query( $query_args );

if ( ! $query->have_posts() ) {
	return;
}

$layout = get_field( 'afp_top_questions_layout' );
if ( ! $layout ) {
	$layout = 'list';
}

$wrapper_attributes = get_block_wrapper_attributes( array(
	'class' => 'afp-top-questions-block afp-top-questions-block--' . $layout,
) );
?>
<div <?php echo $wrapper_attributes; ?>>
	<?php
	/**
	 * Se declenche avant le rendu des top questions.
	 */
	do_action( 'afp_before_top_questions' );

	if ( 'inline' === $layout ) {
		$links = array();
		while ( $query->have_posts() ) {
			$query->the_post();
			$links[] = '<a href="' . esc_url( get_permalink() ) . '">' . esc_html( get_the_title() ) . '</a>';
		}

		/**
		 * Filtre le separateur entre les liens en mode inline.
		 *
		 * @param string $separator Le separateur HTML. Par defaut ' · '.
		 */
		$separator = apply_filters( 'afp_top_questions_inline_separator', ' · ' );

		echo '<p class="afp-top-questions afp-top-questions--inline">' . implode( $separator, $links ) . '</p>';
	} else {
		?>
		<ul class="afp-top-questions">
			<?php while ( $query->have_posts() ) : $query->the_post(); ?>
				<li class="afp-top-question-item">
					<a href="<?php echo esc_url( get_permalink() ); ?>"><?php echo esc_html( get_the_title() ); ?></a>
				</li>
			<?php endwhile; ?>
		</ul>
		<?php
	}

	wp_reset_postdata();

	/**
	 * Se declenche apres le rendu des top questions.
	 */
	do_action( 'afp_after_top_questions' );
	?>
</div>
