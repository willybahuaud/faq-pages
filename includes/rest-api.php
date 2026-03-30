<?php
/**
 * Configuration de l'API REST pour le CPT faq_page.
 *
 * Le CPT est enregistre avec show_in_rest et rest_base, donc l'endpoint
 * /wp-json/wp/v2/faq-pages est deja fonctionnel.
 *
 * Ce fichier expose le permalink dans la reponse REST pour l'autocompletion.
 *
 * @package FAQ_Pages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ajoute le permalink dans la reponse REST du CPT faq_page.
 *
 * Le champ `link` est deja present par defaut dans la REST API WP,
 * mais on expose aussi `permalink` pour plus de clarte.
 *
 * @return void
 */
function afp_register_rest_fields() {
	register_rest_field( 'faq_page', 'permalink', array(
		'get_callback' => 'afp_get_permalink_rest_field',
		'schema'       => array(
			'description' => __( 'Permalink de la question FAQ.', 'faq-pages' ),
			'type'        => 'string',
			'format'      => 'uri',
			'context'     => array( 'view' ),
			'readonly'    => true,
		),
	) );
}
add_action( 'rest_api_init', 'afp_register_rest_fields' );

/**
 * Callback pour le champ REST permalink.
 *
 * @param array $object Le post serialise.
 * @return string Le permalink du post.
 */
function afp_get_permalink_rest_field( $object ) {
	return get_permalink( $object['id'] );
}
