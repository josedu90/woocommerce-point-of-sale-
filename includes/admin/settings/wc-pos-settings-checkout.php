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


if (!class_exists('WC_POS_Admin_Settings_Checkout')) :

    /**
     * WC_POS_Admin_Settings_Layout
     */
    class WC_POS_Admin_Settings_Checkout extends WC_Settings_Page
    {

        /**
         * Constructor.
         */
        public function __construct()
        {
            $this->id = 'checkout_pos';
            $this->label = __('Checkout', 'woocommerce');

            add_filter('wc_pos_settings_tabs_array', array($this, 'add_settings_page'), 20);
            add_action('wc_pos_sections_' . $this->id, array($this, 'output_sections'));
            add_action('wc_pos_settings_' . $this->id, array($this, 'output'));
            add_action('wc_pos_settings_save_' . $this->id, array($this, 'save'));
            add_action('woocommerce_admin_field_installed_payment_gateways', array($this, 'installed_payment_gateways_setting'));

        }

        /**
         * Get sections.
         *
         * @return array
         */
        public function get_sections()
        {
            $sections = array(
                '' => __('Checkout Options', 'woocommerce'),
                'payment_methods' => __('Payment Methods', 'woocommerce'),
            );

            return apply_filters('woocommerce_sections_' . $this->id, $sections);
        }

        /**
         * Get settings array
         *
         * @return array
         */
        public function get_settings($current_section = '')
        {
            global $woocommerce;
            if ('payment_methods' === $current_section) {
                return apply_filters('woocommerce_point_of_sale_payment_methods_settings_fields', array(

                    array('title' => __('Payment Methods', 'woocommerce'), 'type' => 'title', 'id' => 'payment_gateways_options'),

                    array('type' => 'installed_payment_gateways'),

                    array('type' => 'sectionend', 'id' => 'payment_gateways_options'),

                ));
            } else {
                return apply_filters('woocommerce_point_of_sale_general_settings_fields', array(

                    array('title' => __('Checkout Options', 'woocommerce'), 'type' => 'title', 'desc' => '', 'id' => 'general_options'),
                    array(
                        'title' => __('Default Country', 'woocommerce'),
                        'desc_tip' => __('Sets the default country for shipping and customer accounts.', 'wc_point_of_sale'),
                        'id' => 'wc_pos_default_country',
                        'css' => 'min-width:350px;',
                        'default' => 'GB',
                        'type' => 'single_select_country',
                    ),
                    array(
                        'title' => __('SSL Options', 'wc_point_of_sale'),
                        'desc' => __('Force secure checkout', 'wc_point_of_sale'),
                        'id' => 'woocommerce_pos_force_ssl_checkout',
                        'default' => 'no',
                        'type' => 'checkbox',
                        'checkboxgroup' => 'start',
                        'desc_tip' => __('Force SSL (HTTPS) on the POS page (an SSL Certificate is required).', 'wc_point_of_sale'),
                    ),

                    array('type' => 'sectionend', 'id' => 'checkout_pos_options'),

                    array('title' => __('Account Options', 'wc_point_of_sale'), 'desc' => __('The following options affect the account creation process when creating customers.', 'wc_point_of_sale'), 'type' => 'title', 'id' => 'checkout_page_options'),


                    array(
                        'name' => __('Username', 'wc_point_of_sale'),
                        'desc_tip' => __('Choose what the username should be when customer is created.', 'wc_point_of_sale'),
                        'id' => 'woocommerce_pos_end_of_sale_username_add_customer',
                        'type' => 'select',
                        'class' => 'wc-enhanced-select',
                        'options' => array(
                            1 => __('First & Last Name e.g. johnsmith', 'wc_point_of_sale'),
                            2 => __('First & Last Name With Hyphen e.g. john-smith', 'wc_point_of_sale'),
                            3 => __('Email address', 'wc_point_of_sale')
                        ),
                        'autoload' => true
                    ),

                    array(
                        'name' => __('Customer Details', 'wc_point_of_sale'),
                        'id' => 'wc_pos_load_customer_after_selecting',
                        'type' => 'checkbox',
                        'desc' => __('Load customer details after customer selection', 'wc_point_of_sale'),
                        'desc_tip' => __('Automatically displays the customer details screen when searching and selecting a customer.', 'wc_point_of_sale'),
                        'default' => 'no',
                        'autoload' => true
                    ),
                    array(
                        'title' => __('Customer Cards', 'wc_point_of_sale'),
                        'desc' => __('Enable customer cards', 'wc_point_of_sale'),
                        'desc_tip' => __('Allow the ability to scan customers cards to load their account instantly.', 'wc_point_of_sale'),
                        'id' => 'wc_pos_enable_user_card',
                        'default' => 'no',
                        'type' => 'checkbox',
                        'checkboxgroup' => 'start',
                    ),
                    array(
                        'title' => __('Customer Saving', 'wc_point_of_sale'),
                        'desc' => __('Enable customer saving', 'wc_point_of_sale'),
                        'desc_tip' => __('Check this box to default the customer saving toggle when adding new customers.', 'wc_point_of_sale'),
                        'id' => 'wc_pos_customer_saving',
                        'default' => 'no',
                        'type' => 'checkbox',
                        'checkboxgroup' => 'start',
                    ),
                    array(
                        'name' => __('Required Fields', 'wc_point_of_sale'),
                        'id' => 'wc_pos_customer_create_required_fields',
                        'type' => 'multiselect',
                        'class' => 'wc-enhanced-select-required-fields',
                        'desc_tip' => __('Select the fields that are required when creating a customer through the register.', 'wc_point_of_sale'),
                        'options' => array(
                            'billing_first_name' => __('Billing First Name', 'wc_point_of_sale'),
                            'billing_last_name' => __('Billing Last Name', 'wc_point_of_sale'),
                            'billing_email' => __('Billing Email', 'wc_point_of_sale'),
                            'billing_company' => __('Billing Company', 'wc_point_of_sale'),
                            'billing_address_1' => __('Billing Address 1', 'wc_point_of_sale'),
                            'billing_address_2' => __('Billing Address 2', 'wc_point_of_sale'),
                            'billing_city' => __('Billing City', 'wc_point_of_sale'),
                            'billing_state' => __('Billing State', 'wc_point_of_sale'),
                            'billing_postcode' => __('Billing Postcode', 'wc_point_of_sale'),
                            'billing_country' => __('Billing Country', 'wc_point_of_sale'),
                            'billing_phone' => __('Billing Phone', 'wc_point_of_sale'),
                            'shipping_first_name' => __('Shipping First Name', 'wc_point_of_sale'),
                            'shipping_last_name' => __('Shipping Last Name', 'wc_point_of_sale'),
                            'shipping_company' => __('Shipping Company', 'wc_point_of_sale'),
                            'shipping_address_1' => __('Shipping Address 1', 'wc_point_of_sale'),
                            'shipping_address_2' => __('Shipping Address 2', 'wc_point_of_sale'),
                            'shipping_city' => __('Shipping City', 'wc_point_of_sale'),
                            'shipping_state' => __('Shipping State', 'wc_point_of_sale'),
                            'shipping_postcode' => __('Shipping Postcode', 'wc_point_of_sale'),
                            'shipping_country' => __('Shipping Country', 'wc_point_of_sale'),
                        ),
                        'default' => array(
                            'billing_first_name',
                            'billing_last_name',
                            'billing_email',
                            'billing_address_1',
                            'billing_city',
                            'billing_postcode',
                            'billing_country',
                            'billing_phone',
                        ),
                    ),

                    array(
                        'name' => __('Optional Fields', 'wc_point_of_sale'),
                        'id' => 'wc_pos_hide_not_required_fields',
                        'type' => 'checkbox',
                        'desc' => __('Hide optional fields when adding customer', 'wc_point_of_sale'),
                        'desc_tip' => __('Optional fields will not be shown to make capturing of customer data easier for the cashier.', 'wc_point_of_sale'),
                        'default' => 'no',
                        'autoload' => true
                    ),

                    array('type' => 'sectionend', 'id' => 'checkout_page_options'),

                    array('title' => __('Email Options', 'wc_point_of_sale'), 'desc' => __('The following options affect the email notifications when orders are placed and accounts are created.', 'wc_point_of_sale'), 'type' => 'title', 'id' => 'email_options'),

                    array(
                        'name' => __('New Order', 'wc_point_of_sale'),
                        'id' => 'wc_pos_email_notifications',
                        'type' => 'checkbox',
                        'desc' => __('Enable new order notification', 'wc_point_of_sale'),
                        'desc_tip' => sprintf(__('New order emails are sent to the recipient list when an order is received as shown %shere%s.', 'wc_point_of_sale'),
                            '<a href="' . admin_url('admin.php?page=wc-settings&tab=email&section=wc_email_new_order') . '">', '</a>'),
                        'default' => 'no',
                        'autoload' => true
                    ),

                    array(
                        'name' => __('Account Creation', 'wc_point_of_sale'),
                        'id' => 'wc_pos_automatic_emails',
                        'type' => 'checkbox',
                        'desc' => __('Enable account creation notification', 'wc_point_of_sale'),
                        'desc_tip' => sprintf(__('Customer emails are sent to the customer when a customer signs up via checkout or account pages as shown %shere%s.', 'wc_point_of_sale'),
                            '<a href="' . admin_url('admin.php?page=wc-settings&tab=email&section=wc_email_customer_new_account') . '">', '</a>'),
                        'default' => 'yes',
                        'autoload' => true
                    ),

                    array(
                        'name' => __('Guest Checkout', 'wc_point_of_sale'),
                        'id' => 'wc_pos_guest_checkout',
                        'type' => 'checkbox',
                        'desc' => __('Enable guest checkout', 'wc_point_of_sale'),
                        'desc_tip' => __('Allows register cashiers to process and fulfil an order without choosing a customer.', 'wc_point_of_sale'),
                        'default' => 'yes',
                        'autoload' => true
                    ),

                    array('type' => 'sectionend', 'id' => 'email_options'),


                )); // End general settings
            }
        }

        /**
         * Output the settings.
         */
        public function output()
        {
            global $current_section;
            $settings = $this->get_settings($current_section);

            WC_POS_Admin_Settings::output_fields($settings);
        }

        /**
         * Save settings
         */
        public function save()
        {
            global $current_section;
            $settings = $this->get_settings();
            if ($current_section == 'payment_methods') {
                $pos_enabled_gateways = (isset($_POST['pos_enabled_gateways'])) ? $_POST['pos_enabled_gateways'] : array();
                update_option('pos_enabled_gateways', $pos_enabled_gateways);
                $pos_exist_gateways = (isset($_POST['pos_exist_gateways'])) ? $_POST['pos_exist_gateways'] : array();
                update_option('pos_exist_gateways', $pos_exist_gateways);
            } else {
                WC_POS_Admin_Settings::save_fields($settings);
            }
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

        public function installed_payment_gateways_setting()
        {
            ?>
            <tr valign="top">
                <td class="forminp" colspan="2">
                    <style>
                        .wc_gateways th {
                            width: auto;
                        }
                    </style>
                    <table id="pos_wc_gateways" class="wc_gateways widefat" cellspacing="0">
                        <thead>
                        <tr>
                            <?php
                            $columns = array(
                                'sort' => '',
                                'name' => __('Method', 'wc_point_of_sale'),
                                'enabled' => __('Enabled', 'wc_point_of_sale'),
                                'actions' => ''
                            );

                            foreach ($columns as $key => $column) {
                                echo '<th class="' . esc_attr($key) . '">' . esc_html($column) . '</th>';
                            }
                            ?>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $enabled_gateways = get_option('pos_enabled_gateways', array());
                        $payment_gateways = array();
                        $load_gateways = array();

                        foreach (WC()->payment_gateways->payment_gateways() as $gateway) {
                            $load_gateways[esc_attr($gateway->id)] = (object)array('id' => esc_attr($gateway->id), 'title' => $gateway->get_title());
                        }
                        $load_gateways['pos_chip_pin'] = (object)array('id' => 'pos_chip_pin', 'title' => (get_option('pos_chip_pin_name') ? get_option('pos_chip_pin_name'): __('Chip & PIN', 'wc_point_of_sale')));
                        $load_gateways['pos_chip_pin2'] = (object)array('id' => 'pos_chip_pin2', 'title' => (get_option('pos_chip_pin2_name') ? get_option('pos_chip_pin2_name'): __('Chip & PIN 2', 'wc_point_of_sale')));
                        $load_gateways['pos_chip_pin3'] = (object)array('id' => 'pos_chip_pin3', 'title' => (get_option('pos_chip_pin3_name') ? get_option('pos_chip_pin3_name'): __('Chip & PIN 3', 'wc_point_of_sale')));

                        // Get sort order option
                        $ordering = (array)get_option('pos_exist_gateways');

                        $order_end = 999;

                        // Load gateways in order
                        foreach ($load_gateways as $id => $load_gateway) {

                            if (in_array($id, $ordering)) {
                                $key = array_search($id, $ordering);
                                $payment_gateways[$key] = $load_gateway;
                            } else {
                                // Add to end of the array
                                $payment_gateways[$order_end] = $load_gateway;
                                $order_end++;
                            }
                        }

                        ksort($payment_gateways);

                        foreach ($payment_gateways as $gateway) {
                            echo '<tr>';

                            foreach ($columns as $key => $column) {
                                $checked = in_array($gateway->id, $enabled_gateways);
                                switch ($key) {
                                    case 'sort' :
                                        echo '<td width="2%" class="sort"><div class="wc-item-reorder-nav">
                                            <button type="button" class="wc-move-up">Move up</button>
                                            <button type="button" class="wc-move-down">Move down</button>
                                        </div></td>';
                                        break;
                                    case 'enabled' :
                                        echo '<td class="enabled">
					        				<input type="checkbox" id="cb_'.$gateway->id.'" name="pos_enabled_gateways[]" value="' . $gateway->id . '" ' . checked($checked, true, false) . ' />
					        				<label for="cb_'.$gateway->id.'">Toggle</label>
					        				<input type="hidden" name="pos_exist_gateways[]" value="' . $gateway->id . '" />
					        			</td>';
                                        break;
                                    case 'name' :
                                        if ( ($gateway->id == 'pos_chip_pin') ||  ($gateway->id == 'pos_chip_pin2') ||  ($gateway->id == 'pos_chip_pin3')) {
                                            echo '<td class="name">
					        				<a id="'.$gateway->id.'" data-pk="1" data-type="text" class="editable-field editable editable-click">' . $gateway->title . '</a>
					        			    </td>';
                                        } else {
                                            echo '<td class="name">
					        				' . $gateway->title . '
					        			    </td>';
                                        }
                                        break;
                                    case 'actions':
                                        echo '<td>';
                                        if(strpos($gateway->id, 'pos_chip_pin') === false){
                                            $btn_text = $checked ? esc_html__( 'Manage', 'woocommerce' ) : esc_html__( 'Set up', 'woocommerce' );
                                            printf('<a href="%1$s" class="button alignright">%2$s</a>', admin_url('admin.php?page=wc-settings&tab=checkout&section=') . strtolower($gateway->id), $btn_text);
                                        }
                                        echo '</td>';
                                        break;
                                    default :
                                        do_action('woocommerce_payment_gateways_setting_column_' . $key, $gateway->id);
                                        break;
                                }
                            }
                            echo '</tr>';
                        }
                        ?>
                        </tbody>
                    </table>
                    <p><?php _e('To configure each payment gateway, please go to the Checkout tab under WooCommerce > Settings or click <a target="_blank" href="admin.php?page=wc-settings&tab=checkout">here</a>', 'wc_point_of_sale'); ?></p>
                </td>
            </tr>
            <?php
        }

    }

endif;

return new WC_POS_Admin_Settings_Checkout();