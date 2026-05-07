<?php
/**
 * Compatibilite Polylang — helpers et hooks specifiques.
 *
 * Ce fichier est toujours inclus mais ses fonctions n'ont d'effet
 * que lorsque Polylang est actif avec 2+ langues configurees.
 *
 * @package FAQ_Pages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Verifie si le site est en mode multilangue (Polylang actif avec 2+ langues).
 *
 * @return bool
 */
function afp_is_multilingual() {
	return function_exists( 'pll_languages_list' )
		&& count( pll_languages_list() ) > 1;
}

/**
 * Retourne le nom du champ ACF top questions pour la langue courante (front).
 *
 * Sans Polylang ou avec une seule langue, retourne le nom standard.
 *
 * @return string
 */
function afp_get_top_questions_field_name() {
	if ( afp_is_multilingual() ) {
		$lang = pll_current_language();
		if ( $lang ) {
			return 'afp_top_questions_list_' . $lang;
		}
	}
	return 'afp_top_questions_list';
}

/**
 * Retourne le nom du champ ACF top questions pour la langue d'un post donne.
 *
 * Utilise pour la synchronisation post → options.
 *
 * @param int $post_id L'ID du post.
 * @return string
 */
function afp_get_top_questions_field_name_for_post( $post_id ) {
	if ( afp_is_multilingual() ) {
		$lang = pll_get_post_language( $post_id );
		if ( $lang ) {
			return 'afp_top_questions_list_' . $lang;
		}
	}
	return 'afp_top_questions_list';
}

/**
 * Retourne la cle du champ ACF top questions pour la langue d'un post donne.
 *
 * Utilise pour update_field() lors de la synchronisation.
 *
 * @param int $post_id L'ID du post.
 * @return string
 */
function afp_get_top_questions_field_key_for_post( $post_id ) {
	if ( afp_is_multilingual() ) {
		$lang = pll_get_post_language( $post_id );
		if ( $lang ) {
			return 'field_afp_top_questions_list_' . $lang;
		}
	}
	return 'field_afp_top_questions_list';
}

/**
 * Enregistre les champs ACF Top Questions avec un onglet par langue.
 *
 * Cree dynamiquement un tab + un champ relationship par langue active.
 * Chaque relationship est filtre pour n'afficher que les posts de sa langue.
 *
 * @return void
 */
function afp_register_multilingual_top_questions_fields() {
	$slugs = pll_languages_list();
	$names = pll_languages_list( array( 'fields' => 'name' ) );

	if ( empty( $slugs ) ) {
		return;
	}

	$fields = array();
	foreach ( $slugs as $index => $slug ) {
		$fields[] = array(
			'key'   => 'field_afp_top_questions_tab_' . $slug,
			'label' => $names[ $index ],
			'type'  => 'tab',
		);
		$fields[] = array(
			'key'           => 'field_afp_top_questions_list_' . $slug,
			'label'         => __( 'Questions mises en avant', 'faq-pages' ),
			'name'          => 'afp_top_questions_list_' . $slug,
			'type'          => 'relationship',
			'instructions'  => __( 'Glisser-déposer pour réordonner. La synchronisation avec le toggle par question est automatique.', 'faq-pages' ),
			'post_type'     => array( 'faq_page' ),
			'filters'       => array( 'search' ),
			'return_format' => 'id',
		);
	}

	acf_add_local_field_group( array(
		'key'                   => 'group_afp_options',
		'title'                 => __( 'Top Questions', 'faq-pages' ),
		'fields'                => $fields,
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

/**
 * Filtre la requete du champ relationship pour n'afficher que les posts
 * de la langue correspondant a l'onglet.
 *
 * Le slug de langue est extrait du nom du champ (afp_top_questions_list_{lang}).
 *
 * @param array $args    Les arguments WP_Query.
 * @param array $field   Le champ ACF.
 * @param int   $post_id L'ID du post (ou 'options').
 * @return array
 */
function afp_filter_relationship_by_language( $args, $field, $post_id ) {
	if ( strpos( $field['name'], 'afp_top_questions_list_' ) !== 0 ) {
		return $args;
	}

	$lang         = str_replace( 'afp_top_questions_list_', '', $field['name'] );
	$args['lang'] = $lang;

	return $args;
}

/**
 * Enregistre le filtre relationship si Polylang est actif.
 *
 * @return void
 */
function afp_register_polylang_hooks() {
	if ( ! afp_is_multilingual() ) {
		return;
	}

	add_filter( 'acf/fields/relationship/query', 'afp_filter_relationship_by_language', 10, 3 );
}
add_action( 'init', 'afp_register_polylang_hooks' );
