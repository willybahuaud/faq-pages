<?php
/**
 * Enregistrement du CPT faq_page et de la taxonomie faq_category.
 *
 * @package FAQ_Pages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enregistre le Custom Post Type faq_page.
 *
 * @return void
 */
function afp_register_post_type() {
	$labels = array(
		'name'                  => _x( 'FAQ', 'post type general name', 'faq-pages' ),
		'singular_name'         => _x( 'Question', 'post type singular name', 'faq-pages' ),
		'menu_name'             => _x( 'FAQ Pages', 'admin menu', 'faq-pages' ),
		'name_admin_bar'        => _x( 'Question FAQ', 'add new on admin bar', 'faq-pages' ),
		'add_new'               => __( 'Ajouter', 'faq-pages' ),
		'add_new_item'          => __( 'Ajouter une question', 'faq-pages' ),
		'new_item'              => __( 'Nouvelle question', 'faq-pages' ),
		'edit_item'             => __( 'Modifier la question', 'faq-pages' ),
		'view_item'             => __( 'Voir la question', 'faq-pages' ),
		'all_items'             => __( 'Toutes les questions', 'faq-pages' ),
		'search_items'          => __( 'Rechercher dans la FAQ', 'faq-pages' ),
		'not_found'             => __( 'Aucune question trouvée.', 'faq-pages' ),
		'not_found_in_trash'    => __( 'Aucune question dans la corbeille.', 'faq-pages' ),
		'archives'              => __( 'Archives FAQ', 'faq-pages' ),
		'filter_items_list'     => __( 'Filtrer les questions', 'faq-pages' ),
		'items_list'            => __( 'Liste des questions', 'faq-pages' ),
		'items_list_navigation' => __( 'Navigation dans les questions', 'faq-pages' ),
	);

	$args = array(
		'labels'             => $labels,
		'description'        => __( 'Pages FAQ individuelles.', 'faq-pages' ),
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'show_in_rest'       => true,
		'rest_base'          => 'faq-pages',
		'query_var'          => true,
		'rewrite'            => array(
			'slug'       => 'faq',
			'with_front' => false,
		),
		'capability_type'    => 'post',
		'has_archive'        => true,
		'hierarchical'       => false,
		'menu_position'      => 20,
		'menu_icon'          => 'dashicons-editor-help',
		'supports'           => array( 'title', 'editor', 'excerpt', 'author', 'revisions', 'custom-fields' ),
	);

	register_post_type( 'faq_page', $args );
}
add_action( 'init', 'afp_register_post_type' );

/**
 * Enregistre la taxonomie hierarchique faq_category.
 *
 * @return void
 */
function afp_register_taxonomy() {
	$labels = array(
		'name'              => _x( 'Catégories FAQ', 'taxonomy general name', 'faq-pages' ),
		'singular_name'     => _x( 'Catégorie FAQ', 'taxonomy singular name', 'faq-pages' ),
		'search_items'      => __( 'Rechercher une catégorie', 'faq-pages' ),
		'all_items'         => __( 'Toutes les catégories', 'faq-pages' ),
		'parent_item'       => __( 'Catégorie parente', 'faq-pages' ),
		'parent_item_colon' => __( 'Catégorie parente :', 'faq-pages' ),
		'edit_item'         => __( 'Modifier la catégorie', 'faq-pages' ),
		'update_item'       => __( 'Mettre à jour la catégorie', 'faq-pages' ),
		'add_new_item'      => __( 'Ajouter une catégorie', 'faq-pages' ),
		'new_item_name'     => __( 'Nom de la nouvelle catégorie', 'faq-pages' ),
		'menu_name'         => __( 'Catégories', 'faq-pages' ),
	);

	$args = array(
		'labels'            => $labels,
		'hierarchical'      => true,
		'public'            => true,
		'show_ui'           => true,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'query_var'         => true,
		'rewrite'           => array(
			'slug'         => 'faq/categorie',
			'with_front'   => false,
			'hierarchical' => true,
		),
	);

	register_taxonomy( 'faq_category', 'faq_page', $args );
}
add_action( 'init', 'afp_register_taxonomy' );
