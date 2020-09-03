<?php
/**
 * WooCommerce POS General Settings
 *
 * @author    Actuality Extensions
 * @package   WoocommercePointOfSale/Classes/settings
 * @category    Class
 * @since     0.1
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('WC_POS_Admin_Settings_Register')) :

    /**
     * WC_POS_Admin_Settings_Layout
     */
    class WC_POS_Admin_Settings_Register extends WC_Settings_Page
    {

        /**
         * Constructor.
         */
        public function __construct()
        {
            $this->id = 'register_pos';
            $this->label = __('Register', 'wc_point_of_sale');

            add_filter('wc_pos_settings_tabs_array', array($this, 'add_settings_page'), 20);
            add_action('wc_pos_settings_' . $this->id, array($this, 'output'));
            add_action('wc_pos_settings_save_' . $this->id, array($this, 'save'));
            add_action('wc_pos_sections_' . $this->id, array($this, 'output_sections'));

        }

        /**
         * Get sections.
         *
         * @return array
         */
        public function get_sections()
        {
            $sections = array(
                '' => __('Register', ''),
                'scanning' => __('Scanning', 'wc_point_of_sale'),
                'denomination' => __('Denominations', 'wc_point_of_sale'),
            );

            return apply_filters('woocommerce_sections_' . $this->id, $sections);
        }

        /**
         * Output sections.
         */
        public function output_sections()
        {
            global $current_section;

            $sections = $this->get_sections();

            if (empty($sections) || 1 === sizeof($sections)) {
                return;
            }

            echo '<ul class="subsubsub">';

            $array_keys = array_keys($sections);

            foreach ($sections as $id => $label) {
                echo '<li><a href="' . admin_url('admin.php?page=wc_pos_settings&tab=' . $this->id . '&section=' . sanitize_title($id)) . '" class="' . ($current_section == $id ? 'current' : '') . '">' . $label . '</a> ' . (end($array_keys) == $id ? '' : '|') . ' </li>';
            }

            echo '</ul><br class="clear" />';
        }

        /**
         * Get settings array
         *
         * @return array
         */
        public function get_settings()
        {
            global $woocommerce, $current_section;

            if ($current_section == 'scanning') {
                global $woocommerce, $wpdb;

                $barcode_fields = array(
                    '' => __('WooCommerce SKU', 'wc_point_of_sale'),
                );

                $pr_id = $wpdb->get_var("SELECT ID FROM {$wpdb->posts} WHERE post_type = 'product' ORDER BY post_modified DESC LIMIT 1");
                if ($pr_id) {
                    $post_meta = get_post_meta($pr_id);
                    if ($post_meta) {
                        foreach ($post_meta as $key => $value) {
                            if(in_array($key, array('_edit_last', '_edit_lock'))){
                                continue;
                            }
                            $barcode_fields[$key] = $key;
                        }
                    }
                }

                if ( is_plugin_active('woo-add-gtin/woocommerce-gtin.php') ) {
                    unset($barcode_fields['hwp_product_gtin']);
                    $barcode_fields['hwp_product_gtin'] = __('GTIN', 'wc_point_of_sale');
                }

                return apply_filters('woocommerce_point_of_sale_general_settings_fields', array(

                    array('title' => __('Scanning Options', 'wc_point_of_sale'), 'desc' => __('The following options affect the use of scanning hardware such as barcode scanners and magnetic card readers.', 'wc_point_of_sale'), 'type' => 'title', 'id' => 'scanning_options'),

                    array(
                        'title' => __('Barcode Scanning', 'wc_point_of_sale'),
                        'id' => 'woocommerce_pos_register_ready_to_scan',
                        'std' => '',
                        'type' => 'checkbox',
                        'desc' => __('Enable barcode scanning', 'wc_point_of_sale'),
                        'desc_tip' => __('Listens to barcode scanners and adds item to basket. Carriage return in scanner recommended.', 'wc_point_of_sale'),
                        'default' => 'no',
                        'autoload' => false
                    ),

                    array(
                        'title' => __('Scanning Field', 'wc_point_of_sale'),
                        'desc_tip' => __('Control what field is used when using the scanner on the register. Default is SKU.', 'wc_point_of_sale'),
                        'id' => 'woocommerce_pos_register_scan_field',
                        'std' => '',
                        'class' => 'wc-enhanced-select',
                        'css' => 'min-width:300px;',
                        'type' => 'select',
                        'desc' => '',
                        'default' => '',
                        'autoload' => false,
                        'options' => $barcode_fields,
                    ),

                    array(
                        'name' => __('Credit/Debit Card Scanning', 'wc_point_of_sale'),
                        'id' => 'woocommerce_pos_register_cc_scanning',
                        'std' => '',
                        'type' => 'checkbox',
                        'desc' => __('Enable credit/debit card scanning', 'wc_point_of_sale'),
                        'desc_tip' => sprintf(__('Allows magnetic card readers to parse scanned output into checkout fields. Supported payment gateways can be found %shere%s.', 'wc_point_of_sale'),
                            '<a href="http://actualityextensions.com/supported-payment-gateways/" target="_blank">', '</a>'),
                        'default' => 'no',
                        'autoload' => false
                    ),
                    array('type' => 'sectionend', 'id' => 'scanning_options'),

                )); // End general settings
            } elseif ( $current_section == 'denomination' ){
                $pos_nominal = get_option('wc_pos_cash_nominal');
                echo '<h3>' . __('Denomination Options', 'wc_point_of_sale') . '</h3>';
                echo '<div class="cash-nominal-content-main">';
                echo '<div class="cash-nominal-content">';
                if ($pos_nominal) {
                    foreach ($pos_nominal as $nominal) {
                        echo '<div class="nominal-row"><input type="number" name="wc_pos_cash_nominal[]" value="' . $nominal . '" step="0.01"><span class="remove"></span></div>';
                    }
                }
                echo '</div>';
                echo '<a href="#" class="button add-nominal">' . __('Add Denomination', 'wc_point_of_sale') .  '</a>';
                echo '</div>';
                return apply_filters('woocommerce_point_of_sale_nominal_settings_fields', array(
                    array('type' => 'sectionend', 'id' => 'pos_nominal'),
                ));
            }else {
                return apply_filters('woocommerce_point_of_sale_general_settings_fields', array(

                    array('title' => __('Register Options', 'woocommerce'), 'type' => 'title', 'desc' => __('The following options affect the settings that are applied when loading all registers.', 'woocommerce'), 'id' => 'general_options'),

                    array(
                        'name' => __('Auto Update Stock', 'wc_point_of_sale'),
                        'id' => 'wc_pos_autoupdate_stock',
                        'type' => 'checkbox',
                        'desc' => __('Enable update stock automatically ', 'wc_point_of_sale'),
                        'desc_tip' => __('Updates the stock inventories for products automatically whilst running the register. Enabling this may hinder server performance. ', 'wc_point_of_sale'),
                        'default' => 'no',
                        'autoload' => true
                    ),
                    array(
                        'name' => __('Update Interval', 'wc_point_of_sale'),
                        'id' => 'wc_pos_autoupdate_interval',
                        'type' => 'number',
                        'desc_tip' => __('Enter the interval for auto-update in seconds.', 'wc_point_of_sale'),
                        'desc' => __('seconds', 'wc_point_of_sale'),
                        'default' => 240,
                        'autoload' => true,
                        'css' => 'width: 100px;'
                    ),
                    array(
                        'name' => __('Stock Quantity', 'wc_point_of_sale'),
                        'id' => 'wc_pos_show_stock',
                        'type' => 'checkbox',
                        'desc' => __('Enable stock quantity identifier', 'wc_point_of_sale'),
                        'desc_tip' => __('Shows the remaining stock when adding products to the basket.', 'wc_point_of_sale'),
                        'default' => 'yes',
                        'autoload' => true
                    ),
                    array(
                        'name' => __('Out of Stock', 'wc_point_of_sale'),
                        'id' => 'wc_pos_show_out_of_stock_products',
                        'type' => 'checkbox',
                        'desc' => __('Enable out of stock products', 'wc_point_of_sale'),
                        'desc_tip' => __('Shows out of stock products in the product grid.', 'wc_point_of_sale'),
                        'default' => 'no',
                        'autoload' => true
                    ),
                    array(
                        'title' => __('Bill Screen', 'wc_point_of_sale'),
                        'desc' => __('Display bill screen', 'wc_point_of_sale'),
                        'desc_tip' => __('Allows you to display the order on a separate display i.e. pole display.', 'wc_point_of_sale'),
                        'id' => 'wc_pos_bill_screen',
                        'default' => 'no',
                        'type' => 'checkbox',
                        'checkboxgroup' => 'start',
                    ),
                    array(
                        'title' => __('Product Visiblity', 'wc_point_of_sale'),
                        'desc' => __('Enable product visibility control', 'wc_point_of_sale'),
                        'desc_tip' => __('Allows you to show and hide products from either the POS, web or both shops.', 'wc_point_of_sale'),
                        'id' => 'wc_pos_visibility',
                        'default' => 'no',
                        'type' => 'checkbox',
                        'checkboxgroup' => 'start',
                    ),
                    array(
                        'title' => __('Custom Fee', 'wc_point_of_sale'),
                        'desc' => __('Enable custom fee ', 'wc_point_of_sale'),
                        'desc_tip' => __('Allows you to add a fixed or percentage based value to the order.', 'wc_point_of_sale'),
                        'id' => 'wc_pos_custom_fee',
                        'default' => 'no',
                        'type' => 'checkbox',
                        'checkboxgroup' => 'start',
                    ),
                    array(
                        'title' => __('Signature Capture', 'wc_point_of_sale'),
                        'desc' => __('Enable signature capture', 'wc_point_of_sale'),
                        'desc_tip' => __('Presents a modal window to capture the signature of user or customer.', 'wc_point_of_sale'),
                        'id' => 'wc_pos_signature',
                        'default' => 'no',
                        'type' => 'checkbox',
                        'checkboxgroup' => 'start',
                    ),
                    array(
                        'title' => __('Signature Required', 'wc_point_of_sale'),
                        'desc' => __('Enforce capturing of signature', 'wc_point_of_sale'),
                        'desc_tip' => __('Allows you to force user to enter signature before proceeding with register commands.', 'wc_point_of_sale'),
                        'id' => 'wc_pos_signature_required',
                        'class' => 'pos_signature',
                        'default' => 'no',
                        'type' => 'checkbox',
                        'checkboxgroup' => 'start',
                    ),
                    array(
                        'title' => __('Signature Commands', 'wc_point_of_sale'),
                        'desc' => __('', 'wc_point_of_sale'),
                        'desc_tip' => __('Choose which commands would you like the signature panel to be shown for.', 'wc_point_of_sale'),
                        'id' => 'wc_pos_signature_required_on',
                        'class' => 'wc-enhanced-select pos_signature',
                        'default' => 'pay',
                        'type' => 'multiselect',
                        'options' => array(
                            'pay' => __('Pay', 'wc_point_of_sale'),
                            'save' => __('Save', 'wc_point_of_sale'),
                            'void' => __('Void', 'wc_point_of_sale')
                        ),
                    ),
                    array(
                        'title' => __('Product Refunds', 'wc_point_of_sale'),
                        'desc' => __('Enable returns and refunds', 'wc_point_of_sale'),
                        'desc_tip' => __('This gives you the ability to scan order and choose products to return and refund.', 'wc_point_of_sale'),
                        'id' => 'wc_pos_refund',
                        'default' => 'no',
                        'type' => 'checkbox',
                        'checkboxgroup' => 'start',
                    ),
                    array(
                        'title' => __('Refund Permissions', 'wc_point_of_sale'),
                        'desc' => __('Enable refund permission control', 'wc_point_of_sale'),
                        'desc_tip' => __('Allows shop managers to control refund abilities from the user profile page.', 'wc_point_of_sale'),
                        'id' => 'wc_pos_refund_approval',
                        'default' => 'no',
                        'type' => 'checkbox',
                        'checkboxgroup' => 'start',
                    ),
                    array(
                        'title' => __('Force Logout', 'wc_point_of_sale'),
                        'desc' => __('Enable taking over of registers.', 'wc_point_of_sale'),
                        'desc_tip' => __('Allows shop managers to take over an already opened register.', 'wc_point_of_sale'),
                        'id' => 'wc_pos_force_logout',
                        'default' => 'no',
                        'type' => 'checkbox',
                        'checkboxgroup' => 'start',
                    ),
                    array(
                        'title' => __('Auto Logout', 'wc_point_of_sale'),
                        'desc_tip' => __('Choose whether to automatically exit the register screen after inactive time. This will not close the register.', 'wc_point_of_sale'),
                        'id' => 'wc_pos_auto_logout',
                        'default' => '0',
                        'class' => 'wc-enhanced-select',
                        'type' => 'select',
                        'options' => array(
                            0 => __("Disable", "wc_point_of_sale"),
                            30 => __("30 Minutes", "wc_point_of_sale"),
                            60 => __("1 Hour", "wc_point_of_sale"),
                            120 => __("2 Hours", "wc_point_of_sale"),
                        ),
                    ),
                    array(
                        'id' => 'wc_pos_custom_fees',
                        'type' => 'custom_array',
                    ),
                    array('type' => 'sectionend', 'id' => 'checkout_pos_options'),
                )); // End general settings
            }
        }

        /**
         * Save settings
         */
        public function save()
        {
            global $current_section;
            if($current_section == 'denomination'){
                $pos_nominal = (isset($_POST['wc_pos_cash_nominal'])) ? $_POST['wc_pos_cash_nominal'] : '';
                update_option('wc_pos_cash_nominal', $pos_nominal);
            }else{
                $settings = $this->get_settings();
                WC_POS_Admin_Settings::save_fields($settings);
            }
        }

        /**
         * Output the settings.
         */
        public function output()
        {
            $settings = $this->get_settings();
            WC_Admin_Settings::output_fields($settings);
            $custom_fees = unserialize(get_option('wc_pos_custom_fees'));
            if ($custom_fees) {
                $custom_fees = array_values($custom_fees);
            }
            include_once 'wc-pos-setting-register-fee-table.php';
        }
    }
endif;

return new WC_POS_Admin_Settings_Register();