<?php
/**
 * Declaration des champs ACF pour le CPT faq_page.
 *
 * @package FAQ_Pages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enregistre les groupes de champs ACF pour le CPT faq_page.
 *
 * Groupe 1 (sidebar) : flag "Top Question".
 * Groupe 2 (normal)  : CTA configurable + questions associees.
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
		'title'                 => __( 'Contenu complémentaire', 'faq-pages' ),
		'fields'                => array(
			array(
				'key'         => 'field_afp_cta_text',
				'label'       => __( 'Texte du CTA', 'faq-pages' ),
				'name'        => 'afp_cta_text',
				'type'        => 'text',
				'placeholder' => __( 'Contactez-nous', 'faq-pages' ),
				'wrapper'     => array( 'width' => '50' ),
			),
			array(
				'key'     => 'field_afp_cta_url',
				'label'   => __( 'URL du CTA', 'faq-pages' ),
				'name'    => 'afp_cta_url',
				'type'    => 'url',
				'wrapper' => array( 'width' => '50' ),
			),
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
}
add_action( 'acf/init', 'afp_register_acf_fields' );
