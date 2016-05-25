<?php
/*
Plugin Name: 	R3DF - Multisite Language Indicator
Description:    Indicates the site language beside the site title in the toolbar to help identify sites
Plugin URI:		http://r3df.com/
Version: 		1.0.10
Text Domain:	r3df-multisite-language-indicator
Domain Path: 	/lang/
Author:         R3DF
Author URI:     http://r3df.com
Author email:   plugin-support@r3df.com
Copyright: 		R-Cubed Design Forge
*/

/*  Copyright 2015 R-Cubed Design Forge

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// TODO
// Test rtl languages

// Avoid direct calls to this file where wp core files not present
if ( ! function_exists( 'add_action' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

if ( function_exists( 'is_multisite' ) && is_multisite() ) {
	$r3df_multisite_language_indicator = new R3DF_Multisite_Language_Indicator();
}


/**
 * Class R3DF_Dashboard_Language
 *
 */
class R3DF_Multisite_Language_Indicator {
	// options defaults
	private $_global_defaults = array(
		'db_version' => '1.0',
		'save_settings_on_uninstall' => false,
	);
	//private $_local_defaults = array(
	//);
	private $_user_defaults = array(
		'enable_locale_flags' => array( 'before' => true, 'after' => false ),
		'enable_locale_abbreviations' => array( 'before' => false, 'after' => false ),
		'display_language' => array( 'before' => false, 'after' => false ),
	);
	private $_global_options = array();
	//private $_local_options = array();
	private $_user_options = array();


	/**
	 * Class constructor
	 *
	 */
	function __construct() {
		if ( ! is_admin() ) {
			return;
		}

		// get plugin options - can't get user options yet, user is not identified at this point, add_action for them
		$this->_global_options = get_site_option( 'r3df_multisite_language_indicator_global', $this->_global_defaults );
		//$this->_local_options = get_option( 'r3df_multisite_language_indicator', $this->_local_defaults );
		add_action( 'plugins_loaded', array( $this, 'load_user_options' ), 0 );

		// register plugin activation hook
		register_activation_hook( plugin_basename( __FILE__ ), array( &$this, 'activate_plugin' ) );

		// Add plugin text domain hook
		add_action( 'plugins_loaded', array( &$this, '_text_domain' ) );

		// load admin css and javascript
		add_action( 'admin_enqueue_scripts', array( $this, '_load_admin_scripts_and_styles' ) );

		// Add plugin settings page
		add_action( 'admin_menu', array( $this, 'register_r3df_mli_settings_page' ) );
		add_action( 'admin_init', array( $this, 'r3df_mli_settings' ) );

		// User profile settings
		add_action( 'show_user_profile', array( &$this, 'user_profile_settings' ) );
		add_action( 'edit_user_profile', array( &$this, 'user_profile_settings' ) );
		add_action( 'personal_options_update', array( &$this, 'user_profile_settings_update' ) );
		add_action( 'edit_user_profile_update', array( &$this, 'user_profile_settings_update' ) );

		// Add language indicators to toolbar
		add_action( 'wp_before_admin_bar_render', array( $this, 'add_toolbar_indicators' ) );
	}

	/**
	 * Loads user options
	 *
	 */
	function load_user_options() {
		$this->_user_options = get_user_meta( get_current_user_id(), 'r3df_multisite_language_indicator', true );

		// make sure that user options are set
		if ( empty( $this->_user_options ) ) {
			$this->_user_options = $this->_user_defaults;
		}
	}


	/* ****************************************************
     * Toolbar Language Flag Functions
     * ****************************************************/

	/**
	 * Adds the toolbar language indicators
	 *
	 */
	function add_toolbar_indicators() {
		global $wp_admin_bar;

		// if no toolbar or no indicators, bail...
		if ( ( function_exists( 'is_admin_bar_showing' ) && ! is_admin_bar_showing() ) ||
		      ( empty( $this->_user_options['enable_locale_flags']['before'] ) && empty( $this->_user_options['enable_locale_flags']['after'] ) &&
		        empty( $this->_user_options['enable_locale_abbreviations']['before'] ) && empty( $this->_user_options['enable_locale_abbreviations']['after'] ) &&
		        empty( $this->_user_options['display_language']['before'] ) && empty( $this->_user_options['display_language']['after'] )
		      ) ) {
			return;
		}

		// Add indicators to sites in My Sites list
		foreach ( wp_get_sites() as $site ) {
			// get the site defined locale
			$locale = get_blog_option( $site['blog_id'], 'WPLANG' );

			$country_code = strtolower( $this->get_locale_country_code( $locale ) ? $this->get_locale_country_code( $locale ) : 'Unknown' );
			$site_name = $wp_admin_bar->get_node( 'blog-'.$site['blog_id'] );
			if ( ! empty( $site_name ) ) {
				$site_name->title = str_replace( '<div class="blavatar"></div>', '', $site_name->title );
				// language
				$lang = $this->get_locale_language_name( $locale, 'english' ) ? $this->get_locale_language_name( $locale, 'english' ) : 'Unknown';
				if ( ! empty( $this->_user_options['display_language']['before'] ) ) {
					$site_name->title = '<span class="mli_lang mli_lang-'.$lang.'">'.$this->localize_language_name( $lang ).' - ' . $site_name->title;
				}
				if ( ! empty( $this->_user_options['display_language']['after'] ) ) {
					$site_name->title = $site_name->title . ' <span class="mli_lang mli_lang-'.$lang.'"> - '.$this->localize_language_name( $lang ).'</span>';
				}
				// locale
				if ( ! empty( $this->_user_options['enable_locale_abbreviations']['before'] ) ) {
					$site_name->title = '<span class="mli_locale mli_locale-'.$locale.'">('.$locale.')</span> ' . $site_name->title;
				}
				if ( ! empty( $this->_user_options['enable_locale_abbreviations']['after'] ) ) {
					$site_name->title = $site_name->title . ' <span class="mli_locale mli_locale-'.$locale.'">('.$locale.')</span>';
				}
				// flags
				if ( ! empty( $this->_user_options['enable_locale_flags']['before'] ) ) {
					if ( ! empty( $this->_user_options['site_flag'][ $site['blog_id'] ] ) && 'auto' != $this->_user_options['site_flag'][ $site['blog_id'] ] ) {
						$country_code = strtolower( $this->_user_options['site_flag'][ $site['blog_id'] ] ? $this->_user_options['site_flag'][ $site['blog_id'] ] : 'Unknown' );
					}
					$site_name->title = '<span class="mli-flag mli-flag-'.$country_code.( is_rtl() ? ' rtl' : '' ).'"></span>' . $site_name->title;
				} else {
					$site_name->title = '<div class="blavatar"></div>' . $site_name->title;
				}
				$wp_admin_bar->add_node( $site_name );
			}
		}

		// Add indicators to site name
		$locale = get_option( 'WPLANG' );
		$country_code = strtolower( $this->get_locale_country_code( $locale ) ? $this->get_locale_country_code( $locale ) : 'Unknown' );
		$site_name = $wp_admin_bar->get_node( 'site-name' );
		// language
		$lang = $this->get_locale_language_name( $locale, 'english' ) ? $this->get_locale_language_name( $locale, 'english' ) : 'Unknown';
		if ( ! empty( $this->_user_options['display_language']['before'] ) ) {
			$site_name->title = '<span class="mli_lang mli_lang-'.$lang.'">'.$this->localize_language_name( $lang ).' - ' . $site_name->title;
		}
		if ( ! empty( $this->_user_options['display_language']['after'] ) ) {
			$site_name->title = $site_name->title . ' <span class="mli_lang mli_lang-'.$lang.'"> - '.$this->localize_language_name( $lang ).'</span>';
		}
		// locale
		if ( ! empty( $this->_user_options['enable_locale_abbreviations']['before'] ) ) {
			$site_name->title = '<span class="mli_locale mli_locale-'.$locale.'">('.$locale.')</span> ' . $site_name->title;
		}
		if ( ! empty( $this->_user_options['enable_locale_abbreviations']['after'] ) ) {
			$site_name->title = $site_name->title . ' <span class="mli_locale mli_locale-'.$locale.'">('.$locale.')</span>';
		}
		// flag
		if ( ! empty( $this->_user_options['enable_locale_flags']['before'] ) ) {
			if ( ! empty( $this->_user_options['site_flag'][ get_current_blog_id() ] ) && 'auto' != $this->_user_options['site_flag'][ get_current_blog_id() ] ) {
				$country_code = strtolower( $this->_user_options['site_flag'][ get_current_blog_id() ] ? $this->_user_options['site_flag'][ get_current_blog_id() ] : 'Unknown' );
			}
			$site_name->title = '<span class="mli-flag mli-flag-'.$country_code.( is_rtl() ? ' rtl' : '' ).'"></span>' . $site_name->title;
			$site_name->meta['class'] = isset( $site_name->meta['class'] ) ? $site_name->meta['class'] . ' hide-site-name-icon' : 'hide-site-name-icon';
		}
		$wp_admin_bar->add_node( $site_name );
	}


