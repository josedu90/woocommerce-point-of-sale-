<?php
/**
 * WoocommercePointOfSale Functions
 *
 * @author   Actuality Extensions
 * @package  WoocommercePointOfSale/Admin/Functions
 * @since    0.1
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly



function pos_admin_page()
{
    global $post_type;
    if ($post_type == 'product')
        return true;
    $pos_pages = array(
        'wc_pos_settings',
        'wc_pos_barcodes',
        'wc_pos_receipts',
        'wc_pos_users',
        'wc_pos_tiles',
        'wc_pos_grids',
        'wc_pos_outlets',
        'wc_pos_registers',
        'wc_pos_stock_controller',
        'wc_pos_cash_management',
        'wc_pos_bill_screen',
    );
    return isset($_GET['page']) && !empty($_GET['page']) && in_array($_GET['page'], $pos_pages);
}

function pos_tiles_admin_page()
{
    return isset($_GET['page']) && $_GET['page'] == 'wc_pos_tiles';
}

function pos_receipts_admin_page()
{
    return isset($_GET['page']) && $_GET['page'] == 'wc_pos_receipts';
}

function pos_barcodes_admin_page()
{
    return isset($_GET['page']) && $_GET['page'] == 'wc_pos_barcodes';
}

function pos_settings_admin_page()
{
    return isset($_GET['page']) && $_GET['page'] == 'wc_pos_settings';
}

function pos_cash_management_page()
{
    return isset($_GET['page']) && $_GET['page'] == 'wc_pos_cash_management';
}

function pos_bill_screen_page()
{
    return isset($_GET['page']) && $_GET['page'] == 'wc_pos_bill_screen';
}

function pos_shop_order_page()
{
    return (isset($_GET['post_type']) && $_GET['post_type']) || get_post_type() == 'shop_order';
}

/**
 * Output a text input box.
 *
 * @access public
 * @param array $field
 * @return void
 */
function wc_pos_text_input($field)
{
    global $thepostid, $post, $woocommerce;

    $thepostid = empty($thepostid) ? '' : $thepostid;
    $field['placeholder'] = isset($field['placeholder']) ? $field['placeholder'] : '';
    $field['class'] = isset($field['class']) ? $field['class'] : 'short';
    $field['wrapper_class'] = isset($field['wrapper_class']) ? $field['wrapper_class'] : '';
    $field['value'] = isset($field['value']) ? $field['value'] : (!empty($thepostid) ? get_post_meta($thepostid, $field['id'], true) : '');
    $field['name'] = isset($field['name']) ? $field['name'] : $field['id'];
    $field['type'] = isset($field['type']) ? $field['type'] : 'text';
    $data_type = empty($field['data_type']) ? '' : $field['data_type'];

    $field['wrapper_tag'] = isset($field['wrapper_tag']) ? $field['wrapper_tag'] : 'div';
    $field['wrapper_label_tag'] = isset($field['wrapper_label_tag']) ? $field['wrapper_label_tag'] : '%s';
    $field['wrapper_field_tag'] = isset($field['wrapper_field_tag']) ? $field['wrapper_field_tag'] : '%s';

    switch ($data_type) {
        case 'price' :
            $field['class'] .= ' wc_input_price';
            $field['value'] = wc_format_localized_price($field['value']);
            break;
        case 'decimal' :
            $field['class'] .= ' wc_input_decimal';
            $field['value'] = wc_format_localized_decimal($field['value']);
            break;
    }

    // Custom attribute handling
    $custom_attributes = array();

    if (!empty($field['custom_attributes']) && is_array($field['custom_attributes']))
        foreach ($field['custom_attributes'] as $attribute => $value)
            $custom_attributes[] = esc_attr($attribute) . '="' . esc_attr($value) . '"';

    $input = '<input type="' . esc_attr($field['type']) . '" class="' . esc_attr($field['class']) . '" name="' . esc_attr($field['name']) . '" id="' . esc_attr($field['id']) . '" value="' . esc_attr($field['value']) . '" placeholder="' . esc_attr($field['placeholder']) . '" ' . implode(' ', $custom_attributes) . ' /> ';

    if (!empty($field['description'])) {

        if (isset($field['desc_tip']) && false !== $field['desc_tip']) {
            $input .= '<img class="help_tip" data-tip="' . esc_attr($field['description']) . '" src="' . esc_url(WC()->plugin_url()) . '/assets/images/help.png" height="16" width="16" />';
        } else {
            $input .= '<p class="description">' . $field['description'] . '</p>';
        }

    }

    $label = '<label for="' . esc_attr($field['id']) . '">' . wp_kses_post($field['label']) . '</label>';
    echo '<' . $field['wrapper_tag'] . ' class="form-field ' . esc_attr($field['id']) . '_field ' . esc_attr($field['wrapper_class']) . '">' . sprintf($field['wrapper_label_tag'], $label) . sprintf($field['wrapper_field_tag'], $input);


    echo '</' . $field['wrapper_tag'] . '>';
}


/**
 * Output a select input box.
 *
 * @access public
 * @param array $field
 * @return void
 */
function wc_pos_select($field)
{
    global $thepostid, $post, $woocommerce;

    $thepostid = empty($thepostid) ? '' : $thepostid;
    $field['class'] = isset($field['class']) ? $field['class'] : 'select short';
    $field['type'] = isset($field['type']) && $field['type'] == 'multiselect' ? 'multiple="multiple"' : '';
    $field['wrapper_class'] = isset($field['wrapper_class']) ? $field['wrapper_class'] : '';
    $field['value'] = isset($field['value']) ? $field['value'] : (!empty($thepostid) ? get_post_meta($thepostid, $field['id'], true) : '');
    $field['wrapper_tag'] = isset($field['wrapper_tag']) ? $field['wrapper_tag'] : 'div';
    $field['wrapper_label_tag'] = isset($field['wrapper_label_tag']) ? $field['wrapper_label_tag'] : '%s';
    $field['wrapper_field_tag'] = isset($field['wrapper_field_tag']) ? $field['wrapper_field_tag'] : '%s';

    $name = esc_attr($field['id']);
    if (!empty($field['type'])) {
        $name .= '[]';
    }

    // Custom attribute handling
    $custom_attributes = array();

    if (!empty($field['custom_attributes']) && is_array($field['custom_attributes']))
        foreach ($field['custom_attributes'] as $attribute => $value)
            $custom_attributes[] = esc_attr($attribute) . '="' . esc_attr($value) . '"';
    $select = '<select id="' . esc_attr($field['id']) . '" name="' . $name . '" class="' . esc_attr($field['class']) . '" ' . $field['type'] . implode(' ', $custom_attributes) . '>';
    foreach ($field['options'] as $key => $value) {
        if (is_array($field['value'])) {
            $select .= '<option value="' . esc_attr($key) . '" ' . selected(true, in_array($key, $field['value']), false) . '>' . esc_html($value) . '</option>';
        } else {
            $select .= '<option value="' . esc_attr($key) . '" ' . selected(esc_attr($field['value']), esc_attr($key), false) . '>' . esc_html($value) . '</option>';
        }

    }
    $select .= '</select> ';

    if (!empty($field['description'])) {

        if (isset($field['desc_tip']) && false !== $field['desc_tip']) {
            $select .= '<img class="help_tip" data-tip="' . esc_attr($field['description']) . '" src="' . esc_url(WC()->plugin_url()) . '/assets/images/help.png" height="16" width="16" />';
        } else {
            $select .= '<p class="description">' . $field['description'] . '</p>';
        }

    }

    $label = '<label for="' . esc_attr($field['id']) . '">' . wp_kses_post($field['label']) . '</label>';

    echo '<' . $field['wrapper_tag'] . ' class="form-field ' . esc_attr($field['id']) . '_field ' . esc_attr($field['wrapper_class']) . '">' . sprintf($field['wrapper_label_tag'], $label) . sprintf($field['wrapper_field_tag'], $select);


    echo '</' . $field['wrapper_tag'] . '>';
}

