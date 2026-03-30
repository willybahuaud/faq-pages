<?php
/**
 * Systeme de mise a jour automatique via les releases GitHub.
 *
 * Verifie les releases du depot GitHub et injecte les mises a jour
 * dans le systeme natif de WordPress.
 *
 * @package FAQ_Pages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'AFP_GITHUB_OWNER', 'willybahuaud' );
define( 'AFP_GITHUB_REPO', 'faq-pages' );
define( 'AFP_GITHUB_API_URL', 'https://api.github.com' );
define( 'AFP_UPDATER_CACHE_DURATION', 43200 );

/**
 * Verifie si une nouvelle release est disponible sur GitHub.
 *
 * @param object $transient Le transient des mises a jour plugins.
 * @return object Le transient modifie.
 */
function afp_check_for_update( $transient ) {
	if ( empty( $transient->checked ) ) {
		return $transient;
	}

	$release = afp_get_github_release();

	if ( ! $release ) {
		return $transient;
	}

	$latest_version  = afp_parse_version( $release->tag_name );
	$current_version = AFP_VERSION;

	if ( version_compare( $latest_version, $current_version, '>' ) ) {
		$download_url = afp_get_download_url( $release );

		if ( $download_url ) {
			$transient->response[ AFP_BASENAME ] = (object) array(
				'slug'        => 'faq-pages',
				'plugin'      => AFP_BASENAME,
				'new_version' => $latest_version,
				'url'         => $release->html_url,
				'package'     => $download_url,
				'icons'       => array(),
				'banners'     => array(),
				'tested'      => '',
				'requires'    => '6.7',
			);
		}
	}

	return $transient;
}
add_filter( 'pre_set_site_transient_update_plugins', 'afp_check_for_update' );

/**
 * Fournit les details du plugin pour le popup "Voir les details".
 *
 * @param false|object|array $result  Le resultat par defaut.
 * @param string             $action  Le type d'action API.
 * @param object             $args    Les arguments de la requete.
 * @return false|object Les infos du plugin ou false.
 */
function afp_plugin_info( $result, $action, $args ) {
	if ( 'plugin_information' !== $action ) {
		return $result;
	}

	if ( ! isset( $args->slug ) || 'faq-pages' !== $args->slug ) {
		return $result;
	}

	$release = afp_get_github_release();

	if ( ! $release ) {
		return $result;
	}

	$latest_version = afp_parse_version( $release->tag_name );

	return (object) array(
		'name'          => 'FAQ Pages',
		'slug'          => 'faq-pages',
		'version'       => $latest_version,
		'author'        => '<a href="https://wabeo.fr">Willy Bahuaud</a>',
		'homepage'      => 'https://github.com/' . AFP_GITHUB_OWNER . '/' . AFP_GITHUB_REPO,
		'requires'      => '6.7',
		'tested'        => '',
		'downloaded'    => 0,
		'last_updated'  => $release->published_at,
		'sections'      => array(
			'description' => '<p>' . esc_html__( 'Module FAQ complet pour block themes — chaque question est une page avec sa propre URL.', 'faq-pages' ) . '</p>',
			'changelog'   => afp_format_changelog( $release->body ),
		),
		'download_link' => afp_get_download_url( $release ),
	);
}
add_filter( 'plugins_api', 'afp_plugin_info', 10, 3 );

/**
 * Corrige le nom du repertoire apres decompression du ZIP GitHub.
 *
 * GitHub nomme les dossiers "repo-tag" au lieu de "repo".
 *
 * @param string       $source        Le chemin du repertoire extrait.
 * @param string       $remote_source Le chemin source distant.
 * @param \WP_Upgrader $upgrader      L'instance de l'upgrader.
 * @param array        $hook_extra    Les donnees supplementaires.
 * @return string|\WP_Error Le chemin corrige ou une erreur.
 */
