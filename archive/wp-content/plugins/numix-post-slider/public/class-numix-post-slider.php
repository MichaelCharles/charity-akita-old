<?php
/**
 * Numix Post Slider.
 *
 * @package   Numix_Post_Slider
 * @author    Gaurav Padia <gauravpadia14u@gmail.com>
 * @author    Asalam Godhaviya <godhaviya.asalam@gmail.com>
 * @license   GPL-2.0+
 * @link      http://numixtech.com
 * @copyright 2014 Numix Techonologies
 */

/**
 * Plugin class for public-facing side of the WordPress site.
 *
 * @package Numix_Post_Slider
 * @author  Gaurav Padia <gauravpadia14u@gmail.com>
 * @author  Asalam Godhaviya <godhaviya.asalam@gmail.com>
 */
class Numix_Post_Slider {

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since   1.0.0
	 *
	 * @var     string
	 */
	const VERSION = '1.0.2';

	/**
	 * Plugin database table version
	 *
	 * @since 1.0.0
	 *
	 * @var     string
	 */
	const DB_VERSION = '1.0.1';

	/**
	 *
	 *
	 * @TODO - Rename "plugin-name" to the name of your plugin
	 *
	 * Unique identifier for your plugin.
	 *
	 *
	 * The variable name is used as the text domain when internationalizing strings
	 * of text. Its value should match the Text Domain file header in the main
	 * plugin file.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_slug = 'numix-post-slider';

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Database table name
	 *
	 * @since  1.0.0
	 *
	 * @var  string
	 */
	public static $table_name = null;

	/**
	 * Used to store sliders shortcode javascript that will be added to footer
	 *
	 * @since  1.0.0
	 *
	 * @var  array
	 */
	protected $sliders_js = array();

	/**
	 * Used to store sliders shortcode css that will be added to footer
	 *
	 * @since  1.0.0
	 *
	 * @var  array
	 */
	protected $sliders_css = array();

	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {

		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		// Activate plugin when new blog is added
		add_action( 'wpmu_new_blog', array( $this, 'activate_new_site' ) );

		// Load public-facing style sheet and JavaScript.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_shortcode( 'numixslider', array( $this, 'numixslider_shortcode' ) );

		add_action( 'wp_footer', array( $this, 'print_header_styles' ) );
		add_action( 'wp_footer', array( $this, 'print_footer_scripts' ) );
		add_filter( 'pre_get_posts', array( $this, 'exlude_category_numix' ) );

		$this->update_db_check();
	}

	/**
	 * Return the plugin slug.
	 *
	 * @since    1.0.0
	 *
	 * @return    Plugin slug variable.
	 */
	public function get_plugin_slug() {
		return $this->plugin_slug;
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
	 * Set plugin database table name
	 *
	 * @since   1.0.0
	 */
	public static function set_table_name() {
		global $wpdb;
		self::$table_name = $wpdb->prefix . 'numix_post_slider_lite';
	}

	/**
	 * Fired when the plugin is activated.
	 *
	 * @since    1.0.0
	 *
	 * @param boolean $network_wide True if WPMU superadmin uses
	 *                                       "Network Activate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       activated on an individual blog.
	 */
	public static function activate( $network_wide ) {

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( $network_wide  ) {

				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {

					switch_to_blog( $blog_id );
					self::single_activate();
				}

				restore_current_blog();

			} else {
				self::single_activate();
			}
		} else {
			self::single_activate();
		}

	}

	/**
	 * Fired when a new site is activated with a WPMU environment.
	 *
	 * @since    1.0.0
	 *
	 * @param int     $blog_id ID of the new blog.
	 */
	public function activate_new_site( $blog_id ) {

		if ( 1 !== did_action( 'wpmu_new_blog' ) ) {
			return;
		}

		switch_to_blog( $blog_id );
		self::single_activate();
		restore_current_blog();

	}

	/**
	 * Get all blog ids of blogs in the current network that are:
	 * - not archived
	 * - not spam
	 * - not deleted
	 *
	 * @since    1.0.0
	 *
	 * @return   array|false    The blog ids, false if no matches.
	 */
	private static function get_blog_ids() {

		global $wpdb;

		// get an array of blog ids
		$sql = "SELECT blog_id FROM $wpdb->blogs
			WHERE archived = '0' AND spam = '0'
			AND deleted = '0'";

		return $wpdb->get_col( $sql );

	}

