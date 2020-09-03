<?php

class WC_Pos_Cloud_Print
{
    public static $_instance = null;

    public function __construct()
    {
        $this->includes();

        if(get_option("wc_pos_web_receipts", "no") == "yes"){
            add_action('woocommerce_thankyou', array($this, 'wc_pos_woo_on_thankyou'), 1);
        }
    }

    /**
     * get instance of current class
     *
     * @return null|WC_Pos_Cloud_Print
     */
    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function includes()
    {
        include_once "class-wc-pos-cloud-print-inc.php";
        include_once "class-wc-pos-cloud-print-job.php";
    }

    /**
     * print receipt on thank you
     *
     * @param $order_id
     */
    public static function wc_pos_woo_on_thankyou($order_id)
    {
        $register = get_post_meta($order_id,'wc_pos_id_register', true);
        if(!empty($register))
            $register = WC_Pos_Registers::instance()->get_data($register);

        if(!is_array($register)){
            self::instance()->wc_pos_print_web_order_summary($order_id);
            return;
        }

        $receipt = WC_Pos_Receipts::instance()->get_data(isset($register[0]['detail']['receipt_template']) ? $register[0]['detail']['receipt_template'] : "");
        $is_html = $receipt[0]['print_by_pos_printer'] == "html";

        $selectedPrinter = isset($register[0]['settings']['receipt_printer']) ? $register[0]['settings']['receipt_printer'] : "";
        if(empty($selectedPrinter)){
            $printerList = WC_POS_CPI()->star_cloudprnt_get_printer_list();
            if (!empty($printerList))
            {
                foreach ($printerList as $printer)
                {
                    if (get_option('wc_pos_selected_printer') == $printer['printerMAC'])
                    {
                        $selectedPrinter = $printer['printerMAC'];
                        break;
                    }
                }
                if ($selectedPrinter === "" && count($printerList) === 1) $selectedPrinter = $printer['printerMAC'];
            }
        }

        if ($selectedPrinter !== ""){
            if($is_html){
                self::instance()->wc_pos_print_html_order_summary($selectedPrinter, $order_id, $register);
            }else{
                $file = STAR_CLOUDPRNT_PRINTER_PENDING_SAVE_PATH.WC_POS_CPI()->star_cloudprnt_get_os_path("/order_".$order_id."_".time().".bin");
                self::instance()->wc_pos_print_order_summary($selectedPrinter, $file, $order_id, $register);
            }
        };
    }

