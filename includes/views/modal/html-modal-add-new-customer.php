<!-- Add New Customer Popup box -->
<?php
$checkout = new WC_Checkout();
//WC 3.0 Notice
/*if( isset($checkout->checkout_fields['order']) ){
    unset($checkout->checkout_fields['order']['order_comments']);
}*/

$a = isset($checkout->checkout_fields['order']) ? count($checkout->checkout_fields['order']) : 0;
$o = isset($checkout->checkout_fields['pos_custom_order']) ? count($checkout->checkout_fields['pos_custom_order']) : 0;
$c = isset($checkout->checkout_fields['pos_acf']) ? count($checkout->checkout_fields['pos_acf']) : 0;
?>
<div class="md-modal md-dynamicmodal md-close-by-overlay md-register" id="modal-order_customer">
    <div class="md-content woocommerce">
        <h1><?php _e('Customer Details', 'wc_point_of_sale'); ?><span class="md-close"></span></h1>
        <h2 class="nav-tab-wrapper woo-nav-tab-wrapper">
            <a href="#pos_billing_details"
               class="nav-tab nav-tab-active"><?php _e('Billing Details', 'wc_point_of_sale'); ?></a>
            <a href="#pos_shipping_details" class="nav-tab"><?php _e('Shipping Details', 'wc_point_of_sale'); ?></a>
            <a href="#pos_additional_fields"
               class="nav-tab" <?php echo !$a ? 'style="display:none;"' : ''; ?> ><?php _e('Additional Fields', 'wc_point_of_sale'); ?></a>
            <a href="#pos_custom_fields"
               class="nav-tab" <?php echo !$c ? 'style="display:none;"' : ''; ?>><?php _e('Custom Fields', 'wc_point_of_sale'); ?></a>
            <?php if (class_exists('WC_Gateway_Account_Funds') && in_array('accountfunds', get_option('pos_enabled_gateways', array()))): ?>
                <a href="#pos_account_fund_fields" class="nav-tab"><?php _e('Account Funds', 'wc_point_of_sale'); ?></a>
            <?php endif; ?>
        </h2>
        <div id="customer_details" class="col3-set">
        </div>
        <div class="wrap-button">

            <button class="button button-primary wp-button-large alignright" type="button" id="save_customer">
                <?php _e('Save Customer', 'wc_point_of_sale'); ?>
            </button>
            <?php if (get_option('wc_pos_enable_user_card', 'no') =='yes'): ?>
                <button class="button button-primary wp-button-large alignright" type="button" id="scan_card_button">
                    <?php _e('Scan', 'wc_point_of_sale'); ?>
                </button>
            <?php endif; ?>
            <input class="input-checkbox" id="createaccount" type="checkbox" value="1"/><label for="createaccount" class="pos_register_toggle" id="create_new_account"></label>
            <label for="createaccount"><?php _e('Save Customer', 'wc_point_of_sale'); ?></label>
        </div>
    </div>
</div>