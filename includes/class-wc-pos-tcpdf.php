<?php

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

class WC_POS_TCPDF extends TCPDF
{
    public $order;
    public $register_data = array();
    public $receipt_data = array();
    public $outlet_data = array();

    public function __construct($register_data, $order_id)
    {
        parent::__construct(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $this->setupData($register_data, $order_id);
    }

    private function setupData($register_data, $order_id)
    {
        $order = wc_get_order($order_id);
        $register_data = is_array($register_data) ? $register_data : WC_POS()->register()->get_data($register_data)[0];
        $receipt_data = WC_POS()->receipt()->get_data($register_data['detail']['receipt_template']);
        $outlet_data = WC_POS()->outlet()->get_data($register_data['outlet']);

        $receipt_data = count($receipt_data) ? $receipt_data[0] : $receipt_data;
        $outlet_data = count($outlet_data) ? $outlet_data[0] : $outlet_data;

        $this->order = $order;
        $this->register_data = $register_data;
        $this->receipt_data = $receipt_data;
        $this->outlet_data = $outlet_data;
    }

    public function Header()
    {
        $order = $this->order;
        $receipt_data = $this->receipt_data;
        $outlet_data = $this->outlet_data;

        $attachment_image_logo = wp_get_attachment_image_src($receipt_data['logo'], 'full');
        $styles = $this->get_pdf_styles($receipt_data);

        $outlet_address = $outlet_data['contact'];
        $outlet_address['first_name'] = '';
        $outlet_address['last_name'] = '';
        $outlet_address['company'] = '';
        $outlet_address = WC()->countries->get_formatted_address($outlet_address);

        $this->writeSingleLine($receipt_data['receipt_title'], $receipt_data['logo_position']);
        $this->Image($attachment_image_logo[0], '', '', 0, 13.22, wp_check_filetype($attachment_image_logo[0])['ext'], "", "N", 2, "300", $this->getAlignment($receipt_data['logo_position']), false, false, 0, 'CM', false, true);

        if($receipt_data['show_site_name'] == 'yes'){
            $this->writeSingleLineHTML('<strong>' . get_bloginfo("name") . '</strong>', $receipt_data['contact_position']);
        }

        if($receipt_data['show_outlet'] == 'yes'){
            $this->writeSingleLine($outlet_data['name'], $receipt_data['contact_position']);
        }

        if($receipt_data['print_outlet_address'] == 'yes'){
            $this->writeSingleLineHTML($outlet_address, $receipt_data['contact_position']);
        }

        if($receipt_data['print_outlet_contact_details'] == 'yes'){
            $contact = !empty($receipt_options['telephone_label']) ? $receipt_options['telephone_label'] . ': ' : "";
            $contact .= $outlet_data['social']['phone'];
            $this->writeSingleLine($contact, $receipt_data['contact_position']);
        }

        if($receipt_data['socials_display_option'] != 'none' && $receipt_data['socials_display_option'] == 'header'){
            $social = $receipt_data['show_twitter'] == 'yes' ? '<div class="display-twitter">' . __('Twitter: ', 'wc_point_of_sale') . $outlet_data['social']['twitter'] . '</div>' : "";
            $social .= $receipt_data['show_facebook'] == 'yes' ? '<div class="display-facebook">' . __('Facebook: ', 'wc_point_of_sale') . $outlet_data['social']['facebook'] . '</div>' : "";
            $social .= $receipt_data['show_instagram'] == 'yes' ? '<div class="instagram">' . __('Instagram: ', 'wc_point_of_sale') . $outlet_data['social']['instagram'] . '</div>' : "";
            $social .= $receipt_data['show_snapchat'] == 'yes' ? '<div class="show_snapchat">' . __('Snapchat: ', 'wc_point_of_sale') . $outlet_data['social']['snapchat'] . '</div>' : "";
            $this->writeSingleLineHTML($social, $receipt_data['contact_position']);
        }

        if($receipt_data['print_tax_number'] == 'yes'){
            $tax_number = get_post_meta($order->get_id(), 'wc_pos_order_tax_number', true);
            $tax = '<span id="print-tax_number_label">' . $receipt_data['tax_number_label'] . ': </span>';
            $tax .= !empty($tax_number) ? $tax_number : isset($register['detail']['tax_number']) ? $this->register_data['detail']['tax_number'] : '[tax-number]';
            $this->writeSingleLineHTML($tax, $receipt_data['tax_number_position']);
        }

        if(!empty(stripslashes($receipt_data['header_text']))){
            $this->writeSingleLine(stripslashes($receipt_data['header_text']), 'center');
        }

    }

    public function Body()
    {
        $this->Header();
        $this->Ln(1);

        $first_border = array('T' => array('width' => 0.26, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)));
        $normal_border = array('B' => array('width' => 0.26, 'cap' => 'butt', 'join' => 'miter', 'dash' => 1, 'color' => array(238,238,238)));

        $this->MultiCell(90, 0, $this->receipt_data['order_number_label'], array_merge($first_border, $normal_border), 'L', false, 0);
        $this->MultiCell(90, 0, $this->order->get_order_number(), array_merge($first_border, $normal_border), 'L', false, 1);

        if($this->receipt_data['print_order_time'] == 'yes'){
            $format = isset($this->receipt_data['order_date_format']) && !empty($this->receipt_data['order_date_format']) ? $this->receipt_data['order_date_format'] : "jS F Y";
            $date = $this->order->get_date_created()->date_i18n($format);

            $this->MultiCell(90, 0, $this->receipt_data['order_date_label'], $normal_border, 'L', false, 0);
            $this->MultiCell(90, 0, $date, $normal_border, 'L', false, 1);
        }

        if($this->receipt_data['print_customer_name'] == 'yes'){
            if(!empty($this->order->get_billing_first_name()) || !empty($this->order->get_billing_last_name())){
                $name = $this->order->get_billing_first_name() . " " . $this->order->get_billing_last_name();
                $this->MultiCell(90, 0, $this->receipt_data['customer_name_label'], $normal_border, 'L', false, 0);
                $this->MultiCell(90, 0, $name, $normal_border, 'L', false, 1);
            }
        }

        if($this->receipt_data['print_customer_email'] == 'yes'){
            if(!empty($this->order->get_billing_email())){
                $this->MultiCell(90, 0, $this->receipt_data['customer_email_label'], $normal_border, 'L', false, 0);
                $this->MultiCell(90, 0, $this->order->get_billing_email(), $normal_border, 'L', false, 1);
            }
        }

        if($this->receipt_data['print_customer_phone'] == 'yes'){
            if(!empty($this->order->get_billing_phone())){
                $this->MultiCell(90, 0, $this->receipt_data['customer_phone_label'], $normal_border, 'L', false, 0);
                $this->MultiCell(90, 0, $this->order->get_billing_phone(), $normal_border, 'L', false, 1);
            }
        }

        if($this->receipt_data['print_customer_ship_address'] == 'yes'){
            if(!empty($this->order->get_formatted_shipping_address())){
                $this->MultiCell(90, 0, $this->receipt_data['customer_ship_address_label'], $normal_border, 'L', false, 0);
                $this->MultiCell(90, 0, $this->order->get_formatted_shipping_address(), $normal_border, 'L', false, 1);
            }
        }

        if($this->receipt_data['print_server'] == 'yes'){
            $server = get_post_meta($this->order->get_id(), 'wc_pos_served_by');
            $server_name = get_post_meta($this->order->get_id(), 'wc_pos_served_by_name');
            if(!empty($server)){
                $server = get_userdata(intval($server));
                switch ($this->receipt_data['served_by_type']) {
                    case 'nickname':
                        $server_name = $server->nickname;
                        break;
                    case 'display_name':
                        $server_name = $server->display_name;
                        break;
                    default:
                        $server_name = $server->user_nicename;
                        break;
                }
            }

            if(!empty($server_name)){
                $this->MultiCell(90, 0, $this->receipt_data['served_by_label'], $normal_border, 'L', false, 0);
                $this->MultiCell(90, 0, $server_name, $normal_border, 'L', false, 1);
            }
        }

        if($this->receipt_data['print_order_notes'] == 'yes'){
            $note = wptexturize(str_replace("\n", '<br/>', $this->order->get_customer_note()));
            if(!empty($note)){
                $this->MultiCell(90, 0, $this->receipt_data['order_notes_label'], $normal_border, 'L', false, 0);
                $this->MultiCell(90, 0, $note, $normal_border, 'L', false, 1);
            }
        }

        if(get_option('wc_pos_print_diner_option', 'no') == 'yes' && $this->receipt_data['print_dining_option'] == "yes"){
            $dining = get_post_meta($this->order->get_id(), 'wc_pos_dining_option', true);
            if($dining != "None"){
                $this->MultiCell(90, 0, __('Dining options', 'wc_point_of_sale'), $normal_border, 'L', false, 0);
                $this->MultiCell(90, 0, $dining, $normal_border, 'L', false, 1);
            }
        }

        $this->Ln(2);

        $cost = $this->receipt_data['show_cost'] == 'yes' ? __('Cost', 'wc_point_of_sale') : '';

        $this->MultiCell(20, 0, __('Qty', 'wc_point_of_sale'), array_merge($first_border, $normal_border), 'L', false, 0);
        $this->MultiCell(80, 0, __('Product', 'wc_point_of_sale'), array_merge($first_border, $normal_border), 'L', false, 0);
        $this->MultiCell(40, 0, $cost, array_merge($first_border, $normal_border), 'L', false, 0);
        $this->MultiCell(40, 0, __('Total', 'wc_point_of_sale'), array_merge($first_border, $normal_border), 'L', false, 1);

        $items = $this->order->get_items('line_item');
        $_items = array();
        $_items_nosku = array();
        $_items_sku = array();
        $_cart_subtotal = 0;
        foreach ($items as $item_id => $item) {
            $_product = $this->order->get_product_from_item($item);
            if ($_product) {
                $sku = $_product->get_sku();
            } else {
                $sku = '';
            }

            $name = ($_product && $_product->get_sku() && $this->receipt_data['show_sku'] == 'yes') ? esc_html($_product->get_sku()) . ' &ndash; ' : '';
            $name .= esc_html($item['name']);
            $metadata = wc_get_order_item_meta($item_id, '');
            if (!empty($metadata)) {
                $meta_list = array();
                foreach ($metadata as $key => $meta) {
                    // Skip hidden core fields
                    if (in_array($key, apply_filters('woocommerce_hidden_order_itemmeta', array(
                        '_qty',
                        '_tax_class',
                        '_product_id',
                        '_variation_id',
                        '_line_subtotal',
                        '_line_subtotal_tax',
                        '_line_total',
                        '_line_tax',
                    )))) {
                        continue;
                    }

                    // Skip serialised meta
                    if (is_serialized($meta[0])) {
                        continue;
                    }

                    // Get attribute data
                    if (taxonomy_exists(wc_sanitize_taxonomy_name($key))) {
                        $term = get_term_by('slug', $meta[0], wc_sanitize_taxonomy_name($key));
                        $meta['meta_key'] = wc_attribute_label(wc_sanitize_taxonomy_name($key));
                        $meta['meta_value'] = isset($term->name) ? $term->name : $meta[0];
                    } else {
                        $meta['meta_key'] = apply_filters('woocommerce_attribute_label', wc_attribute_label($key, $_product), $key);
                    }

                    $meta_list[] = wp_kses_post(rawurldecode($key)) . ': ' . wp_kses_post(make_clickable(rawurldecode($meta[0])));
                }
                if (!empty($meta_list)) {
                    $name .= '<br> <span class="attribute_receipt_value">' . implode("<br> ", $meta_list) . '</span>';
                }
            }

            $cost = "";
            if ($this->receipt_data['show_cost'] == 'yes') {
                $tax_display = $this->order->get_prices_include_tax();
                $product = wc_get_product($item->get_product_id());
                $price = $product->get_regular_price();

                if ($price == 0) {
                    $variation = $_product->get_data();
                    $price = $variation['regular_price'];
                }

                if ($this->receipt_data['show_discount'] == 'yes' && ($price != $this->order->get_item_subtotal($item, $tax_display, true))){

                    $cost .= '<span style="text-decoration:line-through">'.wc_price($price, array('currency' => $this->order->get_currency())).'</span> <br>';
                }

                if (isset($item['line_total'])) {
                    $cost .= wc_price($this->order->get_item_subtotal($item, $tax_display, true), array('currency' => $this->order->get_currency()));
                }
            }

            $total = "";
            if (isset($item['line_total'])) {
                $total .= $this->order->get_formatted_line_subtotal($item);
            }

            if ($refunded = $this->order->get_total_refunded_for_item($item_id)) {
                $total .= '<small class="refunded">-' . wc_price($refunded, array('currency' => $this->order->get_currency())) . '</small>';
            }

            $height = $this->getStringHeight(80, $name, false, true, '', 0);
            $this->MultiCell(20, $height, $item['qty'], $normal_border, 'L', false, 0);
            $this->writeHTMLCell(80, $height, '', '', $name, $normal_border, 0);
            $this->writeHTMLCell(40, $height, '', '', $cost, $normal_border, 0);
            $this->writeHTMLCell(40, $height, '', '', $total, $normal_border, 1);
        }
    }

