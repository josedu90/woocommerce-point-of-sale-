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

if (!class_exists('WC_POS_Admin_System_Status')) :

    /**
     * WC_POS_Admin_Settings_CSS
     */
    class WC_POS_Admin_System_Status extends WC_Settings_Page
    {
        private $last_update = array(
            'date' => '',
            'log' => array()
        );
        private $force_updates = array(
            '3.2.1' => 'wp-content/plugins/woocommerce-point-of-sale/includes/updates/wc_pos-update-3.2.1.php',
            '3.2.2.0' => 'wp-content/plugins/woocommerce-point-of-sale/includes/updates/wc_pos-update-3.2.2.0.php',
            '4.0.0' => 'wp-content/plugins/woocommerce-point-of-sale/includes/updates/wc_pos-update-4.0.0.php',
            '4.1.9' => 'wp-content/plugins/woocommerce-point-of-sale/includes/updates/wc_pos-update-4.1.9.php',
            '4.1.9.10' => 'wp-content/plugins/woocommerce-point-of-sale/includes/updates/wc_pos-update-4.1.9.10.php',
            '4.3.6' => 'wp-content/plugins/woocommerce-point-of-sale/includes/updates/wc_pos-update-4.3.6.php',
            '4.5.31' => 'wp-content/plugins/woocommerce-point-of-sale/includes/updates/wc_pos-update-4.5.31.php',
        );

        /**
         * Constructor.
         */
        public function __construct()
        {
            $this->id = 'system_status';
            $this->label = __('Advanced', 'wc_point_of_sale');

            add_filter('wc_pos_settings_tabs_array', array($this, 'add_settings_page'), 20);
            add_action('woocommerce_sections_' . $this->id, array($this, 'output_sections'));
            add_action('wc_pos_settings_' . $this->id, array($this, 'output'));
            add_action('wc_pos_settings_save_' . $this->id, array($this, 'save'));

        }

        /**
         * Get settings array
         *
         * @return array
         */
        public function get_settings()
        {
//            $GLOBALS['hide_save_button'] = true;
            global $wpdb;
            $update_status = __('OK', 'wc_point_of_sale');
            $last_update = get_option('wc_pos_last_force_db_update');
            $this->last_update = ($last_update) ? $last_update : $this->last_update;
            ?>
            <table class="widefat striped" style="margin-bottom: 1em;">
                <thead>
                <tr>
                    <th colspan="2">
                        <b><?php _e('WordPress Environment', 'wc_point_of_sale') ?></b>
                    </th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td style="width: 30%;">
                        <?php _e('Site URL:', 'wc_point_of_sale') ?>
                    </td>
                    <td>
                        <?php echo get_site_url(); ?>
                    </td>
                </tr>
                <tr>
                    <td style="width: 30%;">
                        <?php _e('WooCommerce Version:', 'wc_point_of_sale') ?>
                    </td>
                    <td>
                        <?php echo WC()->version; ?> <span class="dashicons <?php echo WC()->version >= 3.5 ? 'dashicons-yes' : 'dashicons-warning'; ?>"></span>
                    </td>
                </tr>
                <tr>
                    <td>
                        <?php _e('WordPress Version:', 'wc_point_of_sale') ?>
                    </td>
                    <td>
                        <?php echo get_bloginfo('version'); ?> <span class="dashicons <?php echo get_bloginfo('version') >= 5.1 ? 'dashicons-yes' : 'dashicons-warning'; ?>"></span>
                    </td>
                </tr>
                <tr>
                    <td>
                        <?php _e('Language:', 'wc_point_of_sale') ?>
                    </td>
                    <td>
                        <?php echo get_locale(); ?>
                    </td>
                </tr>
            </table>
            <table class="widefat striped" style="margin-bottom: 1em;">
                <thead>
                <tr>
                    <th colspan="2">
                        <b><?php _e('Server Environment', 'wc_point_of_sale') ?></b>
                    </th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td style="width: 30%;">
                        <?php _e('Server Info:', 'wc_point_of_sale') ?>
                    </td>
                    <td>
                        <?php echo esc_html($_SERVER['SERVER_SOFTWARE']); ?>
                    </td>
                </tr>
                <tr>
                    <td style="width: 30%;">
                        <?php _e('PHP Version:', 'wc_point_of_sale') ?>
                    </td>
                    <td>
                        <?php echo $php_version = phpversion(); ?> <span class="dashicons <?php echo phpversion() >= 7 ? 'dashicons-yes' : 'dashicons-warning'; ?>"></span>
                    </td>
                </tr>
                <tr>
                    <td style="width: 30%;">
                        <?php _e('PHP Post Max Size:', 'wc_point_of_sale') ?>
                    </td>
                    <td>
                        <?php echo size_format(wc_let_to_num(ini_get('post_max_size'))); ?>
                    </td>
                </tr>
                <tr>
                    <td style="width: 30%;">
                        <?php _e('PHP Time Limit:', 'wc_point_of_sale') ?>
                    </td>
                    <td>
                        <?php echo ini_get('max_execution_time'); ?> <span class="dashicons <?php echo ini_get('max_execution_time') >= 90 ? 'dashicons-yes' : 'dashicons-warning'; ?>"></span>
                    </td>
                </tr>
                <tr>
                    <td style="width: 30%;">
                        <?php _e('PHP Max Input Vars:', 'wc_point_of_sale') ?>
                    </td>
                    <td>
                        <?php echo ini_get('max_input_vars'); ?>
                    </td>
                </tr>
                <tr>
                    <td style="width: 30%;">
                        <?php _e('Max Upload Size:', 'wc_point_of_sale') ?>
                    </td>
                    <td>
                        <?php echo size_format(wp_max_upload_size()); ?>
                    </td>
                </tr>
                <tr>
	                <td style="width: 30%;">
		                <?php _e('WordPress Memory:', 'wc_point_of_sale') ?>
	                </td>
	                <td>
                        <?php
                        $wp_memory_limit = wc_let_to_num( WP_MEMORY_LIMIT );
                        if ( function_exists( 'memory_get_usage' ) ) {
                            $wp_memory_limit = max( $wp_memory_limit, wc_let_to_num( @ini_get( 'memory_limit' ) ) );
                        }
                        ?>
                        <span><?php echo size_format($wp_memory_limit)
                        ?></span>
                        
                        <span class="dashicons <?php echo $wp_memory_limit >= 128 ? 'dashicons-yes' : 'dashicons-warning'; ?>"></span>
	                </td>
                </tr>
                <tr>
	                <td style="width: 30%;">
		                <?php _e('MySQL Version:', 'wc_point_of_sale') ?>
	                </td>
	                <td>
		                <?php
                        $mysql_data = wc_get_server_database_version();
                        echo isset($mysql_data["string"]) ? $mysql_data["string"] : "";
                        ?>
                            <span class="dashicons <?php echo $mysql_data["number"] >= 5.6 ? 'dashicons-yes' : 'dashicons-warning'; ?>"></span>
	                </td>
                </tr>
            </table>
            <table class="widefat striped" style="margin-bottom: 1em;">
                <thead>
                <tr>
                    <th colspan="2">
                        <b><?php _e('Database', 'wc_point_of_sale') ?></b>
                    </th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td style="width: 30%;">
                        <?php _e('Last Forced Update: ', 'wc_point_of_sale') ?>
                    </td>
                    <td>
                        <?php echo !empty($this->last_update['date']) ? $this->last_update['date'] : __('No forced update made', 'wc_point_of_sale') ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <?php echo __('POS Database Version: ', 'wc_point_of_sale') ?>
                    </td>
                    <td>
                        <?php echo get_option('wc_pos_db_version') ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <?php echo __('Database Update: ', 'wc_point_of_sale') ?>
                    </td>
                    <td>
                        <input name="save" class="button" type="submit"
                               value="<?php _e('Force Update', 'wc_point_of_sale'); ?>"/><br><span class="description"
                                                                                                   style="margin-top: .5em; display: inline-block;"><?php echo __('Use with caution: this tool will update the database to the latest version - useful when settings are not being applied as per configured in settings, registers, receipts and outlets.', 'wc_point_of_sale') ?></span>
                    </td>
                </tr>
            </table>
<!--            <table class="widefat striped api_settings" style="margin-bottom: 1em;">-->
<!--                <thead>-->
<!--                <tr>-->
<!--                    <th colspan="2">-->
<!--                        <b>--><?php //_e('API', 'wc_point_of_sale') ?><!--</b>-->
<!--                    </th>-->
<!--                </tr>-->
<!--                </thead>-->
<!--                <tbody>-->
<!--                <tr>-->
<!--                    <td style="width: 30%;">-->
<!--                        --><?php //_e('API :', 'wc_point_of_sale') ?>
<!--                    </td>-->
<!--                    <td>-->
<!--                        <a class="button" id="generate_rest_api" href="--><?php //echo admin_url(); ?><!--admin.php?page=wc-settings&tab=advanced&section=legacy_api">--><?php //_e('Generate API Key', 'wc_point_of_sale'); ?><!--</a>-->
<!--                    </td>-->
<!--                </tr>-->
<!--            </table>-->
            <table class="widefat striped api_settings" style="margin-bottom: 1em;">
                <thead>
                <tr>
                    <th colspan="2">
                        <b><?php _e('Setup', 'wc_point_of_sale') ?></b>
                    </th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td style="width: 30%;">
                        <?php _e('Setup Wizard:', 'wc_point_of_sale') ?>
                    </td>
                    <td>
                        <a class="button"
                           href="<?php echo admin_url(); ?>admin.php?page=wc_pos-setup"><?php _e('Run Setup Wizard', 'wc_point_of_sale'); ?></a>
                    </
                    <br><span class="description"
                              style="margin-top: .5em; display: inline-block;"></<?php echo __('This tool will update the database to the latest version - useful when settings are not being applied as per configured in settings, registers, receipts and outlets.', 'wc_point_of_sale') ?></span>
                    </td>
                </tr>
            </table>
            <input type="hidden" class="update-log" value="<?php var_export($this->last_update) ?>">
            <?php //return $this->last_update;
            $products_count = $wpdb->get_var("SELECT COUNT(ID) FROM {$wpdb->posts} WHERE post_type IN ('product', 'product_variation') AND post_status = 'publish'");
            return apply_filters('woocommerce_point_of_sale_general_settings_fields', array(
                array('type' => 'sectionend', 'id' => 'performance_options'),
                array(
                    'title' => __('Limit Products', 'wc_point_of_sale'),
                    'type' => 'title',
                    'id' => 'slp_options'
                ),
                array(
                    'title' => __('Static Limited Products', 'wc_point_of_sale'),
                    'desc' => __('Enable static limited products', 'wc_point_of_sale'),
                    'desc_tip' => __('Limit the POS Register products.', 'wc_point_of_sale'),
                    'id' => 'wc_pos_product_limit',
                    'default' => 'no',
                    'type' => 'checkbox',
                    'checkboxgroup' => 'start',
                ),
                array(
                    'title' => __('Products Limit', 'wc_point_of_sale'),
                    'desc_tip' => __('Default is 100.', 'wc_point_of_sale'),
                    'id' => 'wc_pos_product_limit_value',
                    'default' => 10,
                    'type' => 'range_slider',
                    'custom_attributes' => array(
                        'min' => 100,
                        'max' => 1000,
                        'step' => 100
                    ),
                    'suffix' => sprintf(__('Total Products %s', 'wc_point_of_sale'), $products_count)
                ),
                array('type' => 'sectionend', 'id' => 'slp_options'),
                array(
                    'title' => __('Offline', 'wc_point_of_sale'),
                    'type' => 'title',
                    'id' => 'offline_options'
                ),
                array(
                    'title' => __('Offline Functionality', 'wc_point_of_sale'),
                    'desc' => __('Enable offline functions', 'wc_point_of_sale'),
                    'desc_tip' => __('Enables the connection status when loading the register. Enabling offline functionality may affect the performance of your shop and Point of Sale register.', 'wc_point_of_sale'),
                    'id' => 'wc_pos_enable_offline',
                    'default' => 'no',
                    'type' => 'checkbox',
                ),
                array('type' => 'sectionend', 'id' => 'offline_options'),
                array(
                    'title' => __('Reset', 'wc_point_of_sale'),
                    'type' => 'title',
                    'id' => 'pos_reset'
                ),
                array(
                    'title' => __('Settings', 'wc_point_of_sale'),
                    'desc_tip' => __('This tool will reset the Point of Sale settings to default. Use this if you would like to restore to factory settings.', 'wc_point_of_sale'),
                    'id' => 'wc_pos_reset_settings',
                    'type' => 'button',
                    'button_title' => __('Reset Settings', 'wc_point_of_sale')
                ),
                array(
                    'name' => __('Cashier Count', 'wc_point_of_sale'),
                    'desc_tip' => __('This tool will reset the order count for cashiers displayed on the Cashiers page. Use this if you would like to reset cashier data.', 'wc_point_of_sale'),
                    'id' => 'wc_pos_clear_cashier_order_count',
                    'type' => 'button',
                    'button_title' => __("Reset Cashier Orders Count", "wc_point_of_sale"),
                    'autoload' => true
                ),
                array('type' => 'sectionend', 'id' => 'pos_reset'),
            ));
        }

        /**
         * Save settings
         */
        public function save()
        {
            $last_update['date'] = date('Y-m-d H:i');
            foreach ($this->force_updates as $version => $update) {
                include(ABSPATH . $update);
                if (isset($result)) {
                    $last_update['log'][$version] = $result;
                    unset($result);
                }
            }

            update_option('wc_pos_last_force_db_update', $last_update);
            update_option('wc_pos_product_limit', isset($_POST['wc_pos_product_limit']) ? 'yes' : 'no');
            update_option('wc_pos_product_limit_value', !empty($_POST['wc_pos_product_limit_value']) ? $_POST['wc_pos_product_limit_value'] : 100);
            update_option('wc_pos_enable_offline', isset($_POST['wc_pos_enable_offline']) ? 'yes' : 'no');
        }
    }

endif;

return new WC_POS_Admin_System_Status();
