<?php
/**
 * Declaration des champs ACF pour le CPT faq_page et la page d'options.
 *
 * @package FAQ_Pages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enregistre la page d'options ACF sous le menu du CPT faq_page.
 *
 * @return void
 */
function afp_register_options_page() {
	if ( ! function_exists( 'acf_add_options_sub_page' ) ) {
		return;
	}

	acf_add_options_sub_page( array(
		'page_title'  => __( 'Réglages FAQ', 'faq-pages' ),
		'menu_title'  => __( 'Réglages', 'faq-pages' ),
		'menu_slug'   => 'afp-settings',
		'parent_slug' => 'edit.php?post_type=faq_page',
		'capability'  => 'manage_options',
	) );
}
add_action( 'acf/init', 'afp_register_options_page' );

/**
 * Enregistre les groupes de champs ACF.
 *
 * - Sidebar post : flag "Top Question".
 * - Normal post  : CTA configurable + questions associees.
 * - Options page : liste des top questions (relationship).
 *
 * @return void
 */
function afp_register_acf_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	// Sidebar : flag Top Question.
	acf_add_local_field_group( array(
		'key'                   => 'group_afp_settings',
		'title'                 => __( 'Réglages FAQ', 'faq-pages' ),
		'fields'                => array(
			array(
				'key'           => 'field_afp_top_question',
				'label'         => __( 'Top Question', 'faq-pages' ),
				'name'          => 'afp_top_question',
				'type'          => 'true_false',
				'instructions'  => __( 'Mettre en avant cette question sur la page d\'archive.', 'faq-pages' ),
				'default_value' => 0,
				'ui'            => 1,
			),
		),
		'location'              => array(
			array(
				array(
					'param'    => 'post_type',
					'operator' => '==',
					'value'    => 'faq_page',
				),
			),
		),
		'menu_order'            => 0,
		'position'              => 'side',
		'style'                 => 'default',
		'label_placement'       => 'top',
		'instruction_placement' => 'label',
	) );

	// Contenu supplementaire : CTA + questions associees.
	acf_add_local_field_group( array(
		'key'                   => 'group_afp_content',
		'title'                 => __( 'Questions associées', 'faq-pages' ),
		'fields'                => array(
			array(
				'key'           => 'field_afp_related_questions',
				'label'         => __( 'Questions associées', 'faq-pages' ),
				'name'          => 'afp_related_questions',
				'type'          => 'relationship',
				'instructions'  => __( 'Sélectionner les questions à afficher en bas de page.', 'faq-pages' ),
				'post_type'     => array( 'faq_page' ),
				'filters'       => array( 'search' ),
				'return_format' => 'id',
			),
		),
		'location'              => array(
			array(
				array(
					'param'    => 'post_type',
					'operator' => '==',
					'value'    => 'faq_page',
				),
			),
		),
		'menu_order'            => 10,
		'position'              => 'normal',
		'style'                 => 'default',
		'label_placement'       => 'top',
		'instruction_placement' => 'label',
	) );

	// Bloc Top Questions : choix du layout.
	acf_add_local_field_group( array(
		'key'                   => 'group_afp_top_questions_block',
		'title'                 => __( 'Réglages du bloc', 'faq-pages' ),
		'fields'                => array(
			array(
				'key'           => 'field_afp_top_questions_layout',
				'label'         => __( 'Mise en forme', 'faq-pages' ),
				'name'          => 'afp_top_questions_layout',
				'type'          => 'radio',
				'instructions'  => '',
				'choices'       => array(
					'list'   => __( 'Liste à puces', 'faq-pages' ),
					'inline' => __( 'Liens en ligne', 'faq-pages' ),
				),
				'default_value' => 'list',
				'layout'        => 'horizontal',
				'return_format' => 'value',
			),
		),
		'location'              => array(
			array(
				array(
					'param'    => 'block',
					'operator' => '==',
					'value'    => 'acf/faq-top-questions',
				),
			),
		),
		'menu_order'            => 0,
		'position'              => 'normal',
		'style'                 => 'default',
		'label_placement'       => 'top',
		'instruction_placement' => 'label',
	) );

	// Page d'options : liste centralisee des top questions.
	acf_add_local_field_group( array(
		'key'                   => 'group_afp_options',
		'title'                 => __( 'Top Questions', 'faq-pages' ),
		'fields'                => array(
			array(
				'key'           => 'field_afp_top_questions_list',
				'label'         => __( 'Questions mises en avant', 'faq-pages' ),
				'name'          => 'afp_top_questions_list',
				'type'          => 'relationship',
				'instructions'  => __( 'Glisser-déposer pour réordonner. La synchronisation avec le toggle par question est automatique.', 'faq-pages' ),
				'post_type'     => array( 'faq_page' ),
				'filters'       => array( 'search' ),
				'return_format' => 'id',
			),
		),
		'location'              => array(
			array(
				array(
					'param'    => 'options_page',
					'operator' => '==',
					'value'    => 'afp-settings',
				),
			),
		),
		'menu_order'            => 0,
		'position'              => 'normal',
		'style'                 => 'default',
		'label_placement'       => 'top',
		'instruction_placement' => 'label',
	) );
}
add_action( 'acf/init', 'afp_register_acf_fields' );