	/* ****************************************************
	 * User profile settings functions
	 * ****************************************************/

	/**
	 * Displays settings on user profile page
	 *
	 * @param $user
	 *
	 */
	function user_profile_settings( $user ) {
		if ( get_current_user_id() == $user->ID ) {
			$options = $this->_user_options;
		} else {
			$options = get_user_meta( $user->ID, 'r3df_multisite_language_indicator', true );
			// make sure that user options are set
			if ( empty( $options ) ) {
				$options = $this->_user_defaults;
			}
		}
		?>
		<h3><?php _e( 'Multisite Language Indicator Settings', 'r3df-multisite-language-indicator' ); ?></h3>
		<table class="form-table">
			<tbody>
			<tr valign="top">
				<th scope="row"><?php _e( 'Choose indicators to display', 'r3df-multisite-language-indicator' ); ?></th>
				<td>
					<label for="enable_locale_flags[before]"><input type="checkbox" id="enable_locale_flags[before]" name="r3df_multisite_language_indicator[enable_locale_flags][before]"<?php echo checked( ! empty( $options['enable_locale_flags']['before'] ), true, false ); ?> value="true">
						<?php _e( 'Country flags - before site name', 'r3df-multisite-language-indicator' ); ?></label>
					<br>
					<label for="enable_locale_abbreviations[before]"><input type="checkbox" id="enable_locale_abbreviations[before]" name="r3df_multisite_language_indicator[enable_locale_abbreviations][before]"<?php echo checked( ! empty( $options['enable_locale_abbreviations']['before'] ), true, false ); ?> value="true">
						<?php _e( 'Locale code - before site name', 'r3df-multisite-language-indicator' ); ?></label>
					<br>
					<label for="enable_locale_abbreviations[after]"><input type="checkbox" id="enable_locale_abbreviations[after]" name="r3df_multisite_language_indicator[enable_locale_abbreviations][after]"<?php echo checked( ! empty( $options['enable_locale_abbreviations']['after'] ), true, false ); ?> value="true">
						<?php _e( 'Locale code - after site name', 'r3df-multisite-language-indicator' ); ?></label>
					<br>
					<label for="display_language[before]"><input type="checkbox" id="display_language[before]" name="r3df_multisite_language_indicator[display_language][before]"<?php echo checked( ! empty( $options['display_language']['before'] ), true, false ) ?> value="true">
						<?php _e( 'Site language - before site name', 'r3df-multisite-language-indicator' ); ?></label>
					<br>
					<label for="display_language[after]"><input type="checkbox" id="display_language[after]" name="r3df_multisite_language_indicator[display_language][after]"<?php echo checked( ! empty( $options['display_language']['after'] ), true, false ); ?> value="true">
						<?php _e( 'Site language - after site name', 'r3df-multisite-language-indicator' ); ?></label>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e( 'Site flag settings', 'r3df-multisite-language-indicator' ); ?></th>
				<td>
					<table class="mli-site-table">
						<tbody>
							<?php
							foreach ( wp_get_sites() as $site ) {
								// get the site defined locale
								$language_locale = get_blog_option( $site['blog_id'], 'WPLANG' ); ?>
								<tr valign="top">
									<td>
										<label for="site_flag[<?php echo $site['blog_id'] ?>]"><?php echo $site['domain'] . ( $site['path'] != '/' ? $site['path']: ''); ?></label>
									</td>
									<td>
										<select id="site_flag[<?php echo $site['blog_id'] ?>]" name="r3df_multisite_language_indicator[site_flag][<?php echo $site['blog_id'] ?>]">
											<?php
											$country_code = strtolower( $this->get_locale_country_code( $language_locale ) ? $this->get_locale_country_code( $language_locale ) : 'Unknown' );
											echo '<option class="mli-flag mli-flag-' . $country_code . ( is_rtl() ? ' rtl' : '' ) . '" value="auto"' . selected( $options['site_flag'][ $site['blog_id'] ], 'auto' ) . '>' . __( 'Auto detect', 'r3df-multisite-language-indicator' ) . '</option>';
											foreach ( $this->get_country_names() as $country_code => $country_name ) {
												echo '<option class="mli-flag mli-flag-' . strtolower( $country_code ) . ( is_rtl() ? ' rtl' : '' ) . '" value="' . $country_code . '"' . selected( $options['site_flag'][ $site['blog_id'] ], $country_code ) . '>' . $country_name . '</option>';
											}
											?>
										</select>
									</td>
								</tr>
							<?php } ?>
						</tbody>
					</table>
					<small>
						<?php _e( 'Auto detect uses the country code of the site\'s locale setting,<br>or you can choose a flag to represent the site.', 'r3df-multisite-language-indicator' ); ?>
					</small>
				</td>
			</tr>
			</tbody>
		</table>

	<?php
	}

