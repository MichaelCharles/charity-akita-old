<?php
/**
 * Plugin Name: WP Hide Dashboard
 * Plugin URI: http://wphidedash.org/
 * Description: A simple plugin that removes the Dashboard menu, the Personal Options section and the Help link on the Profile page, hides the Dashboard links in the toolbar menu (if activated), and prevents Dashboard access to users assigned to the <em>Subscriber</em> role. Useful if you allow your subscribers to edit their own profiles, but don't want them wandering around your WordPress admin section. <strong>Note: This version requires a minimum of WordPress 3.4. If you are running a version less than that, please upgrade your WordPress install now.</strong>
 * Author: Kim Parsell
 * Author URI: http://wphidedash.org/
 * Version: 2.2.1
 * License: MIT License - http://www.opensource.org/licenses/mit-license.php
 */

/*
 * Copyright (c) 2008-2015 Kim Parsell
 * Personal Options removal code: Copyright (c) 2010 Large Interactive, LLC, Author: Matthew Pollotta
 * Originally based on IWG Hide Dashboard plugin by Thomas Schneider, Copyright (c) 2008
 * (http://www.im-web-gefunden.de/wordpress-plugins/iwg-hide-dashboard/)
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this
 * software and associated documentation files (the "Software"), to deal in the Software
 * without restriction, including without limitation the rights to use, copy, modify, merge,
 * publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons
 * to whom the Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or
 * substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

// Disallow direct access to the plugin file.
if ( basename( $_SERVER['PHP_SELF'] ) == basename (__FILE__) ) {
	die( 'Sorry, but you cannot access this page directly.' );
}

/*
 * Plugin config - user capability for the top level you want to hide everything from.
 * [default for Subscriber role = edit_posts]
 */
$wphd_user_capability = 'edit_posts';

/**
 * Handle custom Toolbar links.
 *
 * WordPress 3.1 introduced the toolbar in both the admin area and the public-facing
 * site (if enabled). For subscribers, there's now a link to the Dashboard when they
 * are on the public-facing site. Let's remove the Dashboard link and customize the
 * links in the admin bar.
 *
 * @since 2.2
 */
function wphd_custom_admin_bar_links() {
	global $blog, $current_user, $id, $wp_admin_bar, $wphd_user_capability, $wp_db_version;

	// Bail if earlier version than 3.4.0.
	if ( $wp_db_version < 20596 ) {
		return;
	}

	if ( ! current_user_can( $wphd_user_capability )
		&& is_admin_bar_showing()
		&& is_user_logged_in()
		&& $wp_db_version >= 20596
	) {

		// If single site, remove Dashboard link on public-facing site and WordPress logo menu everywhere.
		if ( ! is_multisite() && ! is_admin() ) {
			// Hide Dashboard link on public-facing site.
			$wp_admin_bar->remove_menu( 'dashboard' );
		}

		// Hide WordPress logo menu completely.
		$wp_admin_bar->remove_menu( 'wp-logo' );

		$user_id = get_current_user_id();
		$blogs = get_blogs_of_user($user_id);

		// If Multisite, check whether they are assigned to any network sites before removing links.
		if ( is_multisite() ) {

			/* Show only user account menu if the user is assigned to no sites. */
			if ( count( $wp_admin_bar->user->blogs ) == 0 ) {
				// Hide WordPress logo menu completely.
				$wp_admin_bar->remove_menu( 'wp-logo' );
				return;
			}

			// Show single site menu if the user is assigned to only 1 site.
			if ( count($wp_admin_bar->user->blogs ) == 1 ) {
				if ( ! is_admin() ) {
					// Hide Dashboard link on public-facing site.
					$wp_admin_bar->remove_menu( 'dashboard' );
				}
				// Hide WordPress logo menu completely.
				$wp_admin_bar->remove_menu( 'wp-logo' );

				//Hide My Sites menu.
				$wp_admin_bar->remove_menu( 'my-sites' );
				return;
			}

			/*
			 * Remove Dashboard and Visit Site links from My Sites menu if the user is assigned
			 * to two or more sites.
			 */
			if ( count( $wp_admin_bar->user->blogs ) >= 2 ) {
				// Hide Dashboard link on public-facing site.
				$wp_admin_bar->remove_menu( 'dashboard' );

				foreach ( (array) $wp_admin_bar->user->blogs as $blog ) {
					$menu_d = 'blog-'.$blog->userblog_id.'-d';
					$menu_v = 'blog-'.$blog->userblog_id.'-v';

					// Remove Dashboard link from My Sites menu.
					$wp_admin_bar->remove_menu( $menu_d );

					// Remove Visit Site link from My Sites menu
					$wp_admin_bar->remove_menu( $menu_v );
				}

				// Change URL for each site from admin URL to site URL */
				$blavatar = '<div class="blavatar"></div>';

				foreach ( (array) $wp_admin_bar->user->blogs as $blog ) {
					$menu_id  = "blog-{$blog->userblog_id}";
					$blogname = ucfirst( $blog->blogname );

					$wp_admin_bar->add_menu( array(
						'parent' => 'my-sites-list',
						'id'     => $menu_id,
						'title'  => $blavatar . $blogname,
						'href'   => get_site_url( $blog->userblog_id ),
					) );
				}
				return;
			}
		}
	}
}
add_action( 'wp_before_admin_bar_render', 'wphd_custom_admin_bar_links' );

