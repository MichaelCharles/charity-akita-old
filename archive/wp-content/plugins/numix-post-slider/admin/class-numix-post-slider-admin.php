<?php
/**
 * Numix Post Slider Admin
 *
 * @package   Numix_Post_Slider
 * @author    Gaurav Padia <gauravpadia14u@gmail.com>
 * @author    Asalam Godhaviya <godhaviya.asalam@gmail.com>
 * @license   GPL-2.0+
 * @link      http://numixtech.com
 * @copyright 2014 Numix Techonologies
 */

/**
 * Plugin class for administrative side of the WordPress site.
 *
 * @package Numix_Post_Slider_Admin
 * @author  Gaurav Padia <gauravpadia14u@gmail.com>
 * @author  Asalam Godhaviya <godhaviya.asalam@gmail.com>
 */
class Numix_Post_Slider_Admin {

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Slug of the plugin screen.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_screen_hook_suffix = null;

	/**
	 * Initialize the plugin by loading admin scripts & styles and adding a
	 * settings page and menu.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {

		/*
		 * Call $plugin_slug from public plugin class.
		 */
		$plugin = Numix_Post_Slider::get_instance();
		$this->plugin_slug = $plugin->get_plugin_slug();

		// Load admin style sheet and JavaScript.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

		// Add the options page and menu item.
		add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );

		// Add an action link pointing to the options page.
		$plugin_basename = plugin_basename( plugin_dir_path( realpath( dirname( __FILE__ ) ) ) . $this->plugin_slug . '.php' );
		add_filter( 'plugin_action_links_' . $plugin_basename, array( $this, 'add_action_links' ) );

		//add admin ajax actions
		add_action( 'wp_ajax_numix_slider_save', array( $this, 'save_slider' ) );
		add_action( 'wp_ajax_numix_slider_display_taxonomies', array( $this, 'display_taxonomies' ) );

	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Register and enqueue admin-specific style sheet.
	 *
	 * @since  1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_styles() {

		if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( $this->plugin_screen_hook_suffix == $screen->id ) {
			wp_enqueue_style( $this->plugin_slug .'-admin-styles', plugins_url( 'assets/css/admin.css', __FILE__ ), array(), Numix_Post_Slider::VERSION );
			wp_enqueue_style( $this->plugin_slug .'-admin-qtip-styles', plugins_url( 'assets/js/qtip/jquery.qtip.min.css', __FILE__ ), array(), Numix_Post_Slider::VERSION );
		}

	}

	/**
	 * Register and enqueue admin-specific JavaScript.
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_scripts() {

		if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( $this->plugin_screen_hook_suffix == $screen->id ) {

			wp_enqueue_script( $this->plugin_slug . '-admin-script', plugins_url( 'assets/js/admin.js', __FILE__ ), array( 'jquery' ), Numix_Post_Slider::VERSION );

			wp_enqueue_script( $this->plugin_slug . '-dropdown-script', plugins_url( 'assets/js/dropdown-checklist/ui.dropdownchecklist.js', __FILE__ ), array( 'jquery-ui-core', 'jquery-ui-widget' ), Numix_Post_Slider::VERSION );

			wp_enqueue_script( $this->plugin_slug . '-qtip-script', plugins_url( 'assets/js/qtip/jquery.qtip.min.js', __FILE__ ), Numix_Post_Slider::VERSION );
			wp_enqueue_script( $this->plugin_slug . '-form2bject-script', plugins_url( 'assets/js/form2js.js', __FILE__ ), Numix_Post_Slider::VERSION );

			wp_localize_script(
				$this->plugin_slug . '-admin-script', 'numixslider_ajax_vars', array(
					'pluginurl' => NUMIX_SLIDER_URL,
					'admin_edit_url' => admin_url( 'admin.php?page=numix-post-slider&action=edit&id=' ),
					'ajaxurl' => admin_url( 'admin-ajax.php' ),
					'numixslider_ajax_nonce' => wp_create_nonce( 'numixslider_ajax_nonce' ),
					'saveText' => __( 'Save Slider', 'numix-post-slider' ),
					'createText' => __( 'Create Slider', 'numix-post-slider' ),
					'deleteDialogText' => __( 'Delete slider permanently?', 'numix-post-slider' ),
					'savingText' => __( 'Saving...', 'numix-post-slider' ),
					'savedText' => __( 'Saved', 'numix-post-slider' ),
					'unsavedText' => __( 'Unsaved', 'numix-post-slider' ),
					'autoText' => __( 'All', 'numix-post-slider' ),
					'emptyTaxonomiesText' => __( 'No taxonomies found for selected post type', 'numix-post-slider' ),
				) 
				);

		}

	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_admin_menu() {

		/*
		 * Add a settings page for this plugin to the Settings menu.
		 */
		$this->plugin_screen_hook_suffix = add_menu_page(
			__( 'Numix Slider', $this->plugin_slug ),
			__( 'Numix Slider', $this->plugin_slug ),
			'manage_options',
			$this->plugin_slug,
			array( $this, 'display_plugin_admin_page' ),
			plugins_url( '/numix-post-slider/admin/assets/images/numix-admin-icon.png' )
		);

	}

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since    1.0.0
	 */
	public function display_plugin_admin_page() {

		global $wpdb;
		if ( isset( $_GET['action'] ) ) {
			$current_page = $_GET['action'];
		} else {
			$current_page = '';
		}

		if ( $current_page == 'add_new' || $current_page == 'edit' ) {

			if ( $current_page == 'edit' && isset( $_GET['id'] ) ) {
				$slider_id = intval( $_GET['id'] );
				if ( $slider_id < 0 )
					die ( 'ns-oops-invalid-ID.' . $slider_id );
				$slider_row = $wpdb->get_row( 'SELECT * FROM ' . Numix_Post_Slider::$table_name . " WHERE id = {$slider_id}", ARRAY_A );
			}
			else {
				$slider_row = false;
				$slider_id  = false;
			}

			include_once 'views/edit-slider.php';
		}
		elseif ( $current_page == 'duplicate' ) {

			if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'numixslider_duplicate_nonce' ) )
				die( 'ns-oops-duplicate-invalid-request' );
			$slider_id = intval( $_GET['id'] );
			if ( $slider_id < 0 )
				die ( 'ns-oops-duplicate-invalid-ID.' . $slider_id );

			$slider_fields = 'name,width,height,max_posts,post_categories,post_orderby,post_order,js_settings,post_relation,arrows_auto_hide,bottom_nav_type,activate_on_click';
			$wpdb->query( 'INSERT INTO '.Numix_Post_Slider::$table_name." ($slider_fields) SELECT $slider_fields FROM ".Numix_Post_Slider::$table_name." WHERE id=$slider_id" );
			$duplicate_id = $wpdb->insert_id;
			wp_redirect( admin_url( "admin.php?page=numix-post-slider&msg=duplicate_done&duplicate_id=$duplicate_id&from_id=$slider_id" ) );
			exit;
		}
		elseif ( $current_page == 'delete' ) {
			if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'numixslider_delete_nonce' ) )
				die( 'ns-oops-delete-invalid-request' );
			$slider_id = intval( $_GET['id'] );
			if ( $slider_id < 0 )
				die ( 'ns-oops-delete-invalid-ID.' . $slider_id );
			$wpdb->query( 'DELETE FROM '.Numix_Post_Slider::$table_name." WHERE id = $slider_id" );
			wp_redirect( admin_url( "admin.php?page=numix-post-slider&msg=delete_done&delete_id=$slider_id" ) );
			exit;
		}
		else {
			include_once 'views/admin.php';
		}

	}

	/**
	 * Add settings action link to the plugins page.
	 *
	 * @since    1.0.0
	 */
	public function add_action_links( $links ) {

		return array_merge(
			array(
				'settings' => '<a href="' . admin_url( 'admin.php?page=' . $this->plugin_slug ) . '">' . __( 'Settings', $this->plugin_slug ) . '</a>'
			),
			$links
		);

	}

	/**
	 * Ajax function to print select options with taxonomies if available
	 *
	 * @since  1.0.0
	 */
	public function display_taxonomies() {

		if ( ! is_admin() && ! current_user_can( 'manage_options' ) || ! wp_verify_nonce( $_POST['numixslider_ajax_nonce'], 'numixslider_ajax_nonce' ) )
			die ( 'ns-oops-taxonomies' );


		if ( $_POST['action'] == 'numix_slider_display_taxonomies' ) {
			$taxonomies = get_object_taxonomies( array( 'post_type' => $_POST['post_type'] ), 'objects' );

			$id = intval( $_POST['id'] );
			if ( $id > 0 ) {
				global $wpdb;
				$slider_row     = $wpdb->get_row( 'SELECT * FROM '.Numix_Post_Slider::$table_name." WHERE id = $id", ARRAY_A );
				$selected_array = (array)json_decode( stripslashes( $slider_row['post_categories'] ) );
			}


			if ( $taxonomies ) {

				foreach ( $taxonomies  as $taxonomy ) {
					$terms = get_terms( $taxonomy->name, 'hide_empty=0' );

					if ( $terms ) {

						$taxonomy_name  = $taxonomy->name;
						$out = "<optgroup id=\"{$taxonomy_name}\" label=\"{$taxonomy->labels->name}\"> \n";
						$selected_value = array();
						if ( isset( $selected_array[$taxonomy_name] ) ) {
							$selected_value = $selected_array[$taxonomy_name];
						}

						foreach ( $terms as $term ) {
							$selected = '';
							if ( $selected_value && in_array( $term->slug, $selected_value ) ) {
								$selected = 'selected="selected"';
							}
							$out .= "<option value=\"{$term->slug}\" $selected>{$term->name}</option>\n";

						}
						$out .= '</optgroup>';
						echo $out;
					}
				}
			}
		}
		die();
	}

	/**
	 * Ajax functon to save slider data.
	 *
	 * @since  1.0.0
	 */

	public function save_slider() {

		if ( ! current_user_can( 'manage_options' ) || ! wp_verify_nonce( $_POST['numixslider_ajax_nonce'], 'numixslider_ajax_nonce' ) ) {
			echo 'no permissions';
			die();
		}

		if ( $_POST['action'] == 'numix_slider_save' ) {
			global $wpdb;
			$slider_table = Numix_Post_Slider::$table_name;

			if ( isset( $_POST['id'] ) ) {
				$id = intval( $_POST['id'] );
			}
			$post_data = array(
				'name' => $_POST['name'],
				'width' => $_POST['width'],
				'height' => $_POST['height'],
				'max_posts' => $_POST['max_posts'],
				'post_categories' => $_POST['post_categories'],
				'post_orderby' => $_POST['post_orderby'],
				'post_order' => $_POST['post_order'],
				'js_settings' => $_POST['js_settings'],
				'post_relation' => $_POST['post_relation'],
				'arrows_auto_hide' => $_POST['arrows_auto_hide'],
				'bottom_nav_type' => $_POST['slider_bottom_nav_type'],
				'activate_on_click' => $_POST['activate_on_click'],
				'display_post_title' => $_POST['display_post_title'],
				'hide_posts' => $_POST['hide_posts'],

			);


			if ( $id <= 0 ) {
				$wpdb->insert(
					$slider_table,
					$post_data,
					array(
						'%s',
						'%s',
						'%s',
						'%d',
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',

					)
				);
				$insert_id = $wpdb->insert_id;
				echo intval( $insert_id );
			} else { // update existing slider

				$wpdb->update(
					$slider_table,
					$post_data,
					array( 'id' => $id ),
					array(
						'%s',
						'%s',
						'%s',
						'%d',
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
					),
					array(
						'%d'
					)
				);

				echo intval( $id );
			}
		}
		die();
	}

}