	/**
	 * Saves user settings on profile update
	 *
	 * @param $user_id
	 *
	 */
	function user_profile_settings_update( $user_id ) {
		if ( ! empty( $_POST['r3df_multisite_language_indicator'] ) ) {
			$input = $_POST['r3df_multisite_language_indicator'];

			$user_settings['enable_locale_flags']['before'] = ! empty( $input['enable_locale_flags']['before'] ) ? true : false;
			$user_settings['enable_locale_flags']['after']  = ! empty( $input['enable_locale_flags']['after'] ) ? true : false;

			$user_settings['enable_locale_abbreviations']['before'] = ! empty( $input['enable_locale_abbreviations']['before'] ) ? true : false;
			$user_settings['enable_locale_abbreviations']['after']  = ! empty( $input['enable_locale_abbreviations']['after'] ) ? true : false;

			$user_settings['display_language']['before'] = ! empty( $input['display_language']['before'] ) ? true : false;
			$user_settings['display_language']['after']  = ! empty( $input['display_language']['after'] ) ? true : false;

			$country_codes                  = array_keys( $this->get_country_names() );
			$country_codes[]                = 'auto';
			$user_settings['site_flag'] = array();
			if ( isset( $input['site_flag'] ) && is_array( $input['site_flag'] ) ) {
				foreach ( $input['site_flag'] as $blog_id => $country_code ) {
					if ( in_array( $country_code, $country_codes ) ) {
						$user_settings['site_flag'][ $blog_id ] = $country_code;
					}
				}
			}
			update_user_meta( $user_id, 'r3df_multisite_language_indicator', $user_settings );
		}
	}


	/*
	 * ****************************************************
	 * Settings Page & Functions
	 * ****************************************************/

	/**
	 * Register plugin settings page
	 *
	 */
	function register_r3df_mli_settings_page() {
		$my_admin_page = add_submenu_page( 'options-general.php', __( 'R3DF - Multisite Language Indicator', 'r3df-multisite-language-indicator' ), __( 'Language Indicator', 'r3df-multisite-language-indicator' ), 'manage_options', 'r3df-multisite-language-indicator', array(
			$this,
			'r3df_mli_settings_page',
		) );
		add_action( 'load-'.$my_admin_page, array( $this, 'add_help_tabs' ) );
	}

	/**
	 * Settings page html content
	 *
	 */
	function r3df_mli_settings_page() { ?>
		<div class="wrap">
			<div id="icon-tools" class="icon32"></div>
			<h2><?php _e( 'R3DF - Multisite Language Indicator', 'r3df-multisite-language-indicator' ); ?></h2>
			<?php printf( __( 'Please see your %s page to select display options for site indicators.' , 'r3df-multisite-language-indicator' ), '<a href="profile.php" target="_blank">'.__( 'Profile','r3df_dashboard_language_switcher' ) .'</a>' );?>

			<form action="options.php" method="post">
				<?php settings_fields( 'r3df_multisite_language_indicator' ); ?>
				<?php do_settings_sections( 'r3df_mli' ); ?>
				<input class="button button-primary" name="Submit" type="submit"
				       value="<?php esc_attr_e( 'Save Changes', 'r3df-multisite-language-indicator' ); ?>"/>
			</form>
		</div>
	<?php }

	/**
	 * Add the settings
	 *
	 */
	function r3df_mli_settings() {
		// Option name in db
		register_setting( 'r3df_multisite_language_indicator', 'r3df_multisite_language_indicator', array( $this, 'r3df_mli_options_validate' ) );

		// Local site settings
		//add_settings_section( 'r3df_mli_local_options', __( 'Options for this site:', 'r3df-multisite-language-indicator' ), array( $this, 'local_options_form_section' ), 'r3df_mli' );

		// Global site settings
		add_settings_section( 'r3df_mli_global_options', __( 'Global options for all sites:', 'r3df-multisite-language-indicator' ), array(
			$this,
			'global_options_form_section',
		), 'r3df_mli' );
		if ( current_user_can( 'manage_network' ) ) {
			add_settings_field( 'save_settings_on_uninstall', __( 'Save all settings at plugin uninstall:', 'r3df-multisite-language-indicator' ), array(
				$this,
				'save_settings_on_uninstall_form_item',
			), 'r3df_mli', 'r3df_mli_global_options', array( 'label_for' => 'save_settings_on_uninstall' ) );
		}
	}

	/**
	 * Validate the settings
	 *
	 * @param $input
	 *
	 * @return mixed
	 */
	function r3df_mli_options_validate( $input ) {

		if ( current_user_can( 'manage_network' ) ) {
			// global settings - save directly with option update
			$global_settings['db_version']           = $this->_global_defaults['db_version'];
			$global_settings['save_settings_on_uninstall'] = ( ! empty( $input['save_settings_on_uninstall'] ) ) ? true : false;

			update_site_option( 'r3df_multisite_language_indicator_global', $global_settings );
		}

		// local settings (for current site) - save with settings api
		// No local settings currently
		$local_settings = false;

		return $local_settings;
	}

	/**
	 * Settings page html content - local_options section
	 *
	 * @param $args
	 *
	 */
	function local_options_form_section( $args ) {
		echo '<hr>'.__( 'The options in this section are for this site only. The settings in this section affect all users.', 'r3df-multisite-language-indicator' );
	}

	/**
	 * Settings page html content - global_options section
	 *
	 * @param $args
	 *
	 */
	function global_options_form_section( $args ) {
		echo '<hr>' . __( 'The options in this section are for ALL sites in the network.', 'r3df-multisite-language-indicator' );
		echo '<br><small>'.__( 'Only users who are Super Admins can see/modify these settings.', 'r3df-multisite-language-indicator' ).'</small>';
		if ( ! current_user_can( 'manage_network' ) ) {
			echo '<table class="form-table"></table>';
		}
	}

	/**
	 * Settings page html content - save_settings_on_uninstall
	 *
	 * @param $args
	 *
	 */
	function save_settings_on_uninstall_form_item( $args ) {
		echo '<input type="checkbox" id="save_settings_on_uninstall" name="r3df_multisite_language_indicator[save_settings_on_uninstall]" '. checked( $this->_global_options['save_settings_on_uninstall'], true, false ) . ' value="true" >';
		echo '<label for="save_settings_on_uninstall">' . __( 'Yes', 'r3df-multisite-language-indicator' ) .'</label>';
	}


	/* ****************************************************
	 * Help tab functions
	 * ****************************************************/

	/**
	 * Add help tabs
	 *
	 */
	function add_help_tabs() {
		$screen = get_current_screen();
		$screen->add_help_tab(array(
			'title' => __( 'Options', 'r3df-multisite-language-indicator' ),
			'id' => 'options',
			'content' => '',
			'callback' => array( $this, 'help_global_options' )
		));
	}

	/**
	 *
	 */
	function help_global_options() {
		?>
		<h2><?php echo 'R3DF - Multisite Language Indicator'; ?></h2>
		<h3><?php echo __( 'Options', 'r3df-multisite-language-indicator' ); ?></h3>
		<p><?php echo __( 'TBD', 'r3df-multisite-language-indicator' ); ?></p>
		<p class="r3df-help">
			<a href="http://wordpress.org/extend/plugins/r3df-multisite-language-indicator/" target="_blank"><?php echo __( 'Plugin Directory', 'r3df-multisite-language-indicator' ) ?></a> |
			<a href="http://wordpress.org/extend/plugins/r3df-multisite-language-indicator/changelog/" target="_blank"><?php echo __( 'Change Logs', 'r3df-multisite-language-indicator' ) ?></a>
			<span class="alignright">&copy; 2015 <?php echo __( 'by', 'r3df-multisite-language-indicator' ) ?> <a href="http://r3df.com/" target="_blank">R3DF</a></span>
		</p>
		<?php
	}