/**
 * Output a radio input box.
 *
 * @access public
 * @param array $field
 * @return void
 */
function wc_pos_radio($field)
{
    global $thepostid, $post, $woocommerce;

    $thepostid = empty($thepostid) ? '' : $thepostid;
    $field['class'] = isset($field['class']) ? $field['class'] : 'select short';
    $field['wrapper_class'] = isset($field['wrapper_class']) ? $field['wrapper_class'] : '';
    $field['value'] = isset($field['value']) ? $field['value'] : (!empty($thepostid) ? get_post_meta($thepostid, $field['id'], true) : '');
    $field['name'] = isset($field['name']) ? $field['name'] : $field['id'];
    $field['wrapper_tag'] = isset($field['wrapper_tag']) ? $field['wrapper_tag'] : 'div';
    $field['wrapper_label_tag'] = isset($field['wrapper_label_tag']) ? $field['wrapper_label_tag'] : '%s';
    $field['wrapper_field_tag'] = isset($field['wrapper_field_tag']) ? $field['wrapper_field_tag'] : '%s';

    $label = '<label for="' . esc_attr($field['id']) . '">' . wp_kses_post($field['label']) . '</label>';
    $inputs = '<ul class="wc-radios">';
    foreach ($field['options'] as $key => $value) {

        $inputs .= '<li><label><input
			        		name="' . esc_attr($field['name']) . '"
			        		value="' . esc_attr($key) . '"
			        		type="radio"
			        		class="' . esc_attr($field['class']) . '"
			        		' . checked(esc_attr($field['value']), esc_attr($key), false) . '
			        		/> ' . esc_html($value) . '</label>
    						</li>';
    }
    $inputs .= '</ul>';
    if (!empty($field['description'])) {

        if (isset($field['desc_tip']) && false !== $field['desc_tip']) {
            $inputs .= '<img class="help_tip" data-tip="' . esc_attr($field['description']) . '" src="' . esc_url(WC()->plugin_url()) . '/assets/images/help.png" height="16" width="16" />';
        } else {
            $inputs .= '<p class="description">' . $field['description'] . '</p>';
        }

    }

    echo '<' . $field['wrapper_tag'] . ' class="form-field ' . esc_attr($field['id']) . '_field ' . esc_attr($field['wrapper_class']) . '">' . sprintf($field['wrapper_label_tag'], $label) . sprintf($field['wrapper_field_tag'], $inputs);


    echo '</' . $field['wrapper_tag'] . '>';
}

function pos_set_register_lock($register_id)
{
    global $wpdb;

    $table_name = $wpdb->prefix . "wc_poin_of_sale_registers";

    $db_data = $wpdb->get_results("SELECT * FROM $table_name WHERE ID = $register_id");

    if (!$db_data)
        return false;

    if (0 == ($user_id = get_current_user_id()))
        return false;

    $now = current_time('mysql');

    $data['opened'] = $now;
    $data['_edit_last'] = $user_id;
    $rows_affected = $wpdb->update($table_name, $data, array('ID' => $register_id));
    return array($now, $user_id);
}

function pos_check_register_lock($register_id)
{
    global $wpdb;

    $table_name = $wpdb->prefix . "wc_poin_of_sale_registers";

    $db_data = $wpdb->get_results("SELECT * FROM $table_name WHERE ID = $register_id");

    if (!$db_data)
        return false;

    $row = $db_data[0];

    $user = $row->_edit_last;

    if (strtotime($row->opened) >= strtotime($row->closed) && $user != get_current_user_id()) {
        return $user;
    }
    return false;
}

function pos_check_register_is_open($register_id)
{
    global $wpdb;

    $table_name = $wpdb->prefix . "wc_poin_of_sale_registers";

    $db_data = $wpdb->get_results("SELECT * FROM $table_name WHERE ID = $register_id");

    if (!$db_data)
        return false;

    $row = $db_data[0];

    if ($row->_edit_last > 0 && strtotime($row->opened) > strtotime($row->closed))
        return true;
    else
        return false;
}

function pos_check_user_can_open_register($register_id)
{
    if(!current_user_can('view_register')){
        return false;
    }

    global $wpdb;

    $table_name = $wpdb->prefix . "wc_poin_of_sale_registers";

    $db_data = $wpdb->get_results("SELECT * FROM $table_name WHERE ID = $register_id");

    if (!$db_data)
        return false;

    $row = $db_data[0];

    if (!$outlet = $row->outlet)
        return false;

    $value_user_meta = (array) get_user_meta(get_current_user_id(), 'outlet', true);

    if(in_array($outlet, $value_user_meta)){
        return true;
    }

    return false;
}


function set_outlet_taxable_address($address)
{
    $register_id = 0;
    if (isset($_POST['register_id']) && !empty($_POST['register_id'])) {
        $register_id = absint($_POST['register_id']);
    } elseif (isset($_GET['page']) && $_GET['page'] == 'wc_pos_registers' && isset($_GET['action']) && $_GET['action'] == 'view' && isset($_GET['id']) && !empty($_GET['action'])) {
        $register_id = absint($_GET['id']);
    }
    if ($register_id) {
        $id_outlet = getOutletID($register_id);

        $outlet = WC_POS()->outlet()->get_data($id_outlet);
        $address_data = $outlet[0]['contact'];
        return array($address_data['country'], $address_data['state'], $address_data['postcode'], $address_data['city']);
    } else {
        return $address;
    }
}

