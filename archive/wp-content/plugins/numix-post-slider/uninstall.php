<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package   Numix_Post_Slider
 * @author    Gaurav Padia <gauravpadia14u@gmail.com>
 * @author    Asalam Godhaviya <godhaviya.asalam@gmail.com>
 * @license   GPL-2.0+
 * @link      http://numixtech.com
 * @copyright 2014 Numix Techonologies
 */

// If uninstall not called from WordPress, then exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

if ( is_multisite() ) {
	global $wpdb;
	$blogs = $wpdb->get_results( "SELECT blog_id FROM {$wpdb->blogs}", ARRAY_A );
	delete_option( 'npts_db_version' );
	$GLOBALS['wpdb']->query( "DROP TABLE `".$GLOBALS['wpdb']->prefix."numix_post_slider_lite`" );
	if ( $blogs ) {
		foreach ( $blogs as $blog ) {
			switch_to_blog( $blog['blog_id'] );
			delete_option( 'npts_db_version' );
			$GLOBALS['wpdb']->query( "DROP TABLE `".$GLOBALS['wpdb']->prefix."numix_post_slider_lite`" );
			restore_current_blog();
		}
	}
}
else {
	delete_option( 'npts_db_version' );
	$GLOBALS['wpdb']->query( "DROP TABLE `".$GLOBALS['wpdb']->prefix."numix_post_slider_lite`" );
}
