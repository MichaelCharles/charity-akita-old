<?php
/*
Plugin Name: Rebuild Permalinks
Plugin URI: http://gerrytucker.co.uk/wp-plugins/rebuild-permalinks.zip
Description: Rebuild Permalinks
Author: Gerry Tucker
Author URI: http://gerrytucker.co.uk/
Version: 1.1
License: GPLv2 or later
*/

function rebuild_permalinks_admin() {
	
	include 'rebuild-permalinks-admin.php';
	
}

function rebuild_permalinks_admin_actions() {

	add_options_page(
		__('Rebuild Permalinks'),
		__('Rebuild Permalinks'),
		'administrator',
		'rebuild-permalinks',
		'rebuild_permalinks_admin'
	);
	
}

add_action('admin_menu', 'rebuild_permalinks_admin_actions');

function rebuild_permalinks( $posttype = 'post' ) {
	
	global $wpdb;
	
	$rows = $wpdb->get_results(
		"SELECT id, post_title
		FROM $wpdb->posts
		WHERE post_status = 'publish'
		AND post_type = '$posttype'"
	);
	
	$count = 0;
	
	foreach( $rows as $row ) {

		$post_title = remove_accents( $row->post_title );
		$post_name = sanitize_title_with_dashes( $post_title );
		$guid = home_url() . '/' . sanitize_title_with_dashes( $post_title );
		$wpdb->query(
			"UPDATE $wpdb->posts
			SET post_name = '" . $post_name . "',
			guid = '" . $guid . "'
			WHERE ID = $row->id"
		);
		$count++;
	}
	
	return $count;
}