	/* ****************************************************
	 * Utility functions
	 * ****************************************************/

	/**
	 * Plugin language file loader
	 *
	 */
	function _text_domain() {
		// Load language files - files must be r3df-mli-xx_XX.mo
		load_plugin_textdomain( 'r3df-multisite-language-indicator', false, dirname( plugin_basename( __FILE__ ) ) . '/lang' );
	}

	/**
	 * Admin scripts and styles loader
	 *
	 * @param $hook
	 *
	 */
	function _load_admin_scripts_and_styles( $hook ) {
		// Get the plugin version (added to js file loaded to clear browser caches on change)
		$plugin = get_file_data( __FILE__, array( 'Version' => 'Version' ) );

		// Register and enqueue the global css files
		wp_register_style( 'r3df_mli_admin_style', plugins_url( '/css/admin-style.css', __FILE__ ), false, $plugin['Version'] );
		wp_enqueue_style( 'r3df_mli_admin_style' );

		wp_register_style( 'r3df_mli_flag_icon_style', plugins_url( '/css/flag-icon-css.css', __FILE__ ), false, $plugin['Version'] );
		wp_enqueue_style( 'r3df_mli_flag_icon_style' );

		// Register and enqueue the plugin settings page css files
		if ( 'settings_page_r3df-multisite-language-indicator' == $hook ) {
			wp_register_style( 'r3df_mli_plugin_page_style', plugins_url( '/css/plugin-page-style.css', __FILE__ ), false, $plugin['Version'] );
			wp_enqueue_style( 'r3df_mli_plugin_page_style' );
		}
	}


	/* ****************************************************
	 * Activate and deactivate functions
	 * ****************************************************/

	/**
	 * Initialize options and abort with error on insufficient requirements
	 *
	 */
	function activate_plugin() {
		global $wp_version;
		$version_error = array();
		if ( ! version_compare( $wp_version, '4.1', '>=' ) ) {
			$version_error['WordPress Version'] = array( 'required' => '4.1', 'found' => $wp_version );
		}
		//if ( ! version_compare( phpversion(), '4.4.3', '>=' ) ) {
		//	$error['PHP Version'] = array( 'required' => '4.4.3', 'found' => phpversion() );
		//}
		if ( 0 != count( $version_error ) ) {
			$current = get_option( 'active_plugins' );
			array_splice( $current, array_search( plugin_basename( __FILE__ ), $current ), 1 );
			update_option( 'active_plugins', $current );
			if ( 0 != count( $version_error ) ) {
				echo '<table>';
				echo '<tr class="r3df-header"><td><strong>'.__( 'Plugin can not be activated.', 'r3df-multisite-language-indicator' ) . '</strong></td><td> | '.__( 'required', 'r3df-multisite-language-indicator' ) . '</td><td> | '.__( 'actual', 'r3df-multisite-language-indicator' ) . '</td></tr>';
				foreach ( $version_error as $key => $value ) {
					echo '<tr><td>'.$key.'</td><td align=\"center\"> &gt;= <strong>' . $value['required'] . '</strong></td><td align="center"><span class="r3df-alert">' . $value['found'] . '</span></td></tr>';
				}
				echo '</table>';
			}
			exit();
		}
	}


	/* ****************************************************
	 * General locale & language functions
     * ****************************************************/

	/**
	 * Return installed languages
	 *
	 * @return array
	 *
	 */
	function get_installed_languages() {
		$languages = get_available_languages();
		$languages[] = 'en_US';
		sort( $languages );

		return $languages;
	}

	/**
	 * Return alpha-2 code for locale's language
	 *
	 * @param $locale
	 *
	 * @return string
	 *
	 */
	function get_locale_language_code( $locale ) {
		$language_code = false;
		if ( $position = strpos( $locale, '_' ) ) {
			$language_code = substr( $locale, 0, $position );
			if ( empty( $language_code ) ) {
				$language_code = false;
			}
		}
		return $language_code;
	}

	/**
	 * Return language name for a locale (in native tongue, or english)
	 *
	 * @param $locale
	 * @param $mode
	 *
	 * @return string
	 *
	 */
	function get_locale_language_name( $locale, $mode = 'native' ) {
		if ( ! in_array( $mode, array( 'english', 'native', 'localized' ) ) ) {
			$mode = 'native';
		}
		$localized = false;
		if ( 'localized' == $mode ) {
			$mode = 'english';
			$localized = true;
		}
		// Get names using concepts from wp_dropdown_languages in I10n.php
		require_once( ABSPATH . 'wp-admin/includes/translation-install.php' );
		$translations = wp_get_available_translations();
		$translations['en_US'] = array( 'language' => 'en_US', "{$mode}_name" => 'English (United States)' );
		foreach ( $translations as $translation ) {
			$language_names[ $translation['language'] ] = $translation[ "{$mode}_name" ];
		}
		$language_name = isset( $language_names[ $locale ] ) ? $language_names[ $locale ] : false;
		if ( $language_name && $localized ) {
			return $this->localize_language_name( $language_name );
		}
		return $language_name;
	}


	/**
	 * Return localized name for an English (WordPress) language name
	 *
	 * @param $language_name
	 *
	 * @return string
	 *
	 */
	function localize_language_name( $language_name ) {
		$language_names = $this->get_language_names();
		return ( isset( $language_names[ $language_name ] ) ? $language_names[ $language_name ] : false );
	}

	/**
	 * Return alpha-2 code for locale's country
	 *
	 * @param $locale
	 *
	 * @return string
	 *
	 */
	function get_locale_country_code( $locale ) {
		$country_code = false;
		if ( $position = strpos( $locale, '_' ) ) {
			$country_code = substr( $locale, $position + 1 );
			if ( empty( $country_code ) ) {
				$country_code = false;
			}
		}
		return $country_code;
	}

	/**
	 * Return country name for a locale (localized if site language is not English)
	 *
	 * @param $locale
	 *
	 * @return string
	 *
	 */
	function get_locale_country_name_localized( $locale ) {
		$country_names = $this->get_country_names();
		$country_code = $this->get_locale_country_code( $locale );
		return ( isset( $country_names[ $country_code ] ) ? $country_names[ $country_code ] : false );
	}


	/* ****************************************************
	 * Name list functions
	 * ****************************************************/