function afp_fix_directory_name( $source, $remote_source, $upgrader, $hook_extra ) {
	global $wp_filesystem;

	if ( ! isset( $hook_extra['plugin'] ) || $hook_extra['plugin'] !== AFP_BASENAME ) {
		return $source;
	}

	$plugin_slug       = 'faq-pages';
	$source_normalized = untrailingslashit( $source );
	$expected_dir      = untrailingslashit( trailingslashit( $remote_source ) . $plugin_slug );

	if ( $source_normalized === $expected_dir ) {
		return $source;
	}

	if ( basename( $source_normalized ) === $plugin_slug ) {
		return $source;
	}

	if ( $wp_filesystem->move( $source, trailingslashit( $expected_dir ) ) ) {
		return trailingslashit( $expected_dir );
	}

	return new \WP_Error(
		'rename_failed',
		__( 'Impossible de renommer le repertoire du plugin.', 'faq-pages' )
	);
}
add_filter( 'upgrader_source_selection', 'afp_fix_directory_name', 10, 4 );

/**
 * Recupere les donnees de la derniere release GitHub.
 *
 * Utilise un double cache : variable statique + transient (12h).
 *
 * @return object|false Les donnees de la release ou false en cas d'erreur.
 */
function afp_get_github_release() {
	static $cache = null;

	if ( null !== $cache ) {
		return $cache;
	}

	$cached = get_transient( 'afp_github_release' );

	if ( false !== $cached ) {
		$cache = $cached;
		return $cached;
	}

	$api_url = sprintf(
		'%s/repos/%s/%s/releases/latest',
		AFP_GITHUB_API_URL,
		AFP_GITHUB_OWNER,
		AFP_GITHUB_REPO
	);

	$response = wp_remote_get(
		$api_url,
		array(
			'timeout' => 10,
			'headers' => array(
				'Accept'     => 'application/vnd.github.v3+json',
				'User-Agent' => 'FAQPages/' . AFP_VERSION,
			),
		)
	);

	if ( is_wp_error( $response ) ) {
		$cache = false;
		return false;
	}

	$code = wp_remote_retrieve_response_code( $response );

	if ( 200 !== $code ) {
		$cache = false;
		return false;
	}

	$body = wp_remote_retrieve_body( $response );
	$data = json_decode( $body );

	if ( empty( $data ) || ! isset( $data->tag_name ) ) {
		$cache = false;
		return false;
	}

	set_transient( 'afp_github_release', $data, AFP_UPDATER_CACHE_DURATION );
	$cache = $data;

	return $data;
}

/**
 * Extrait le numero de version depuis un tag GitHub.
 *
 * @param string $tag_name Le nom du tag (ex: "v1.0.0").
 * @return string Le numero de version (ex: "1.0.0").
 */
function afp_parse_version( $tag_name ) {
	return ltrim( $tag_name, 'vV' );
}

/**
 * Recupere l'URL de telechargement du ZIP depuis une release.
 *
 * Prefere un asset ZIP uploade, sinon utilise le zipball automatique.
 *
 * @param object $release Les donnees de la release GitHub.
 * @return string|false L'URL de telechargement ou false.
 */
function afp_get_download_url( $release ) {
	if ( ! empty( $release->assets ) && is_array( $release->assets ) ) {
		foreach ( $release->assets as $asset ) {
			if ( isset( $asset->content_type ) && 'application/zip' === $asset->content_type ) {
				return $asset->browser_download_url;
			}
			if ( isset( $asset->name ) && '.zip' === substr( $asset->name, -4 ) ) {
				return $asset->browser_download_url;
			}
		}
	}

	if ( ! empty( $release->zipball_url ) ) {
		return $release->zipball_url;
	}

	return false;
}

/**
 * Convertit le body markdown d'une release en HTML basique.
 *
 * @param string $body Le body de la release (Markdown).
 * @return string Le HTML formate.
 */
function afp_format_changelog( $body ) {
	if ( empty( $body ) ) {
		return '<p>' . esc_html__( 'Pas de notes de version disponibles.', 'faq-pages' ) . '</p>';
	}

	$html = esc_html( $body );
	$html = nl2br( $html );

	$html = preg_replace( '/^- (.+)$/m', '<li>$1</li>', $html );
	$html = preg_replace( '/(<li>.*<\/li>)/s', '<ul>$1</ul>', $html );

	return wp_kses( $html, array(
		'ul' => array(),
		'li' => array(),
		'br' => array(),
		'p'  => array(),
	) );
}