    /**
     * printer order summary
     *
     * @param $selectedPrinter
     * @param $file
     * @param $order_id
     * @param $register
     */
    public function wc_pos_print_order_summary($selectedPrinter, $file, $order_id, $register)
    {
        $order = wc_get_order($order_id);
        $shipping_items = @array_shift($order->get_items('shipping'));
        $order_meta = get_post_meta($order_id);
        $order = wc_get_order($order_id);
        $outlet_data = isset($register[0]) ? WC_Pos_Outlets::instance()->get_data($register[0]['outlet']) : array();
        $cashier = get_post_meta($order_id,'wc_pos_served_by_name', true);
        $receipt_id = isset($register[0]['detail']['receipt_template']) ? $register[0]['detail']['receipt_template'] : 0;
        $receipt = WC_Pos_Receipts::instance()->get_data($receipt_id);
        $align = "set_text_";

        $printer = new WC_Pos_Cloud_Print_Job($selectedPrinter, $file);
        $printer->set_codepage("20"); // 20 hex == 32 decimal == 1252 Windows Latin-1
        if (get_option('star-cloudprnt-print-logo-top-input')) $printer->add_nv_logo(esc_attr(get_option('star-cloudprnt-print-logo-top-input')));
        $printer->set_text_emphasized();
        $printer->{$this->get_alignment($receipt[0]['title_position'])}();
        $printer->add_text_line(!empty($receipt[0]['receipt_title']) ? $receipt[0]['receipt_title'] : "Receipt");
        $printer->set_text_left_align();
        $printer->cancel_text_emphasized();
        $printer->add_new_line(1);

        $this->set_print_headers($printer, $register, $outlet_data, $receipt);

        $printer->add_new_line(1);
        $printer->add_text_line("Order #".$order_id);
        $printer->add_text_line("Date: ".$order->get_date_created()->date('Y-m-d H:i:s'));
        if($receipt[0]["print_server"] == "yes" && !empty($cashier)){
            $printer->add_text_line("Cashier: ".$cashier);
        }
        if(!empty($register[0]['name'])){
            $printer->add_text_line("Register: ".$register[0]['name']);
        }
        if (isset($shipping_items['name']))
        {
            $printer->add_new_line(1);
            $printer->add_text_line("Shipping Method: ".$shipping_items['name']);
        }
        $printer->add_text_line("Payment Method: ".$order_meta['_payment_method_title'][0]);
        if($receipt[0]['print_number_items'] == "yes"){
            $item_label = !empty($receipt[0]['items_label']) ? $receipt[0]['items_label'] : __("Items", "wc_point_of_sale");
            $printer->add_new_line($item_label . ": " . $order->get_item_count());
        }
        $printer->add_text_line("Items: ".$order->get_item_count());
        $printer->add_new_line(1);
        $printer->set_text_emphasized();
        $printer->add_text_line($this->star_cloudprnt_get_column_separated_data(array('Item', 'Total')));
        $printer->cancel_text_emphasized();
        $printer->add_text_line($this->star_cloudprnt_get_seperator());

        $this->wc_pos_set_order_items($order, $printer, $receipt);

        $printer->add_new_line(1);
        $printer->set_text_right_align();
        $formatted_overall_total_price = number_format($order_meta['_order_total'][0], 2, '.', '');
        $printer->add_text_line("Total     ".$this->star_cloudprnt_get_codepage_1252_currency_symbol().$formatted_overall_total_price);
        if(!empty($change = get_post_meta($order_id, 'wc_pos_amount_change', true))){
            $printer->add_text_line("Change     ".$this->star_cloudprnt_get_codepage_1252_currency_symbol().number_format($change, 2, '.', ''));
        }
        $printer->set_text_left_align();
        $printer->add_new_line(1);
        $printer->add_text_line("All prices are inclusive of tax (if applicable).");
        $printer->add_new_line(1);
        if(get_post_meta($order_id, 'wc_pos_order_type', true) == "POS" && $order->get_user()){
            $this->star_cloudprnt_create_address($order, $order_meta, $printer);
        }
        $printer->add_new_line(1);
        $note = $order->get_customer_note();
        if(!empty($note)){
            $printer->set_text_emphasized();
            $printer->add_text_line(!empty($receipt[0]["order_notes_label"]) ? $receipt[0]["order_notes_label"] : __("Customer Notes", "wc_point_of_sale") );
            $printer->cancel_text_emphasized();
            $printer->add_new_line(1);
            $printer->add_text($note);
        }
        if (get_option('star-cloudprnt-print-logo-bottom-input')) $printer->add_nv_logo(esc_attr(get_option('star-cloudprnt-print-logo-bottom-input')));

        $this->set_custom_footer($printer, $outlet_data, $receipt);
        $printer->add_text_line("Printed: ".date("d-m-y H:i:s", time()));
        $printer->printjob($receipt[0]["print_copies_count"]);
    }

    public function wc_pos_print_html_order_summary($printer, $order_id, $register)
    {
        $file = STAR_CLOUDPRNT_PRINTER_PENDING_SAVE_PATH.WC_POS_CPI()->star_cloudprnt_get_os_path("/order_".$order_id."_".time().".pdf");

        $pdf = new WC_POS_TCPDF($register[0], $order_id);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(PDF_MARGIN_LEFT, 5, PDF_MARGIN_RIGHT);
        $pdf->SetFont('helvetica', '', 12);
        $pdf->AddPage();
        $pdf->Body();
        $pdf->output($file, "F");

        WC_POS_CPI()->star_cloudprnt_queue_add_print_job($printer, $file, 1);
    }