	/**
	 * Return language names list (will be localized)
	 * This function is basically just a place to put the names so they can be translated
	 * Name list generated from wp_get_available_translations() 'english_name'
	 *
	 * @return array
	 *
	 */
	function get_language_names() {
		$language_names = array(
			'Arabic' => __( 'Arabic', 'r3df-multisite-language-indicator' ),
			'Azerbaijani' => __( 'Azerbaijani', 'r3df-multisite-language-indicator' ),
			'Bulgarian' => __( 'Bulgarian', 'r3df-multisite-language-indicator' ),
			'Bosnian' => __( 'Bosnian', 'r3df-multisite-language-indicator' ),
			'Catalan' => __( 'Catalan', 'r3df-multisite-language-indicator' ),
			'Welsh' => __( 'Welsh', 'r3df-multisite-language-indicator' ),
			'Danish' => __( 'Danish', 'r3df-multisite-language-indicator' ),
			'German' => __( 'German', 'r3df-multisite-language-indicator' ),
			'German (Switzerland)' => __( 'German (Switzerland)', 'r3df-multisite-language-indicator' ),
			'Greek' => __( 'Greek', 'r3df-multisite-language-indicator' ),
			'English (Canada)' => __( 'English (Canada)', 'r3df-multisite-language-indicator' ),
			'English (UK)' => __( 'English (UK)', 'r3df-multisite-language-indicator' ),
			'English (Australia)' => __( 'English (Australia)', 'r3df-multisite-language-indicator' ),
			'Esperanto' => __( 'Esperanto', 'r3df-multisite-language-indicator' ),
			'Spanish (Mexico)' => __( 'Spanish (Mexico)', 'r3df-multisite-language-indicator' ),
			'Spanish (Peru)' => __( 'Spanish (Peru)', 'r3df-multisite-language-indicator' ),
			'Spanish (Spain)' => __( 'Spanish (Spain)', 'r3df-multisite-language-indicator' ),
			'Spanish (Chile)' => __( 'Spanish (Chile)', 'r3df-multisite-language-indicator' ),
			'Basque' => __( 'Basque', 'r3df-multisite-language-indicator' ),
			'Persian' => __( 'Persian', 'r3df-multisite-language-indicator' ),
			'Finnish' => __( 'Finnish', 'r3df-multisite-language-indicator' ),
			'French (France)' => __( 'French (France)', 'r3df-multisite-language-indicator' ),
			'Scottish Gaelic' => __( 'Scottish Gaelic', 'r3df-multisite-language-indicator' ),
			'Galician' => __( 'Galician', 'r3df-multisite-language-indicator' ),
			'Hazaragi' => __( 'Hazaragi', 'r3df-multisite-language-indicator' ),
			'Hebrew' => __( 'Hebrew', 'r3df-multisite-language-indicator' ),
			'Croatian' => __( 'Croatian', 'r3df-multisite-language-indicator' ),
			'Hungarian' => __( 'Hungarian', 'r3df-multisite-language-indicator' ),
			'Indonesian' => __( 'Indonesian', 'r3df-multisite-language-indicator' ),
			'Icelandic' => __( 'Icelandic', 'r3df-multisite-language-indicator' ),
			'Italian' => __( 'Italian', 'r3df-multisite-language-indicator' ),
			'Japanese' => __( 'Japanese', 'r3df-multisite-language-indicator' ),
			'Korean' => __( 'Korean', 'r3df-multisite-language-indicator' ),
			'Lithuanian' => __( 'Lithuanian', 'r3df-multisite-language-indicator' ),
			'Burmese' => __( 'Burmese', 'r3df-multisite-language-indicator' ),
			'Norwegian (Bokmål)' => __( 'Norwegian (Bokmål)', 'r3df-multisite-language-indicator' ),
			'Dutch' => __( 'Dutch', 'r3df-multisite-language-indicator' ),
			'Polish' => __( 'Polish', 'r3df-multisite-language-indicator' ),
			'Pashto' => __( 'Pashto', 'r3df-multisite-language-indicator' ),
			'Portuguese (Portugal)' => __( 'Portuguese (Portugal)', 'r3df-multisite-language-indicator' ),
			'Portuguese (Brazil)' => __( 'Portuguese (Brazil)', 'r3df-multisite-language-indicator' ),
			'Romanian' => __( 'Romanian', 'r3df-multisite-language-indicator' ),
			'Russian' => __( 'Russian', 'r3df-multisite-language-indicator' ),
			'Slovak' => __( 'Slovak', 'r3df-multisite-language-indicator' ),
			'Slovenian' => __( 'Slovenian', 'r3df-multisite-language-indicator' ),
			'Serbian' => __( 'Serbian', 'r3df-multisite-language-indicator' ),
			'Swedish' => __( 'Swedish', 'r3df-multisite-language-indicator' ),
			'Thai' => __( 'Thai', 'r3df-multisite-language-indicator' ),
			'Turkish' => __( 'Turkish', 'r3df-multisite-language-indicator' ),
			'Uighur' => __( 'Uighur', 'r3df-multisite-language-indicator' ),
			'Ukrainian' => __( 'Ukrainian', 'r3df-multisite-language-indicator' ),
			'Chinese (Taiwan)' => __( 'Chinese (Taiwan)', 'r3df-multisite-language-indicator' ),
			'Chinese (China)' => __( 'Chinese (China)', 'r3df-multisite-language-indicator' ),
			'English (United States)' => __( 'English (United States)', 'r3df-multisite-language-indicator' ),
			'Unknown' => __( 'Unknown', 'r3df-multisite-language-indicator' ),
		);
		asort( $language_names );

		return ( $language_names );
	}