function isChangeUserAfterSale($register_id = 0)
{
    if ($register_id) {
        $register_data = WC_POS()->register()->get_data($register_id);
        return $register_data[0]['settings']['change_user'] ? true : false;
    }
    return false;
}

function isPrintReceipt($register_id = 0)
{
    if ($register_id) {
        $register_data = WC_POS()->register()->get_data($register_id);
        return $register_data[0]['settings']['print_receipt'] ? true : false;
    }
    return false;
}

function isNoteRequest($register_id = 0)
{
    if ($register_id) {
        $register_data = WC_POS()->register()->get_data($register_id);
        return $register_data[0]['settings']['note_request'] ? $register_data[0]['settings']['note_request'] : false;
    }
    return false;
}

function isEmailReceipt($register_id = 0)
{
    if ($register_id) {
        $register_data = WC_POS()->register()->get_data($register_id);
        if ($register_data[0]['settings']['email_receipt']) {
            return array(
                'receipt_template' => $register_data[0]['detail']['receipt_template'],
                'outlet' => $register_data[0]['outlet']
            );
        }
        return false;
    }
    return false;
}

function sentEmailReceipt($order_id)
{
    $order_email_receipt = get_post_meta($order_id, 'pos_payment_email_receipt', true);
    $status_tansition = get_post_meta($order_id, 'pos_status_transition', true);
    $email_notifications = get_option('wc_pos_email_notifications');
    $order = wc_get_order($order_id);

    $mail = WC()->mailer();
    $customer_emails = array('customer_processing_order', 'customer_completed_order', 'customer_invoice');

    if ($email_notifications == 'yes') {
        set_email_actions();

        foreach($customer_emails as $email){
            add_filter('woocommerce_email_enabled_' . $email, '__return_false');
        }

        do_action('woocommerce_order_status_' . $status_tansition['from'] . '_to_' . $status_tansition['to'] . '_notification', $order_id, $order);

        foreach($customer_emails as $email){
            remove_filter('woocommerce_email_enabled_' . $email, '__return_false');
        }
    }

    if (!empty($order_email_receipt)) {

        switch ($order->get_status()) {
            case 'processing':
                $customer_email = $mail->emails['WC_Email_Customer_Processing_Order'];
                break;
            case 'on-hold':
                $customer_email = $mail->emails['WC_Email_Customer_On_Hold_Order'];
                break;
            case 'completed':
                $customer_email = $mail->emails['WC_Email_Customer_Completed_Order'];
                break;
            case 'cancelled':
                $customer_email = $mail->emails['WC_Email_Cancelled_Order'];
                break;
            case 'refunded':
                $customer_email = $mail->emails['WC_Email_Customer_Refunded_Order'];
                break;
            case 'failed':
                $customer_email = $mail->emails['WC_Email_Failed_Order'];
                break;
            default:
                break;
        }

        if(isset($customer_email)){
            /**
             * override filters to enable email and send only too the customer
             */
            add_filter('woocommerce_email_enabled_' . $customer_email->id, '__return_true');
            remove_all_filters('woocommerce_email_recipient_' . $customer_email->id);

            $order->set_billing_email($order_email_receipt);
            $customer_email->trigger($order_id, $order);

            remove_filter('woocommerce_email_enabled_' . $customer_email->id, '__return_true');
        }

    }

}

function pos_set_html_content_type()
{
    return 'text/html';
}

function isChangeUser($register_id = 0)
{
    if ($register_id) {
        $register_data = WC_POS()->register()->get_data($register_id);
        return $register_data[0]['settings']['email_receipt'];
    }
    return false;
}

function wc_pos_get_outlet_location($id_register = 0)
{
    $location = array();
    if (!$id_register && !isset($_GET['reg'])) return $location;

    if ($id_register > 0) {
        $data = WC_POS()->register()->get_data($id_register);
        $data = $data[0];
        $outlet_id = $data['outlet'];
        $outlet = WC_POS()->outlet()->get_data($outlet_id);
    } else {

        $slug = $_GET['reg'];
        $data = WC_POS()->register()->get_data_by_slug($slug);
        $data = $data[0];
        $slug = $data['slug'];
        $register = $slug;
        $outlet_id = $data['outlet'];
        $outlet = WC_POS()->outlet()->get_data($outlet_id);
    }
    if ($outlet) {
        $location = $outlet[0];
    }
    return $location;
}

function wc_pos_get_shop_location()
{
    return array(
        'country' => WC()->countries->get_base_country(),
        'state' => WC()->countries->get_base_state(),
        'postcode' => WC()->countries->get_base_postcode(),
        'city' => WC()->countries->get_base_city()
    );
}

function wc_pos_find_all_rates()
{
    global $wpdb;
    // Run the query

    $tax_class = '';
    $rates = array();
    $sql = "SELECT tax_rates.*
			FROM {$wpdb->prefix}woocommerce_tax_rates as tax_rates
			LEFT OUTER JOIN {$wpdb->prefix}woocommerce_tax_rate_locations as locations ON tax_rates.tax_rate_id = locations.tax_rate_id
			LEFT OUTER JOIN {$wpdb->prefix}woocommerce_tax_rate_locations as locations2 ON tax_rates.tax_rate_id = locations2.tax_rate_id
			GROUP BY tax_rate_id
			ORDER BY tax_rate_priority, tax_rate_order
		";
    $found_rates = $wpdb->get_results($sql);


    foreach ($found_rates as $key_rate => $found_rate) {

        $sql = "SELECT location_code FROM {$wpdb->prefix}woocommerce_tax_rate_locations WHERE tax_rate_id = {$found_rate->tax_rate_id} AND location_type = 'postcode' ";
        $found_postcodes = $wpdb->get_results($sql);
        $postcode = array();
        if ($found_postcodes)
            foreach ($found_postcodes as $code) {
                $postcode[] = $code->location_code;
            }

        $sql = "SELECT location_code FROM {$wpdb->prefix}woocommerce_tax_rate_locations WHERE tax_rate_id = {$found_rate->tax_rate_id} AND location_type = 'city' ";
        $found_postcodes = $wpdb->get_results($sql);
        $city = array();
        if ($found_postcodes)
            foreach ($found_postcodes as $code) {
                $city[] = $code->location_code;
            }

        $rates[$found_rate->tax_rate_id] = array(
            'rate' => (float)$found_rate->tax_rate,
            'label' => $found_rate->tax_rate_name,
            'shipping' => $found_rate->tax_rate_shipping ? 'yes' : 'no',
            'compound' => $found_rate->tax_rate_compound ? 'yes' : 'no',
            'country' => $found_rate->tax_rate_country,
            'state' => $found_rate->tax_rate_state,
            'city' => implode(';', $city),
            'postcode' => implode(';', $postcode),
            'taxclass' => $found_rate->tax_rate_class,
            'priority' => $found_rate->tax_rate_priority
        );
    }

    return $rates;
}