    public function wc_pos_print_web_order_summary($order_id)
    {
        $is_enabled = get_option('wc_pos_enable_cloud_print', 'disable') == 'enable';
        if(!$is_enabled){
            return;
        }

        $selectedPrinter = get_option('wc_pos_selected_printer', '');
        if(empty($selectedPrinter) || !WC_Pos_Cloud_Print_Handler::is_valid_mac($selectedPrinter)){
            return;
        }

        $file = STAR_CLOUDPRNT_PRINTER_PENDING_SAVE_PATH.WC_POS_CPI()->star_cloudprnt_get_os_path("/order_".$order_id."_".time().".bin");
        $order = wc_get_order($order_id);
        $shipping_items = @array_shift($order->get_items('shipping'));
        $order_meta = get_post_meta($order_id);
        $receipt = array(
            array(
                'show_sku' => 'no'
            )
        );

        $printer = new WC_Pos_Cloud_Print_Job($selectedPrinter, $file);
        $printer->add_text_line("Order #".$order_id);
        $printer->add_text_line("Date: ".$order->get_date_created()->date('Y-m-d H:i:s'));
        if (isset($shipping_items['name']))
        {
            $printer->add_new_line(1);
            $printer->add_text_line("Shipping Method: ".$shipping_items['name']);
        }
        $printer->add_text_line("Payment Method: ".$order_meta['_payment_method_title'][0]);
        $printer->add_text_line(__("Items", "wc_point_of_sale") . ": " . $order->get_item_count());
        $printer->add_new_line(1);
        $printer->set_text_emphasized();
        $printer->add_text_line($this->star_cloudprnt_get_column_separated_data(array('Item', 'Total')));
        $printer->cancel_text_emphasized();
        $printer->add_text_line($this->star_cloudprnt_get_seperator());

        $this->wc_pos_set_order_items($order, $printer, $receipt);

        $printer->add_new_line(1);
        $printer->set_text_right_align();
        $formatted_overall_total_price = number_format($order_meta['_order_total'][0], 2, '.', '');
        $printer->add_text_line("Total     ".$this->star_cloudprnt_get_codepage_1252_currency_symbol().$formatted_overall_total_price);
        if(!empty($change = get_post_meta($order_id, 'wc_pos_amount_change', true))){
            $printer->add_text_line("Change     ".$this->star_cloudprnt_get_codepage_1252_currency_symbol().number_format($change, 2, '.', ''));
        }
        $printer->set_text_left_align();
        $printer->add_new_line(1);
        $printer->add_text_line("All prices are inclusive of tax (if applicable).");
        $printer->add_new_line(1);
        $this->star_cloudprnt_create_address($order, $order_meta, $printer);
        $printer->add_new_line(1);
        $note = $order->get_customer_note();
        if(!empty($note)){
            $printer->set_text_emphasized();
            $printer->add_text_line(!empty($receipt[0]["order_notes_label"]) ? $receipt[0]["order_notes_label"] : __("Customer Notes", "wc_point_of_sale") );
            $printer->cancel_text_emphasized();
            $printer->add_new_line(1);
            $printer->add_text($note);
        }
        $printer->add_text_line("Printed: ".date("d-m-y H:i:s", time()));
        $printer->printjob(1);
    }

    /**
     * add custom header to receipt
     *
     * @param $printer Star_CloudPRNT_Star_Line_Mode_Job
     * @param array $register
     * @param array $outlet_data
     * @param array $receipt
     */
    public function set_print_headers($printer, $register=array(), $outlet_data=array(), $receipt=array())
    {
        if(!count($register) || !count($outlet_data) || !count($receipt)) return;

        $receipt_data = $receipt[0];
        $register_data = $register[0];
        $outlet_data = $outlet_data[0];
        $social_media = array("facebook", "twitter", "instagram", "snapchat");

        $printer->{$this->get_alignment($receipt_data['contact_position'])}();

        if($receipt_data['show_site_name'] == 'yes'){
            $printer->set_text_emphasized();
            $printer->add_text_line(ucwords(get_bloginfo("name")));
            $printer->cancel_text_emphasized();
        }

        if($receipt_data['show_outlet'] == 'yes'){
            $printer->add_text_line($outlet_data['name']);
        }

        if($receipt_data['print_outlet_address'] == 'yes'){
            $address = explode('<br/>', WC()->countries->get_formatted_address($outlet_data['contact']));
            if(count($address)){
                foreach ($address as $add){
                    $printer->add_text_line($add);
                }
            }
        }

        if($receipt_data['print_outlet_contact_details'] == 'yes'){
            $_contacts = array(
                'telephone' => $outlet_data['social']['phone'],
                'fax' => $outlet_data['social']['fax'],
                'email' => $outlet_data['social']['email'],
                'website' => $outlet_data['social']['website']
            );
                    $printer->add_new_line(1);
            foreach ($_contacts as $key => $contact){
                if(!empty($contact) && !in_array($key, $social_media)){
                    $label = $key . "_label";
                    if(!empty($receipt_data[$label]))
                        $printer->add_text($receipt_data[$label] . ": ");
                    $printer->add_text($contact);
                    $printer->add_new_line(1);
                }
            }

            if($receipt_data['socials_display_option'] == 'header'){
                $printer->add_new_line(1);
                foreach ($social_media as $media){
                    if(!empty($outlet_data['social'][$media]))
                        $printer->add_text_line($outlet_data['social'][$media]);
                }
            }
        }

        $printer->set_text_left_align();

        if($receipt_data['print_tax_number'] == 'yes'){
            $printer->add_new_line(1);
            $printer->{$this->get_alignment($receipt_data['tax_number_position'])}();
            if(!empty($receipt_data['tax_number_label']))
                $printer->add_text($receipt_data['tax_number_label'] . ": ");
            $printer->add_text($register_data['detail']['tax_number']);
            $printer->add_new_line(1);
        }

        if(!empty($receipt_data['header_text'])){
            $printer->set_text_center_align();
            $printer->add_text_line($receipt_data['header_text']);
            $printer->set_text_left_align();
        }

        $printer->add_text_line($this->star_cloudprnt_get_seperator());
    }

