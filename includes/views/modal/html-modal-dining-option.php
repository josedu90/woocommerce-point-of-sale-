<div class="md-modal md-dynamicmodal md-close-by-overlay md-register" id="modal-dining_option">
    <div class="md-content">
        <h1><?php _e( 'Dining Option', 'wc_point_of_sale' ); ?><span class="md-close"></span></h1>
        <?php
        if (isset($data['detail']['dining_option_default'])) {
            $dining = $data['detail']['dining_option_default'];
        } else {
            $dining = 'none';
        }
        ?>
        <div id="dining-option-selector-container">
                    <div data-option="None" class="dining-option-selector <?php echo ($dining == 'none' ? 'checked':''); ?>"><span><?php _e( 'None', 'wc_point_of_sale' ); ?></span></div>
                    <div data-option="Eat In" class="dining-option-selector <?php echo ($dining == 'eat_in' ? 'checked':''); ?>"><span><?php _e( 'Eat In', 'wc_point_of_sale' ); ?></span></div>
                    <div data-option="Take Away" class="dining-option-selector <?php echo ($dining == 'take_away' ? 'checked':''); ?>"><span><?php _e( 'Take Away', 'wc_point_of_sale' ); ?></span></div>
                    <div data-option="Delivery" class="dining-option-selector <?php echo ($dining == 'delivery' ? 'checked':''); ?>"><span><?php _e( 'Delivery', 'wc_point_of_sale' ); ?></span></div>
        </div>
        <div class="wrap-button">
            <button class="alignright"
                    id="save_dining_option"><?php _e( 'Update Dining Option', 'wc_point_of_sale' ); ?></button>
        </div>
    </div>
</div>