    private function get_pdf_styles($receipt_data = array()){
        $styles = "";
        $receipt_style = WC_POS()->receipt()->get_style_templates();

        $styles .= <<<EOF
        <style>
            body.pos_receipt, 
            table.order-info, 
            table.receipt_items, 
            table.customer-info, 
            div#pos_receipt_title, 
            div#pos_receipt_address, 
            div#pos_receipt_contact, 
            div#pos_receipt_header, 
            div#pos_receipt_footer, 
            div#pos_receipt_tax, 
            div#pos_receipt_info, 
            div#pos_receipt_items, 
            div#pos_receipt_tax_breakdown, 
            table.tax_breakdown {
                font-family: "Helvetica Neue",sans-serif;
                line-height: 1.25;
                font-size: 10px;
                background: transparent;
                color: #000000;
                box-shadow: none;
                text-shadow: none;
                margin: 0;
            }

            div#pos_receipt_logo {
                text-align: center;
            }
    
            div#print_receipt_logo {
                height: 50px;
                width: auto;
            }
    
            body.pos_receipt h1,
            body.pos_receipt h2,
            body.pos_receipt h3,
            body.pos_receipt h4,
            body.pos_receipt h5,
            body.pos_receipt h6 {
                margin: 0;
            }
    
            table.customer-info, 
            table.order-info, 
            table.receipt_items, 
            table.tax_breakdown {
                width: 100%;
                border-collapse: collapse;
                border-spacing: 0;
            }
    
            table.receipt_items tbody tr,
            table.receipt_items thead tr,
            table.order-info tbody tr {
                border-style: solid;
                border-color: #eee;
                border-bottom-width: 1px;
            }
            table.order-info tbody tr:last-child {
                border-width: 0;
            }
    
            table.receipt_items tfoot {
                border-top: 1px solid #000;
            }
    
            table.customer-info th, table.order-info th,
            table.customer-info td, table.order-info td, table.receipt_items td,
            table.tax_breakdown td, table.tax_breakdown th {
                padding: 2px 0;
            }
    
            strong, b {
                font-weight: 600;
            }
    
            table.receipt_items thead th {
                padding: 5px 0;
            }
    
            table.receipt_items td {
                vertical-align: top;
            }
    
            table.order-info th {
                text-align: left;
                width: 33%;
                vertical-align: top;
            }
    
            table.receipt_items tr .column-product-image {
                text-align: center;
                white-space: nowrap;
            }
    
            table.receipt_items .column-product-image img {
                height: auto;
                margin: 0;
                max-height: 20px;
                max-width: 20px;
                vertical-align: middle;
                width: auto;
            }
    
            table.receipt_items tfoot td small.includes_tax {
                display: none;
            }
    
            table.receipt_items tfoot th {
                vertical-align: top;
                padding: 2px 0;
            }
    
            table.receipt_items thead th {
                text-align: left;
            }
            table.tax_breakdown thead th:first-child,
            table.tax_breakdown tbody td:first-child {
                text-align: left !important;
            }
    
            table.receipt_items tfoot th,
            table.tax_breakdown tfoot th,
            table.tax_breakdown tbody td,
            table.tax_breakdown thead th {
                text-align: right;
            }
    
            table.receipt_items th:last-child,
            table.receipt_items td:last-child,
            table.tax_breakdown th:last-child,
            table.tax_breakdown td:last-child,
            th.product-price {
                text-align: right !important;
            }
    
            div#pos_customer_info, 
            div#pos_receipt_title, 
            div#pos_receipt_logo, 
            div#pos_receipt_contact, 
            div#pos_receipt_tax, 
            div#pos_receipt_header, 
            div#pos_receipt_items, 
            div.display-socials, 
            div#pos_receipt_address, 
            div#pos_receipt_info, 
            div#pos_receipt_tax_breakdown {
                margin-bottom: 10px;
            }
            
            div#pos_receipt_items {
                border-top: 1px solid #000;
            }
            div#pos_receipt_header, 
            div#pos_receipt_title, 
            div#pos_receipt_footer {
                text-align: center;
            }
    
            div#pos_receipt_barcode,
            div#pos_receipt_tax_breakdown {
                border-top: 1px solid #000;
            }
            
            div#pos_receipt_barcode div#print_barcode img {
                height: 40px;
            }
            
            div.attribute_receipt_value {
                line-height: 1.5;
                float: left;
            }
    
            div.break {
                page-break-after: always;
            }
    
            div.woocommerce-help-tip {
                display: none;
            }
    
            td.product-price,
            td.product-amount {
                text-align: right;
            }
        </style>   
EOF;

        $styles .= "<style>";
        foreach ($receipt_style as $style_key => $style) {
            if ( isset($receipt_data[$style_key]) ){
                $k = $receipt_data[$style_key];
                if( isset( $style[$k] ) ){
                    $styles .= $style[$k];
                }
            }
        }
        $styles .= "</style>";

        return $styles;
    }

    private function writeSingleLine($text, $align = "left", $border = 0){

        $align = $this->getAlignment($align);
        $this->Cell(0, 0, $text, $border, 2, $align);
    }

    private function writeSingleLineHTML($html, $align = "left", $border = 0){

        $align = $this->getAlignment($align);
        $this->writeHTML($html, true, false, false, false, $align);
    }

    private function getAlignment($align){

        switch ($align){
            case 'left':
                $align = 'L';
                break;
            case 'right':
                $align = 'R';
                break;
            case 'center':
                $align = 'C';
                break;
            default:
                $align = '';
                break;
        }

        return $align;
    }
}