    /**
     * add custom footer to receipt
     *
     * @param $printer Star_CloudPRNT_Star_Line_Mode_Job
     * @param array $outlet_data
     * @param array $receipt
     */
    public function set_custom_footer($printer, $outlet_data=array(), $receipt=array())
    {
        $receipt_data = $receipt[0];
        $outlet_data = isset($outlet_data[0]) ? $outlet_data[0] : array();
        $social_media = array("facebook", "twitter", "instagram", "snapchat");

        if($receipt_data['socials_display_option'] == 'footer'){
            $printer->add_new_line(1);
            foreach ($social_media as $media){
                if(!empty($outlet_data['social'][$media]))
                    $printer->add_text_line($outlet_data['social'][$media]);
            }
        }

        $printer->add_text_line($this->star_cloudprnt_get_seperator());

        if(!empty($receipt_data['footer_text'])){
            $printer->set_text_center_align();
            $printer->add_text_line($receipt_data['footer_text']);
            $printer->set_text_left_align();
        }
    }

    /**
     * @param $order WC_Order
     * @param $printer WC_Pos_Cloud_Print_Job
     */
    public function wc_pos_set_order_items($order, $printer, $receipt)
    {
        $order_items = $order->get_items();
        foreach ($order_items as $item_id => $item_data)
        {
            $product_name = $item_data['name'];
            $product_id = $item_data['product_id'];
            $variation_id = $item_data['variation_id'];
            $product = new WC_Product($product_id);

            $item_qty = wc_get_order_item_meta ($item_id, "_qty", true);
            $item_total_price = floatval(wc_get_order_item_meta($item_id, "_line_total", true))
                +floatval(wc_get_order_item_meta($item_id, "_line_tax", true));
            $item_price = floatval($item_total_price) / intval($item_qty);
            $currencyHex = $this->star_cloudprnt_get_codepage_1252_currency_symbol();
            $formatted_item_price = number_format($item_price, 2, '.', '');
            $formatted_total_price = number_format($item_total_price, 2, '.', '');

            $printer->set_text_emphasized();
            $printer->add_text_line(str_replace('&ndash;', '-', $product_name));
            $printer->cancel_text_emphasized();

            if ($variation_id != 0)
            {
                $product_variation = new WC_Product_Variation( $variation_id );
                $variation_data = $product_variation->get_variation_attributes();
                $variation_detail = $this->star_cloudprnt_get_formatted_variation($variation_data, $order, $item_id);
                $exploded = explode("||", $variation_detail);
                foreach($exploded as $exploded_variation)
                {
                    $printer->add_text_line(" ".ucwords($exploded_variation));
                }
                if($receipt[0]['show_sku'] == 'yes' && !empty($product_variation->get_sku())){
                    $printer->add_text_line(" SKU: " . $product_variation->get_sku());
                }
            }
            if($receipt[0]['show_sku'] == 'yes' && $variation_id == 0 && !empty($product->get_sku())){
                $printer->add_text_line(" SKU: " . $product->get_sku());
            }
            $printer->add_text_line($this->star_cloudprnt_get_column_separated_data(array(" Qty: ".
                $item_qty." x Cost: ".$currencyHex.$formatted_item_price,
                $currencyHex.$formatted_total_price)));
        }
    }

    public function star_cloudprnt_parse_order_status($status)
    {
        if ($status === 'wc-pending') return 'Pending Payment';
        else if ($status === 'wc-processing') return 'Processing';
        else if ($status === 'wc-on-hold') return 'On Hold';
        else if ($status === 'wc-completed') return 'Completed';
        else if ($status === 'wc-cancelled') return 'Cancelled';
        else if ($status === 'wc-refunded') return 'Refunded';
        else if ($status === 'wc-failed') return 'Failed';
        else return "Unknown";
    }

    public function star_cloudprnt_get_seperator()
    {
        $max_chars = STAR_CLOUDPRNT_MAX_CHARACTERS_THREE_INCH;
        return str_repeat('_', $max_chars);
    }

