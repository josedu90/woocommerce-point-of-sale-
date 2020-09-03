<?php

if (!defined('ABSPATH')) {
    exit;
}

class WC_POS_Admin_Settings_Integrations extends WC_Settings_Page
{

    /**
     * WC_POS_Admin_Settings_Integrations constructor.
     */
    public function __construct()
    {
        $this->id = 'integrations_pos';
        $this->label = __('Integrations', 'wc_point_of_sale');

        add_filter('wc_pos_settings_tabs_array', array($this, 'add_settings_page'), 20);
        add_action('wc_pos_settings_' . $this->id, array($this, 'output'));
        add_action('wc_pos_settings_save_' . $this->id, array($this, 'save'));
        //add_action('wc_pos_sections_' . $this->id, array($this, 'output_sections'));
    }

    public function get_settings()
    {
        $integration_fields = array();

        if(class_exists('acf')){
            $integration_fields = array_merge($integration_fields, array(
                array(
                    'title' => __('Advanced Custom Fields', 'wc-point-of-sale'),
                    'type' => 'title',
                    'desc' => __('The following options are related to the Advanced Custom Fields integration with Point of Sale.', 'wc-point-of-sale'),
                    'id' => 'acf_options'
                ),
                array(
                    'name' => __('Order Fields', 'wc_point_of_sale'),
                    'id' => 'wc_pos_integrate_order_field',
                    'type' => 'checkbox',
                    'desc' => __('Enable order fields action', 'wc_point_of_sale'),
                    'desc_tip' => __('Displays an action button within POS register to allow users to add order meta details.', 'wc_point_of_sale'),
                    'default' => 'no',
                    'autoload' => true
                ),
                array(
                    'name' => __('Customer Field', 'wc_point_of_sale'),
                    'id' => 'wc_pos_integrate_customer_field',
                    'type' => 'checkbox',
                    'desc' => __('Enable customer fields', 'wc_point_of_sale'),
                    'desc_tip' => __('Displays an additional tab in the Customer modal to allow users to add customer meta details.', 'wc_point_of_sale'),
                    'default' => 'no',
                    'autoload' => true
                ),
                array('type' => 'sectionend', 'id' => 'acf_options'),
            ));
        }

        if(class_exists('WC_Product_Addons')){
            $integration_fields = array_merge($integration_fields, array(
                array(
                    'title' => __('Product Add Ons', 'wc-point-of-sale'),
                    'type' => 'title',
                    'desc' => __('The following options are related to the WooCommerce Product Add-Ons integration with Point of Sale.', 'wc-point-of-sale'),
                    'id' => 'product_addons_options'
                ),
                array(
                    'title' => __('Integration Status', 'wc-point-of-sale'),
                    'type' => 'text',
                    'desc' => '',
                    'id' => 'product_addons_status',
                    'class' => $this->is_product_addons_compatible() ? "status_field status_warning" : "status_field status_fail",
                    'default' => $this->is_product_addons_compatible() ? 'Updates Pending' : 'Updates Pending',
                    'custom_attributes' => array('disabled' => 'disabled')
                ),
                array(
                    'name' => __('Force Enable', 'wc_point_of_sale'),
                    'id' => 'wc_pos_force_enable_addons',
                    'type' => 'checkbox',
                    'desc' => __('Enable Product Add-Ons integration', 'wc_point_of_sale'),
                    'desc_tip' => __('Displays the additional product fields setup for the products within the the register.', 'wc_point_of_sale'),
                    'default' => 'no',
                    'autoload' => true
                ),
                array('type' => 'sectionend', 'id' => 'product_addons_options'),
            ));
        }

        return apply_filters('woocommerce_point_of_sale_integrations_settings_fields', $integration_fields);

    }

    public function output()
    {
        $settings = $this->get_settings();
        WC_Admin_Settings::output_fields($settings);
    }

    private function is_product_addons_compatible(){
        return defined('WC_PRODUCT_ADDONS_VERSION') && WC_PRODUCT_ADDONS_VERSION >= 3;
    }

}

return new WC_POS_Admin_Settings_Integrations();