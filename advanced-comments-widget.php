<?php
/**
 * Advanced Comments Widget
 *
 * @package ACW_Recent_Comments
 *
 * @license     http://www.gnu.org/licenses/gpl-2.0.txt GPL-2.0+
 * @version     1.0
 *
 * Plugin Name: Advanced Comments Widget
 * Plugin URI:  http://darrinb.com/plugins/advanced-comments-widget
 * Description: A highly customizable recent comments widget.
 * Version:     1.0
 * Author:      Darrin Boutote
 * Author URI:  http://darrinb.com
 * Text Domain: advanced-comments-widget
 * Domain Path: /lang
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */


// No direct access
if ( ! defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}


/**
 * Instantiates the main Advanced Comment Widget instance
 *
 * @since 1.0
 */
function _acw_init() {
	
	include dirname( __FILE__ ) . '/inc/class-acw-recent-comments-utilities.php';
	include dirname( __FILE__ ) . '/inc/class-widget-acw-recent-comments.php';
	include dirname( __FILE__ ) . '/inc/class-acw-recent-comments.php';
	
	$ACW_Recent_Comments = new ACW_Recent_Comments( __FILE__ );
	$ACW_Recent_Comments->init();

}
add_action( 'plugins_loaded', '_acw_init', 99 );