<?php
/**
 * WooCommerce POS CSS Settings
 *
 * @author    Actuality Extensions
 * @package   WoocommercePointOfSale/Classes/settings
 * @category    Class
 * @since     0.1
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('WC_POS_Admin_Settings_Restaurant')) :

    /**
     * WC_POS_Admin_Settings_CSS
     */
    class WC_POS_Admin_Settings_Restaurant extends WC_Settings_Page
    {

        /**
         * Constructor.
         */
        public function __construct()
        {
            $this->id = 'pos_restaurant';
            $this->label = __('Restaurant', 'wc_point_of_sale');

            add_filter('wc_pos_settings_tabs_array', array($this, 'add_settings_page'), 20);
            add_action('woocommerce_sections_' . $this->id, array($this, 'output_sections'));
            add_action('wc_pos_settings_' . $this->id, array($this, 'output'));
            add_action('wc_pos_settings_save_' . $this->id, array($this, 'save'), 999);

        }

        /**
         * Output installed payment gateway settings.
         *
         * @access public
         * @return void
         */
        public function text_style_editor_setting()
        {
            $pos_nominal = get_option('wc_pos_restaurant');
            ?>
            <p><?php _e('Restaurant', 'wc_point_of_sale'); ?></p>
            <?php
        }

        /**
         * Get settings array
         *
         * @return array
         */
        public function get_settings()
        {
            global $woocommerce;
            $pos_nominal = get_option('wc_pos_restaurant');

            return apply_filters('woocommerce_point_of_sale_nominal_settings_fields', array(
                array('type' => 'sectionend', 'id' => 'wc_pos_settings_pos_restaurant'),

                array('title' => __('Restaurant', 'woocommerce'), 'desc' => __('The following options affect the hospitality features within the register.', 'woocommerce'), 'type' => 'title', 'id' => 'tabs_options'),
                /*array(
                    'title' => __('Enable', 'wc_point_of_sale'),
                    'desc' => __('Enable restaurant functionality', 'wc_point_of_sale'),
                    'id' => 'wc_pos_restaurant_enable',
                    'default' => 'no',
                    'type' => 'checkbox',
                    'checkboxgroup' => 'start',
                ),*/
                array(
                    'title' => __('Tabs', 'wc_point_of_sale'),
                    'desc' => __('Enable tab management', 'wc_point_of_sale'),
                    'desc_tip' => __('Allows you to open a tab of ordered items for customers.', 'wc_point_of_sale'),
                    'id' => 'wc_pos_tabs_management',
                    'default' => 'no',
                    'type' => 'checkbox',
                    'checkboxgroup' => 'start',
                ),

                array(
                    'title' => __('Spending Limit', 'wc_point_of_sale'),
                    'desc_tip' => __('Set the default spending limit of the amount that this tab can have.', 'wc_point_of_sale'),
                    'id' => 'wc_pos_tab_default_spend_limit',
                    'type' => 'number',
                    'css' => 'width: 100px; text-align: right;',
                    'custom_attributes' => array(
                        'min' => 0
                    )
                ),

                array(
                    'title' => __('Dining Option', 'wc_point_of_sale'),
                    'desc' => __('Enable dining options', 'wc_point_of_sale'),
                    'desc_tip' => __('Define whether the dining order is dine in, take out or delivery.', 'wc_point_of_sale'),
                    'id' => 'wc_pos_print_diner_option',
                    'default' => 'no',
                    'type' => 'checkbox',
                    'checkboxgroup' => 'start',
                ),

                array('type' => 'sectionend', 'id' => 'dinner_options'),

            ));
        }

        /**
         * Save settings
         */
        public function save()
        {
            $wc_pos_tabs_management = (isset($_POST['wc_pos_tabs_management'])) ? $_POST['wc_pos_tabs_management'] : 'no';
            $wc_pos_tab_default_spend_limit = (isset($_POST['wc_pos_tab_default_spend_limit'])) ? $_POST['wc_pos_tab_default_spend_limit'] : '';
            update_option('wc_pos_restaurant', $wc_pos_tabs_management);
            update_option('wc_pos_tab_default_spend_limit', $wc_pos_tab_default_spend_limit);

            $settings = $this->get_settings();

            WC_POS_Admin_Settings::save_fields($settings);
        }

    }

endif;

return new WC_POS_Admin_Settings_Restaurant();
