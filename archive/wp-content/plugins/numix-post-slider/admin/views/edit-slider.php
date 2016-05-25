<?php
/**
 * Represents Add/Edit Slider.
 *
 *
 * @package   Numix_Post_Slider
 * @author    Gaurav Padia <gauravpadia14u@gmail.com>
 * @author    Asalam Godhaviya <godhaviya.asalam@gmail.com>
 * @license   GPL-2.0+
 * @link      http://numixtech.com
 * @copyright 2014 Numix Techonologies
 */
?>
<div class="numix-admin wrap">
	<a href="admin.php?page=numix-post-slider" class="back-to-list-link">&larr; <?php _e( 'Back to Numix Slider list', 'numix-post-slider' ); ?></a>
	<h2>
		<?php
		if ( $slider_id > 0 && $slider_id != '' ) {
			_e( 'Edit Numix Slider #', 'numix-post-slider' ) . $slider_id;
		}
		else {
			_e( 'Add New Numix Slider', 'numix-post-slider' );
		}
		?>
	</h2>
	<div id="poststuff" class="metabox-holder">
		<div class="sortable-slides-body">
			<div class="sortable-slides-container">
				<div id="titlediv">
					<div id="titlewrap">
						<input type="text" name="slider_name" size="40" maxlength="255" placeholder="<?php _e( 'Type Slider name here', 'numix-post-slider' ); ?>" id="slider_name" value="<?php if ( $slider_row ) echo esc_html( $slider_row['name'] ); ?>">
					</div>
				</div>
				<h4>
					<?php _e( 'Select taxonomies for featured images to be used in slider', 'numix-post-slider' ); ?>
				</h4>
				<table class="settings-table">
					<tr>
						<td width="150px">
							<label><?php _e( 'Post taxonomies', 'numix-post-slider' ); ?></label>
						</td>
						<td>
							<div style="float:left;">
								<select id="post_categories_select" multiple="multiple">
								</select>
							</div>
							<div style="float:left;">
								<select id="post_taxonomy_relation">
									<option <?php if ( ! $slider_row || $slider_row['post_relation'] == 'OR' ) echo 'selected="selected"'; ?> value="OR">
										<?php _e( 'Match any', 'numix-post-slider' ); ?>
									</option>
									<option <?php if ( $slider_row['post_relation'] == 'AND' ) echo 'selected="selected"'; ?> value="AND">
										<?php _e( 'Match all', 'numix-post-slider' ); ?>
									</option>
								</select>
							</div>
						</td>
					</tr>
					<tr>
						<td width="150px">
							<label for="max_posts_include"><?php _e( 'Max posts to include', 'numix-post-slider' ); ?></label>
						</td>
						<td>
							<input id="max_posts_include" type="number" min="1" max="40" value="<?php if ( $slider_row ) echo intval( $slider_row['max_posts'] ); else echo '10'; ?>">
						</td>
					</tr>
					<tr>
						<td width="150px">
							<label><?php _e( 'Order by', 'numix-post-slider' ); ?></label>
						</td>
						<td>
							<div style="float:left;" class="radio-buttons">
								<label><input type="radio" <?php if ( ! $slider_row || $slider_row['post_orderby'] == 'post_date' ) echo 'checked="checked"'; ?> value="post_date" name="post-order-radio"><?php _e( 'Date', 'numix-post-slider' ); ?></label> <label><input type="radio" <?php if ( $slider_row['post_orderby'] == 'menu_order' ) echo 'checked="checked"'; ?> value="menu_order" name="post-order-radio"><?php _e( 'Menu Order', 'numix-post-slider' ); ?></label>
							</div>
							<div style="float:left;">
								<select id="post_order">
									<option <?php if ( ! $slider_row || $slider_row['post_order'] == 'ASC' ) echo 'selected="selected"'; ?> value="ASC">
										<?php _e( 'Ascending', 'numix-post-slider' ); ?>
									</option>
									<option <?php if ( $slider_row['post_order'] == 'DESC' ) echo 'selected="selected"'; ?> value="DESC">
										<?php _e( 'Descending', 'numix-post-slider' ); ?>
									</option>
								</select>
							</div>
						</td>
					</tr>
				<tr>
						<td width="150px">
							<label for="hide_posts"><?php _e( 'Hide posts from homepage', 'numix-post-slider' ); ?></label>
						</td>
						<td>
							<input id="hide_posts" name="hide_posts" type="checkbox" <?php if ( $slider_row['hide_posts'] == 'true' ) echo 'checked="checked"'; ?> value="true" /> <?php _e( 'Checking this option will hide posts of selected categories on homepage.', 'numix-post-slider' ); ?>
						</td>
					</tr>
				</table>
				<div class="clear"></div>
				<br>
				<div>
					<a href="http://numixtech.com/plugins/numix-post-touch-slider/" target="_blank"><img src="<?php echo NUMIX_SLIDER_URL; ?>/admin/assets/images/numix_slider_premium.png" /></a>
				</div>
			</div>
		</div>
		<div id="side-info-column" class="options-sidebar">
			<p class="tc-tip description">
				<?php _e( 'Tip: Hover over labels to learn more about options.', 'numix-post-slider' ); ?>
			</p>
			<div class="postbox action actions-holder">
				<a class="alignleft button-primary button80" id="save-slider" href="#"><?php if ( $slider_id == '' ) _e( 'Create Slider', 'numix-post-slider' );  else _e( 'Save Slider', 'numix-post-slider' ); ?></a>
				<div id="save-progress" class="waiting ajax-saved" style="background-image: url('<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>');"></div><br class="clear">
			</div>
			<div id="numixlisder-options" class="meta-box-sortables ui-sortable">
				<?php include 'numixslider-options.php';  ?>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
var sliderSettings = <?php echo json_encode( stripslashes( $slider_row['js_settings'] ) ); ?>;
var sliderID = <?php if ( $slider_id ) echo intval( $slider_id ); else echo "''"; ?>;
</script>
