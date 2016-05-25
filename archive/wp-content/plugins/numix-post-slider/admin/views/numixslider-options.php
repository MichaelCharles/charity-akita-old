<?php
/**
 * Represents Slider Options.
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
<div class="postbox">
	<h3>
		<span><?php _e( 'Slider Options', 'numix-post-slider' ); ?></span>
	</h3>
	<div class="inside">
		<!-- Width and height -->
		<div class="fields-group">
			<div class="field-row">
				<label for="width" data-help="<?php _e( 'Slider width. Any CSS acceptable value.', 'numix-post-slider' ); ?>"><?php _e( 'Width', 'numix-post-slider' ); ?></label>
				<input id="width" data-visible="false" name="width" type="text" value="<?php if ( $slider_row ) echo esc_html( $slider_row['width'] ); else echo '600px'; ?>" size="5" />
			</div>
			<div class="field-row">
				<label for="height" data-help="<?php _e( 'Slider height. Any CSS acceptable value. Set according to all images height.', 'numix-post-slider' ); ?>"><?php _e( 'Height', 'numix-post-slider' ); ?></label>
				<input id="height" data-visible="false" name="height" type="text" value="<?php if ( $slider_row ) echo esc_html( $slider_row['height'] ); else echo '400px'; ?>" size="5" />
			</div>
		</div>

		<!-- Infinite -->
		<div class="fields-group">
			<div class="field-row">
				<label for="infinite" data-help="<?php _e( 'Makes the slider infinite/continous.', 'numix-post-slider' ); ?>"><?php _e( 'Infinite', 'numix-post-slider' ); ?></label>
				<input id="infinite" name="infinite" type="checkbox" value="true" checked />
			</div>
		</div>
		<!-- Loop -->
		<div class="fields-group" id="loop_option">
			<div class="field-row">
				<label for="loop" data-help="<?php _e( 'Jump last slide to first slide (This works only when infinite is not set.)', 'numix-post-slider' ); ?>"><?php _e( 'Loop', 'numix-post-slider' ); ?></label>
				<input id="loop" name="loop" type="checkbox" value="true" />
			</div>
		</div>

		<!-- Center -->
		<div class="fields-group">
			<div class="field-row">
				<label for="center" data-help="<?php _e( 'Display current slide in center.', 'numix-post-slider' ); ?>"><?php _e( 'Center', 'numix-post-slider' ); ?></label>
				<input id="center" name="center" type="checkbox" value="true" checked />
			</div>
		</div>

		<!-- Slideshow(autoplay) -->
		<div class="fields-group">
			<div class="field-row group-leader">
				<label for="autoplay" data-help="<?php _e( 'Enable autoplay.', 'numix-post-slider' ); ?>"><?php _e( 'Autoplay', 'numix-post-slider' ); ?></label>
				<input id="autoplay" name="autoPlay" type="checkbox" value="true" />
			</div>
			<div id="autoplay_options">
				<div class="field-row">
					<label for="autoplay_interval" data-help="<?php _e( 'Time in milliseconds before next slide is shown.', 'numix-post-slider' ); ?>"><?php _e( 'Autoplay Interval', 'numix-post-slider' ); ?></label>
					<input id="autoplay_interval" name="autoPlayInterval" type="number" value="3000" size="5" min="0" step="100" max="20000" />
					<span class="unit"> &nbsp;ms</span>
				</div>
				<div class="field-row">
					<label for="autoplay_stop_action" data-help="<?php _e( 'Stops autoplay when user takes control over Slider(drag, click, arrow e.t.c.).', 'numix-post-slider' ); ?>"><?php _e( 'Autoplay Stop on action', 'numix-post-slider' ); ?></label>
					<input id="autoplay_stop_action" name="autoPlayStopAction" type="checkbox" value="true" checked/>
				</div>
			</div>
		</div>


		<!-- Arrows navigation -->
		<div class="fields-group">
			<div class="field-row group-leader">
				<label for="arrows_nav" data-help="<?php _e( 'Enable next and previous arrows.', 'numix-post-slider' ); ?>"><?php _e( 'Arrows (next, prev)', 'numix-post-slider' ); ?></label>
				<input id="arrows_nav" name="arrowsNav" type="checkbox" value="true" />
			</div>
			<div class="field-row" id="arrows_option">
				<label for="arrows_auto_hide" data-help="<?php _e( 'Auto hide next and previous arrows when mouse leaves slider area.', 'numix-post-slider' ); ?>"><?php _e( 'Arrows auto-hide', 'numix-post-slider' ); ?></label>
				<input id="arrows_auto_hide" data-visible="false" name="arrows_auto_hide" type="checkbox" value="true" <?php if ( ! $slider_row || $slider_row['arrows_auto_hide'] == 'true' ) echo ' checked="checked"'; ?> />
			</div>
		</div>

		<!-- Transition Speed -->
		<div class="fields-group">
			<div class="field-row">
				<label for="animation_speed" data-help="<?php _e( 'Slide transition speed.', 'numix-post-slider' ); ?>"><?php _e( 'Animation speed', 'numix-post-slider' ); ?></label>
				<input id="animation_speed" name="animationSpeed" size="5" type="number" step="50" value="800" min="0" max="10000" />
				<span class="unit"> &nbsp;ms</span>
			</div>
		</div>


		<!-- Link slide to post URL -->
		<div class="fields-group">
			<div class="field-row">
				<label for="activate_on_click" data-help="<?php _e( 'Redirect to post URL on click. If this option is not set clicked slide will be active.', 'numix-post-slider' ); ?>"><?php _e( 'Link slide to post', 'numix-post-slider' ); ?></label>
				<input id="activate_on_click" name="activateOnClick" type="checkbox" value="false" />
			</div>
		</div>

		<div class="fields-group">
			<div class="field-row">
				<label for="display_post_title" data-help="<?php _e( 'Display post title on slide.', 'numix-post-touch-slider' ); ?>"><?php _e( 'Display Post Title', 'numix-post-touch-slider' ); ?></label>
				<input id="display_post_title" name="display_post_title" data-visible="false" type="checkbox" <?php if ( $slider_row && $slider_row['display_post_title'] == 'true' ) echo ' checked="checked"'; ?> value="true" />
			</div>
		</div>
		
		<!-- Slider Bottom Navigation Type -->
		<div class="fields-group">
			<div class="field-row">
				<label for="slider_bottom_nav_type" data-help="<?php _e( 'Set slider bottom navigation type.', 'numix-post-slider' ); ?>"><?php _e( 'Slider Bottom Navigation', 'numix-post-slider' ); ?></label>
				<select id="slider_bottom_nav_type" data-visible="false" name="slider_bottom_nav_type">
					<option value="thumbnail" <?php if ( $slider_row['bottom_nav_type'] == 'thumbnail' ) echo 'selected="selected"'; ?>>Thumbnail</option>
					<option value="" <?php if ( $slider_row && $slider_row['bottom_nav_type'] == '' ) echo 'selected="selected"'; ?>>None</option>
				</select>
			</div>
		</div>

	</div>
</div>
