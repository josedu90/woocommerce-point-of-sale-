<html>
<head>
    <meta charset="utf-8">
    <title><?php _e('Receipt', 'woocommerce-point-of-sale'); ?></title>
    <style>
        @media print {
        <?php if ($receipt_options['receipt_width'] == '0') { ?>
            .pos_receipt {
                min-width: 100%;
                width: 100%;
                margin: 0;
                padding: 0;
                background: transparent !important;
            }

        <?php } else { ?>
            .pos_receipt {
                width: <?php echo $receipt_options['receipt_width'] ?>mm;
                margin: auto;
                padding: 0;
                background: transparent !important;
            }

        <?php } ?>
            @page {
                margin: 0;
            }
        }
        @media screen {
            <?php if ($receipt_options['receipt_width'] == '0') { ?>
            .pos_receipt {
                min-width: 100%;
                width: 100%;
                margin: 0;
                padding: 0;
                background: #fff;
            }

        <?php } else { ?>
            .pos_receipt_body {
                background: #23282d;
            }
            .pos_receipt {
                width: <?php echo $receipt_options['receipt_width'] ?>mm;
                margin: auto;
                padding: 2mm;
                background: #fff !important;
            }
            .break:not(:last-child) {
                display: block;
                background: #23272c;
                height: 30px;
                width: calc(100% + 4mm);
                position: relative;
                margin: 0 -2mm;
                margin-bottom: 2mm;
            }
        .pos_receipt {
            height: min-content;
        }

        <?php } ?>
        }

        .pos_receipt, table.order-info, table.receipt_items, table.customer-info, #pos_receipt_title, #pos_receipt_address, #pos_receipt_contact, #pos_receipt_header, #pos_receipt_footer, #pos_receipt_tax, #pos_receipt_info, #pos_receipt_items, pos_receipt_tax_breakdown, table.tax_breakdown, #pos_receipt_wifi, .display-socials {
            font-family: "Helvetica Neue",sans-serif;
            line-height: 1.25;
            font-size: 10px;
            background: transparent;
            color: #000000;
            box-shadow: none;
            text-shadow: none;
            margin: 0;
        }

        #pos_receipt_logo {
            text-align: center;
        }

        #print_receipt_logo {
            height: 50px;
            width: auto;
        }

        .pos_receipt h1,
        .pos_receipt h2,
        .pos_receipt h3,
        .pos_receipt h4,
        .pos_receipt h5,
        .pos_receipt h6 {
            margin: 0;
        }

        table.customer-info, table.order-info, table.receipt_items, table.tax_breakdown {
            width: 100%;
            border-collapse: collapse;
            border-spacing: 0;
        }

        table.receipt_items tbody tr,
        table.receipt_items thead tr,
        table.order-info tbody tr {
            border-bottom: 1px dotted #eee;
        }
        table.order-info tbody tr:last-child {
            border: none;
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

        #pos_receipt_info {
            border-top: 1px solid #000;
            padding-top: 10px;
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

        #pos_customer_info, #pos_receipt_title, #pos_receipt_logo, #pos_receipt_contact, #pos_receipt_tax, #pos_receipt_header, #pos_receipt_items, .display-socials, #pos_receipt_address, #pos_receipt_info, #pos_receipt_tax_breakdown, #pos_receipt_wifi {
            margin-bottom: 10px;
        }
        #pos_receipt_items {
            border-top: 1px solid #000;
        }
        #pos_receipt_header, #pos_receipt_title, #pos_receipt_footer {
            text-align: center;
        }

        #pos_receipt_barcode,
        #pos_receipt_tax_breakdown {
            border-top: 1px solid #000;
        }
        #pos_receipt_barcode #print_barcode img {
            height: 40px;
        }
        .attribute_receipt_value {
            line-height: 1.5;
            float: left;
        }

        .break {
            page-break-after: always;
        }

        .woocommerce-help-tip {
            display: none;
        }

        td.product-price,
        td.product-amount {
            text-align: right;
        }
    </style>

    <?php if (isset($receipt_style)) {
        ?>
        <style id="receipt_style">
            <?php
                foreach ($receipt_style as $style_key => $style) {
                    if ( isset($receipt_options[$style_key]) ){
                        $k = $receipt_options[$style_key];
                        if( isset( $style[$k] ) ){
                            echo $style[$k];
                        }
                    }
                }
            ?>

            <?php echo $receipt_options['custom_css']; ?>
            @media print {
            }
        </style>
        <?php
    }
    ?>
</head>
<?php
function sv_change_email_tax_label($label)
{
    $label = '';
    return $label;
}

