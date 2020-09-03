<!-- Tabs popup-->
<div class="md-modal md-dynamicmodal md-register md-close-by-overlay" id="modal-tabs">
    <div class="md-content woocommerce">
        <h1><?php _e('Tabs', 'wc_point_of_sale'); ?><span class="md-close"></span></h1>
        <div class="left-col">
            <?php for ($i = 1; $i <= 20; $i++) { ?>
                <?php if (array_key_exists($i, $data['tabs'])) {
                    $order = new WC_Order($data['tabs'][$i]->order_id);
                    ?>
                    <div class="box-tab saved"
                         data-tab_title="<?php echo $data['tabs'][$i]->title ?>"
                         data-tab_limit="<?php echo $data['tabs'][$i]->spend_limit ?>"
                         data-tab_register="<?php echo $data['tabs'][$i]->register_id ?>"
                         data-tab_id="<?php echo $data['tabs'][$i]->id ?>"
                         data-tab_customer="<?php echo $data['tabs'][$i]->customer ?>"
                         data-tab_order_id="<?php echo $data['tabs'][$i]->order_id ?>"
                         data-tab_number="<?php echo $i ?>"
                         data-time="<?php echo $data['tabs'][$i]->time ?>">
                        <p>
                            <span class="tab-timer"></span>
                        </p>
                        <p>
                            <span class="tab-key"><?php echo $i ?></span>
                        </p>
                        <?php if ($data['tabs'][$i]->title) { ?>
                            <p>
                                <span class="tab-title"><?php echo $data['tabs'][$i]->title ?></span>
                            </p>
                        <?php } ?>
                        <p>
                            <span class="status"><?php echo __('Opened', 'wc_point_of_sale') ?></span>
                        </p>
                        <p>
                            <span class="opened-amount"><?php echo wc_price($order->get_total()) ?></span>
                        </p>
                    </div>
                <?php } else { ?>
                    <div class="box-tab"
                         data-tab_title="" data-tab_limit="<?php echo $tab_default_limit ?>"
                         data-tab_register="" data-tab_id="tab_<?php echo $i ?>" data-tab_order_id=""
                         data-tab_number="<?php echo $i ?>">
                        <p>
                            <span class="tab-timer"></span>
                        </p>
                        <p>
                            <span class="tab-key"><?php echo $i ?></span>
                        </p>
                        <p>
                            <span class="tab-title"></span>
                        </p>
                        <p>
                            <span class="status"><?php echo __('Available', 'wc_point_of_sale') ?></span>
                        </p>
                        <p>
                            <span class="opened-amount"></span>
                        </p>
                    </div>
                <?php } ?>
            <?php } ?>
        </div>
        <div class="right-col">
            <div class="content">
                <div class="select-tab"><p><?php _e('Please select a tab', 'wc_point_of_sale') ?></p></div>
                <div class="tab-form">
                    <p class="tab_open_header">
                        <?php _e('You are about to open', 'wc_point_of_sale') ?>
                    </p>
                    <p class="tab_open_number">
                        <?php _e('Tab ', 'wc_point_of_sale') ?><span class="tab-number"></span>
                    </p>
                    <p>
                        <label for="tab-title"><?php _e('Tab Name', 'wc_point_of_sale') ?></label>
                        <input type="text" id="tab-title">
                    </p>
                    <p>
                        <label for="tab-limit"><?php _e('Spending Limit', 'wc_point_of_sale') ?></label>
                        <input type="number" id="tab-limit" min="0">
                    </p>
                    <a href="#" class="button button-primary wp-button-large"
                       id="open_tab"><?php _e("Open Tab", 'wc_point_of_sale') ?></a>
                </div>
            </div>
        </div>
    </div>
</div>