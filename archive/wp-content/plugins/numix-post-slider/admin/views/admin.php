<?php
/**
 * Represents the view for the administration dashboard.
 *
 * This includes the header, options, and other information that should provide
 * The User Interface to the end user.
 *
 * @package   Numix_Post_Slider
 * @author    Gaurav Padia <gauravpadia14u@gmail.com>
 * @author    Asalam Godhaviya <godhaviya.asalam@gmail.com>
 * @license   GPL-2.0+
 * @link      http://numixtech.com
 * @copyright 2014 Numix Techonologies
 */
?>

<div class="wrap">

	<h2><?php echo esc_html( get_admin_page_title() ); ?> <a href="<?php echo esc_url( admin_url( 'admin.php?page=numix-post-slider&action=add_new' ) ); ?>" class="add-new-h2">Add New</a></h2>

	<?php if ( isset( $_GET['msg'] ) && $_GET['msg'] == 'duplicate_done' && isset( $_GET['from_id'] ) && isset( $_GET['duplicate_id'] ) ) : ?>
		<div id="message" class="updated below-h2">
			<p><?php printf( __( 'Slider #%d duplicated to new slider #%d.', 'numix-post-slider' ), $_GET['from_id'], $_GET['duplicate_id'] ); ?></p>
		</div>
	<?php endif; ?>
	<?php if ( isset( $_GET['msg'] ) && $_GET['msg'] == 'delete_done' && isset( $_GET['delete_id'] ) ) : ?>
		<div id="message" class="updated below-h2">
			<p><?php printf( __( 'Slider #%d permanently deleted.', 'numix-post-slider' ), $_GET['delete_id'] ); ?></p>
		</div>
	<?php endif; ?>

	<table class="wp-list-table widefat fixed pages">
		<thead>
		<tr>
			<th width="5%"><?php _e( 'ID', 'numix-post-slider' ); ?></th>
			<th width="45%"><?php _e( 'Name', 'numix-post-slider' ); ?></th>
			<th width="30%"><?php _e( 'Actions', 'numix-post-slider' ); ?></th>
			<th width="20%"><?php _e( 'Shortcode', 'numix-post-slider' ); ?></th>
		</tr>
		</thead>
		<tbody>
		<?php
		global $wpdb;
		$numix_sliders = $wpdb->get_results( 'SELECT * FROM ' . Numix_Post_Slider::$table_name . ' ORDER BY id' );
		if ( count( $numix_sliders ) == 0 ) :
		?>
		<tr>
			<td colspan="100%"><?php _e( 'No slider has been created yet. Please Click on "Add new" button to create new slider.', 'numix-post-slider' )?></td>
		</tr>
		<?php
		else :

			foreach ( $numix_sliders as $numix_slider ) :

				$slider_display_name = $numix_slider->name;
				if ( empty( $slider_display_name ) ) :
					$slider_display_name = 'NumixSlider #' . $numix_slider->id . ' (no name)';
				endif;
		?>
		<tr>
			<td><?php echo intval( $numix_slider->id ); ?></td>
			<td>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=numix-post-slider&action=edit&id=' . $numix_slider->id ) ); ?>" title="<?php _e( 'Edit', 'numix-post-slider' ) ?>"><?php echo esc_html( $slider_display_name ); ?></a>
			</td>
			<td>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=numix-post-slider&action=edit&id=' . $numix_slider->id ) ); ?>" title="<?php _e( 'Edit this item', 'numix-post-slider' ) ?>"><?php _e( 'Edit', 'numix-post-slider' ) ?></a> |
				<a class="delete-nslider-btn" href="#" data-protected-href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=numix-post-slider&noheader=true&action=delete&id='  . $numix_slider->id ), 'numixslider_delete_nonce' ) ); ?>" title="<?php _e( 'Delete slider permanently', 'numix-post-slider' ); ?>" ><?php _e( 'Delete', 'numix-post-slider' ) ?> </a> |
				<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=numix-post-slider&action=duplicate&noheader=true&id='  . $numix_slider->id ), 'numixslider_duplicate_nonce' ) ); ?>" title="<?php _e( 'Duplicate Slider', 'numix-post-slider' ); ?>"><?php _e( 'Duplicate', 'numix-post-slider' ); ?></a>
			</td>
			<td>
				<input type="text" value="[numixslider id='<?php echo intval( $numix_slider->id ); ?>']"></input>
			</td>
		</tr>
		<?php
			endforeach;
		endif;
		?>
		</tbody>
	</table>
	<br>
	<div style="text-align:right;">
		<a href="http://numixtech.com/plugins/numix-post-touch-slider/" target="_blank"><img src="<?php echo NUMIX_SLIDER_URL; ?>/admin/assets/images/numix_slider_premium.png" /></a>
	</div>
</div>
