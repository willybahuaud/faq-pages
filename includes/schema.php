<?php
/**
 * Balisage Schema.org JSON-LD pour les pages FAQ.
 *
 * Injecte un script JSON-LD dans le <head> sur les pages single faq_page.
 * Desactivable via le filtre `afp_enable_schema`.
 *
 * @package FAQ_Pages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Injecte le balisage Schema.org FAQPage en JSON-LD dans le head.
 *
 * @return void
 */
function afp_output_schema_jsonld() {
	if ( ! is_singular( 'faq_page' ) ) {
		return;
	}

	/**
	 * Permet de desactiver le balisage Schema.org.
	 *
	 * @param bool $enabled True pour activer, false pour desactiver.
	 */
	if ( ! apply_filters( 'afp_enable_schema', true ) ) {
		return;
	}

	$post    = get_queried_object();
	$content = apply_filters( 'the_content', $post->post_content );
	$content = wp_strip_all_tags( $content );

	$schema = array(
		'@context'   => 'https://schema.org',
		'@type'      => 'FAQPage',
		'mainEntity' => array(
			array(
				'@type'          => 'Question',
				'name'           => get_the_title( $post ),
				'acceptedAnswer' => array(
					'@type' => 'Answer',
					'text'  => $content,
				),
			),
		),
	);

	/**
	 * Filtre les donnees Schema.org avant serialisation JSON.
	 *
	 * @param array   $schema Les donnees Schema.org.
	 * @param WP_Post $post   Le post courant.
	 */
	$schema = apply_filters( 'afp_schema_data', $schema, $post );

	echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
}
add_action( 'wp_head', 'afp_output_schema_jsonld' );