function wc_pos_get_register($id)
{
    global $wpdb;
    $table_name = $wpdb->prefix . "wc_poin_of_sale_registers";
    $reg = $wpdb->get_row("SELECT * FROM $table_name WHERE ID = $id LIMIT 1");
    if (isset($reg))
        return $reg;
    else
        return false;
}

function getOutletID($reg_id = 0)
{
    global $wpdb;
    if (!$reg_id) return 0;

    $db_data = wc_pos_get_register($reg_id);
    return $db_data->outlet;
}

function wc_pos_check_can_delete($type, $ids)
{
    global $wpdb;
    $table_reg = $wpdb->prefix . "wc_poin_of_sale_registers";
    switch ($type) {
        case 'outlet':
            if (is_array($ids)) {
                foreach ($ids as $key => $id) {
                    $result = $wpdb->get_results("SELECT ID FROM $table_reg WHERE outlet = $id");
                    if ($result)
                        unset($ids[$key]);
                }
                if (!empty($ids)) {
                    $ids = implode(',', array_map('intval', $ids));
                    return "WHERE ID IN ($ids)";
                }
            } else {
                $result = $wpdb->get_results("SELECT ID FROM $table_reg WHERE outlet = $ids");
                if (!$result)
                    return "WHERE ID = $ids";
            }
            return false;
            break;
        case 'grid':
            if (is_array($ids)) {
                foreach ($ids as $key => $id) {
                    $result = $wpdb->get_results("SELECT ID FROM $table_reg WHERE detail LIKE '%\"grid_template\":\"$id\"%' ");
                    if ($result)
                        unset($ids[$key]);
                }

                if (!empty($ids))
                    return $ids;
            } else {
                $result = $wpdb->get_results("SELECT ID FROM $table_reg WHERE detail LIKE '%\"grid_template\":\"$ids\"%' ");
                if (!$result)
                    return $ids;
            }
            return false;
            break;
        case 'receipt':
            $default_receipt = (int)get_option('_pos_default_receipt');
            if (is_array($ids)) {
                foreach ($ids as $key => $id) {
                    if ($default_receipt && $default_receipt == (int)$id) {
                        unset($ids[$key]);
                    } else {
                        $result = $wpdb->get_results("SELECT ID FROM $table_reg WHERE detail LIKE '%\"receipt_template\":\"$id\"%' ");
                        if ($result)
                            unset($ids[$key]);
                    }
                }

                if (!empty($ids))
                    return $ids;
            } else {
                if ($default_receipt && $default_receipt == (int)$ids) {
                    return false;
                }
                $result = $wpdb->get_results("SELECT ID FROM $table_reg WHERE detail LIKE '%\"receipt_template\":\"$ids\"%' ");
                if (!$result)
                    return $ids;
            }
            return false;
            break;

        default:
            return false;
            break;
    }
}

function pos_term_relationships()
{
    $relationships = array();
    $hierarchy = array();
    $parents = array();

    $args = array('orderby' => 'name', 'order' => 'ASC', 'fields' => 'all');
    $terms = get_terms('product_cat', $args);

    $relationships[0] = pos_get_non_cat_products();
    $out_of_stock = get_option('wc_pos_show_out_of_stock_products', 'no');
    $default_order = get_option('wc_pos_default_tile_orderby');

    if ($terms) {
        foreach ($terms as $term) {
            $term_id = (int)$term->term_id;
            $parent = (int)$term->parent;
            if ($parent == 0) {

                if (!isset($hierarchy[$term_id]))
                    $hierarchy[$term_id] = array();

                $parents[$term_id] = $term_id;
            }

            if ($parent > 0)
                $hierarchy[$parent][] = $term_id;

//            // Ordering
//            switch ($default_order) {
//                case 'date':
//                case 'price':
//                case 'popularity':
//                case 'rating':
//                    $ordering = WC()->query->get_catalog_ordering_args($default_order);
//                    break;
//                case 'price-desc':
//                    $ordering = WC()->query->get_catalog_ordering_args('price', 'DESC');
//                    break;
//
//                default:
//                    $ordering = WC()->query->get_catalog_ordering_args();
//                    break;
//            }
//
//            $args = array(
//                'posts_per_page' => -1,
//                'post_type' => 'product',
//                'fields' => 'ids',
//                'order' => $ordering['order'],
//                'orderby' => $ordering['orderby'],
//                'tax_query' => array(
//                    array(
//                        'taxonomy' => 'product_cat',
//                        'field' => 'term_id',
//                        'terms' => $term->term_id,
//                    ),
//                ),
//            );
//
//            if (isset($ordering['meta_key'])) {
//                $args['meta_key'] = $ordering['meta_key'];
//            }
//            $products = new WP_Query($args);
            $relationships[$term->term_id] = array();
//            if ($products->have_posts()) {
//                if ($out_of_stock != 'yes') {
//                    foreach ($products->posts as $key => $_id) {
//                        $product = wc_get_product($_id);
//
//                        if ($product && $product->is_in_stock()) {
//                            $relationships[$term->term_id][] = $_id;
//                        }
//                    }
//                } else {
//                    $relationships[$term->term_id] = $products->posts;
//                }
//            }

        }
    }

    return array(
        'relationships' => $relationships,
        'hierarchy' => $hierarchy,
        'parents' => $parents
    );
}

function pos_get_non_cat_products()
{
    global $wpdb;
    $products = array();

    $tax = "SELECT tax.term_taxonomy_id tax_id FROM {$wpdb->term_taxonomy} tax WHERE tax.taxonomy = 'product_cat' ";
    $taxonomy = $wpdb->get_results($tax);
    $t = array();
    if ($taxonomy) {
        foreach ($taxonomy as $tx) {
            $t[] = $tx->tax_id;
        }
    }
    if (!empty($t)) {
        $t = implode(',', $t);
    } else {
        $t = 0;
    }
    $query = "SELECT post.ID FROM {$wpdb->posts} post 
    LEFT JOIN {$wpdb->term_relationships} rel ON(rel.object_id = post.ID AND rel.term_taxonomy_id IN({$t}) )
    WHERE post.post_type = 'product' AND post.post_status = 'publish' AND rel.object_id IS NULL
    ";
    $result = $wpdb->get_results($query);
    if ($result) {
        foreach ($result as $value) {
            $products[] = (int)$value->ID;
        }
    }

    return $products;
}


