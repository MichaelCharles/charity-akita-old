<?php
/**
 * Plugin Name: 	Ninja Forms Analytics
 * Plugin URI:		http://plugin9.com/ninja-forms-analytics
 * Description:		Get basic user traffic analytics information and save to each submission
 * Version: 		1.0.0
 * Author: 			Plugin9
 * Author URI: 		http://www.plugin9.com/start
 * Text Domain: 	ninja-form-analytics
 */

/*  Copyright 2015 Plugin9 (email: terry@plugin9.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class Ninja_Forms_Analytics
 *
 * Main Ninja_Forms_Analytics class initializes the plugin.
 *
 * @class		Ninja_Forms_Analytics
 * @version		1.0.0
 * @author		Terry Tsang
 */
class Ninja_Forms_Analytics {


	/**
	 * Plugin version.
	 *
	 * @since 1.0.0
	 * @var string $version Plugin version number.
	 */
	public $version = '1.0.0';


	/**
	 * Plugin file.
	 *
	 * @since 1.0.0
	 * @var string $file Plugin file path.
	 */
	public $file = __FILE__;


	/**
	 * Instace of Ninja_Forms_Analytics.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var object $instance The instance of Ninja_Forms_Analytics.
	 */
	private static $instance;


	/**
	 * Construct.
	 *
	 * Initialize the class and plugin.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// Initialize plugin parts
		$this->init();

	}


	/**
	 * Instance.
	 *
	 * An global instance of the class. Used to retrieve the instance
	 * to use on other files/plugins/themes.
	 *
	 * @since 1.0.0
	 * @return object Instance of the class.
	 */
	public static function instance() {

		if ( is_null( self::$instance ) ) :
			self::$instance = new self();
		endif;

		return self::$instance;

	}


	/**
	 * Init.
	 *
	 * Initialize plugin parts.
	 *
	 * @since 1.0.0
	 */
	public function init() {

		// Load textdomain
		$this->load_textdomain();

		add_action( 'admin_init', array( $this, 'ninja_forms_analytics_setup_license') );

		add_action( 'init', array( $this, 'register_ninja_form_fields') );

	}

	public function load_textdomain() {

		// Load textdomain
		load_plugin_textdomain( 'ninja-forms-analytics', false, basename( dirname( __FILE__ ) ) . '/languages' );

	}

	public function ninja_forms_analytics_setup_license() {
		if ( class_exists( 'NF_Extension_Updater' ) ) {
			$NF_Extension_Updater = new NF_Extension_Updater( 'Ninja Form Analytics', '1.0', 'Plugin9', __FILE__, 'option_prefix' );
		}
	}

	public function register_ninja_form_fields() {
		$argsIp = array(
			'name' => __( 'User IP', 'ninja-forms-analytics'),
			'display_function' => array($this, 'collect_user_ip_address'),
			'sidebar' => 'template_fields',
			'display_label' => false,
			'display_wrap' => false,
		);

		$argsUA = array(
			'name' => __( 'User Agent', 'ninja-forms-analytics'),
			'display_function' => array($this, 'collect_user_agent'),
			'sidebar' => 'template_fields',
			'display_label' => false,
			'display_wrap' => false,
		);

		$argsReferrerURL = array(
			'name' => __( 'Referrer URL', 'ninja-forms-analytics'),
			'display_function' => array($this, 'collect_user_referrer_url'),
			'sidebar' => 'template_fields',
			'display_label' => false,
			'display_wrap' => false,
		);

		$argsMobile = array(
			'name' => __( 'Mobile Device', 'ninja-forms-analytics'),
			'display_function' => array($this, 'collect_user_detect_mobile'),
			'sidebar' => 'template_fields',
			'display_label' => false,
			'display_wrap' => false,
		);

		if( function_exists( 'ninja_forms_register_field' ) )
		{
			ninja_forms_register_field('user_ip', $argsIp);
			ninja_forms_register_field('user_agent', $argsUA);
			ninja_forms_register_field('user_referrer', $argsReferrerURL);
			ninja_forms_register_field('user_mobile', $argsMobile);
		}
	}

	public function collect_user_ip_address( $field_id, $data )
	{
		global $post;

		$ip_address = $_SERVER["REMOTE_ADDR"];

		if(!empty($post))
		{
		?>
			<input type="hidden" name="ninja_forms_field_<?php echo $field_id;?>" value="<?php echo $ip_address;?>">
		<?php
		}
		
	    if(is_admin())
		{
			?>
				<div class="field-wrap text-wrap label-above">
					<label for="ninja_forms_field_<?php echo $field_id;?>"><?php _e( 'User IP', 'ninja-forms-analytics' ); ?></label>
					<input type="text" name="ninja_forms_field_<?php echo $field_id;?>" value="<?php echo $data['default_value'];?>">
					<p><a href="http://whois.domaintools.com/<?php echo $data['default_value'];?>" target="_blank">Find out more about this person</a></p>
				</div>
			<?php
		}
	}

	public function collect_user_agent( $field_id, $data )
	{
		global $post;

		$user_agent = $_SERVER['HTTP_USER_AGENT'];

		if(!empty($post))
		{
		?>
			<input type="hidden" name="ninja_forms_field_<?php echo $field_id;?>" value="<?php echo $user_agent;?>">
		<?php
		}
		
	    if(is_admin())
		{
			?>
				<div class="field-wrap text-wrap label-above">
					<label for="ninja_forms_field_<?php echo $field_id;?>"><?php _e( 'User Agent', 'ninja-forms-analytics' ); ?></label>
					<input type="text" name="ninja_forms_field_<?php echo $field_id;?>" value="<?php echo $data['default_value'];?>">
				</div>
			<?php
		}
	}

	public function collect_user_referrer_url( $field_id, $data )
	{
		global $post;

		$referrer_url = $_SERVER['HTTP_REFERER'];

		if(!empty($post))
		{
		?>
			<input type="hidden" name="ninja_forms_field_<?php echo $field_id;?>" value="<?php echo $referrer_url;?>">
		<?php
		}
		
	    if(is_admin())
		{
			?>
				<div class="field-wrap text-wrap label-above">
					<label for="ninja_forms_field_<?php echo $field_id;?>"><?php _e( 'Referrer URL', 'ninja-forms-analytics' ); ?></label>
					<input type="text" name="ninja_forms_field_<?php echo $field_id;?>" value="<?php echo $data['default_value'];?>">
				</div>
			<?php
		}
	}

	public function collect_user_detect_mobile( $field_id, $data )
	{
		global $post;

		if(wp_is_mobile())
			$is_mobile = __( 'Yes', 'ninja-forms-analytics');
		else
			$is_mobile = __( 'No', 'ninja-forms-analytics');

		if(!empty($post))
		{
		?>
			<input type="hidden" name="ninja_forms_field_<?php echo $field_id;?>" value="<?php echo $is_mobile;?>">
		<?php
		}
		
	    if(is_admin())
		{
			?>
				<div class="field-wrap text-wrap label-above">
					<label for="ninja_forms_field_<?php echo $field_id;?>"><?php _e( 'Mobile Device', 'ninja-forms-analytics' ); ?></label>
					<input type="text" name="ninja_forms_field_<?php echo $field_id;?>" value="<?php echo $data['default_value'];?>">
				</div>
			<?php
		}
	}


}

if ( ! function_exists( 'Ninja_Forms_Analytics' ) ) :

 	function Ninja_Forms_Analytics() {
		return Ninja_Forms_Analytics::instance();
	}

endif;

Ninja_Forms_Analytics();


?>