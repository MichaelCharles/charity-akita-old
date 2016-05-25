<?php
/*
Plugin Name: Log In & Out
Plugin URL: 
Description: Show login or logout link. Show register or site-admin link.
Version: 1.0
Author: khalil.ahammad
Author URL: https://profiles.wordpress.org/khalilahammad
License: GPLv2 or later
*/

class WP_Widget_Login_Logout extends WP_Widget {

	function WP_Widget_Login_Logout() { 
		$widget_ops = array('classname' => 'widget_login_logout', 'description' => __( 'Login-Logout widget', 'login-logout' ) );
		$this->WP_Widget('login_logout', __('Login-Logout', 'login-logout'), $widget_ops);
	}
	
	function widget( $args, $instance ) { 
		extract($args);
		
		$title = apply_filters('widget_title', $instance['title']);
		$login_text = empty($instance['login_text']) ? __('Log in', 'login-logout') : $instance['login_text'];
		$logout_text = empty($instance['logout_text']) ? __('Log out', 'login-logout') : $instance['logout_text'];
		$show_welcome_text = $instance['show_welcome_text'] ? '1' : '0';
		$welcome_text = empty($instance['welcome_text']) ? __('Welcome [username]', 'login-logout') : $instance['welcome_text'];
		$register_link = $instance['register_link'] ? '1' : '0';
		$register_text = empty($instance['register_text']) ? __('Register', 'login-logout') : $instance['register_text'];
		$admin_link = $instance['admin_link'] ? '1' : '0';
		$admin_text = empty($instance['admin_text']) ? __('Admin section', 'login-logout') : $instance['admin_text'];
		$login_redirect_to = $instance['login_redirect_to'];
		$logout_redirect_to = $instance['logout_redirect_to'];
		$inline = $instance['inline'] ? '1' : '0';
		$login_extra = $instance['login_extra'];
		$logout_extra = $instance['logout_extra'];
		
		echo $before_widget;
		if ( $title ){
			echo $before_title . $title . $after_title;
		}
		$redirect_to_self = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		
		if( empty( $login_redirect_to ) ){
			$login_redirect_to = $redirect_to_self;
		}
		if( empty( $logout_redirect_to ) ){
			$logout_redirect_to = $redirect_to_self;
		}
		
		if( $inline ){
			$wrap_before = '<p class="wrap_login_logout">';
			$wrap_after = '</p>';
			$item_before = '<span class='; 
			$item_after = '</span>';
			$split_char = ' | ';
		} else {
			$wrap_before = '<ul class="wrap_login_logout">';
			$wrap_after = '</ul>';
			$item_before = '<li class='; 
			$item_after = '</li>';
			$split_char = '';
		}
		echo "\n".'<!-- Login-Logout plugin v.2.9 wordpress.org/plugins/login-logout/ -->'."\n";
		echo $wrap_before."\n";
		if ( $show_welcome_text ){
			if ( is_user_logged_in() ){
				$current_user = wp_get_current_user();
				$username = $current_user->display_name;
				$username_link = '<a href="'.admin_url('profile.php').'">'.$username.'</a>';
				$welcome_text_new = str_replace('[username]', $username_link, $welcome_text);
				echo $item_before.'"item_welcome">'.$welcome_text_new.$item_after.$split_char;
			}
		}
		echo $item_before;
		
		if ( ! is_user_logged_in() ){
			echo '"item_login">';
			echo '<a href="'.esc_url( wp_login_url( $login_redirect_to ) ).'">'.$login_text.'</a>';
		} else {
			echo '"item_logout">';
			echo '<a href="'.esc_url( wp_logout_url( $logout_redirect_to ) ).'">'.$logout_text.'</a>';
		}
		echo $item_after;
		
		if( $register_link ){ 
			if ( ! is_user_logged_in() ) {
				if ( get_option('users_can_register') ){
					echo $split_char.$item_before.'"item_register">'.'<a href="'.site_url('wp-login.php?action=register', 'login').'">'.$register_text.'</a>'.$item_after;
				}
			}
		}
		if( $admin_link ){ 
			if ( is_user_logged_in() ) {
				echo $split_char.$item_before.'"item_admin">'.'<a href="'.admin_url().'">'.$admin_text.'</a>'.$item_after;
			}
		}
		
		if ( is_user_logged_in() ) { 
			if( $login_extra ){
				echo $split_char.$item_before.'"item_extra_login">'.$login_extra.$item_after;
			}
		} else {
			if( $logout_extra ){
				echo $split_char.$item_before.'"item_extra_logout">'.$logout_extra.$item_after;
			}
		}
		
		echo "\n".$wrap_after."\n";
		
		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) { 
		$instance = $old_instance;
		$new_instance = wp_parse_args( 
			(array) $new_instance,
			array(
				'title' => '',
				'register_link' => 0,
				'admin_link' => 0,
				'login_redirect_to' => '',
				'logout_redirect_to' => '',
				'inline' => 0
			)
		);
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['login_text'] = trim($new_instance['login_text']);
		$instance['logout_text'] = trim($new_instance['logout_text']);
		$instance['show_welcome_text'] = $new_instance['show_welcome_text'] ? 1 : 0;
		$instance['welcome_text'] = trim($new_instance['welcome_text']);
		$instance['register_link'] = $new_instance['register_link'] ? 1 : 0;
		$instance['register_text'] = trim($new_instance['register_text']);
		$instance['admin_link'] = $new_instance['admin_link'] ? 1 : 0;
		$instance['admin_text'] = trim($new_instance['admin_text']);
		$instance['login_redirect_to'] = strip_tags($new_instance['login_redirect_to']);
		$instance['logout_redirect_to'] = strip_tags($new_instance['logout_redirect_to']);
		$instance['inline'] = $new_instance['inline'] ? 1 : 0;
		$instance['login_extra'] = trim($new_instance['login_extra']);
		$instance['logout_extra'] = trim($new_instance['logout_extra']);
		return $instance;
	}

