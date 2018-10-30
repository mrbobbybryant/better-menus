<?php
/**
 * Plugin Name:  Better WordPress Menus
 * Plugin URI:   https://github.com/mrbobbybryant/better-menus
 * Description:  Plugin saves WordPress Menu data in custom database tables for more performant queries.
 * Version:      1.0.2
 * Author:       Bobby Bryant
 * Author URI:   https://github.com/mrbobbybryant
 * License:      GPL2
 * License URI:  https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package WordPress
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'BETTER_MENUS_DIR' ) ) {
	define( 'BETTER_MENUS_DIR', dirname( __FILE__ ) );
}

if ( ! defined( 'BETTER_MENUS_FILE' ) ) {
	define( 'BETTER_MENUS_FILE', __FILE__ );
}

if ( ! defined( 'BETTER_MENUS_VERSION' ) ) {
	define( 'BETTER_MENUS_VERSION', '1.0.2' );
}

require_once 'includes/index.php';

BETTER_MENUS\setup();