    public function star_cloudprnt_get_column_separated_data($columns)
    {
        $max_chars = STAR_CLOUDPRNT_MAX_CHARACTERS_THREE_INCH;
        $total_columns = count($columns);

        if ($total_columns == 0) return "";
        if ($total_columns == 1) return $columns[0];
        if ($total_columns == 2)
        {
            $total_characters = strlen($columns[0])+strlen($columns[1]);
            $total_whitespace = $max_chars - $total_characters;
            if ($total_whitespace < 0) return "";
            return $columns[0].str_repeat(" ", $total_whitespace).$columns[1];
        }

        $total_characters = 0;
        foreach ($columns as $column)
        {
            $total_characters += strlen($column);
        }
        $total_whitespace = $max_chars - $total_characters;
        if ($total_whitespace < 0) return "";
        $total_spaces = $total_columns-1;
        $space_width = floor($total_whitespace / $total_spaces);
        $result = $columns[0].str_repeat(" ", $space_width);
        for ($i = 1; $i < ($total_columns-1); $i++)
        {
            $result .= $columns[$i].str_repeat(" ", $space_width);
        }
        $result .= $columns[$total_columns-1];

        return $result;
    }

    public function star_cloudprnt_get_codepage_1252_currency_symbol()
    {
        $symbol = get_woocommerce_currency_symbol();
        if ($symbol === "&pound;") return "\xA3"; // � pound
        else if ($symbol === "&#36;") return "\x24"; // $ dollar
        else if ($symbol === "&euro;") return "\x80"; // � euro
        return ""; // return blank by default
    }

    public function star_cloudprnt_get_formatted_variation($variation, $order, $item_id)
    {
        $return = '';
        if (is_array($variation))
        {
            $variation_list = array();
            foreach ($variation as $name => $value)
            {
                // If the value is missing, get the value from the item
                if (!$value)
                {
                    $meta_name = esc_attr(str_replace('attribute_', '', $name));
                    $value = $order->get_item_meta($item_id, $meta_name, true);
                }

                // If this is a term slug, get the term's nice name
                if (taxonomy_exists(esc_attr(str_replace('attribute_', '', $name))))
                {
                    $term = get_term_by('slug', $value, esc_attr(str_replace('attribute_', '', $name)));
                    if (!is_wp_error($term) && ! empty($term->name))
                    {
                        $value = $term->name;
                    }
                }
                else
                {
                    $value = ucwords(str_replace( '-', ' ', $value ));
                }
                $variation_list[] = wc_attribute_label(str_replace('attribute_', '', $name)) . ': ' . rawurldecode($value);
            }
            $return .= implode('||', $variation_list);
        }
        return $return;
    }

    public function star_cloudprnt_create_address($order, $order_meta, &$printer)
    {
        $fname = $order_meta['_shipping_first_name'][0];
        $lname = $order_meta['_shipping_last_name'][0];
        $a1 = $order_meta['_shipping_address_1'][0];
        $a2 = $order_meta['_shipping_address_2'][0];
        $city = $order_meta['_shipping_city'][0];
        $state = $order_meta['_shipping_state'][0];
        $postcode = $order_meta['_shipping_postcode'][0];
        $tel = $order_meta['_billing_phone'][0];

        $printer->set_text_emphasized();
        if ($a1 == '')
        {
            $printer->add_text_line("Billing Address:");
            $printer->cancel_text_emphasized();
            $fname = $order_meta['_billing_first_name'][0];
            $lname = $order_meta['_billing_last_name'][0];
            $a1 = $order_meta['_billing_address_1'][0];
            $a2 = $order_meta['_billing_address_2'][0];
            $city = $order_meta['_billing_city'][0];
            $state = $order_meta['_billing_state'][0];
            $postcode = $order_meta['_billing_postcode'][0];
        }
        else
        {
            $printer->add_text_line("Shipping Address:");
            $printer->cancel_text_emphasized();
        }

        $printer->add_text_line($fname." ".$lname);
        $printer->add_text_line($a1);
        if ($a2 != '') $printer->add_text_line($a2);
        if ($city != '') $printer->add_text_line($city);
        if ($state != '') $printer->add_text_line($state);
        if ($postcode != '') $printer->add_text_line($postcode);
        $printer->add_text_line("Tel: ".$tel);
    }

    public function get_alignment($align = "center")
    {
        return "set_text_" . $align . "_align";
    }

}

WC_Pos_Cloud_Print::instance();