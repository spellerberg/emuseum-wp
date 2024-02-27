<?php
/**
 * Plugin Name: eMuseum WP
 * Description: Integration with eMuseum.
 * Plugin URI: https://spellerberg.org/
 * Author: Zack Rothauser
 * Author URI: https://spellerberg.org/
 * Version: 1.0
 * License: GPL2
 * Text Domain: Text Domain
 * Domain Path: Domain Path
 *
 * @package mocp-emuseum-integration
 */

namespace MoCP\EMuseum_Integration;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Useful global constants.
define( __NAMESPACE__ . '\VERSION', '0.0.1' );
define( __NAMESPACE__ . '\URL', plugin_dir_url( __FILE__ ) );
define( __NAMESPACE__ . '\PATH', dirname( __FILE__ ) . '/' );
define( __NAMESPACE__ . '\INC', PATH . 'includes/' );

// Require API.
require_once INC . 'api/emuseum-api-proxy.php';
require_once INC . 'api/class-all-content-endpoint.php';

// Require blocks.
require_once PATH . 'blocks/emuseum-object/emuseum-object.php';

/**
 * Set up the plugin on init.
 */
function setup_plugin() {
	( new All_Content_Endpoint() )->init();
}
add_action( 'init', __NAMESPACE__ . '\setup_plugin' );
