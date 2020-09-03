<!-- Order Customer fields popup -->
<?php
$checkout = new WC_Checkout();
$o = isset($checkout->checkout_fields['pos_custom_order']) ? count($checkout->checkout_fields['pos_custom_order']) : 0;
?>
<div class="md-modal md-dynamicmodal md-close-by-overlay md-register" id="modal-acf_order_information">
    <div class="md-content woocommerce">
        <h1><?php _e('Order Details', 'wc_point_of_sale'); ?><span class="md-close"></span></h1>
        <div id="order_details" class="col3-set">

        </div>
        <div class="wrap-button">
            <button class="button button-primary wp-button-large alignright" type="button" id="save_order_fields">
                <?php _e('Save Order Fields', 'wc_point_of_sale'); ?>
            </button>
        </div>
    </div>
</div>