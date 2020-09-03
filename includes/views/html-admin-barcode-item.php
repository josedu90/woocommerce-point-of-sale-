<?php

/**
 * @var WC_Product $_product
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$product_link  = $_product ? admin_url( 'post.php?post=' . absint( $_product->get_id() ) . '&action=edit' ) : '';
$thumbnail     = $_product ? apply_filters( 'woocommerce_admin_order_item_thumbnail', $_product->get_image( 'thumbnail', array( 'title' => '' ), false ), 0, null ) : '';
$item_id       = $_product->get_id();
$parent_id     = $_product->is_type('variation') ? $_product->get_parent_id() : $_product->get_id();

?>
<tr class="item <?php echo $class; ?> item_<?php echo $item_id; ?>" data-prid="<?php echo $item_id; ?>" data-parentid="<?php echo $parent_id; ?>" >
	<td class="thumb">
		<?php
			echo '<div class="wc-order-item-thumbnail">' . wp_kses_post( $thumbnail ) . '</div>';
		?>
	</td>
	<td class="name">
		<?php
			echo $product_link ? '<a href="' . esc_url( $product_link ) . '" class="wc-order-item-name">' .  esc_html( $_product->get_title() ) . '</a>' : '<div class="class="wc-order-item-name"">' . esc_html( $_product->get_title() ) . '</div>';
            $sku = '';
            if ( $_product && $_product->get_sku() ) {
                $sku = esc_html( $_product->get_sku() );
                echo '<div class="wc-order-item-sku sku_text"><strong>' . esc_html( $_product->get_sku() ) . '</strong></div>';
            }else{
                echo '<div class="wrong_sku sku_text"></div>';
            }
			if ( $_product->is_type('variation') ) {

				echo '<div class="wc-order-item-variation"><strong>' . __( 'Variation ID:', 'woocommerce' ) . '</strong> ';
                echo $_product->get_id();
				echo '</div>';

				$variation = $_product->get_attributes();
				if ( is_array( $variation ) ) {
					echo '<div class="view"><table cellspacing="0" class="display_meta"><tbody>';

					foreach ( $variation as $name => $value ) {
						if ( ! $value ) {
							continue;
						}

						// If this is a term slug, get the term's nice name
						if ( taxonomy_exists( esc_attr( str_replace( 'attribute_', '', $name ) ) ) ) {
							$term = get_term_by( 'slug', $value, esc_attr( str_replace( 'attribute_', '', $name ) ) );
							if ( ! is_wp_error( $term ) && ! empty( $term->name ) ) {
								$value = $term->name;
							}
						} else {
							$value = ucwords( str_replace( '-', ' ', $value ) );
						}
						
						echo'<tr><th>' . wc_attribute_label( str_replace( 'attribute_', '', $name ) ) . ':</th><td>' . rawurldecode( $value ) . '</td></tr>';
					}

					echo '</tbody></table></div>';

				}
			}

		?>
	</td>
	<td class="item_cost" width="1%">
		<div class="view product_price">
			<?php
					echo wc_price( $_product->get_price() );
			?>
		</div>
	</td>
	<td class="quantity" width="1%">
		<div class="view">
			<?php
				echo '<small class="times">&times;</small> <span>1</span>';
			?>
		</div>
		<div class="edit" style="display: none;">
			<?php $item_qty = 1; ?>
			<input type="number" step="<?php echo apply_filters( 'woocommerce_quantity_input_step', '1', $_product ); ?>" min="1" autocomplete="off" placeholder="1" value="1" size="4" class="quantity" />
		</div>
	</td>
	<td class="line_barcode">
		<div class="barcode_border">
			<?php
            $text = !empty($_product->get_sku()) ? $_product->get_sku() : $_product->get_id();
            $barcode_url = WC_POS()->barcode_url() . '&text=' . $text;
			?>
			<img src="<?php echo $barcode_url . '&font_size=12'; ?>" alt="" data-barcode_url="<?php echo $barcode_url; ?>">
			<div class="barcode_text"></div>
		</div>									
	</td>
	<td class="wc-order-edit-line-item" width="1%">
		<div class="wc-order-edit-line-item-actions">
			<a class="edit-order-item tips" href="#" data-tip="<?php esc_attr_e( 'Edit item', 'woocommerce' ); ?>"></a>
			<a class="delete-order-item tips" href="#" data-tip="<?php esc_attr_e( 'Delete item', 'woocommerce' ); ?>"></a>
		</div>
	</th>
</tr>