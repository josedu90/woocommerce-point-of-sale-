<?php
/**
 * WooCommerce POS License Settings
 *
 * @author      Actuality Extensions
 * @package     WoocommercePointOfSale/Classes/settings
 * @category    Class
 * @since       4.38.
 */

class WC_POS_Admin_Settings_License extends WC_Settings_Page
{

    /**
     * WC_POS_Admin_Settings_License constructor.
     */
    public function __construct()
    {
        $this->id = 'pos_license';
        $this->label = __('License', 'wc_point_of_sale');

        add_filter('wc_pos_settings_tabs_array', array($this, 'add_settings_page'), 20);
        add_action('woocommerce_sections_' . $this->id, array($this, 'output_sections'));
        add_action('wc_pos_settings_' . $this->id, array($this, 'output'));
        add_action('wc_pos_settings_save_' . $this->id, array($this, 'save'));
    }

    public function output()
    {
        $GLOBALS['hide_save_button'] = true;

        $purchase_codes = get_option(AEBaseApi::PURCHASE_CODES_OPTION_KEY, array());
        if(empty($purchase_codes["woocommerce-point-of-sale"])){
            WC_Admin_Settings::add_error("You have not entered a purchase code therefore you are not receiving the latest updates. Please enter the purchase code below.");
        }else {
            $validate = ae_updater_validate_code('woocommerce-point-of-sale', $purchase_codes["woocommerce-point-of-sale"]);
            if(isset($validate->error) || $validate == false){
                WC_Admin_Settings::add_error(__("Could not find a sale with this purchase code. Please double check.", "wc_point_of_sale"));
            }else{
                WC_Admin_Settings::add_message(__("Your purchase code is valid. Thank you! Enjoy our plugin and automatic updates.", "wc_point_of_sale"));
            }
        }
        WC_Admin_Settings::show_messages();

        include_once(WC_POS()->dir . '/updater/pages/index.php');
    }

    public function save()
    {
        $rm = strtoupper($_SERVER['REQUEST_METHOD']);
        if('POST' == $rm)
        {
            if(isset($_POST['envato-update-plugins_purchase_code']) ){
                $purchase_codes = array_map('trim', $_POST['envato-update-plugins_purchase_code']);
                update_option(AEBaseApi::PURCHASE_CODES_OPTION_KEY, $purchase_codes);
            }
        }
    }
}

return new WC_POS_Admin_Settings_License();