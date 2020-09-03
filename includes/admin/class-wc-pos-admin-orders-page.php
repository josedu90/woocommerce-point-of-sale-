<?php
/**
 *
 * @author   Actuality Extensions
 * @category Admin
 * @package  WC_POS_Admin/Admin
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WC_POS_Admin_Orders_Page' ) ) :

/**
 * WC_POS_Admin_Orders_Page Class
 *
 * Handles the edit posts views and some functionality on the edit post screen for WC post types.
 */
class WC_POS_Admin_Orders_Page {

  	/**
     * Hook into ajax events
     */
    public function __construct() {
      /* Change the Guest in to Walk in Customer */
      add_filter('manage_shop_order_posts_custom_column', array($this, 'pos_custom_columns'), 2);
      add_action( 'wp_trash_post', array($this, 'delete_tile'), 10 );
      add_filter( 'manage_edit-shop_order_columns', array( $this, 'add_order_type_column'), 9999);            
      add_action( 'manage_shop_order_posts_custom_column', array( $this, 'display_order_type_column'), 2 );
      add_action( 'restrict_manage_posts', array( $this, 'restrict_manage_orders' ), 5 );
      add_action( 'add_meta_boxes', array($this, 'add_pos_print_metabox') );
      add_action('woocommerce_admin_order_item_values', array($this, 'add_print_refund_button'), 10, 3);
    }
    /**
     * Change the Guest in to Walk in Customer
     */
    function pos_custom_columns() {
        global $post, $woocommerce, $the_order;
        if (empty($the_order) || $the_order->get_id() != $post->ID) {
            $the_order = new WC_Order($post->ID);
        }

        if (!$the_order->get_billing_first_name()) {

            $the_order->set_billing_first_name('Walk-in Customer');
        }
    }
    function delete_tile($pid){
        global $wpdb;
        $table_name = $wpdb->prefix . "wc_poin_of_sale_tiles";
        $query = "DELETE FROM $table_name WHERE product_id = $pid";
        $wpdb->query( $query );
    }

    function add_order_type_column($columns)
    {
        $new_columns = array();
        foreach ($columns as $key => $value) {
            if($key == 'order_number')
                $new_columns['wc_pos_order_type'] = __( '<span class="order-type tips" data-tip="Order Type">Order Type</span>', 'wc_point_of_sale' );
            $new_columns[$key] = $value;
        }
        return $new_columns;
    }
    
    function display_order_type_column($column)
    {
        global $post, $woocommerce, $the_order;

            if ( empty( $the_order ) || $the_order->get_id() != $post->ID )
                $the_order = new WC_Order( $post->ID );

            if ( $column == 'wc_pos_order_type' ) {
	            $order_id = $the_order->get_id();
	            $created_via = get_post_meta($order_id, '_created_via', true);
				if($created_via == 'checkout'){
                $order_type = __( '<span class="order-type-web tips" data-tip="Website"><span>', 'wc_point_of_sale' );
                } else {
	            $order_type = __( '<span class="order-type-staff tips" data-tip="Manual"><span>', 'wc_point_of_sale' );
                }
                $amount_change = get_post_meta( $order_id, 'wc_pos_order_type', true );
                if($amount_change) $order_type = __( '<span class="order-type-pos tips" data-tip="Point of Sale"><span>', 'wc_point_of_sale' );
                echo $order_type;
            }
    }

    public function restrict_manage_orders($value='')
    {
        global $woocommerce, $typenow;
        if ( 'shop_order' != $typenow ) {
            return;
        }
        $req_type = isset($_REQUEST['shop_order_wc_pos_order_type']) ? $_REQUEST['shop_order_wc_pos_order_type'] : '';
        $req_reg  = isset($_REQUEST['shop_order_wc_pos_filter_register']) ? $_REQUEST['shop_order_wc_pos_filter_register'] : '';
        $req_out  = isset($_REQUEST['shop_order_wc_pos_filter_outlet']) ? $_REQUEST['shop_order_wc_pos_filter_outlet'] : '';
        ?>
        <select name='shop_order_wc_pos_order_type' id='dropdown_shop_order_wc_pos_order_type'>
            <option value=""><?php _e( 'All types', 'wc_point_of_sale' ); ?></option>
            <option value="online" <?php selected($req_type, 'online', true); ?> ><?php _e( 'Online', 'wc_point_of_sale' ); ?></option>
            <option value="POS" <?php selected($req_type, 'POS', true); ?> ><?php _e( 'POS', 'wc_point_of_sale' ); ?></option>
        </select>
        <?php
        $filters = get_option('woocommerce_pos_order_filters', array('register'));

        if( !$filters || !is_array($filters)) return;

        if( in_array('register', $filters)) {
            $registers = WC_POS()->register()->get_data();
            if($registers){
            ?>
            <select name='shop_order_wc_pos_filter_register' id='shop_order_wc_pos_filter_register'>
            <option value=""><?php _e('All registers', 'wc_point_of_sale'); ?></option>
            <?php
            foreach ($registers as $register) {
                echo '<option value="'.$register['ID'].'" ' . selected($req_reg, $register['ID'], false) . ' >'.$register['name'].'</option>';
            }
            ?>
            </select>
            <?php
            }
        }
        if( in_array('outlet', $filters)) {
            $outlets = WC_POS()->outlet()->get_data();
            if($outlets){
            ?>
            <select name='shop_order_wc_pos_filter_outlet' id='shop_order_wc_pos_filter_outlet'>
            <option value=""><?php _e('All outlets', 'wc_point_of_sale'); ?></option>
            <?php
            foreach ($outlets as $outlet) {
                echo '<option value="'.$outlet['ID'].'" ' . selected($req_out, $outlet['ID'], false) . ' >'.$outlet['name'].'</option>';
            }
            ?>
            </select>
            <?php
            }
        }
        
    }