	function form( $instance ) { 
		$instance = wp_parse_args(
			(array) $instance,
			array(
				'title' => '',
				'register_link' => 0,
				'admin_link' => 0,
				'login_redirect_to' => '',
				'logout_redirect_to' => '',
				'inline' => 0
			)
		);
		$title = strip_tags($instance['title']);
		$login_text = trim($instance['login_text']);
		$logout_text = trim($instance['logout_text']);
		$show_welcome_text = $instance['show_welcome_text'] ? 'checked="checked"' : '';
		$welcome_text = trim($instance['welcome_text']);
		$register_link = $instance['register_link'] ? 'checked="checked"' : '';
		$register_text = trim($instance['register_text']);
		$admin_link = $instance['admin_link'] ? 'checked="checked"' : '';
		$admin_text = trim($instance['admin_text']);
		$login_redirect_to = strip_tags($instance['login_redirect_to']);
		$logout_redirect_to = strip_tags($instance['logout_redirect_to']);
		$inline = $instance['inline'] ? 'checked="checked"' : '';
		$login_extra = trim($instance['login_extra']);
		$logout_extra = trim($instance['logout_extra']);
		
?>
			<p>
				<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', 'login-logout'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('login_text'); ?>"><?php _e('Login text', 'login-logout'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('login_text'); ?>" name="<?php echo $this->get_field_name('login_text'); ?>" type="text" value="<?php echo esc_attr($login_text); ?>" />
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('logout_text'); ?>"><?php _e('Logout text', 'login-logout'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('logout_text'); ?>" name="<?php echo $this->get_field_name('logout_text'); ?>" type="text" value="<?php echo esc_attr($logout_text); ?>" />
			</p>
			<p>
				<input class="checkbox" type="checkbox" <?php echo $show_welcome_text; ?> id="<?php echo $this->get_field_id('show_welcome_text'); ?>" name="<?php echo $this->get_field_name('show_welcome_text'); ?>" /> <label for="<?php echo $this->get_field_id('show_welcome_text'); ?>"><?php _e('Show welcome text if user is logged in', 'login-logout'); ?></label>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('welcome_text'); ?>"><?php _e('Welcome text (use [username] for showing the name of the logged user)', 'login-logout'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('welcome_text'); ?>" name="<?php echo $this->get_field_name('welcome_text'); ?>" type="text" value="<?php echo esc_attr($welcome_text); ?>" />
			</p>
			<p>
				<input class="checkbox" type="checkbox" <?php echo $register_link; ?> id="<?php echo $this->get_field_id('register_link'); ?>" name="<?php echo $this->get_field_name('register_link'); ?>" /> <label for="<?php echo $this->get_field_id('register_link'); ?>"><?php _e('Show register link (if user is logged out and if users can register)', 'login-logout'); ?></label>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('register_text'); ?>"><?php _e('Register text', 'login-logout'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('register_text'); ?>" name="<?php echo $this->get_field_name('register_text'); ?>" type="text" value="<?php echo esc_attr($register_text); ?>" />
			</p>
			<p>
				<input class="checkbox" type="checkbox" <?php echo $admin_link; ?> id="<?php echo $this->get_field_id('admin_link'); ?>" name="<?php echo $this->get_field_name('admin_link'); ?>" /> <label for="<?php echo $this->get_field_id('admin_link'); ?>"><?php _e('Show admin link (if user is logged in)', 'login-logout'); ?></label>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('admin_text'); ?>"><?php _e('Admin text', 'login-logout'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('admin_text'); ?>" name="<?php echo $this->get_field_name('admin_text'); ?>" type="text" value="<?php echo esc_attr($admin_text); ?>" />
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('login_redirect_to'); ?>"><?php _e('Redirect to this page after login', 'login-logout'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('login_redirect_to'); ?>" name="<?php echo $this->get_field_name('login_redirect_to'); ?>" type="text" value="<?php echo esc_attr($login_redirect_to); ?>" />
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('logout_redirect_to'); ?>"><?php _e('Redirect to this page after logout', 'login-logout'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('logout_redirect_to'); ?>" name="<?php echo $this->get_field_name('logout_redirect_to'); ?>" type="text" value="<?php echo esc_attr($logout_redirect_to); ?>" />
			</p>
			<p>
				<input class="checkbox" type="checkbox" <?php echo $inline; ?> id="<?php echo $this->get_field_id('inline'); ?>" name="<?php echo $this->get_field_name('inline'); ?>" /> <label for="<?php echo $this->get_field_id('inline'); ?>"><?php _e('Inline (list or line of links)', 'login-logout'); ?></label>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('login_extra'); ?>"><?php _e('Extra item when user is logged in', 'login-logout'); ?></label>
				<textarea class="widefat" rows="10" cols="10" id="<?php echo $this->get_field_id('login_extra'); ?>" name="<?php echo $this->get_field_name('login_extra'); ?>"><?php echo esc_attr($login_extra); ?></textarea>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('logout_extra'); ?>"><?php _e('Extra item when user is logged out', 'login-logout'); ?></label>
				<textarea class="widefat" rows="10" cols="10" id="<?php echo $this->get_field_id('logout_extra'); ?>" name="<?php echo $this->get_field_name('logout_extra'); ?>"><?php echo esc_attr($logout_extra); ?></textarea>
			</p>
<?php
	}
}



add_action('widgets_init', create_function('', 'return register_widget("WP_Widget_Login_Logout");'));


if ( ! function_exists( 'login_logout_plugin_unqprfx_init' ) ) :
	function login_logout_plugin_unqprfx_init() { 
		load_plugin_textdomain('login-logout', false, dirname( plugin_basename(__FILE__) ) . '/languages/');
	}
	add_action('init', 'login_logout_plugin_unqprfx_init');
endif; 


if ( ! function_exists( 'login_logout_unqprfx_plugin_meta' ) ) :
	function login_logout_unqprfx_plugin_meta( $links, $file ) {
		if ( strpos( $file, 'login-logout.php' ) !== false ) {
			$links = array_merge( $links, array( '<a href="#"> </a>' ) );
			$links = array_merge( $links, array( '<a href="#""></a>' ) );
		}
		return $links;
	}
	add_filter( 'plugin_row_meta', 'login_logout_unqprfx_plugin_meta', 10, 2 );
endif; 