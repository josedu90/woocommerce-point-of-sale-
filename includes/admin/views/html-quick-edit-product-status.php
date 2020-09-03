<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
global $post;

?>

<fieldset class="inline-edit-col-left clear">
	<div id="visibility-fields" class="inline-edit-col">

		<h4><?php esc_html_e( 'Point of Sale', 'wc_point_of_sale' ); ?></h4>
		<div class="inline-edit-group">
			<label class="alignleft">
				<span class="title"><?php esc_html_e( 'POS Status', 'wc_point_of_sale' ); ?></span>
				<span class="input-text-wrap">
					<select class="pos_visibility" name="_pos_visibility">
					<?php
                    $pos_visibility = ($pos_visibility = get_post_meta($post->ID, '_pos_visibility', true)) ? $pos_visibility : 'pos_online';
                    $visibility_options = apply_filters('woocommerce_pos_visibility_options', array(
                        'pos_online' => __('POS & Online', 'wc_point_of_sale'),
                        'pos' => __('POS Only', 'wc_point_of_sale'),
                        'online' => __('Online Only', 'wc_point_of_sale'),
                    ));
						foreach ( $visibility_options as $key => $value ) {
							echo "<option ". selected($key, $pos_visibility, false) ." value='" . esc_attr( $key ) . "'>" . esc_html( $value ) . "</option>";
						}
					?>
					</select>
				</span>
			</label>
		</div>
	</div>
</fieldset>