function pos_get_registers_by_outlet($outlet_id = 0)
{
    global $wpdb;
    $registers = array();

    if ($outlet_id) {
        $table = $wpdb->prefix . 'wc_poin_of_sale_registers';
        $query = "SELECT ID FROM {$table} WHERE outlet = $outlet_id";
        $result = $wpdb->get_results($query);
        if ($result)
            foreach ($result as $reg)
                $registers[] = $reg->ID;

    }
    return $registers;
}

function pos_get_registers_by_cashier($cashier_id = 0)
{
    global $wpdb;
    $registers = array();

    if ($cashier_id) {
        $order_types = wc_get_order_types('order-count');
        $order_types = implode("','", $order_types);
        $query = "SELECT CAST( meta_register_id.meta_value as SIGNED) as register_id
									FROM {$wpdb->posts} AS posts
									LEFT JOIN {$wpdb->postmeta} AS meta_register_id ON ( posts.ID = meta_register_id.post_id AND meta_register_id.meta_key = 'wc_pos_id_register' )
									LEFT JOIN {$wpdb->postmeta} AS meta_order_type ON ( posts.ID = meta_order_type.post_id AND meta_order_type.meta_key = 'wc_pos_order_type' )
      						WHERE posts.post_type IN ( '{$order_types}' )
      						AND posts.post_author = {$cashier_id}
        					AND meta_order_type.meta_value = 'POS' GROUP BY register_id";

        $result = $wpdb->get_results($query);

        if ($result)
            foreach ($result as $reg)
                $registers[] = (int)$reg->register_id;

    }

    return $registers;
}

function pos_enable_generate_password($value)
{
    return 'yes';
}

/**
 * Get an array of product attribute taxonomies.
 *
 * @access public
 * @return array
 */
function pos_get_attribute_taxonomy_names()
{
    $taxonomy_names = array();
    $attribute_taxonomies = wc_get_attribute_taxonomies();
    if ($attribute_taxonomies) {
        foreach ($attribute_taxonomies as $tax) {
            $taxonomy_names[wc_attribute_taxonomy_name($tax->attribute_name)] = $tax->attribute_name;
        }
    }
    return $taxonomy_names;
}

function pos_get_user_html($user_to_add)
{
    if ($user_to_add > 0) {
        $customer = new WP_User($user_to_add);

        $b_addr = array(
            'first_name' => $customer->billing_first_name,
            'last_name' => $customer->billing_last_name,
            'company' => $customer->billing_company,
            'address_1' => $customer->billing_address_1,
            'address_2' => $customer->billing_address_2,
            'city' => $customer->billing_city,
            'state' => $customer->billing_state,
            'postcode' => $customer->billing_postcode,
            'country' => $customer->billing_country,
            'email' => $customer->billing_email,
            'phone' => $customer->billing_phone,
        );
        $s_addr = array(
            'first_name' => $customer->shipping_first_name,
            'last_name' => $customer->shipping_last_name,
            'company' => $customer->shipping_company,
            'address_1' => $customer->shipping_address_1,
            'address_2' => $customer->shipping_address_2,
            'city' => $customer->shipping_city,
            'state' => $customer->shipping_state,
            'postcode' => $customer->shipping_postcode,
            'country' => $customer->shipping_country,
        );

        $user_data = array(
            'first_name' => $customer->first_name,
            'last_name' => $customer->last_name,
            'email' => $customer->user_email
        );

        if (empty($b_addr['first_name'])) {
            $b_addr['first_name'] = $user_data['first_name'];
        }
        if (empty($b_addr['last_name'])) {
            $b_addr['last_name'] = $user_data['last_name'];
        }
        if (empty($b_addr['email'])) {
            $b_addr['email'] = $user_data['email'];
        }
    }
    ob_start();
    require(WC_POS()->plugin_path() . '/includes/views/html-admin-registers-customer.php');
    $out = ob_get_contents();
    return $out;
}



if (!function_exists('is_pos')) {
    function is_pos()
    {
        if ( is_callable( array( 'WP_Query', 'get_posts' ) ) ) {
            global $wp;
            if ( isset( $wp->query_vars ) ) {
                $q = $wp->query_vars;
                if ( isset( $q['page'] ) && $q['page'] == 'wc_pos_registers' && isset( $q['action'] ) && $q['action'] == 'view' ) {
                    return true;
                }
            }

            return false;
        }

    }
}