	/**
	 * Return country names list (will be localized)
	 *
	 * @return array
	 *
	 */
	function get_country_names() {
		$country_names = array(
			'AD' => __( 'Andorra', 'r3df-multisite-language-indicator' ),
			'AE' => __( 'United Arab Emirates', 'r3df-multisite-language-indicator' ),
			'AF' => __( 'Afghanistan', 'r3df-multisite-language-indicator' ),
			'AG' => __( 'Antigua and Barbuda', 'r3df-multisite-language-indicator' ),
			'AI' => __( 'Anguilla', 'r3df-multisite-language-indicator' ),
			'AL' => __( 'Albania', 'r3df-multisite-language-indicator' ),
			'AM' => __( 'Armenia', 'r3df-multisite-language-indicator' ),
			'AN' => __( 'Netherlands Antilles', 'r3df-multisite-language-indicator' ),
			'AO' => __( 'Angola', 'r3df-multisite-language-indicator' ),
			'AQ' => __( 'Antarctica', 'r3df-multisite-language-indicator' ),
			'AR' => __( 'Argentina', 'r3df-multisite-language-indicator' ),
			'AS' => __( 'American Samoa', 'r3df-multisite-language-indicator' ),
			'AT' => __( 'Austria', 'r3df-multisite-language-indicator' ),
			'AU' => __( 'Australia', 'r3df-multisite-language-indicator' ),
			'AW' => __( 'Aruba', 'r3df-multisite-language-indicator' ),
			'AZ' => __( 'Azerbaijan', 'r3df-multisite-language-indicator' ),
			'BA' => __( 'Bosnia and Herzegovina', 'r3df-multisite-language-indicator' ),
			'BB' => __( 'Barbados', 'r3df-multisite-language-indicator' ),
			'BD' => __( 'Bangladesh', 'r3df-multisite-language-indicator' ),
			'BE' => __( 'Belgium', 'r3df-multisite-language-indicator' ),
			'BF' => __( 'Burkina Faso', 'r3df-multisite-language-indicator' ),
			'BG' => __( 'Bulgaria', 'r3df-multisite-language-indicator' ),
			'BH' => __( 'Bahrain', 'r3df-multisite-language-indicator' ),
			'BI' => __( 'Burundi', 'r3df-multisite-language-indicator' ),
			'BJ' => __( 'Benin', 'r3df-multisite-language-indicator' ),
			'BL' => __( 'Saint Barthélemy', 'r3df-multisite-language-indicator' ),
			'BM' => __( 'Bermuda', 'r3df-multisite-language-indicator' ),
			'BN' => __( 'Brunei', 'r3df-multisite-language-indicator' ),
			'BO' => __( 'Bolivia', 'r3df-multisite-language-indicator' ),
			'BQ' => __( 'British Antarctic Territory', 'r3df-multisite-language-indicator' ),
			'BR' => __( 'Brazil', 'r3df-multisite-language-indicator' ),
			'BS' => __( 'Bahamas', 'r3df-multisite-language-indicator' ),
			'BT' => __( 'Bhutan', 'r3df-multisite-language-indicator' ),
			'BV' => __( 'Bouvet Island', 'r3df-multisite-language-indicator' ),
			'BW' => __( 'Botswana', 'r3df-multisite-language-indicator' ),
			'BY' => __( 'Belarus', 'r3df-multisite-language-indicator' ),
			'BZ' => __( 'Belize', 'r3df-multisite-language-indicator' ),
			'CA' => __( 'Canada', 'r3df-multisite-language-indicator' ),
			'CC' => __( 'Cocos [Keeling] Islands', 'r3df-multisite-language-indicator' ),
			'CD' => __( 'Congo - Kinshasa', 'r3df-multisite-language-indicator' ),
			'CF' => __( 'Central African Republic', 'r3df-multisite-language-indicator' ),
			'CG' => __( 'Congo - Brazzaville', 'r3df-multisite-language-indicator' ),
			'CH' => __( 'Switzerland', 'r3df-multisite-language-indicator' ),
			'CI' => __( 'Côte d’Ivoire', 'r3df-multisite-language-indicator' ),
			'CK' => __( 'Cook Islands', 'r3df-multisite-language-indicator' ),
			'CL' => __( 'Chile', 'r3df-multisite-language-indicator' ),
			'CM' => __( 'Cameroon', 'r3df-multisite-language-indicator' ),
			'CN' => __( 'China', 'r3df-multisite-language-indicator' ),
			'CO' => __( 'Colombia', 'r3df-multisite-language-indicator' ),
			'CR' => __( 'Costa Rica', 'r3df-multisite-language-indicator' ),
			'CS' => __( 'Serbia and Montenegro', 'r3df-multisite-language-indicator' ),
			'CT' => __( 'Canton and Enderbury Islands', 'r3df-multisite-language-indicator' ),
			'CU' => __( 'Cuba', 'r3df-multisite-language-indicator' ),
			'CV' => __( 'Cape Verde', 'r3df-multisite-language-indicator' ),
			'CX' => __( 'Christmas Island', 'r3df-multisite-language-indicator' ),
			'CY' => __( 'Cyprus', 'r3df-multisite-language-indicator' ),
			'CZ' => __( 'Czech Republic', 'r3df-multisite-language-indicator' ),
			'DE' => __( 'Germany', 'r3df-multisite-language-indicator' ),
			'DJ' => __( 'Djibouti', 'r3df-multisite-language-indicator' ),
			'DK' => __( 'Denmark', 'r3df-multisite-language-indicator' ),
			'DM' => __( 'Dominica', 'r3df-multisite-language-indicator' ),
			'DO' => __( 'Dominican Republic', 'r3df-multisite-language-indicator' ),
			'DZ' => __( 'Algeria', 'r3df-multisite-language-indicator' ),
			'EC' => __( 'Ecuador', 'r3df-multisite-language-indicator' ),
			'EE' => __( 'Estonia', 'r3df-multisite-language-indicator' ),
			'EG' => __( 'Egypt', 'r3df-multisite-language-indicator' ),
			'EH' => __( 'Western Sahara', 'r3df-multisite-language-indicator' ),
			'ER' => __( 'Eritrea', 'r3df-multisite-language-indicator' ),
			'ES' => __( 'Spain', 'r3df-multisite-language-indicator' ),
			'ET' => __( 'Ethiopia', 'r3df-multisite-language-indicator' ),
			'FI' => __( 'Finland', 'r3df-multisite-language-indicator' ),
			'FJ' => __( 'Fiji', 'r3df-multisite-language-indicator' ),
			'FK' => __( 'Falkland Islands', 'r3df-multisite-language-indicator' ),
			'FM' => __( 'Micronesia', 'r3df-multisite-language-indicator' ),
			'FO' => __( 'Faroe Islands', 'r3df-multisite-language-indicator' ),
			'FQ' => __( 'French Southern and Antarctic Territories', 'r3df-multisite-language-indicator' ),
			'FR' => __( 'France', 'r3df-multisite-language-indicator' ),
			'FX' => __( 'Metropolitan France', 'r3df-multisite-language-indicator' ),
			'GA' => __( 'Gabon', 'r3df-multisite-language-indicator' ),
			'GB' => __( 'United Kingdom', 'r3df-multisite-language-indicator' ),
			'GD' => __( 'Grenada', 'r3df-multisite-language-indicator' ),
			'GE' => __( 'Georgia', 'r3df-multisite-language-indicator' ),
			'GF' => __( 'French Guiana', 'r3df-multisite-language-indicator' ),
			'GG' => __( 'Guernsey', 'r3df-multisite-language-indicator' ),
			'GH' => __( 'Ghana', 'r3df-multisite-language-indicator' ),
			'GI' => __( 'Gibraltar', 'r3df-multisite-language-indicator' ),
			'GL' => __( 'Greenland', 'r3df-multisite-language-indicator' ),
			'GM' => __( 'Gambia', 'r3df-multisite-language-indicator' ),
			'GN' => __( 'Guinea', 'r3df-multisite-language-indicator' ),
			'GP' => __( 'Guadeloupe', 'r3df-multisite-language-indicator' ),
			'GQ' => __( 'Equatorial Guinea', 'r3df-multisite-language-indicator' ),
			'GR' => __( 'Greece', 'r3df-multisite-language-indicator' ),
			'GS' => __( 'South Georgia and the South Sandwich Islands', 'r3df-multisite-language-indicator' ),
			'GT' => __( 'Guatemala', 'r3df-multisite-language-indicator' ),
			'GU' => __( 'Guam', 'r3df-multisite-language-indicator' ),
			'GW' => __( 'Guinea-Bissau', 'r3df-multisite-language-indicator' ),
			'GY' => __( 'Guyana', 'r3df-multisite-language-indicator' ),
			'HK' => __( 'Hong Kong SAR China', 'r3df-multisite-language-indicator' ),
			'HM' => __( 'Heard Island and McDonald Islands', 'r3df-multisite-language-indicator' ),
			'HN' => __( 'Honduras', 'r3df-multisite-language-indicator' ),
			'HR' => __( 'Croatia', 'r3df-multisite-language-indicator' ),
			'HT' => __( 'Haiti', 'r3df-multisite-language-indicator' ),
			'HU' => __( 'Hungary', 'r3df-multisite-language-indicator' ),
			'ID' => __( 'Indonesia', 'r3df-multisite-language-indicator' ),
			'IE' => __( 'Ireland', 'r3df-multisite-language-indicator' ),
			'IL' => __( 'Israel', 'r3df-multisite-language-indicator' ),
			'IM' => __( 'Isle of Man', 'r3df-multisite-language-indicator' ),
			'IN' => __( 'India', 'r3df-multisite-language-indicator' ),
			'IO' => __( 'British Indian Ocean Territory', 'r3df-multisite-language-indicator' ),
			'IQ' => __( 'Iraq', 'r3df-multisite-language-indicator' ),
			'IR' => __( 'Iran', 'r3df-multisite-language-indicator' ),
			'IS' => __( 'Iceland', 'r3df-multisite-language-indicator' ),
			'IT' => __( 'Italy', 'r3df-multisite-language-indicator' ),
			'JE' => __( 'Jersey', 'r3df-multisite-language-indicator' ),
			'JM' => __( 'Jamaica', 'r3df-multisite-language-indicator' ),
			'JO' => __( 'Jordan', 'r3df-multisite-language-indicator' ),
			'JP' => __( 'Japan', 'r3df-multisite-language-indicator' ),
			'JT' => __( 'Johnston Island', 'r3df-multisite-language-indicator' ),
			'KE' => __( 'Kenya', 'r3df-multisite-language-indicator' ),
			'KG' => __( 'Kyrgyzstan', 'r3df-multisite-language-indicator' ),
			'KH' => __( 'Cambodia', 'r3df-multisite-language-indicator' ),
			'KI' => __( 'Kiribati', 'r3df-multisite-language-indicator' ),
			'KM' => __( 'Comoros', 'r3df-multisite-language-indicator' ),
			'KN' => __( 'Saint Kitts and Nevis', 'r3df-multisite-language-indicator' ),
			'KP' => __( 'North Korea', 'r3df-multisite-language-indicator' ),
			'KR' => __( 'South Korea', 'r3df-multisite-language-indicator' ),
			'KW' => __( 'Kuwait', 'r3df-multisite-language-indicator' ),
			'KY' => __( 'Cayman Islands', 'r3df-multisite-language-indicator' ),
			'KZ' => __( 'Kazakhstan', 'r3df-multisite-language-indicator' ),
			'LA' => __( 'Laos', 'r3df-multisite-language-indicator' ),
			'LB' => __( 'Lebanon', 'r3df-multisite-language-indicator' ),
			'LC' => __( 'Saint Lucia', 'r3df-multisite-language-indicator' ),
			'LI' => __( 'Liechtenstein', 'r3df-multisite-language-indicator' ),
			'LK' => __( 'Sri Lanka', 'r3df-multisite-language-indicator' ),
			'LR' => __( 'Liberia', 'r3df-multisite-language-indicator' ),
			'LS' => __( 'Lesotho', 'r3df-multisite-language-indicator' ),
			'LT' => __( 'Lithuania', 'r3df-multisite-language-indicator' ),
			'LU' => __( 'Luxembourg', 'r3df-multisite-language-indicator' ),
			'LV' => __( 'Latvia', 'r3df-multisite-language-indicator' ),
			'LY' => __( 'Libya', 'r3df-multisite-language-indicator' ),
			'MA' => __( 'Morocco', 'r3df-multisite-language-indicator' ),
			'MC' => __( 'Monaco', 'r3df-multisite-language-indicator' ),
			'MD' => __( 'Moldova', 'r3df-multisite-language-indicator' ),
			'ME' => __( 'Montenegro', 'r3df-multisite-language-indicator' ),
			'MF' => __( 'Saint Martin', 'r3df-multisite-language-indicator' ),
			'MG' => __( 'Madagascar', 'r3df-multisite-language-indicator' ),
			'MH' => __( 'Marshall Islands', 'r3df-multisite-language-indicator' ),
			'MI' => __( 'Midway Islands', 'r3df-multisite-language-indicator' ),
			'MK' => __( 'Macedonia', 'r3df-multisite-language-indicator' ),
			'ML' => __( 'Mali', 'r3df-multisite-language-indicator' ),
			'MM' => __( 'Myanmar [Burma]', 'r3df-multisite-language-indicator' ),
			'MN' => __( 'Mongolia', 'r3df-multisite-language-indicator' ),
			'MO' => __( 'Macau SAR China', 'r3df-multisite-language-indicator' ),
			'MP' => __( 'Northern Mariana Islands', 'r3df-multisite-language-indicator' ),
			'MQ' => __( 'Martinique', 'r3df-multisite-language-indicator' ),
			'MR' => __( 'Mauritania', 'r3df-multisite-language-indicator' ),
			'MS' => __( 'Montserrat', 'r3df-multisite-language-indicator' ),
			'MT' => __( 'Malta', 'r3df-multisite-language-indicator' ),
			'MU' => __( 'Mauritius', 'r3df-multisite-language-indicator' ),
			'MV' => __( 'Maldives', 'r3df-multisite-language-indicator' ),
			'MW' => __( 'Malawi', 'r3df-multisite-language-indicator' ),
			'MX' => __( 'Mexico', 'r3df-multisite-language-indicator' ),
			'MY' => __( 'Malaysia', 'r3df-multisite-language-indicator' ),
			'MZ' => __( 'Mozambique', 'r3df-multisite-language-indicator' ),
			'NA' => __( 'Namibia', 'r3df-multisite-language-indicator' ),
			'NC' => __( 'New Caledonia', 'r3df-multisite-language-indicator' ),
			'NE' => __( 'Niger', 'r3df-multisite-language-indicator' ),
			'NF' => __( 'Norfolk Island', 'r3df-multisite-language-indicator' ),
			'NG' => __( 'Nigeria', 'r3df-multisite-language-indicator' ),
			'NI' => __( 'Nicaragua', 'r3df-multisite-language-indicator' ),
			'NL' => __( 'Netherlands', 'r3df-multisite-language-indicator' ),
			'NO' => __( 'Norway', 'r3df-multisite-language-indicator' ),
			'NP' => __( 'Nepal', 'r3df-multisite-language-indicator' ),
			'NQ' => __( 'Dronning Maud Land', 'r3df-multisite-language-indicator' ),
			'NR' => __( 'Nauru', 'r3df-multisite-language-indicator' ),
			'NU' => __( 'Niue', 'r3df-multisite-language-indicator' ),
			'NZ' => __( 'New Zealand', 'r3df-multisite-language-indicator' ),
			'OM' => __( 'Oman', 'r3df-multisite-language-indicator' ),
			'PA' => __( 'Panama', 'r3df-multisite-language-indicator' ),
			'PC' => __( 'Pacific Islands Trust Territory', 'r3df-multisite-language-indicator' ),
			'PE' => __( 'Peru', 'r3df-multisite-language-indicator' ),
			'PF' => __( 'French Polynesia', 'r3df-multisite-language-indicator' ),
			'PG' => __( 'Papua New Guinea', 'r3df-multisite-language-indicator' ),
			'PH' => __( 'Philippines', 'r3df-multisite-language-indicator' ),
			'PK' => __( 'Pakistan', 'r3df-multisite-language-indicator' ),
			'PL' => __( 'Poland', 'r3df-multisite-language-indicator' ),
			'PM' => __( 'Saint Pierre and Miquelon', 'r3df-multisite-language-indicator' ),
			'PN' => __( 'Pitcairn Islands', 'r3df-multisite-language-indicator' ),
			'PR' => __( 'Puerto Rico', 'r3df-multisite-language-indicator' ),
			'PS' => __( 'Palestinian Territories', 'r3df-multisite-language-indicator' ),
			'PT' => __( 'Portugal', 'r3df-multisite-language-indicator' ),
			'PU' => __( 'U.S. Miscellaneous Pacific Islands', 'r3df-multisite-language-indicator' ),
			'PW' => __( 'Palau', 'r3df-multisite-language-indicator' ),
			'PY' => __( 'Paraguay', 'r3df-multisite-language-indicator' ),
			'PZ' => __( 'Panama Canal Zone', 'r3df-multisite-language-indicator' ),
			'QA' => __( 'Qatar', 'r3df-multisite-language-indicator' ),
			'RE' => __( 'Réunion', 'r3df-multisite-language-indicator' ),
			'RO' => __( 'Romania', 'r3df-multisite-language-indicator' ),
			'RS' => __( 'Serbia', 'r3df-multisite-language-indicator' ),
			'RU' => __( 'Russia', 'r3df-multisite-language-indicator' ),
			'RW' => __( 'Rwanda', 'r3df-multisite-language-indicator' ),
			'SA' => __( 'Saudi Arabia', 'r3df-multisite-language-indicator' ),
			'SB' => __( 'Solomon Islands', 'r3df-multisite-language-indicator' ),
			'SC' => __( 'Seychelles', 'r3df-multisite-language-indicator' ),
			'SD' => __( 'Sudan', 'r3df-multisite-language-indicator' ),
			'SE' => __( 'Sweden', 'r3df-multisite-language-indicator' ),
			'SG' => __( 'Singapore', 'r3df-multisite-language-indicator' ),
			'SH' => __( 'Saint Helena', 'r3df-multisite-language-indicator' ),
			'SI' => __( 'Slovenia', 'r3df-multisite-language-indicator' ),
			'SJ' => __( 'Svalbard and Jan Mayen', 'r3df-multisite-language-indicator' ),
			'SK' => __( 'Slovakia', 'r3df-multisite-language-indicator' ),
			'SL' => __( 'Sierra Leone', 'r3df-multisite-language-indicator' ),
			'SM' => __( 'San Marino', 'r3df-multisite-language-indicator' ),
			'SN' => __( 'Senegal', 'r3df-multisite-language-indicator' ),
			'SO' => __( 'Somalia', 'r3df-multisite-language-indicator' ),
			'SR' => __( 'Suriname', 'r3df-multisite-language-indicator' ),
			'ST' => __( 'São Tomé and Príncipe', 'r3df-multisite-language-indicator' ),
			'SU' => __( 'Union of Soviet Socialist Republics', 'r3df-multisite-language-indicator' ),
			'SV' => __( 'El Salvador', 'r3df-multisite-language-indicator' ),
			'SY' => __( 'Syria', 'r3df-multisite-language-indicator' ),
			'SZ' => __( 'Swaziland', 'r3df-multisite-language-indicator' ),
			'TC' => __( 'Turks and Caicos Islands', 'r3df-multisite-language-indicator' ),
			'TD' => __( 'Chad', 'r3df-multisite-language-indicator' ),
			'TF' => __( 'French Southern Territories', 'r3df-multisite-language-indicator' ),
			'TG' => __( 'Togo', 'r3df-multisite-language-indicator' ),
			'TH' => __( 'Thailand', 'r3df-multisite-language-indicator' ),
			'TJ' => __( 'Tajikistan', 'r3df-multisite-language-indicator' ),
			'TK' => __( 'Tokelau', 'r3df-multisite-language-indicator' ),
			'TL' => __( 'Timor-Leste', 'r3df-multisite-language-indicator' ),
			'TM' => __( 'Turkmenistan', 'r3df-multisite-language-indicator' ),
			'TN' => __( 'Tunisia', 'r3df-multisite-language-indicator' ),
			'TO' => __( 'Tonga', 'r3df-multisite-language-indicator' ),
			'TR' => __( 'Turkey', 'r3df-multisite-language-indicator' ),
			'TT' => __( 'Trinidad and Tobago', 'r3df-multisite-language-indicator' ),
			'TV' => __( 'Tuvalu', 'r3df-multisite-language-indicator' ),
			'TW' => __( 'Taiwan', 'r3df-multisite-language-indicator' ),
			'TZ' => __( 'Tanzania', 'r3df-multisite-language-indicator' ),
			'UA' => __( 'Ukraine', 'r3df-multisite-language-indicator' ),
			'UG' => __( 'Uganda', 'r3df-multisite-language-indicator' ),
			'UM' => __( 'U.S. Minor Outlying Islands', 'r3df-multisite-language-indicator' ),
			'US' => __( 'United States', 'r3df-multisite-language-indicator' ),
			'UY' => __( 'Uruguay', 'r3df-multisite-language-indicator' ),
			'UZ' => __( 'Uzbekistan', 'r3df-multisite-language-indicator' ),
			'VA' => __( 'Vatican City', 'r3df-multisite-language-indicator' ),
			'VC' => __( 'Saint Vincent and the Grenadines', 'r3df-multisite-language-indicator' ),
			'VD' => __( 'North Vietnam', 'r3df-multisite-language-indicator' ),
			'VE' => __( 'Venezuela', 'r3df-multisite-language-indicator' ),
			'VG' => __( 'British Virgin Islands', 'r3df-multisite-language-indicator' ),
			'VI' => __( 'U.S. Virgin Islands', 'r3df-multisite-language-indicator' ),
			'VN' => __( 'Vietnam', 'r3df-multisite-language-indicator' ),
			'VU' => __( 'Vanuatu', 'r3df-multisite-language-indicator' ),
			'WF' => __( 'Wallis and Futuna', 'r3df-multisite-language-indicator' ),
			'WK' => __( 'Wake Island', 'r3df-multisite-language-indicator' ),
			'WS' => __( 'Samoa', 'r3df-multisite-language-indicator' ),
			'YD' => __( 'People\'s Democratic Republic of Yemen', 'r3df-multisite-language-indicator' ),
			'YE' => __( 'Yemen', 'r3df-multisite-language-indicator' ),
			'YT' => __( 'Mayotte', 'r3df-multisite-language-indicator' ),
			'ZA' => __( 'South Africa', 'r3df-multisite-language-indicator' ),
			'ZM' => __( 'Zambia', 'r3df-multisite-language-indicator' ),
			'ZW' => __( 'Zimbabwe', 'r3df-multisite-language-indicator' ),
		);

		asort( $country_names );

		return ( $country_names );
	}
}