for ($rc = 1;
     $rc <= $receipt_options['print_copies_count'];
     $rc++) { ?>
    <body>
        <div class="pos_receipt_body">
            <div class="pos_receipt" id="pos_receipt">
                <div id="pos_receipt_title">
                    <?php echo $receipt_options['receipt_title']; ?>
                </div>
                <div id="pos_receipt_logo">
                    <img src="<?php echo $attachment_image_logo[0]; ?>"
                        id="print_receipt_logo" <?php echo (!$receipt_options['logo']) ? 'style="display: none;"' : ''; ?>>
                </div>
                <div id="pos_receipt_address">
                    <strong>
                        <?php if ($receipt_options['show_site_name'] == 'yes') { ?>
                            <?php echo bloginfo('name'); ?>
                        <?php } ?>
                    </strong>
                    <br>
                    <?php if ($receipt_options['show_outlet'] == 'yes') { ?>
                        <?php echo $outlet['name']; ?>
                    <?php } ?>
                    <?php
                    if ($receipt_options['print_outlet_address'] == 'yes') { ?>
                        <br>
                        <?php echo $outlet_address; ?>
                    <?php } ?>
                </div>
                <div id="pos_receipt_contact">
                    <?php if ($receipt_options['print_outlet_contact_details'] == 'yes') { ?>
                        <?php if ($outlet['social']['phone']) {
                            if ($receipt_options['telephone_label']) echo $receipt_options['telephone_label'] . ': ';
                            echo $outlet['social']['phone'] . '<br>';
                        }
                        ?>
                        <?php if ($outlet['social']['fax']) {
                            if ($receipt_options['fax_label']) echo $receipt_options['fax_label'] . ': ';
                            echo $outlet['social']['fax'] . '<br>';
                        }
                        ?>
                        <?php if ($outlet['social']['email']) {
                            if ($receipt_options['email_label']) echo $receipt_options['email_label'] . ': ';
                            echo $outlet['social']['email'] . '<br>';
                        }
                        ?>
                        <?php if ($outlet['social']['website']) {
                            if ($receipt_options['website_label']) echo $receipt_options['website_label'] . ': ';
                            echo $outlet['social']['website'];
                        }
                        ?>
                    <?php } ?>
                </div>
                <?php if($receipt_options['print_wifi'] == "yes"): ?>
                <div id="pos_receipt_wifi">
                    <?php if ($outlet['contact']['wifi_network']) {
                        echo __('WI-FI Network: ', 'wc_point_of_sale');
                        echo $outlet['contact']['wifi_network'] . '<br>';
                    }
                    ?>
                    <?php if ($outlet['contact']['wifi_password']) {
                        echo __('WI-FI Password: ', 'wc_point_of_sale');
                        echo $outlet['contact']['wifi_password'];
                    }
                    ?>
                </div>
                <?php endif; ?>
                <?php if ($receipt_options['socials_display_option'] != 'none' && $receipt_options['socials_display_option'] == 'header') { ?>
                    <div class="display-socials">
                        <?php if ($receipt_options['show_twitter'] == 'yes') { ?>
                            <div class="display-twitter"><?php echo __('Twitter: ', 'wc_point_of_sale') . $outlet['social']['twitter'] ?></div>
                        <?php } ?>
                        <?php if ($receipt_options['show_facebook'] == 'yes') { ?>
                            <div class="display-facebook"><?php echo __('Facebook: ', 'wc_point_of_sale') . $outlet['social']['facebook'] ?></div>
                        <?php } ?>
                        <?php if ($receipt_options['show_instagram'] == 'yes') { ?>
                            <div class="display-instagram"><?php echo __('Instagram: ', 'wc_point_of_sale') . $outlet['social']['instagram'] ?></div>
                        <?php } ?>
                        <?php if ($receipt_options['show_snapchat'] == 'yes') { ?>
                            <div class="display-snapchat"><?php echo __('Snapchat: ', 'wc_point_of_sale') . $outlet['social']['snapchat'] ?></div>
                        <?php } ?>
                    </div>
                <?php } ?>
                <div id="pos_receipt_tax">
                    <?php if ($receipt_options['print_tax_number'] == 'yes') { ?>
                        <span id="print-tax_number_label"><?php echo $receipt_options['tax_number_label'] . ': '; ?></span>
                        <?php
                        $tax_number = get_post_meta($order->get_id(), 'wc_pos_order_tax_number', true);
                        if ($tax_number == '')
                            echo isset($register['detail']['tax_number']) ? $register['detail']['tax_number'] : '[tax-number]';
                        else
                            echo $tax_number;
                        ?>
                    <?php } ?>
                </div>
                <div id="pos_receipt_header">
                    <?php echo stripslashes($receipt_options['header_text']); ?>
                </div>
                <div id="pos_receipt_info">
                    <table class="order-info">
                        <tbody>
                        <?php if ($receipt_options['order_number_label']) { ?>
                            <tr>
                                <th><?php echo $receipt_options['order_number_label']; ?></th>
                                <td><?php echo $order->get_order_number(); ?></td>
                            </tr>
                        <?php } else {
                            echo $order->get_order_number();
                        } ?>
                        <?php if ($receipt_options['print_order_time'] == 'yes') { ?>
                            <tr>
                                <th><?php echo $receipt_options['order_date_label']; ?></th>
                                <td><?php if ($receipt_options['order_date_label']) {
                                        $format = isset($receipt_options['order_date_format']) && !empty($receipt_options['order_date_format']) ? $receipt_options['order_date_format'] : "jS F Y";
                                        echo $order->get_date_created()->date_i18n($format); ?>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php } ?>
                        <?php if ($receipt_options['print_customer_name'] == 'yes' && ($order->get_billing_first_name() || $order->get_billing_first_name())) { ?>
                            <tr>
                                <th><?php echo $receipt_options['customer_name_label']; ?></th>
                                <td>
                                    <?php echo esc_html($order->get_billing_first_name()); ?> <?php echo esc_html($order->get_billing_last_name()); ?>
                                </td>
                            </tr>
                        <?php } ?>
                        <?php if ($receipt_options['print_customer_email'] == 'yes' && $order->get_billing_email()) { ?>
                            <tr>
                                <th><?php echo $receipt_options['customer_email_label']; ?></th>
                                <td><?php echo esc_html($order->get_billing_email()); ?></td>
                            </tr>
                        <?php } ?>
                        <?php if ($receipt_options['print_customer_phone'] == 'yes' && $order->get_billing_phone()) { ?>
                            <tr>
                                <th><?php echo $receipt_options['customer_phone_label']; ?></th>
                                <td><?php echo esc_html($order->get_billing_phone()); ?></td>
                            </tr>
                        <?php } ?>
                        <?php if ($receipt_options['print_customer_ship_address'] == 'yes' && $order->get_shipping_methods() && $order->get_shipping_address_1()) { ?>
                            <tr>
                                <th><?php echo $receipt_options['customer_ship_address_label']; ?></th>
                                <td>
                                    <?php echo ($address = $order->get_formatted_shipping_address()) ? $address : __('N/A', 'woocommerce'); ?>
                                </td>
                            </tr>
                        <?php } ?>
                        <?php if ($receipt_options['print_server'] == 'yes') {
                            $post_author = $order->get_meta('wc_pos_served_by', true);
                            $served_by = get_userdata($post_author);
                            if ($served_by) {
                                switch ($receipt_options['served_by_type']) {
                                    case 'nickname':
                                        $served_by_name = $served_by->nickname;
                                        break;
                                    case 'display_name':
                                        $served_by_name = $served_by->display_name;
                                        break;
                                    default:
                                        $served_by_name = $served_by->user_nicename;
                                        break;
                                }
                            } else {
                                $served_by_name = get_post_meta($order->get_id(), 'wc_pos_served_by_name', true);
                            }
                            ?>
                            <tr>
                                <th><?php echo $receipt_options['served_by_label']; ?></th>
                                <td><?php echo $served_by_name; ?>
                                    <?php if ($receipt_options['show_register'] == 'yes') { ?>
                                    on <?php echo $register_name; ?></td>
                                <?php } ?>
                            </tr>
                        <?php } ?>
                        <?php if ($receipt_options['print_order_notes'] == 'yes' && $order->get_customer_note()) { ?>
                            <tr>
                                <th><?php echo $receipt_options['order_notes_label']; ?></th>
                                <td><?php echo wptexturize(str_replace("\n", '<br/>', $order->get_customer_note())); ?></td>
                            </tr>
                        <?php } ?>

                        <?php if (get_option('wc_pos_print_diner_option', 'no') == 'yes' && $receipt_options['print_dining_option'] == "yes" ) { ?>
                            <?php if (get_post_meta($order->get_id(), 'wc_pos_dining_option', true) != 'None'): ?>
                                <tr>
                                    <th><?php _e('Dining options', 'wc_point_of_sale'); ?></th>
                                    <td><?php echo get_post_meta($order->get_id(), 'wc_pos_dining_option', true); ?></td>
                                </tr>
                            <?php endif; ?>
                        <?php } ?>

                        <?php
                        if($receipt_options['print_tab'] == "yes") {

                            if (isset($tab)) { ?>
                                <tr>
                                    <th><?php echo __('Tab', 'wc_point_of_sale') ?></th>
                                    <td><?php echo $tab ?></td>
                                </tr>
                            <?php } elseif (isset($order) && $order->get_meta('order_tab')) { ?>
                                <tr>
                                    <th><?php echo __('Tab', 'wc_point_of_sale') ?></th>
                                    <td><?php echo $order->get_meta('order_tab') ?></td>
                                </tr>
                            <?php }

                        }
                        ?>
                        </tbody>
                    </table>
                </div>
                <div id="pos_receipt_items">
                    <table class="receipt_items">
                        <thead>
                        <tr>
                            <th><?php _e('Qty', 'wc_point_of_sale'); ?></th>
                            <?php if ($receipt_options['show_image_product'] == 'yes') { ?>
                                <th class="column-product-image"></th>
                            <?php } ?>
                            <th><?php _e('Product', 'wc_point_of_sale'); ?></th>
                            <th class="product-price"><?php echo ($receipt_options['show_cost'] == 'yes') ? __('Cost', 'wc_point_of_sale') : '' ?></th>
                            <th><?php _e('Total', 'wc_point_of_sale'); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $items = $order->get_items('line_item');
                        $_items = array();
                        $_items_nosku = array();
                        $_items_sku = array();
                        $_cart_subtotal = 0;
                        $tax_display = isset($receipt_options['print_tax']) ? $receipt_options['print_tax'] == "yes" ? "excl" : "incl" : get_option('woocommerce_tax_display_cart', 'incl');
                        add_filter('woocommerce_countries_ex_tax_or_vat', 'sv_change_email_tax_label');
                        foreach ($items as $item_id =>  $item) {

                            $_product = $order->get_product_from_item($item);
                            if ($_product) {
                                $sku = $_product->get_sku();
                            } else {
                                $sku = '';
                            }
                            ob_start();
                            ?>
                            <tr>
                                <td><?php echo $item['qty']; ?></td>
                                <?php if ($receipt_options['show_image_product'] == 'yes') { ?>
                                    <td class="column-product-image">
                                        <?php
                                        $thumbnail = $_product ? apply_filters('woocommerce_admin_order_item_thumbnail', $_product->get_image('thumbnail', array('title' => ''), false), $item_id, $item) : '';
                                        echo '<div class="wc-order-item-thumbnail">' . wp_kses_post($thumbnail) . '</div>';
                                        ?>
                                    </td>
                                <?php } ?>
                                <td class="product-name">
                                    <strong><?php echo $name = esc_html($item['name']); ?></strong>
                                    <small class="product-sku"><?php echo ($_product && $_product->get_sku() && $receipt_options['show_sku'] == 'yes') ? '<br>' . esc_html($_product->get_sku()) : ''; ?></small>
                                    <?php

                                    if ($metadata = wc_get_order_item_meta($item_id, '')) {
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
                                                '_reduced_stock'
                                            )))) {
                                                continue;
                                            }

                                            // Skip serialised meta
                                            if (is_serialized($meta[0])) {
                                                continue;
                                            }

                                            // Get attribute data
                                            $term = get_term_by('slug', $meta[0], wc_sanitize_taxonomy_name($key));
                                            if (taxonomy_exists(wc_sanitize_taxonomy_name($key))) {
                                                $meta['meta_key'] = wc_attribute_label($key);
                                            } else {
                                                $meta['meta_key'] = apply_filters('woocommerce_attribute_label', wc_attribute_label($key, $_product), $key, $_product);
                                            }
                                            $meta['meta_value'] = isset($term->name) ? $term->name : $meta[0];

                                            $meta_list[] = wp_kses_post(rawurldecode( $meta['meta_key'])) . ': ' . wp_kses_post(make_clickable(rawurldecode($meta['meta_value'])));
                                        }
                                        if (!empty($meta_list)) {
                                            echo '<br><small class="attribute_receipt_value">' . implode("<br> ", $meta_list);
                                        }
                                    }
                                    ?>
                                </td>
                                <td class="product-price">
                                    <?php
                                    if ($receipt_options['show_cost'] == 'yes') {

                                        $product = wc_get_product($item->get_product_id());

                                        $price = $product->get_regular_price();

                                        if ($price == 0) {
                                            $variation = $_product->get_data();
                                            $price = $variation['regular_price'];
                                        }
                                        $item_total = $order->get_item_total($item, $tax_display == "incl", true);
                                        $item_sub_total = $order->get_item_subtotal($item, $tax_display == "incl", true);
                                        if ($receipt_options['show_discount'] == 'yes' && ($item_total != $item_sub_total )){
                                            echo '<span style="text-decoration:line-through">'.wc_price($item_sub_total, array('currency' => $order->get_currency())).'</span> <br>';
                                        }

                                        if (isset($item['line_total'])) {
                                            echo wc_price($item_total, array('currency' => $order->get_currency()));
                                        }
                                    }
                                    ?>
                                </td>
                                <td class="product-amount">
                                    <?php
                                    if (isset($item['line_total'])) {
                                        echo $order->get_formatted_line_subtotal($item, $tax_display);
                                    }

                                    if ($refunded = $order->get_total_refunded_for_item($item_id)) {
                                        echo '<small class="refunded">-' . wc_price($refunded, array('currency' => $order->get_currency())) . '</small>';
                                    }
                                    ?>
                                </td>
                            </tr>
                            <?php
                            if (empty($sku)) {
                                $_items_nosku[$item_id] = $name;
                            } else {
                                $_items_sku[$item_id] = $sku . $name;
                            }

                            $_items[$item_id] = ob_get_contents();

                            ob_end_clean();
                        }
                        asort($_items_sku);
                        foreach ($_items_sku as $key => $_item) {
                            echo $_items[$key];
                        }
                        asort($_items_nosku);
                        foreach ($_items_nosku as $key => $_item) {
                            echo $_items[$key];
                        }
                        ?>
                        </tbody>
                        <tfoot>
                        <?php
                        if (($totals = $order->get_order_item_totals($tax_display ))) {
                            $i = 0;
                            $total_order = 0;
                            foreach ($totals as $total_key => $total) {
                                switch ($total_key) {
                                    case 'cart_subtotal':
                                        $total_label = __('Subtotal', 'wc_point_of_sale');
                                        break;
                                    case 'order_total':
                                        $total_label = '<span id="print-total_label">' . __('Total', 'wc_point_of_sale') . '</span>';
                                        $total_order = $total['value'];
                                        break;
                                    case 'discount':
                                        $coupons = $order->get_items('coupon');
                                        $reason = "";
                                        foreach ($coupons as $coupon){
                                            if(strtolower($coupon->get_name()) == 'pos discount'){
                                                $reason = wc_get_order_item_meta($coupon->get_id(), 'wc_pos_discount_reason', true);
                                                break;
                                            }
                                        }
                                        $total_label = !empty($reason) && strtoupper($reason) != "NONE" ? $reason : __('Discount', 'wc_point_of_sale');;
                                        break;
                                    case 'shipping':
                                        $total_label = __('Shipping', 'wc_point_of_sale');
                                        break;
                                    case 'payment_method':
                                        continue 2;
                                        break;
                                    default :
                                        $total_label = $total['label'];
                                        break;
                                }
                                $i++;
                                if ($total_key == 'order_total') {
                                // Tax for tax exclusive prices
                                    $total_rows = array();
                                    if ($tax_display) {
                                        if (get_option('woocommerce_tax_total_display') == 'itemized') {
                                            foreach ($order->get_tax_totals() as $code => $tax) {
                                                $total_rows[$code] = array(
                                                    'label' => $tax->label,
                                                    'value' => $tax->formatted_amount
                                                );
                                            }
                                        } else {
                                            $total_rows[] = array(
                                                'label' => WC()->countries->tax_or_vat(),
                                                'value' => wc_price($order->get_total_tax(), array('currency' => $order->get_currency()))
                                            );
                                        }
                                    }
                    //                if (!empty($total_rows)) {
                    //                    foreach ($total_rows as $row) { ?>
                    <!--                            <tr>-->
                    <!--                                --><?php //if ($receipt_options['show_image_product'] == 'yes') {  ?>
                    <!--                                    <th class="column-product-image"></th>-->
                    <!--                                --><?php //} ?>
                    <!--                                <th scope="row" colspan="3">-->
                    <!--                                    --><?php //echo $row['label'];  ?><!-- – <span id="print-tax_label">-->
                    <!--                                        --><?php //if ($preview) {
                    //                                            echo $receipt_options['tax_label'];
                    //                                        } elseif ($receipt_options['tax_label']) {
                    //                                            echo $receipt_options['tax_label'];
                    //                                        }  ?>
                    <!--                                        </span></th>-->
                    <!--                                <td>--><?php //echo $row['value']; ?><!--</td>-->
                    <!--                            </tr>-->
                    <!--                    --><?php //}
                    //                }
                                }?>
                                <tr>
                                    <?php if ($receipt_options['show_image_product'] == 'yes') { ?>
                                        <th class="column-product-image"></th>
                                    <?php } ?>
                                    <th scope="row" colspan="3">
                                        <?php echo rtrim($total_label, ":"); ?>
                                    </th>
                                    <td>
                                        <?php echo $total['value']; ?>
                                    </td>
                                </tr>
                            <?php
                            }
                            ?>
                        <tr>
                            <?php if ($receipt_options['show_image_product'] == 'yes') { ?>
                                <th class="column-product-image"></th>
                            <?php } ?>
                            <th scope="row" colspan="3">
                                <?php echo $order->get_payment_method_title(); ?> <span
                                        id="print-payment_label"><?php echo $receipt_options['payment_label']; ?></span>
                            </th>
                            <td>
                                <?php
                                $amount_pay = get_post_meta($order->get_id(), 'wc_pos_amount_pay', true);
                                if ($amount_pay) {
                                    echo wc_price($amount_pay, array('currency' => $order->get_currency()));
                                } else {
                                    echo $total_order;
                                }
                                ?>
                            </td>
                        </tr>
                        <?php if ($order->get_payment_method() == 'cod') { ?>
                            <tr>
                                <?php if ($receipt_options['show_image_product'] == 'yes') { ?>
                                    <th class="column-product-image"></th>
                                <?php } ?>
                                <th scope="row" colspan="3">
                                    <?php _e('Change', 'wc_point_of_sale'); ?>
                                </th>
                                <td>
                                    <?php
                                    $amount_change = get_post_meta($order->get_id(), 'wc_pos_amount_change', true);
                                    if ($amount_change) {
                                        echo wc_price($amount_change, array('currency' => $order->get_currency()));
                                    } else {
                                        echo wc_price(0, array('currency' => $order->get_currency()));
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php } ?>
                        <?php if ($preview || $receipt_options['print_number_items'] == 'yes') { ?>
                            <tr id="print_number_items">
                                <?php if ($receipt_options['show_image_product'] == 'yes') { ?>
                                    <th class="column-product-image"></th>
                                <?php } ?>
                                <th scope="row" colspan="3">
                                    <span id="print-items_label"><?php echo $receipt_options['items_label']; ?></span>
                                </th>
                                <td>
                                    <?php echo $order->get_item_count(); ?>
                                </td>
                            </tr>
                        <?php } ?>
                        <?php
                        }
                        ?>
                        </tfoot>
                    </table>
                </div>
                <?php if (isset($receipt_options['tax_summary']) && $receipt_options['tax_summary'] == 'yes') { ?>
                    <div id="pos_receipt_tax_breakdown">
                        <table class="tax_breakdown">
                            <thead>
                            <tr>
                                <th colspan="3"><?php echo $receipt_options['tax_label']; ?><?php _e(' Summary', 'wc_point_of_sale'); ?></th>
                            <tr>
                                <th><?php echo $receipt_options['tax_label']; ?><?php _e(' Name', 'wc_point_of_sale'); ?></th>
                                <th><?php echo $receipt_options['tax_label']; ?><?php _e(' Rate', 'wc_point_of_sale'); ?></th>
                                <th><?php echo $receipt_options['tax_label']; ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            $tax_display = $order->get_prices_include_tax();
                            $order_taxes = $order->get_taxes();
                            if (!empty($order_taxes)) {
                                foreach ($order_taxes as $row) {
                                    $tax_rate = WC_Tax::_get_tax_rate($row->get_rate_id());
                                    ?>
                                    <tr>
                                        <td><?php echo $row->get_label() ?></td>
                                        <td><?php echo number_format($tax_rate['tax_rate'], 2) ?></td>
                                        <td><?php echo wc_price($row->get_tax_total()) ?></td>
                                    </tr>
                                    <?php
                                    if($row->get_shipping_tax_total() != "0"){
                                    ?>
                                    <tr>
                                        <td><?php echo $row->get_label() . __(" (Shipping)", "wc_point_of_sale"); ?></td>
                                        <td><?php echo number_format($tax_rate['tax_rate'], 2); ?></td>
                                        <td><?php echo wc_price($row->get_shipping_tax_total()); ?></td>
                                    </tr>
                                    <?php
                                    }
                                }
                            }
                            ?>
                            </tbody>
                        </table>
                    </div>
                <?php } ?>
                <?php if (get_option('wc_pos_signature', 'no') == 'yes') { ?>
                    <?php if (get_post_meta($order->get_id(), 'wc_pos_signature', true) != null): ?>
                        <div style="width: 400px; height: 150px;">
                            <center>
                                <img src="data:image/png;base64,<?php echo get_post_meta($order->get_id(), 'wc_pos_signature', true); ?>">
                            </center>
                        </div>
                    <?php endif; ?>
                <?php } ?>
                <?php if ($receipt_options['socials_display_option'] != 'none' && $receipt_options['socials_display_option'] == 'footer') { ?>
                    <div class="display-socials">
                        <?php if ($receipt_options['show_twitter'] == 'yes') { ?>
                            <div class="display-twitter"><?php echo __('Twitter: ', 'wc_point_of_sale') . $outlet['social']['twitter'] ?></div>
                        <?php } ?>
                        <?php if ($receipt_options['show_facebook'] == 'yes') { ?>
                            <div class="display-facebook"><?php echo __('Facebook: ', 'wc_point_of_sale') . $outlet['social']['facebook'] ?></div>
                        <?php } ?>
                        <?php if ($receipt_options['show_instagram'] == 'yes') { ?>
                            <div class="display-instagram"><?php echo __('Instagram: ', 'wc_point_of_sale') . $outlet['social']['instagram'] ?></div>
                        <?php } ?>
                        <?php if ($receipt_options['show_snapchat'] == 'yes') { ?>
                            <div class="display-snapchat"><?php echo __('Snapchat: ', 'wc_point_of_sale') . $outlet['social']['snapchat'] ?></div>
                        <?php } ?>
                    </div>
                <?php } ?>
                <div id="pos_receipt_barcode">
                    <center>
                        <?php if ($receipt_options['print_barcode'] == 'yes') : ?>
                            <p id="print_barcode">
                                <img src="<?php echo WC_POS()->plugin_url() . '/includes/lib/barcode/image.php?filetype=PNG&dpi=72&scale=2&rotation=0&font_family=0&font_size=12&thickness=30&start=NULL&code=BCGcode128&text=' . str_replace("#", "", $order->get_order_number()); ?>" alt="">
                            </p>
                        <?php endif; ?>
                    </center>
                </div>
                <div id="pos_receipt_footer">
                    <?php echo stripslashes($receipt_options['footer_text']); ?>
                </div>
            </div>
        </div>
    </body>
<p class="break">
    <?php } ?>
    <!--Gift receipt-->
    <?php if (isset($_GET['gift_receipt']) && $_GET['gift_receipt'] == 'true') { ?>
    <?php for ($rc = 1;
    $rc <= $receipt_options['print_copies_count'];
    $rc++) { ?>
    <body>
        <div class="pos_receipt_body">
            <div class="pos_receipt" id="pos_receipt">
                <div id="pos_receipt_title">
                    <?php echo $receipt_options['gift_receipt_title'] ?>
                </div>
                <div id="pos_receipt_logo">
                    <img src="<?php echo $attachment_image_logo[0]; ?>"
                        id="print_receipt_logo" <?php echo (!$receipt_options['logo']) ? 'style="display: none;"' : ''; ?>>
                </div>
                <div id="pos_receipt_address">
                    <strong>
                    <?php if ($receipt_options['show_site_name'] == 'yes') { ?>
                        <?php echo bloginfo('name'); ?>
                    <?php } ?>
                    </strong>
                    <br>
                        <?php if ($receipt_options['show_outlet'] == 'yes') { ?>
                            <?php echo $outlet['name']; ?>
                        <?php } ?>
                    <?php
                    if ($receipt_options['print_outlet_address'] == 'yes') { ?>
                        <br>
                        <?php echo $outlet_address; ?>
                    <?php } ?>
                </div>
                <div id="pos_receipt_contact">

                    <?php if ($receipt_options['print_outlet_contact_details'] == 'yes') { ?>
                        <?php if ($outlet['social']['phone']) {
                            if ($receipt_options['telephone_label']) echo $receipt_options['telephone_label'] . ': ';
                            echo $outlet['social']['phone'] . '<br>';
                        }
                        ?>
                        <?php if ($outlet['social']['fax']) {
                            if ($receipt_options['fax_label']) echo $receipt_options['fax_label'] . ': ';
                            echo $outlet['social']['fax'] . '<br>';
                        }
                        ?>
                        <?php if ($outlet['social']['email']) {
                            if ($receipt_options['email_label']) echo $receipt_options['email_label'] . ': ';
                            echo $outlet['social']['email'] . '<br>';
                        }
                        ?>
                        <?php if ($outlet['social']['website']) {
                            if ($receipt_options['website_label']) echo $receipt_options['website_label'] . ': ';
                            echo $outlet['social']['website'];
                        }
                        ?>
                    <?php } ?>
                </div>
                <?php if($receipt_options['print_wifi'] == "yes") { ?>
                <div id="pos_receipt_wifi">
                    <?php if ($outlet['contact']['wifi_network']) {
                        echo __('WI-FI Network: ', 'wc_point_of_sale');
                        echo $outlet['contact']['wifi_network'] . '<br>';
                    }
                    ?>
                    <?php if ($outlet['contact']['wifi_password']) {
                        echo __('WI-FI Password: ', 'wc_point_of_sale');
                        echo $outlet['contact']['wifi_password'];
                    }
                    ?>
                </div>
                <?php } ?>
                <div id="pos_receipt_tax">
                    <?php if ($receipt_options['print_tax_number'] == 'yes') { ?>
                        <span id="print-tax_number_label"><?php echo $receipt_options['tax_number_label'] . ': '; ?></span>
                        <?php
                        $tax_number = get_post_meta($order->get_id(), 'wc_pos_order_tax_number', true);
                        if ($tax_number == '')
                            echo isset($register['detail']['tax_number']) ? $register['detail']['tax_number'] : '[tax-number]';
                        else
                            echo $tax_number;
                        ?>
                    <?php } ?>
                </div>
                <div id="pos_receipt_header">
                    <?php echo $receipt_options['header_text']; ?>
                </div>
                <div id="pos_receipt_info">
                    <table class="order-info">
                        <tbody>
                        <?php if ($receipt_options['order_number_label']) { ?>
                            <tr>
                                <th><?php echo $receipt_options['order_number_label']; ?></th>
                                <td><?php echo $order->get_order_number(); ?></td>
                            </tr>
                        <?php } else {
                            echo $order->get_order_number();
                        } ?>
                        <?php if ($receipt_options['print_order_time'] == 'yes') { ?>
                            <tr>
                                <th><?php echo $receipt_options['order_date_label']; ?></th>
                                <td><?php if ($receipt_options['order_date_label']) {
                                        $format = isset($receipt_options['order_date_format']) && !empty($receipt_options['order_date_format']) ? $receipt_options['order_date_format'] : "jS F Y";
                                        echo $order->get_date_created()->date_i18n($format); ?>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php } ?>
                        <?php if ($receipt_options['print_customer_name'] == 'yes' && ($order->get_billing_first_name() || $order->get_billing_first_name())) { ?>
                            <tr>
                                <th><?php echo $receipt_options['customer_name_label']; ?></th>
                                <td>
                                    <?php echo esc_html($order->get_billing_first_name()); ?><?php echo esc_html($order->get_billing_last_name()); ?>
                                </td>
                            </tr>
                        <?php } ?>
                        <?php if ($receipt_options['print_customer_email'] == 'yes' && $order->get_billing_email()) { ?>
                            <tr>
                                <th><?php echo $receipt_options['customer_email_label']; ?></th>
                                <td><?php echo esc_html($order->get_billing_email()); ?></td>
                            </tr>
                        <?php } ?>
                        <?php if ($receipt_options['print_customer_phone'] == 'yes' && $order->get_billing_phone()) { ?>
                            <tr>
                                <th><?php echo $receipt_options['customer_phone_label']; ?></th>
                                <td><?php echo esc_html($order->get_billing_phone()); ?></td>
                            </tr>
                        <?php } ?>
                        <?php if ($receipt_options['print_customer_ship_address'] == 'yes' && ($order->get_shipping_address_1())) { ?>
                            <tr>
                                <th><?php echo $receipt_options['customer_ship_address_label']; ?></th>
                                <td>
                                    <?php echo ($address = $order->get_formatted_shipping_address()) ? $address : __('N/A', 'woocommerce'); ?>
                                </td>
                            </tr>
                        <?php } ?>
                        <?php if ($receipt_options['print_server'] == 'yes') {
                            $post_author = $order->get_user_id();
                            $served_by = get_userdata($post_author);
                            if ($served_by) {
                                switch ($receipt_options['served_by_type']) {
                                    case 'nickname':
                                        $served_by_name = $served_by->nickname;
                                        break;
                                    case 'display_name':
                                        $served_by_name = $served_by->display_name;
                                        break;
                                    default:
                                        $served_by_name = $served_by->user_nicename;
                                        break;
                                }
                            } else {
                                $served_by_name = get_post_meta($order->get_id(), 'wc_pos_served_by_name', true);
                            }
                            ?>
                            <tr>
                                <th><?php echo $receipt_options['served_by_label']; ?></th>
                                <td><?php echo $served_by_name; ?>
                                    <?php if ($receipt_options['show_register'] == 'yes') { ?>
                                    on <?php echo $register_name; ?></td>
                                <?php } ?>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
                <div id="pos_receipt_items">
                    <table class="receipt_items">
                        <thead>
                        <tr>
                            <th><?php _e('Qty', 'wc_point_of_sale'); ?></th>
                            <?php if ($receipt_options['show_image_product'] == 'yes') { ?>
                                <th class="column-product-image"></th>
                            <?php } ?>
                            <th><?php _e('Product', 'wc_point_of_sale'); ?></th>
                            <?php if (!isset($_GET['gift_receipt']) || $_GET['gift_receipt'] == 'false') { ?>
                                <th><?php echo ($receipt_options['show_cost'] == 'yes') ? __('Cost', 'wc_point_of_sale') : '' ?></th>
                                <th><?php _e('Total', 'wc_point_of_sale'); ?></th>
                            <?php } ?>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $items = $order->get_items('line_item');
                        $_items = array();
                        $_items_nosku = array();
                        $_items_sku = array();
                        $_cart_subtotal = 0;
                        foreach ($items as $item_id => $item) {
                            $_product = $order->get_product_from_item($item);
                            if ($_product) {
                                $sku = $_product->get_sku();
                            } else {
                                $sku = '';
                            }
                            ob_start();
                            ?>
                            <tr>
                                <td><?php echo $item['qty']; ?></td>
                                <?php if ($receipt_options['show_image_product'] == 'yes') { ?>
                                    <td class="column-product-image">
                                        <?php
                                        $thumbnail = $_product ? apply_filters('woocommerce_admin_order_item_thumbnail', $_product->get_image('thumbnail', array('title' => ''), false), $item_id, $item) : '';
                                        echo '<div class="wc-order-item-thumbnail">' . wp_kses_post($thumbnail) . '</div>';
                                        ?>
                                    </td>
                                <?php } ?>
                                <td class="product-name"><strong>
                                        <?php echo ($_product && $_product->get_sku() && $receipt_options['show_sku'] == 'yes') ? esc_html($_product->get_sku()) . ' &ndash; ' : ''; ?>
                                        <?php echo $name = esc_html($item['name']); ?></strong>
                                    <?php
                                    if ($metadata = wc_get_order_item_meta($item_id, '')) {
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
                                                '_reduced_stock'
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
                                            echo '<br> <span class="attribute_receipt_value">' . implode("<br> ", $meta_list);
                                        }
                                    }
                                    ?>
                                </td>
                                <?php if (!isset($_GET['gift_receipt']) || $_GET['gift_receipt'] == 'false') { ?>
                                    <td class="product-price">
                                        <?php
                                        if ($receipt_options['show_cost'] == 'yes') {
                                            $tax_display = $order->get_prices_include_tax();

                                            $product = new WC_Product($item->get_product_id());


                                            $price = $product->get_regular_price();

                                            if ($price == 0) {
                                                $variation = $_product->get_data();
                                                $price = $variation['regular_price'];
                                            }


                                            if ($receipt_options['show_discount'] == 'yes' && ($price != $order->get_item_subtotal($item, $tax_display, true))){

                                                echo '<span style="text-decoration:line-through">'.wc_price($price, array('currency' => $order->get_currency())).'</span> <br>';
                                            }

                                            if (isset($item['line_total'])) {
                                                echo wc_price($order->get_item_subtotal($item, $tax_display, true), array('currency' => $order->get_currency()));
                                            }
                                        }
                                        ?>
                                    </td>
                                    <td class="product-amount">
                                        <?php ?>
                                        <?php
                                        if (isset($item['line_total'])) {
                                            echo $order->get_formatted_line_subtotal($item);
                                        }

                                        if ($refunded = $order->get_total_refunded_for_item($item_id)) {
                                            echo '<small class="refunded">-' . wc_price($refunded, array('currency' => $order->get_currency())) . '</small>';
                                        }
                                        ?>

                                    </td>
                                <?php } ?>
                            </tr>
                            <?php
                            if (empty($sku)) {
                                $_items_nosku[$item_id] = $name;
                            } else {
                                $_items_sku[$item_id] = $sku . $name;
                            }

                            $_items[$item_id] = ob_get_contents();

                            ob_end_clean();
                        }
                        asort($_items_sku);
                        foreach ($_items_sku as $key => $_item) {
                            echo $_items[$key];
                        }
                        asort($_items_nosku);
                        foreach ($_items_nosku as $key => $_item) {
                            echo $_items[$key];
                        }
                        ?>
                        </tbody>
                        <tfoot>
                        <?php
                        if (($totals = $order->get_order_item_totals()) && (!isset($_GET['gift_receipt']) || $_GET['gift_receipt'] == 'false')) {
                            $i = 0;
                            $total_order = 0;
                            foreach ($totals as $total_key => $total) {
                                if ($total_key == 'cart_subtotal') {
                                    $total_label = __('Subtotal', 'wc_point_of_sale');
                                } elseif ($total_key == 'order_total') {
                                    $total_label = '<span id="print-total_label">' . __('Total', 'wc_point_of_sale') . '</span>';
                                    $total_order = $total['value'];
                                } elseif ($total_key == 'discount') {
                                    $total_label = __('Discount', 'wc_point_of_sale');
                                } elseif ($total_key == 'shipping') {
                                    $total_label = __('Shipping', 'wc_point_of_sale');
                                } else {
                                    continue;
                                }
                                $i++;
                                if ($total_key == 'order_total') {
                                    // Tax for tax exclusive prices
                                    $tax_display = $order->get_prices_include_tax();
                                    if ('excl' == $tax_display) {
                                        if (get_option('woocommerce_tax_total_display') == 'itemized') {
                                            foreach ($order->get_tax_totals() as $code => $tax) {
                                                $total_rows[] = array(
                                                    'label' => $tax->label,
                                                    'value' => $tax->formatted_amount
                                                );
                                            }
                                        } else {
                                            $total_rows[] = array(
                                                'label' => WC()->countries->tax_or_vat(),
                                                'value' => wc_price($order->get_total_tax(), array('currency' => $order->get_currency()))
                                            );
                                        }
                                    }
                        /*if (!empty($total_rows)) {
                            foreach ($total_rows as $row) {
                                */ ?><!--
                                            <tr>
                                                <?php /*if ($receipt_options['show_image_product'] == 'yes') { */ ?>
                                                    <th class="column-product-image"></th>
                                                <?php /*} */ ?>
                                                <th scope="row" colspan="3">
                                                    <?php /*echo $row['label']; */ ?> <span id="print-tax_label">
                                                        <?php /*if ($preview) {
                                                            echo '(' . $receipt_options['tax_label'] . ')';
                                                        } elseif ($receipt_options['tax_label']) {
                                                            echo '(' . $receipt_options['tax_label'] . ')';
                                                        } */ ?>
                                                        </span></th>
                                                <td><?php /*echo $row['value']; */ ?></td>
                                            </tr>
                                            --><?php
                        /*                            }
                                                }*/
                                }
                                ?>
                            <tr>
                                <?php if ($receipt_options['show_image_product'] == 'yes') { ?>
                                    <th class="column-product-image"></th>
                                <?php } ?>
                                <th scope="row" colspan="3">
                                    <?php echo $total_label; ?>
                                </th>
                                <td>
                                    <?php echo $total['value']; ?>
                                </td>
                            </tr>
                        <?php
                        }
                        ?>
                        <tr>
                            <?php if ($receipt_options['show_image_product'] == 'yes') { ?>
                                <th class="column-product-image"></th>
                            <?php } ?>
                            <th scope="row" colspan="3">
                                <?php echo $order->get_payment_method_title(); ?> <span
                                        id="print-payment_label"><?php echo $receipt_options['payment_label']; ?></span>
                            </th>
                            <td>
                                <?php
                                $amount_pay = get_post_meta($order->get_id(), 'wc_pos_amount_pay', true);
                                if ($amount_pay) {
                                    echo wc_price($amount_pay, array('currency' => $order->get_currency()));
                                } else {
                                    echo $total_order;
                                }
                                ?>
                            </td>
                        </tr>
                        <?php if ($order->get_payment_method() == 'cod') { ?>
                            <tr>
                                <?php if ($receipt_options['show_image_product'] == 'yes') { ?>
                                    <th class="column-product-image"></th>
                                <?php } ?>
                                <th scope="row" colspan="3">
                                    <?php _e('Change', 'wc_point_of_sale'); ?>
                                </th>
                                <td>
                                    <?php
                                    $amount_change = get_post_meta($order->get_id(), 'wc_pos_amount_change', true);
                                    if ($amount_change) {
                                        echo wc_price($amount_change, array('currency' => $order->get_currency()));
                                    } else {
                                        echo wc_price(0, array('currency' => $order->get_currency()));
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php } ?>
                        <?php if ($preview || $receipt_options['print_number_items'] == 'yes') { ?>
                            <tr id="print_number_items">
                                <?php if ($receipt_options['show_image_product'] == 'yes') { ?>
                                    <th class="column-product-image"></th>
                                <?php } ?>
                                <th scope="row" colspan="3">
                                    <span id="print-items_label"><?php echo $receipt_options['items_label']; ?></span>
                                </th>
                                <td>
                                    <?php echo $order->get_item_count(); ?>
                                </td>
                            </tr>
                        <?php } ?>
                        <?php
                        }
                        ?>
                        </tfoot>
                    </table>
                </div>

                <div id="pos_customer_info">
                    <table class="customer-info">
                        <tbody>
                        <?php if ($receipt_options['print_order_notes'] == 'yes' && $order->get_customer_note()) { ?>
                            <tr>
                                <th><?php echo $receipt_options['order_notes_label']; ?></th>
                                <td><?php echo wptexturize(str_replace("\n", '<br/>', $order->get_customer_note())); ?></td>
                            </tr>
                        <?php } ?>

                        <?php if (get_option('wc_pos_print_diner_option', 'no') == 'yes' && $receipt_options['print_dining_option'] == "yes") { ?>
                            <?php if (get_post_meta($order->get_id(), 'wc_pos_dining_option', true) != 'None'): ?>
                                <tr>
                                    <th><?php _e('Dining options', 'wc_point_of_sale'); ?></th>
                                    <td><?php echo get_post_meta($order->get_id(), 'wc_pos_dining_option', true); ?></td>
                                </tr>
                            <?php endif; ?>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
                <div id="pos_receipt_barcode">
                    <center>
                    <?php if ($receipt_options['print_barcode'] == 'yes') : ?>
                    <p id="print_barcode">
                        <img src="<?php echo WC_POS()->plugin_url() . '/includes/lib/barcode/image.php?filetype=PNG&dpi=72&scale=2&rotation=0&font_family=0&font_size=12&thickness=30&start=NULL&code=BCGcode128&text=' . str_replace("#", "", $order->get_order_number()); ?>" alt="">
                    </p>
                    <?php endif; ?>
                    <?php } ?>
                    </center>
                </div>
            <div id="pos_receipt_footer">
                <?php echo $receipt_options['footer_text']; ?>
            </div>
        </div>
    </div>
</body>
<p class="break">
    <?php } ?>
</html>
<?php if (isset($_GET['print_from_wc'])): ?>
<script>
    window.print();
</script>
<?php endif; ?>