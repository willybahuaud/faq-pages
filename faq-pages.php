<?php
/**
 * Plugin Name:       FAQ Pages
 * Plugin URI:        https://github.com/willybahuaud/faq-pages
 * Description:       Module FAQ complet pour block themes — chaque question est une page avec sa propre URL.
 * Version:           0.1.2
 * Requires at least: 6.7
 * Requires PHP:      8.0
 * Requires Plugins:  advanced-custom-fields-pro
 * Author:            Willy Bahuaud
 * Author URI:        https://wabeo.fr
 * License:           GPL-2.0-or-later
 * Text Domain:       faq-pages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'AFP_VERSION', '0.1.2' );
define( 'AFP_FILE', __FILE__ );
define( 'AFP_PATH', plugin_dir_path( __FILE__ ) );
define( 'AFP_URL', plugin_dir_url( __FILE__ ) );
define( 'AFP_BASENAME', plugin_basename( __FILE__ ) );

require_once AFP_PATH . 'includes/cpt.php';
require_once AFP_PATH . 'includes/acf-fields.php';
require_once AFP_PATH . 'includes/blocks.php';
require_once AFP_PATH . 'includes/templates.php';
require_once AFP_PATH . 'includes/rest-api.php';
require_once AFP_PATH . 'includes/schema.php';
require_once AFP_PATH . 'includes/search.php';
require_once AFP_PATH . 'includes/assets.php';
require_once AFP_PATH . 'includes/updater.php';

/**
 * Flush les regles de reecriture a l'activation.
 *
 * @return void
 */
function afp_activate() {
	afp_register_post_type();
	afp_register_taxonomy();
	flush_rewrite_rules();
}
register_activation_hook( AFP_FILE, 'afp_activate' );

/**
 * Flush les regles de reecriture a la desactivation.
 *
 * @return void
 */
function afp_deactivate() {
	flush_rewrite_rules();
	delete_transient( 'afp_github_release' );
}
register_deactivation_hook( AFP_FILE, 'afp_deactivate' );