function wc_pos_get_available_payment_gateways()
{
    /*$available = array();
    $gateways  = WC()->payment_gateways->payment_gateways();
    $enabled   = get_option( 'pos_enabled_gateways', array() );
    foreach ($gateways as $key => $gateway) {
        if( in_array($key, $enabled)){
            $available[$key] = $gateway;
        }
    }
    return $available;*/

    $enabled_gateways = get_option('pos_enabled_gateways', array());
    $payment_gateways = array();
    $load_gateways = array();

    foreach (WC()->payment_gateways()->get_available_payment_gateways() as $gateway) {
        $g = $gateway;
        $g->title = $gateway->get_title();
        $load_gateways[esc_attr($gateway->id)] = $g;
    }
    $load_gateways['pos_chip_pin'] = (object)array('id' => 'pos_chip_pin', 'title' => (get_option('pos_chip_pin_name') ? get_option('pos_chip_pin_name'): __('Chip & PIN', 'wc_point_of_sale')));
    $load_gateways['pos_chip_pin2'] = (object)array('id' => 'pos_chip_pin2', 'title' => (get_option('pos_chip_pin2_name') ? get_option('pos_chip_pin2_name'): __('Chip & PIN 2', 'wc_point_of_sale')));
    $load_gateways['pos_chip_pin3'] = (object)array('id' => 'pos_chip_pin3', 'title' => (get_option('pos_chip_pin3_name') ? get_option('pos_chip_pin3_name'): __('Chip & PIN 3', 'wc_point_of_sale')));

    // Get sort order option
    $ordering = (array)get_option('pos_exist_gateways');

    $order_end = 999;

    // Load gateways in order
    foreach ($load_gateways as $id => $load_gateway) {
        if (!in_array($id, $enabled_gateways)) {
            continue;
        }
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
    return $payment_gateways;
}

function wc_pos_tax_enabled()
{
    $pos_calc_taxes = get_option('woocommerce_pos_tax_calculation', 'enabled');
    if ($pos_calc_taxes == 'enabled' && wc_tax_enabled()) {
        return true;
    } else {
        return false;
    }
}


function pos_localize_script($script_name = '')
{
    $pos_tax_based_on = get_option('woocommerce_pos_calculate_tax_based_on', 'outlet');
    if ($pos_tax_based_on == 'default') {
        $pos_tax_based_on = get_option('woocommerce_tax_based_on');
    }

    wp_localize_script($script_name, 'wc_pos_params', apply_filters('wc_pos_params', array(
        'wp_debug' => defined('WP_DEBUG') ? WP_DEBUG : false,
        'avatar' => get_avatar(0, 30),
        'sound_path' => WC_POS()->plugin_sound_url(),
        'ajax_url' => WC()->ajax_url(),
        'admin_url' => admin_url(),
        'ajax_loader_url' => apply_filters('woocommerce_ajax_loader_url', WC()->plugin_url() . '/assets/images/ajax-loader@2x.gif'),
        'reprint_receipt_url' => wp_nonce_url(admin_url('admin.php?print_pos_receipt=true&order_id=_order_id_'), 'print_pos_receipt'),
        'post_id' => isset($post->ID) ? $post->ID : '',
        'def_img' => wc_placeholder_img_src(),
        'spinner' => WC_POS()->plugin_url() . '/assets/images/spinner.gif',
        'custom_pr_id' => (int)get_option('wc_pos_custom_product_id'),
        'attr_tax_names' => pos_get_attribute_taxonomy_names(),
        'hidden_order_itemmeta' => array_flip(apply_filters('woocommerce_hidden_order_itemmeta', array(
            '_qty',
            '_tax_class',
            '_product_id',
            '_variation_id',
            '_line_subtotal',
            '_line_subtotal_tax',
            '_line_tax_data',
            '_line_total',
            '_line_tax',
        ))),

        'new_update_pos_outlets_address_nonce' => wp_create_nonce("new-update-pos-outlets-address"),
        'edit_update_pos_outlets_address_nonce' => wp_create_nonce("edit-update-pos-outlets-address"),
        'search_variations_for_product' => wp_create_nonce("search_variations_for_product"),
        'printing_receipt_nonce' => wp_create_nonce("printing_receipt"),
        'add_product_to_register' => wp_create_nonce("add_product_to_register"),
        'remove_product_from_register' => wp_create_nonce("remove_product_from_register"),
        'add_customers_to_register' => wp_create_nonce("add_customers_to_register"),
        'check_shipping' => wp_create_nonce("check_shipping"),
        'load_order_data' => wp_create_nonce("load_order_data"),
        'load_pending_orders' => wp_create_nonce("load_pending_orders"),
        'search_products_and_variations' => wp_create_nonce("search-products"),
        'add_product_grid' => wp_create_nonce("add-product_grid"),
        'search_customers' => wp_create_nonce("search-customers"),
        'void_register_nonce' => wp_create_nonce("void_register"),

        'remove_item_notice' => __("Are you sure you want to remove the selected items?", 'wc_point_of_sale'),


        'barcode_url' => plugins_url('includes/lib/barcode/image.php?filetype=PNG&dpi=72&scale=2&rotation=0&font_family=0&thickness=30&start=NULL&code=BCGcode128', WC_POS_FILE)
    )));
}

function wc_pos_woocommerce_version_check($version = '2.5')
{
    global $woocommerce;

    if ($woocommerce && version_compare($woocommerce->version, $version, ">=")) {
        return true;
    }
    return false;
}

function wc_pos_clear_transient()
{
    global $wpdb;

    $wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_wc_pos%';");
}

function is_pos_referer()
{
    $referer = wp_get_referer();
    $pos_url = get_home_url() . "/point-of-sale/";

    if (strpos($referer, $pos_url) !== false) {
        return true;
    }
    return false;
}


function pos_get_acf_fields()
{

    if(!is_plugin_active( 'advanced-custom-fields/acf.php' )){
        return array();
    }

    add_filter('acf/location/rule_match/ef_crm_customers', '__return_true');

    $acf_fields = array();
    $acfs = wc_pos_get_acf_field_groups(array(
        'ef_crm_customers' => true,
        'user_role' => true,
        'user_form' => true,
        'post_type' => 'shop_order'
    ));
    if ($acfs) {
        foreach ($acfs as $acf) {
            $fields = acf_get_fields( $acf );
            foreach ($fields as $field) {
                $acf_fields[] = $field['name'];
            }
        }
    }
    remove_filter('acf/location/rule_match/ef_crm_customers', '__return_true');
    return $acf_fields;
}

function pos_get_acf_order_fields()
{
    if(!is_plugin_active( 'advanced-custom-fields/acf.php' )){
        return array();
    }

    $acf_fields = array();
    $acfs = wc_pos_get_acf_field_groups(array(
        'post_type' => 'shop_order'
    ));
    if ($acfs) {
        foreach ($acfs as $acf) {
            $fields = acf_get_fields($acf);
            foreach ($fields as $field) {
                $acf_fields[] = $field['name'];
            }
        }
    }
    return $acf_fields;
}

function pos_get_custom_order_fields()
{
    $custom_fields = array();
    if (function_exists('wc_admin_custom_order_fields')) {
        foreach (wc_admin_custom_order_fields()->get_order_fields() as $field_id => $field) {

            $custom_fields[] = '_wc_acof_' . $field_id;
        }
    }
    return $custom_fields;
}


function pos_close_register($register_id = 0, $force = false)
{
    global $wpdb;

    if ($register_id) {
        $table_name = $wpdb->prefix . "wc_poin_of_sale_registers";
        $db_data = $wpdb->get_results("SELECT * FROM $table_name WHERE ID = $register_id");

        if ($db_data && 0 != ($user_id = get_current_user_id())) {
            $row = $db_data[0];

            $lock_user = $row->_edit_last;
            if ($force || $lock_user == $user_id) {
                $now = current_time('mysql');
                $data['closed'] = $now;
                $data['_edit_last'] = $user_id;
                $wpdb->update($table_name, $data, array('ID' => $register_id));

                $db_data[0]->closed = $now;
                $db_data[0]->forced = $force ? true : false;

                $session = new WC_Pos_Session_Reports($db_data[0], $lock_user);

                return $session->save();
            }
        }
    }
    return false;
}

function isMobilePOS()
{
    $is_mobile = false;
    $useragent = $_SERVER['HTTP_USER_AGENT'];
    if (preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i', $useragent) || preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i', substr($useragent, 0, 4)))
        $is_mobile = true;
    return $is_mobile;
}