/**
 * Flag global pour eviter les boucles infinies lors de la synchro.
 *
 * @var bool
 */
$afp_sync_in_progress = false;

/**
 * Synchronise le toggle "Top Question" d'un post vers la liste de la page d'options.
 *
 * @param int $post_id L'ID du post sauvegarde.
 * @return void
 */
function afp_sync_post_to_options( $post_id ) {
	global $afp_sync_in_progress;

	if ( $afp_sync_in_progress ) {
		return;
	}

	if ( get_post_type( $post_id ) !== 'faq_page' ) {
		return;
	}

	$afp_sync_in_progress = true;

	$is_top     = (bool) get_field( 'afp_top_question', $post_id );
	$option_ids = (array) get_field( 'afp_top_questions_list', 'option' );
	$option_ids = array_filter( $option_ids );
	$in_list    = in_array( $post_id, $option_ids, true );

	if ( $is_top && ! $in_list ) {
		$option_ids[] = $post_id;
		update_field( 'field_afp_top_questions_list', $option_ids, 'option' );
	} elseif ( ! $is_top && $in_list ) {
		$option_ids = array_values( array_diff( $option_ids, array( $post_id ) ) );
		update_field( 'field_afp_top_questions_list', $option_ids, 'option' );
	}

	$afp_sync_in_progress = false;
}
add_action( 'acf/save_post', 'afp_sync_post_to_options', 20 );

/**
 * Synchronise la liste de la page d'options vers les toggles individuels.
 *
 * @param int $post_id L'ID du post sauvegarde (ici 'options').
 * @return void
 */
function afp_sync_options_to_posts( $post_id ) {
	global $afp_sync_in_progress;

	if ( $afp_sync_in_progress ) {
		return;
	}

	if ( 'options' !== $post_id ) {
		return;
	}

	$afp_sync_in_progress = true;

	$new_ids = (array) get_field( 'afp_top_questions_list', 'option' );
	$new_ids = array_filter( $new_ids );

	// Recuperer les posts actuellement flagges "top".
	$current_top_query = new WP_Query( array(
		'post_type'      => 'faq_page',
		'posts_per_page' => -1,
		'no_found_rows'  => true,
		'fields'         => 'ids',
		'meta_query'     => array(
			array(
				'key'     => 'afp_top_question',
				'value'   => '1',
				'compare' => '=',
			),
		),
	) );
	$current_ids = $current_top_query->posts;

	// Posts a activer.
	$to_enable = array_diff( $new_ids, $current_ids );
	foreach ( $to_enable as $pid ) {
		update_field( 'field_afp_top_question', 1, $pid );
	}

	// Posts a desactiver.
	$to_disable = array_diff( $current_ids, $new_ids );
	foreach ( $to_disable as $pid ) {
		update_field( 'field_afp_top_question', 0, $pid );
	}

	$afp_sync_in_progress = false;
}
add_action( 'acf/save_post', 'afp_sync_options_to_posts', 20 );