    public function add_pos_print_metabox()
    {
        global $post_id;
        if(get_post_meta($post_id, 'wc_pos_order_type', true) != "POS"){
            return;
        }
        add_meta_box( 'wc-pos-print', __( 'Point of Sale', 'wc_point_of_sale' ), array($this, 'pos_print_output'), 'shop_order', 'side' );
    }

    public function pos_print_output()
    {
        global $post_id;
        $register = WC_Pos_Registers::instance()->get_data(get_post_meta($post_id, 'wc_pos_id_register', true));
        $cashier = get_post_meta($post_id, 'wc_pos_served_by_name', true);
        $amount = get_post_meta($post_id, 'wc_pos_amount_pay', true);
        $change = get_post_meta($post_id, 'wc_pos_amount_change', true);
        $order = get_post_meta($post_id, 'wc_pos_prefix_suffix_order_number', true);
        $signature = get_post_meta($post_id, 'wc_pos_signature', true);
        ?>

        <div class="register_meta_data">
            <?php _e('Register: ', 'wc_point_of_sale') ?>
            <span><strong><?php echo $register[0]["name"] ?></strong></span>
        </div>
        <div class="register_meta_data">
            <?php _e('Cashier: ', 'wc_point_of_sale') ?>
            <span><strong><?php echo $cashier ?></strong></span>
        </div>
        <div class="register_meta_data">
            <?php _e('Total: ', 'wc_point_of_sale') ?>
            <span><strong><?php echo wc_price(get_post_meta($post_id, '_order_total', true)); ?></strong></span>
        </div>
        <div class="register_meta_data">
            <?php _e('Tendered: ', 'wc_point_of_sale') ?>
            <span><strong><?php echo wc_price($amount) ?></strong></span>
        </div>
        <?php if($change > 0) : ?>
        <div class="register_meta_data">
            <?php _e('Change: ', 'wc_point_of_sale') ?>
            <span><strong><?php echo wc_price($change) ?></strong></span>
        </div>
    <?php endif; ?>
        <?php if($change < 0) : ?>
        <div class="register_meta_data amount_due">
            <?php _e('Amount Due: ', 'wc_point_of_sale') ?>
            <span><strong><?php echo wc_price($change) ?></strong></span>
        </div>
    <?php endif; ?>

        <div class="register_meta_data">
            <?php _e('Order #: ', 'wc_point_of_sale') ?>
            <span><strong><?php echo $order ?></strong></span>
        </div>

        <?php
        $coupons = wc_get_order($post_id)->get_items('coupon');
        $reason = "";
        foreach ($coupons as $coupon){
            if(strtolower($coupon->get_name()) == 'pos discount'){
                $reason = wc_get_order_item_meta($coupon->get_id(), 'wc_pos_discount_reason', true);
                break;
            }
        }

        if(!empty($reason)) : ?>
            <div class="register_meta_data">
                <?php _e('Discount Reason: ', 'wc_point_of_sale') ?>
                <span><strong><?php echo $reason ?></strong></span>
            </div>
        <?php endif; ?>

        <?php if($signature != "null") : ?>
        <?php add_thickbox(); ?>
        <div id="signature-content" style="display:none;">
            <img src="data:image/png;base64,<?php echo $signature ?>" alt="signature" id="signature-img">
        </div>
        <a href="#TB_inline?width=300&height=300&inlineId=signature-content" id="preview-signature" class="thickbox button button-block"><?php _e('Signature', 'wc_point_of_sale') ?></a>
        <?php endif; ?>

        <a href="<?php echo wp_nonce_url(admin_url('admin.php?print_pos_receipt=true&print_from_wc=true&order_id=' . $post_id), 'print_pos_receipt'); ?>"
           target="_blank"
           id="view-post-receipt"
           class="button button-block">
            <?php _e('View Receipt', 'wc_point_of_sale') ?>
        </a>
        <button id="print-post-receipt" class="button button-primary button-block"><?php _e('Print Receipt', 'wc_point_of_sale') ?></button>
        <?php
    }

    public function add_print_refund_button($null, $refund, $refund_id)
    {
        $button_url = admin_url("admin.php?print_pos_receipt=true&refund_id=" . $refund_id . "&_wpnonce=" . wp_create_nonce("print_pos_receipt"));
        ?>
        <script>
            jQuery(document).ready(function($){
                var row = $("#order_refunds").find("tr[data-order_refund_id=<?php echo $refund_id ?>]");
                row.children('td.item_cost')
                    .html('')
                    .append('<a href="<?php echo $button_url  ?>" class="button wc_print_refund" target="_blank"><?php _e("Print", "wc_point_of_sale") ?></a>');
            });
        </script>
        <?php
    }

}

new WC_POS_Admin_Orders_Page();

endif;