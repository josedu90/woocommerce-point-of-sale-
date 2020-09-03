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

if (!class_exists('WC_POS_Admin_Settings_Cloud_Print')) :

    /**
     * WC_POS_Admin_Settings_Cloud_Print
     */
    class WC_POS_Admin_Settings_Cloud_Print extends WC_Settings_Page
    {

        /**
         * Constructor.
         */
        public function __construct()
        {
            $this->id = 'pos_cloud_print';
            $this->label = __('Printing', 'wc_point_of_sale');

            add_filter('wc_pos_settings_tabs_array', array($this, 'add_settings_page'), 20);
            add_action('wc_pos_settings_' . $this->id, array($this, 'output'));
            add_action('wc_pos_settings_save_' . $this->id, array($this, 'save'));
            add_action('wc_pos_sections_' . $this->id, array($this, 'output_sections'));
            add_action('woocommerce_admin_field_printer_setup_fields', array($this, 'printer_setup_fields_settings'));

            include_once(WC_POS()->plugin_path() . "/includes/class-wc-pos-cloud-print-inc.php");

        }

        public function get_sections()
        {
            $sections = array(
                '' => __('Printer Seup', 'wc_point_of_sale'),
                'available_printers' => __('Available Printers', 'wc_point_of_sale'),
                'selected_settings' => __('Printer Information', 'wc_point_of_sale'),
            );

            return apply_filters('woocommerce_sections_' . $this->id, $sections);
        }

        public function output()
        {
            global $current_section;
            if($current_section != 'selected_settings'){
                parent::output();
            }else{
                $this->get_settings();
            }
        }

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

            $printers = WC_POS_CPI()->star_cloudprnt_get_printer_list();

            if ($current_section == 'available_printers'){
                ?>
                <br class="clear">
                <table class="wc_status_table widefat">
                    <thead>
                        <tr>
                            <td><?php _e("Printer Name", "wc_point_of_sale") ?></td>
                            <td><?php _e("Client Type", "wc_point_of_sale") ?></td>
                            <td><?php _e("Location", "wc_point_of_sale") ?></td>
                            <td><?php _e("Status", "wc_point_of_sale") ?></td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($printers as $printer): ?>
                        <tr>
                            <td>
                            <?php
                            $settings_url = get_admin_url(null, "admin.php?page=wc_pos_settings&tab=pos_cloud_print&section=selected_settings&mac=".$printer['printerMAC']);
                            echo '<a href="'.$settings_url.'">' . $printer["name"] . '</a>';
                            ?>
                            </td>
                            <td><?php echo $printer["ClientType"] ?></td>
                            <td>
                            <?php
                            $outlet = WC_POS()->outlet()->get_data_names();
                            echo !empty($outlet[$printer["printerLocation"]]) ? $outlet[$printer["printerLocation"]] : "N/A";
                            ?>
                            </td>
                            <td>
                            <?php
                            echo isset($printer['printerOnline']) && $printer['printerOnline'] ?
                                '<span style="color:green">' . __('Online', 'wc_point_of_sale') . '</span>' :
                                '<span style="color:orange">' . __('Offline', 'wc_point_of_sale') . '</span>';
                             ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php
                return array();
            }elseif ($current_section == 'selected_settings'){
                $s_printer = isset($_GET['mac']) && !empty($_GET['mac']) ? $_GET['mac'] : get_option('wc_pos_selected_printer', '');
                $printer_data = array();
                foreach ($printers as $key => $printer){
                    if($s_printer == $key){
                        $printer_data = $printer;
                        break;
                    }
                }
                $online = __('Unknown', 'wc_point_of_sale');
                $not_available = __('N/A', 'wc_point_of_sale');
                if(isset($printer_data['printerOnline'])) {
                    $online = $printer_data['printerOnline'] ?
                        '<span style="color:green">' . __('Online', 'wc_point_of_sale') . '</span>' :
                        '<span style="color:orange">' . __('Offline', 'wc_point_of_sale') . '</span>';
                }
                ?>
				<br>
				<input type="hidden" name="_printerMAC" value="<?php echo $printer_data['printerMAC']; ?>">
                <table class="wc_status_table widefat">
					<thead>
						<tr>
							<th colspan="2" data-export-label="Printer Information"><h2><?php _e( 'Printer Information', 'wc_point_of_sale' ); ?></h2></th>
						</tr>
					</thead>
                    <tbody>
                        <tr valign="top">
                            <td><?php _e('Printer Name', 'wc_point_of_sale') ?></td>
                            <td class="forminp forminp-text">
                            <?php 
                            if(!isset($printer_data['name'])){
                                echo $not_available;
                            }else{
                            ?>
                                <input type="text" name="wc_pos_printer_name" id="wc_pos_printer_name" value="<?php echo $printer_data["name"] ?>">
                            <?php
                            }
                            ?>
                            </td>
                        </tr>
                        <tr valign="top">
                            <td><?php _e('Printer Location', 'wc_point_of_sale') ?></td>
                            <td class="forminp forminp-select">
                            <?php
                            $outlets = WC_POS()->outlet()->get_data_names();
                            $s_oulet = $printer_data['printerLocation'];
                            ?>
                                <select name="wc_pos_printer_location" id="wc_pos_printer_location" style="min-width: 162px" class="wc-enhanced-select">
                                    <?php
                                    if(count($outlets)){
                                        foreach ($outlets as $id => $outlet) : ?>
                                        <option value="<?php echo $id ?>" <?php selected($s_oulet, $id, true) ?> ><?php echo $outlet ?></option>
                                        <?php endforeach;
                                    }else{ ?>
                                        <option value="-1"><?php _e('No outlets available', 'wc_point_of_sale') ?></option>
                                    <?php } ?>
                                </select>
                            </td>
                        </tr>
                        <tr valign="top">
                            <td><?php _e('Poll Interval', 'wc_point_of_sale') ?></td>
                            <td class="forminp forminp-text"><?php echo isset($printer_data['GetPollInterval']) ? $printer_data["GetPollInterval"] : $not_available ?></td>
                        </tr>
                        <tr valign="top">
                            <td><?php _e('Connectivity', 'wc_point_of_sale') ?></td>
                            <td class="forminp forminp-text"><?php echo $online ?></td>
                        </tr>
                        <tr valign="top">
                            <td><?php _e('ASB Status Code', 'wc_point_of_sale') ?></td>
                            <td class="forminp forminp-text"><?php echo isset($printer_data['status']) ? $printer_data['status'] : $not_available ?></td>
                        </tr>
                        <tr valign="top">
                            <td><?php _e('HTTP Status Code', 'wc_point_of_sale') ?></td>
                            <td class="forminp forminp-text"><?php echo isset($printer_data['statusCode']) ? urldecode($printer_data['statusCode']) : $not_available ?></td>
                        </tr>
                        <tr valign="top">
                            <td><?php _e('Last Communication', 'wc_point_of_sale') ?></td>
                            <td class="forminp forminp-text"><?php echo isset($printer_data['lastActive']) ? date("D j M y - H:i:s", $printer_data['lastActive']) : $not_available ?></td>
                        </tr>
                        <tr valign="top">
                            <td><?php _e('Print Test', 'wc_point_of_sale') ?></td>
                            <td class="forminp forminp-text"><button class="button" id="print-this" <?php echo $printer_data['printerOnline'] ?  "" : "disabled"; ?> ><?php _e('Print Test Page', 'wc_point_of_sale'); ?></button></td>
                        </tr>
                    </tbody>
                </table>
                <table class="wc_status_table widefat">
					<thead>
						<tr>
							<th colspan="3" data-export-label="Identification"><h2><?php _e( 'Printer Identification', 'wc_point_of_sale' ); ?></h2></th>
						</tr>
					</thead>
                    <tbody>
                        <tr valign="top">
                            <td><?php _e('MAC Address', 'wc_point_of_sale') ?></td>
                            <td class="forminp forminp-text"><?php echo isset($printer_data['printerMAC']) ? strtoupper($printer_data['printerMAC']) : $not_available ?></td>
                        </tr>
                        <tr valign="top">
                            <td><?php _e('IP Address', 'wc_point_of_sale') ?></td>
                            <td class="forminp forminp-text"><?php echo isset($printer_data['ipAddress']) ? $printer_data['ipAddress'] : $not_available ?></td>
                        </tr>
                    </tbody>
                <table class="wc_status_table widefat">
					<thead>
						<tr>
							<th colspan="3" data-export-label="Interface"><h2><?php _e( 'Printer Interface', 'wc_point_of_sale' ); ?></h2></th>
						</tr>
					</thead>
                    <tbody>
                        <tr>
                            <td><?php _e('Client Type', 'wc_point_of_sale') ?></td>
                            <td class="forminp forminp-text"><?php echo isset($printer_data['ClientType']) ? $printer_data['ClientType'] : $not_available ?></td>
                        </tr>
                        <tr>
                            <td><?php _e('Client Version', 'wc_point_of_sale') ?></td>
                            <td class="forminp forminp-text"><?php echo isset($printer_data['ClientVersion']) ? $printer_data['ClientVersion'] : $not_available ?></td>
                        </tr>
                    </tbody>
                </table>
                <table class="wc_status_table widefat">
					<thead>
						<tr>
							<th colspan="3" data-export-label="Supported Encodings"><h2><?php _e( 'Printer Supported Encodings', 'wc_point_of_sale' ); ?></h2></th>
						</tr>
					</thead>
                    <tbody>
                        <tr>
                            <td colspan="3">
                <?php
                if(isset($printer_data['Encodings'])){
                    $encodings = explode(";", $printer_data['Encodings']);
                    foreach ($encodings as $encoding){
                        echo $encoding . "<br/>";
                    }
                }
                if(isset($printer_data['printerMAC'])){
                    ?>
                    </td>
                        </tr>
                    </tbody>
                </table>
                    <?php
                    $queueItems = WC_POS_CPI()->star_cloudprnt_queue_get_queue_list($printer_data['printerMAC']);
                    if (!count($queueItems)) :?>
	                <table class="wc_status_table widefat">
                        <thead>
							<tr>
								<th colspan="3" data-export-label="Printer Queue"><h2><?php _e( 'Printer Queue', 'wc_point_of_sale' ); ?></h2></th>
							</tr>
                        </thead>
		                <tbody>
			                <tr>
				                <td>
									<?php _e('No items found in printer queue.', 'wc_point_of_sale');?>
				                </td>
				            </tr>
		                </tbody>
	                </table>
                    <?php else : ?>
	                <table class="wc_status_table widefat" id="printer-order-queue">
	                        <thead>
								<tr>
									<th colspan="3" data-export-label="Printer Queue">
									<h2 class="heading-inline"><?php _e( 'Printer Queue', 'wc_point_of_sale' ); ?></h2>
									<button type="button" class="button right" id="clr_order_queue"><?php _e("Clear order queue"); ?></button>
									</th>
								</tr>
	                            <tr>
	                                <td><?php _e('Priority', 'wc_point_of_sale') ?></td>
	                                <td><?php _e('Order ID', 'wc_point_of_sale') ?></td>
	                                <td><?php _e('Queued On', 'wc_point_of_sale') ?></td>
	                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            foreach ($queueItems as $q => $queueItem){
                                $queue_parts = explode('_', $queueItem);
                                $order_id = $queue_parts[2];
                                $queue_time = intval($queue_parts[3]);
                                echo '<tr>';
                                echo '<td>'.$q.'</td>';
                                echo '<td>'.$order_id.'</td>';
                                echo '<td>'.date("H:i:s (d/m/y)", $queue_time).'</td>';
                                echo '</tr>';
                            }
                            ?>
                            </tbody>
                        </table>
                    <?php endif;
                } ?>
                <?php
                $orderHistory = WC_POS_CPI()->star_cloudprnt_queue_get_order_history();
                if(!count($orderHistory)) :?>
	                <table class="wc_status_table widefat">
                        <thead>
							<tr>
								<th colspan="4" data-export-label="Printer Order History"><h2><?php _e( 'Printer Order History', 'wc_point_of_sale' ); ?></h2></th>
							</tr>
                        </thead>
		                <tbody>
			                <tr>
				                <td>
									<?php _e('No printed previous orders have been logged.', 'wc_point_of_sale'); ?>
				                </td>
				            </tr>
		                </tbody>
	                </table>
               <?php else : ?>
	                <table class="wc_status_table widefat" id="printer-order-history">
                        <thead>
							<tr>
								<th colspan="4" data-export-label="Printed Order History">
								<h2 class="heading-inline"><?php _e( 'Printer Order History', 'wc_point_of_sale' ); ?></h2>
								<button type="button" class="button right" id="clr_order_history"><?php _e("Clear order history"); ?></button>
								</th>
							</tr>
                            <tr>
                                <td><?php _e('Order ID', 'wc_point_of_sale') ?></td>
                                <td><?php _e('Copy Count', 'wc_point_of_sale') ?></td>
                                <td><?php _e('Queued On', 'wc_point_of_sale') ?></td>
                                <td><?php _e('Printed On', 'wc_point_of_sale') ?></td>
                            </tr>
                        </thead>
	                    <tbody>
                        <?php
                        foreach ($orderHistory as $item)
                        {
                            $exploded = explode('_', $item);
                            $copy = intval($exploded[0])+1;
                            $order_id = $exploded[2];
                            $queue_time = intval($exploded[3]);
                            $printed_time = intval($exploded[4]);

                            echo '<tr>';
                            echo '<td>'.$order_id.'</td>';
                            echo '<td>'.$copy.'</td>';
                            echo '<td>'.date("H:i:s (d/m/y)", $queue_time).'</td>';
                            echo '<td>'.date("H:i:s (d/m/y)", $printed_time).'</td>';
                            echo '</tr>';
                        }
                        ?>
	                    </tbody>
                    </table>
                <?php endif; ?>
            <?php }else{
                $options = array();
                foreach (WC_POS_CPI()->star_cloudprnt_get_printer_list() as $key => $printer){
                    $options[$key] = $printer['name'];
                }
                return apply_filters('woocommerce_point_of_sale_cloud_print_settings_fields', array(
                    array('type' => 'printer_setup_fields'),
                    array(
                        'title' => __('', 'wc_point_of_sale' ),
                        'desc' => '',
                        'type' => 'title'
                    ),
                    array(
                        'title' => __('Star CloudPRNT', 'wc_point_of_sale' ),
                        'desc_tip' => __('To enable the CloudPRNT API, you must enable it from here first.', 'wc_point_of_sale'),
                        'type' => 'select',
                        'default' => 'disable',
						'class' => 'wc-enhanced-select',
                        'options' => array(
                            'enable' => 'Enable',
                            'disable' => 'Disable'
                        ),
                        'id' => 'wc_pos_enable_cloud_print'
                    ),
                    array(
                        'title' => __('Primary Printer', 'wc_point_of_sale' ),
                        'desc_tip' => __("Please select a printer from the list. If there is no printer listed, then please check if the server URL is correctly set.", 'wc_point_of_sale'),
                        'type' => 'select',
                        'default' => 'disable',
						'class' => 'wc-enhanced-select',
                        'options' => $options,
                        'id' => 'wc_pos_selected_printer'
                    ),
                    array(
                        'title' => __('Web Orders', 'wc_point_of_sale' ),
                        'desc' => __("Enable receipt printing for web orders.", 'wc_point_of_sale'),
                        'desc_tip' => __('Prints a receipt when a customer orders through the website store to the primary printer.', 'wc_point_of_sale'),
                        'type' => 'checkbox',
                        'id' => 'wc_pos_web_receipts'
                    ),
                    array('type' => 'sectionend', 'id' => 'wc_pos_settings_pos_cloud_print'),
                ));
            }
        }

        /**
         *   settings
         */
        public function save()
        {
            global $current_section;
            if($current_section != "selected_settings"){
                parent::save();
            }
            if($current_section == "selected_settings"){
                $s_printer = isset($_POST['_printerMAC']) && !empty($_POST['_printerMAC']) ? $_POST['_printerMAC'] : get_option('wc_pos_selected_printer', '');
                if(empty($s_printer)) {
                    return;
                }

                $printer = new WC_Pos_Cloud_Print_Printer($s_printer);

                if(isset($_POST['wc_pos_printer_name']) && !empty($_POST['wc_pos_printer_name'])){
                    $printer->updatePrinterData('name', $_POST['wc_pos_printer_name']);
                }
                if(isset($_POST['wc_pos_printer_location']) && !empty($_POST['wc_pos_printer_location'])){
                    $printer->updatePrinterData('location', $_POST['wc_pos_printer_location']);
                }
            }
        }

        public function printer_setup_fields_settings()
        {
            $print_url = WC_POS()->plugin_url() . "/cloud-print/cloud-print.php";
            ?>
            <h2><?php _e('Printer Setup', 'wc_point_of_sale'); ?></h2>
            <ol>
                <li><?php _e('Setup your Star CloudPRNT printer onto your network, and run a self-test print to get the IP address.', 'wc_point_of_sale'); ?></li>
                <li><?php _e('Type the printer IP address into a web browser to access the web interface. Default details are username "root" and password "public".', 'wc_point_of_sale'); ?></li>
                <li><?php _e('Navigate to Configuration > CloudPRNT.', 'wc_point_of_sale'); ?></li>
                <li><?php _e('Enter the Server URL below into the Server URL field.', 'wc_point_of_sale'); ?></li>
                <li><?php _e('Click Submit and then click Save.', 'wc_point_of_sale'); ?></li>
                <li><?php _e('Choose Save > Configuration Printing > Restart Device.', 'wc_point_of_sale'); ?></li>
                <li><?php _e('Click Execute and wait for 2 to 3 minutes.', 'wc_point_of_sale'); ?></li>
                <li><?php _e('Once the printer has rebooted, go back to this page and refresh it. You will notice the printer has been populated in the printer list.', 'wc_point_of_sale'); ?></li>
            </ol>
            <strong><?php _e('Requirements', 'wc_point_of_sale'); ?></strong>
            <ul>
                <li><?php _e('PHP 5.6 or greater.', 'wc_point_of_sale'); ?></li>
                <li><?php _e('Star TSP650II, TSP700II, TSP800II or SP700 series printer with a IFBD-HI01X/HI02X interface.', 'wc_point_of_sale'); ?></li>
                <li><?php _e('Recommended printer interface firmware 1.4 or greater.', 'wc_point_of_sale'); ?></li>
            </ul>
            <table class="form-table">
                <tbody>
                <tr valign="top">
                    <th scope="row" class="titledesc">
                        <label><?php _e('Server URL', 'wc_point_of_sale'); ?></label>
                        <span class="woocommerce-help-tip"
                              data-tip="<?php _e('This is the destination to connect your IP connected printer to this store.', 'wc_point_of_sale'); ?>"></span>
                    </th>
                    <td class="forminp">
                        <p><code><?php echo $print_url ?></code></p>
                    </td>
                </tr>
                </tbody>
            </table>
            <?php
        }

    }

endif;

return new WC_POS_Admin_Settings_Cloud_Print();