//TODO: States array bug with no customer in POS
function get_single_country_states($key, $args, $current_cc, $value = null)
{
    $field = '';
    $field_container = '<p class="form-row %1$s" id="%2$s">%3$s</p>';
    if ($args['required']) {
        $args['class'][] = 'validate-required';
        $required = ' <abbr class="required" title="' . esc_attr__('required', 'woocommerce') . '">*</abbr>';
    } else {
        $required = '';
    }
    foreach ($current_cc as $code => $value) {
        $states = WC()->countries->get_states($code);
    }

    if (is_array($states) && empty($states)) {

        $field_container = '<p class="form-row %1$s" id="%2$s" style="display: none">%3$s</p>';

        $field .= '<input type="hidden" class="hidden" name="' . esc_attr($key) . '" id="custom_shipping_state" placeholder="' . esc_attr($args['placeholder']) . '" />';

    } elseif (is_array($states)) {

        $field .= '<select name="' . esc_attr($key) . '" id="custom_shipping_state" class="state_select" data-placeholder="' . esc_attr($args['placeholder']) . '">
						<option value="">' . esc_html__('Select a state&hellip;', 'woocommerce') . '</option>';

        foreach ($states as $ckey => $cvalue) {
            $field .= '<option value="' . esc_attr($ckey) . '" ' . selected($value, $ckey, false) . '>' . $cvalue . '</option>';
        }

        $field .= '</select>';

    } else {

        $field .= '<input type="text" class="input-text value="' . esc_attr($value) . '"  placeholder="' . esc_attr($args['placeholder']) . '" name="' . esc_attr($key) . '" id="custom_shipping_state" />';

    }

    if (!empty($field)) {
        $field_html = '';

        if ($args['label'] && 'checkbox' != $args['type']) {
            $field_html .= '<label for="custom_shipping_state" class="shipping_state">' . $args['label'] . $required . '</label>';
        }

        $field_html .= $field;

        if (isset($args['description']) && $args['description']) {
            $field_html .= '<span class="description">' . esc_html($args['description']) . '</span>';
        }

        $container_class = esc_attr(implode(' ', $args['class']));
        $container_id = (isset($args['id'])) ? esc_attr($args['id']) . '_field' : '';
        $field = sprintf($field_container, $container_class, $container_id, $field_html);
    }
    echo $field;
}

function pos_get_all_shipping_methods()
{
    global $wpdb;
    $result = array();
    $shipping = array();
    $sql = "SELECT `option_name`,`option_value` FROM {$wpdb->options} 
            WHERE `option_name` LIKE 'woocommerce_flat_rate%' 
            OR `option_name` LIKE 'woocommerce_free_shipping_%'
            OR `option_name` LIKE 'woocommerce_local_pickup_%'
            ORDER BY `option_name`";
    $result = $wpdb->get_results($sql);
    if ($result) {
        foreach ($result as $key => $res) {
            $id = pos_get_shipping_method_id($res->option_name);
            $s_data = (array) unserialize($res->option_value);
            $shipping[$id] = $s_data;
        }
    }
    return $shipping;
}

function get_tabs($register_id)
{
    global $wpdb;
    $result = array();
    $res = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}wc_poin_of_sale_tabs WHERE `register_id` = {$register_id}");
    foreach ($res as $val) {
        $result[$val->tab_number] = $val;
        $result[$val->tab_number]->time = time() - strtotime($val->opened);
    }
    return $result;
}

function get_tabs_without_register()
{
    global $wpdb;
    $result = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}wc_poin_of_sale_tabs WHERE `register_id` = 0");
    return $result;
}

function wc_crm_remove_filters_for_anonymous_class( $hook_name = '', $class_name = '', $method_name = '', $priority = 0 ) {
    global $wp_filter;
    // Take only filters on right hook name and priority
    if ( ! isset( $wp_filter[ $hook_name ][ $priority ] ) || ! is_array( $wp_filter[ $hook_name ][ $priority ] ) ) {
        return false;
    }
    // Loop on filters registered
    foreach ( (array) $wp_filter[ $hook_name ][ $priority ] as $unique_id => $filter_array ) {
        // Test if filter is an array ! (always for class/method)
        if ( isset( $filter_array['function'] ) && is_array( $filter_array['function'] ) ) {
            // Test if object is a class, class and method is equal to param !
            if ( is_object( $filter_array['function'][0] ) && get_class( $filter_array['function'][0] ) && get_class( $filter_array['function'][0] ) == $class_name && $filter_array['function'][1] == $method_name ) {
                // Test for WordPress >= 4.7 WP_Hook class (https://make.wordpress.org/core/2016/09/08/wp_hook-next-generation-actions-and-filters/)
                if ( is_a( $wp_filter[ $hook_name ], 'WP_Hook' ) ) {
                    unset( $wp_filter[ $hook_name ]->callbacks[ $priority ][ $unique_id ] );
                } else {
                    unset( $wp_filter[ $hook_name ][ $priority ][ $unique_id ] );
                }
            }
        }
    }
    return false;
}


/**
 * override ACF acf_get_field_groups function as it uses `suppress_filters`
 * @param array $args
 * @return array|mixed
 */
function wc_pos_get_acf_field_groups($args = array()){

    $field_groups = array();
    $cache_key = acf_cache_key( "acf_get_field_group_posts" );
    $post_ids = wp_cache_get( $cache_key, 'acf' );
    if( $post_ids === false ) {

        $posts = get_posts(array(
            'posts_per_page'			=> -1,
            'post_type'					=> 'acf-field-group',
            'orderby'					=> 'menu_order title',
            'order'						=> 'ASC',
            'cache_results'				=> true,
            'update_post_meta_cache'	=> false,
            'update_post_term_cache'	=> false,
            'post_status'				=> array('publish', 'acf-disabled'),
        ));

        $post_ids = array();
        foreach( $posts as $post ) {
            $post_ids[] = $post->ID;
        }

        wp_cache_set( $cache_key, $post_ids, 'acf' );
    }

    $raw_field_groups = array();
    foreach( $post_ids as $post_id ) {
        $raw_field_groups[] = acf_get_raw_field_group( $post_id );
    }

    if( $raw_field_groups ) {
        foreach( $raw_field_groups as $raw_field_group ) {
            $field_groups[] = acf_get_field_group( $raw_field_group['ID'] );
        }
    }


    $field_groups = apply_filters( 'acf/load_field_groups', $field_groups );
    $field_groups = apply_filters( 'acf/get_field_groups', $field_groups );

    // Filter results.
    if( $args ) {
        return acf_filter_field_groups( $field_groups, $args );
    }

    // Return field groups.
    return $field_groups;
}