	/**
	 * Install plugin database table and maintain db version.
	 *
	 * @since 1.0.0
	 */
	public static function install_db() {

		global $wpdb;

		$table_name = self::$table_name;

		$installed_db_version = get_option( 'npts_db_version' );

		if ( $installed_db_version != self::DB_VERSION ) {

			$sql = "CREATE TABLE {$table_name} (
						`id` mediumint(9) NOT NULL AUTO_INCREMENT,
						`name` tinytext NOT NULL,
						`width` varchar(50) NOT NULL,
						`height` varchar(50) NOT NULL,
						`max_posts` tinyint(4) NOT NULL,
						`post_categories` text NOT NULL,
						`post_orderby` varchar(20) NOT NULL,
						`post_order` tinytext NOT NULL,
						`js_settings` text NOT NULL,
						`post_relation` varchar(10) NOT NULL,
						`arrows_auto_hide` varchar(10) NOT NULL,
						`bottom_nav_type` varchar(20) NOT NULL,
						`activate_on_click` varchar(10) NOT NULL,
						`display_post_title` varchar(10) NOT NULL,
						`hide_posts` varchar(10) NOT NULL,
						UNIQUE KEY id (id)
					);";

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );

			update_option( 'npts_db_version', self::DB_VERSION );
		}
	}

	/**
	 * Fired for each blog when the plugin is activated.
	 *
	 * @since    1.0.0
	 */
	private static function single_activate() {
		self::install_db();
	}

	/**
	 * Fired to check database db version update
	 *
	 * @since    1.0.1
	 */
	public function update_db_check() {
	    if ( get_site_option( 'npts_db_version' ) != self::DB_VERSION ) {
	        self::install_db();
	    }
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		$domain = $this->plugin_slug;
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );
		
		load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, FALSE, basename( plugin_dir_path( dirname( __FILE__ ) ) ) . '/languages/' );

	}

	/**
	 * Register and enqueue public-facing style sheet.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_slug . '-plugin-styles', plugins_url( 'assets/css/numix-slider.css', __FILE__ ), array(), self::VERSION );
	}

	/**
	 * Register and enqueues public-facing JavaScript files.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( $this->plugin_slug . '-plugin-script', plugins_url( 'assets/js/jquery.numix-slider.min.js', __FILE__ ), array( 'jquery', 'jquery-effects-core' ), self::VERSION );
	}

	/**
	 * Add numix slider shortcode tag
	 *
	 * @since  1.0.0
	 * 
	 * @param array   $atts Shortcode attributes
	 *
	 * @return  string          Slider HTML and javascript
	 */
	public function numixslider_shortcode( $atts ) {

		extract(
			shortcode_atts(
				array(
					'id' => '-1',
				), $atts 
				) 
			);
		return $this->get_numixslider( $id );
	}

	/**
	 * Get numix slider with HTML and javscript
	 *
	 * @since  1.0.0
	 *
	 * @param int $id Slider id
	 *
	 * @return  string     Slider HTML and javascript
	 */
	public function get_numixslider( $id ) {

		$id = intval( $id );
		if ( $id <= 0 )
			die ( 'ns-oops-invalid-slider-id' );

		global $wpdb;
		$slider_row = $wpdb->get_row( 'SELECT * FROM '.Numix_Post_Slider::$table_name." WHERE id = $id", ARRAY_A );

		if ( ! $slider_row ) {
			return '<p>'.__( "Oops, Numix Slider with ID $id not found.", 'numix-post-slider' ).'</p>';
		}

		$post_taxonomies_arr = (array)json_decode( stripslashes( $slider_row['post_categories'] ) );

		$taxonomies_query_arr = array();

		if ( ! empty( $post_taxonomies_arr ) ) {

			//$taxonomies_query_arr['relation'] = $slider_row['post_relation'];
			$taxonomies_query_arr['relation'] = 'OR';

			$count = 0;
			foreach ( $post_taxonomies_arr as  $key => $taxonomy ) {
				$taxonomies_query_arr[$count]['taxonomy'] =	$key;
				$taxonomies_query_arr[$count]['terms']    = $taxonomy;
				$taxonomies_query_arr[$count]['field']    = 'slug';
				$taxonomies_query_arr[$count]['operator'] = $slider_row['post_relation'] == 'AND' ? 'AND' : 'IN';
				$count++;
			}
		}

		$posts = get_posts(
			array(
				'posts_per_page' => intval( $slider_row['max_posts'] ),
				'orderby' => $slider_row['post_orderby'],
				'order' => $slider_row['post_order'],
				'post_type' => 'post',
				'tax_query' => $taxonomies_query_arr,
				'meta_query' => array(
					array(
						'key' => '_thumbnail_id',
						'compare' => '!=',
						'value' => '',
					),
				)
			)
		);


		if ( ! empty( $posts ) ) {

			$slider_ele_id  = 'nslider_'.$id;
			$slider_markup  = '<div class="nslider-wrapper" style="max-width:'.$slider_row['width'].'">';
			$slider_markup .= '<div id="'.$slider_ele_id.'" class="nslider">';

			$slider_markup .= '<ul>';
			$slider_height  = filter_var( $slider_row['height'], FILTER_SANITIZE_NUMBER_INT );
			$total_slides   = 0;
			if ( $slider_row['bottom_nav_type'] == 'thumbnail' ) {
				$thumbnails = '';
			}

			foreach ( $posts as $post ) {

				$featured_image = $this->get_featured_image( $post->ID, $slider_height );

				if ( $featured_image == '' ) {
					continue;
				}

				$total_slides++;
				$slider_markup .= '<li>';
				$post_url       = '#';
				if ( $slider_row['activate_on_click'] == 'false' ) {
					$post_url = get_permalink( $post->ID );
				}
				$slider_markup .= '<a href="'.$post_url.'">';
				$slider_markup .= '<img src="'.$featured_image.'" alt="'.$post->post_title.'">';
				$slider_markup .= '</a>';
				
				if ( $slider_row['display_post_title'] == 'true' ) {
					$post_url       = get_permalink( $post->ID );
					$slider_markup .= '<div class="ns-caption">';
					$slider_markup .= '<span><a href="'.$post_url.'">'.$post->post_title.'</a></span>';
					$slider_markup .= '</div>';
				}

				$slider_markup .= '</li>';
				if ( $slider_row['bottom_nav_type'] == 'thumbnail' ) {
					$thumbnails .= '<a href="#">';
					$thumbnails .= '<img src="'.$this->get_featured_image_thumbnail( $post->ID, 50, 50, true ).'" width="50" height="50" alt="'.$post->post_title.'">';
					$thumbnails .= '</a>';
				}
			}
			$slider_markup .= '</ul>';
			$slider_markup .= '</div>';

			if ( $slider_row['bottom_nav_type'] == 'thumbnail' ) {
				$slider_markup .= '<div class="nslider-controls ns-'.$slider_row['bottom_nav_type'].'">';
				$slider_markup .= $thumbnails;
				$slider_markup .= '</div>';
			}

			$slider_markup .= '<div class="nslider-preloader"></div>';
			$slider_markup .= '</div>';

			if ( $slider_row['arrows_auto_hide'] == 'false' ) {
				$arrows_no_auto_hide_css = "\ndiv#$slider_ele_id.nslider + div.nslider-arrow-navigation a{opacity:1;-ms-filter: \"progid:DXImageTransform.Microsoft.Alpha(Opacity=100)\";}";
			} else {
				$arrows_no_auto_hide_css = '';
			}

			$this->sliders_css[] = 'div#'.$slider_ele_id.'.nslider{width:'.$slider_row['width'].';height:'.$slider_row['height'].' !important;}'."\n".'div#'.$slider_ele_id.'.nslider img{height:'.$slider_row['height'].' !important;}'.$arrows_no_auto_hide_css;

			$this->sliders_js[] = '$("#'.$slider_ele_id.'").numixSlider('.stripslashes( $slider_row['js_settings'] ).')'."\n";


			return $slider_markup;
		} else {
			return '<p>'.__( 'Warning: No posts found with selected settings', 'numix-post-slider' ).'</p>';
		}

	}

	/**
	 * Get resized post featured image URL based on height
	 *
	 * @since 1.0.0
	 * 
	 * @param int     $post_id       Post ID from the loop
	 * @param int     $slider_height Slider height set from plugin admin
	 *
	 * @return string Resized featured image url
	 */
	public function get_featured_image( $post_id, $slider_height ) {

		$attachment_id = get_post_thumbnail_id( $post_id );
		$file_path     = get_attached_file( $attachment_id, true );
		$image_url     = '';
		if ( $file_path ) {
			list( $image_url, $width, $height ) = wp_get_attachment_image_src( $attachment_id, 'full' );
			if ( $height > $slider_height ) {
				list( $new_width, $new_height ) = wp_constrain_dimensions( $width, $height, 0, $slider_height );
				$file_pathinfo = pathinfo( $file_path );
				$new_file_path = "{$file_pathinfo['dirname']}/{$file_pathinfo['basename']}-{$new_width}x{$new_height}.{$file_pathinfo['extension']}";
				if ( ! file_exists( $new_file_path ) ) {
					$image = wp_get_image_editor( $file_path );
					if ( ! is_wp_error( $image ) ) {
						$image->resize( $new_width, $new_height, false );
						$new_image = $image->save( $new_file_path );
						$image_url = str_replace( ABSPATH, trailingslashit( get_bloginfo( 'wpurl' ) ), $new_image['path'] );
					}
				} else {
					$image_url = str_replace( ABSPATH, trailingslashit( get_bloginfo( 'wpurl' ) ), $new_file_path );
				}
			}
		}
		return $image_url;
	}

	/**
	 * Get resized post featured image thumbnail URL
	 *
	 * @since 1.0.0
	 * 
	 * @param int     $post_id      Post ID from the loop
	 * @param int     $thumb_width  Thumbnail width set from plugin admin
	 * @param int     $thumb_height Thumbnail height set from plugin admin
	 * @param bool    $thumb_crop   Crop thumbnail option set from plugin admin
	 *
	 * @return string Resized featured image url
	 */
	public function get_featured_image_thumbnail( $post_id, $thumb_width = 50, $thumb_height = 50, $crop = true ) {
		$attachment_id = get_post_thumbnail_id( $post_id );
		$file_path     = get_attached_file( $attachment_id, true );
		$image_url     = '';
		if ( $file_path ) {
			list( $image_url, $width, $height ) = wp_get_attachment_image_src( $attachment_id, 'full' );

			$file_pathinfo = pathinfo( $file_path );
			$new_file_path = "{$file_pathinfo['dirname']}/{$file_pathinfo['basename']}-{$thumb_width}x{$thumb_height}.{$file_pathinfo['extension']}";
			if ( ! file_exists( $new_file_path ) ) {
				$image = wp_get_image_editor( $file_path );
				if ( ! is_wp_error( $image ) ) {
					$image->resize( $thumb_width, $thumb_height, $crop );
					$new_image = $image->save( $new_file_path );
					$image_url = str_replace( ABSPATH, trailingslashit( get_bloginfo( 'wpurl' ) ), $new_image['path'] );
				}
			} else {
				$image_url = str_replace( ABSPATH, trailingslashit( get_bloginfo( 'wpurl' ) ), $new_file_path );
			}
		}
		return $image_url;
	}

	/**
	 * Print footer sliders javascript stored in array
	 *
	 * @since 1.0.0
	 */
	public function print_footer_scripts() {
		if ( ! empty( $this->sliders_js ) ) {
			echo '<script type="text/javascript">';
			echo 'jQuery(document).ready(function($) {';
			foreach ( $this->sliders_js as $slider_js ) {
				echo $slider_js;
			}
			echo '});';
			echo '</script>';
		}
	}

	/**
	 * Print footer sliders styles stored in array
	 *
	 * @since 1.0.0
	 */
	public function print_header_styles() {
		if ( ! empty( $this->sliders_css ) ) {
			echo '<style type="text/css">';
			foreach ( $this->sliders_css as $slider_css ) {
				echo $slider_css;
			}
			echo '</style>';
		}
	}

	/**
	 * Exclude category posts from homepage
	 *
	 * @since 1.0.1
	 *
	 * @return object updated $query
	 */
	public function exlude_category_numix( $query ) {
		if( $query->is_home ) {
			 
			global $wpdb;

			$slider_rows          = $wpdb->get_results( 'SELECT post_categories, hide_posts FROM '.Numix_Post_Slider::$table_name, ARRAY_A );
			$categories_arr_numix = array();
			$cats_to_exclude      = array();

			foreach( $slider_rows as $slider_row ) {

				if ( $slider_row['hide_posts'] == 'true' ) {

					$post_taxonomies_arr = (array) json_decode( stripslashes( $slider_row['post_categories'] ) );

					if( !empty( $categories_arr_numix ) ) {

						$categories_arr_numix = array_unique( array_merge( $categories_arr_numix, $post_taxonomies_arr['category'] ) );

					} else {

						$categories_arr_numix = $post_taxonomies_arr['category'];

					}
				}
			}			
			if( !empty( $categories_arr_numix ) ) {
				foreach( $categories_arr_numix as $cat ) {
					$catobj = get_category_by_slug( $cat ); 
	  				$catid  = $catobj->term_id;
	  				if( $catid != '' ) {
	  					$cats_to_exclude[]=$catid;
	  				}
				}	
			}
			if( !empty( $cats_to_exclude ) ) {  

				(array) $cats_exclude_before = $query->get("category__not_in");
				
				if( !empty( $cat_exclude_before ) && is_array( $cats_exclude_before ) ) { 
					
					$cats_to_exclude = array_unique( array_merge( $cats_to_exclude, $cats_exclude_before ) );
				
				}
				
				$query->set( "category__not_in", $cats_to_exclude );

			}			
		 
		}
		
		return $query;
	}
}