/**
 * Replace toolbar Dashboard link on public-facing site with link to the user's profile.
 *
 * @since 2.2
 */
function wphd_add_admin_bar_profile_link() {
	global $blog, $current_user, $id, $wp_admin_bar, $wphd_user_capability, $wp_db_version;

	// Bail if earlier version than 3.4.0.
	if ( $wp_db_version < 20596 ) {
		return;
	}

	if ( ! current_user_can( $wphd_user_capability )
		&& is_admin_bar_showing()
		&& ! is_admin()
		&& $wp_db_version >= 20596
	) {
		$wp_admin_bar->add_menu( array(
			'parent' => 'site-name',
			'id'     => 'profile',
			'title'  => __( 'Profile' ),
			'href'   => admin_url( 'profile.php' ),
		) );
	}
}
add_action( 'admin_bar_menu', 'wphd_add_admin_bar_profile_link' );

/**
 * Hide the Dashboard & Help menus, Upgrade notices, and Personal Options section.
 *
 * @since 2.2
 */
function wphd_hide_dashboard() {
	global $blog, $current_user, $id, $parent_file, $wphd_user_capability, $wp_db_version;

	// Bail if earlier version than 3.4.0.
	if ( $wp_db_version < 20596 ) {
		return;
	}

	if ( ! current_user_can( $wphd_user_capability ) && $wp_db_version >= 20596 ) {

		// First, let's get rid of the Help menu, Update nag, Personal Options section.
		echo "\n" . '<style type="text/css" media="screen">#your-profile { display: none; } .update-nag, #contextual-help-wrap, #contextual-help-link-wrap { display: none !important; }</style>';
		echo "\n" . '<script type="text/javascript">jQuery(document).ready(function($) { $(\'form#your-profile > h3:first\').hide(); $(\'form#your-profile > table:first\').hide(); $(\'form#your-profile\').show(); });</script>' . "\n";

		// Now, let's fix the sidebar admin menu - go away, Dashboard link. */

		// If Multisite, check whether they are in the User Dashboard before removing links.
		$user_id = get_current_user_id();
		$blogs   = get_blogs_of_user( $user_id );

		if ( is_multisite() && is_admin() && empty( $blogs ) ) {
			return;
		} else {
			// Hides Dashboard menu.
			remove_menu_page( 'index.php');

			// Hides separator under Dashboard menu.
			remove_menu_page( 'separator1' );
		}


		// Redirect folks to their profile when they login, or if they try to access the Dashboard via direct URL.
		if ( 'index.php' == $parent_file ) {
			if ( headers_sent() ) {
				echo '<meta http-equiv="refresh" content="0;url=' . admin_url( 'profile.php' ) . '">';
				echo '<script type="text/javascript">document.location.href="' . admin_url( 'profile.php' ) . '"</script>';
			} else {
				if ( wp_redirect( admin_url( 'profile.php' ) ) ) {
					exit();
				}
			}
		}
	}
}
add_action( 'admin_head', 'wphd_hide_dashboard', 0 );

// That's all folks. You were expecting more?
