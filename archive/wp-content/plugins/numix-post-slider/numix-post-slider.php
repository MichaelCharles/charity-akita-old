<?php
/**
 * Numix Post Slider Carousel main plugin file
 *
 * @package   Numix_Post_Slider
 * @author    Gaurav Padia <gauravpadia14u@gmail.com>
 * @author    Asalam Godhaviya <godhaviya.asalam@gmail.com>
 * @license   GPL-2.0+
 * @link      http://numixtech.com
 * @copyright 2014 Numix Techonologies
 *
 * @wordpress-plugin
 * Plugin Name:       Numix Post Slider
 * Plugin URI:        http://www.numixtech.com
 * Description:       Variable width and infinite post slider carousel for posts
 * Version:           1.0.2
 * Author:            Numix Technologies
 * Author URI:        http://numixtech.com
 * Text Domain:       numix-post-slider
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'NUMIX_SLIDER_PATH', plugin_dir_path( __FILE__ ) );
define( 'NUMIX_SLIDER_NAME', basename( NUMIX_SLIDER_PATH ) );
define( 'NUMIX_SLIDER_URL', plugins_url( NUMIX_SLIDER_NAME ) );

/*----------------------------------------------------------------------------*
 * Public-Facing Functionality
 *----------------------------------------------------------------------------*/

require_once plugin_dir_path( __FILE__ ) . 'public/class-numix-post-slider.php';
Numix_Post_Slider::set_table_name();

/*
 * Register hooks that are fired when the plugin is activated or deactivated.
 * When the plugin is deleted, the uninstall.php file is loaded.
 *
 */
register_activation_hook( __FILE__, array( 'Numix_Post_Slider', 'activate' ) );


add_action( 'plugins_loaded', array( 'Numix_Post_Slider', 'get_instance' ) );

/*----------------------------------------------------------------------------*
 * Dashboard and Administrative Functionality
 *----------------------------------------------------------------------------*/

if ( is_admin() ) {

	require_once plugin_dir_path( __FILE__ ) . 'admin/class-numix-post-slider-admin.php';
	add_action( 'plugins_loaded', array( 'Numix_Post_Slider_Admin', 'get_instance' ) );

}