function wc_pos_merge_acf_field_data($data = array()){
    $field = array(
        'type'              => isset($data['type']) ? $data['type']: 'text',
        'label'             => isset($data['label']) ? $data['label']: '',
        'description'       => isset($data['instructions']) ? $data['instructions']: '',
        'placeholder'       => isset($data['placeholder']) ? $data['placeholder']: '',
        'maxlength'         => isset($data['maxlength']) ? $data['maxlength']: false,
        'required'          => isset($data['maxlength']) ? $data['maxlength']: false,
        'autocomplete'      => isset($data['maxlength']) ? $data['maxlength']: false,
        'id'                => isset($data['name']) ? $data['name']: false,
        'class'             => isset($data['class']) ? explode(" ", $data['class']): array(),
        'label_class'       => array(),
        'input_class'       => array(),
        'return'            => false,
        'options'           => isset($data['choices']) ? $data['choices']: array(),
        'custom_attributes' => array(),
        'validate'          => array(),
        'default'           => isset($data['default_value']) ? $data['default_value']: array(),
        'autofocus'         => '',
        'priority'          => '',
    );

    if($field['type'] === 'select'){
        $field['input_class'][] = 'wc-enhanced-select';
    }

    return $field;
}

function get_merged_acf_array($field){
    $defaults = array(
        'type'              => isset($field['type']) ? $field['type'] : 'text',
        'description'       => $field['instructions'],
        'id'                => 'acf-field-'.$field['name'],
        'class'             => isset($field['class']) ? array($field['class']) : array(),
        'label_class'       => array(),
        'input_class'       => array(),
        'return'            => false,
        'options'           => isset($field['choices']) ? $field['choices'] : array(),
        'custom_attributes' => array(),
        'default'           => isset($field['default_value']) ? $field['default_value'] : '',
        'value'             => '{{ acf_fields.'.$field["name"].' }}'
    );

    switch ($defaults['type']) {
        case 'wysiwyg' :
            $defaults['type'] = 'textarea';
            break;
        case 'true_false' :
            $defaults['type']    = 'checkbox';
            $defaults['options'] = array(1 => $field['message']);
            break;
        case 'color_picker' :
            $defaults['class'][] = 'acf-color_picker';
            $defaults['type'] = 'text';
            break;
        case 'page_link':
        case 'post_object':
        case 'user':
            $defaults['class'][] = 'wc-enhanced-select';
            break;
        case 'taxonomy':
            $defaults['type'] = $field['field_type'];
            if( $defaults['type'] == 'multi_select'){
                $defaults['type']  = 'select';
                $field['multiple'] = 1;
            }
            $defaults['options'] = array();
            $terms = get_terms( array(
                'taxonomy' => $field['taxonomy'],
                'hide_empty' => false,
            ) );
            if( $terms ){
                foreach ($terms as $term) {
                    $defaults['options'][$term->term_id] = $term->name;
                }
            }
            break;
    }
    $custom_attributes = array('rows', 'multiple');
    $intersect = array_intersect($custom_attributes, array_keys($field) );
    if( !empty($intersect) ){
        foreach ($intersect as $attr_key) {
            switch ($attr_key) {
                case 'multiple':
                    if( $field[$attr_key] > 0){
                        $defaults['custom_attributes']['multiple'] = 'multiple';
                    }
                    break;
                default:
                    $defaults['custom_attributes'][$attr_key] = $field[$attr_key];
                    break;
            }
        }
    }
    if( $defaults['type'] == 'select' ){
        $data_attributes = array('allow_null', 'multiple');
        $intersect = array_intersect($data_attributes, array_keys($field) );
        if( !empty($intersect) ){
            foreach ($intersect as $data_key) {
                switch ($data_key) {
                    case 'multiple':
                        if( $field[$data_key] > 0){
                            $defaults['custom_attributes']['data-multiple'] = true;
                        }
                        break;
                    case 'allow_null':
                        $defaults['custom_attributes']['data-allow_clear'] = $field[$data_key] > 0 ? true : false;
                        break;
                    default:
                        $defaults['custom_attributes']['data-' . $data_key] = $field[$data_key];
                        break;
                }
            }
        }
        $defaults['input_class'] = array('wc-enhanced-select');
    }
    return array_merge($field, $defaults);
}


/**
 * @param WP_Query $object
 */
function pos_unset_suppress_filters($object){
    $object->query_vars['suppress_filters'] = true;
}

function pos_get_shipping_method_id($title){
    if(strpos($title, 'flat_rate') !== false){
        return 'flat_rate';
    }

    if(strpos($title, 'free_shipping') !== false){
        return 'free_shipping';
    }

    if(strpos($title, 'local_pickup') !== false){
        return 'local_pickup';
    }

    return $title;
}

function set_email_actions()
{
    $email_actions = apply_filters(
        'woocommerce_email_actions', array(
            'woocommerce_low_stock',
            'woocommerce_no_stock',
            'woocommerce_product_on_backorder',
            'woocommerce_order_status_pending_to_processing',
            'woocommerce_order_status_pending_to_completed',
            'woocommerce_order_status_processing_to_cancelled',
            'woocommerce_order_status_pending_to_failed',
            'woocommerce_order_status_pending_to_on-hold',
            'woocommerce_order_status_failed_to_processing',
            'woocommerce_order_status_failed_to_completed',
            'woocommerce_order_status_failed_to_on-hold',
            'woocommerce_order_status_cancelled_to_processing',
            'woocommerce_order_status_cancelled_to_completed',
            'woocommerce_order_status_cancelled_to_on-hold',
            'woocommerce_order_status_on-hold_to_processing',
            'woocommerce_order_status_on-hold_to_cancelled',
            'woocommerce_order_status_on-hold_to_failed',
            'woocommerce_order_status_completed',
            'woocommerce_order_fully_refunded',
            'woocommerce_order_partially_refunded',
            'woocommerce_new_customer_note',
            'woocommerce_created_customer',
        )
    );

    if ( apply_filters( 'woocommerce_defer_transactional_emails', false ) ) {
        WC_Emails::instance()->background_emailer = new WC_Background_Emailer();

        foreach ( $email_actions as $action ) {
            add_action( $action, array( 'WC_Emails', 'queue_transactional_email' ), 10, 10 );
        }
    } else {
        foreach ( $email_actions as $action ) {
            add_action( $action, array( 'WC_Emails', 'send_transactional_email' ), 10, 10 );
        }
    }

}