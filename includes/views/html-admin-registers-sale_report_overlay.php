<script type="text/javascript">
    var register_id = <?php echo $_GET['report']; ?>
</script>
<?php
global $wpdb;
$canceled_orders = array();
$saved_orders = array();
$refunded_orders = array();
$ids = array();
$report_opened = $data['opened'];
$report_closed = $data['closed'];

$save_order_status = get_option('wc_pos_save_order_status', 'pending');
$save_order_status = 'wc-' === substr($save_order_status, 0, 3) ? substr($save_order_status, 3) : $save_order_status;

$sql = "SELECT ID, post_status, post_parent FROM {$wpdb->posts}
        INNER JOIN {$wpdb->postmeta} reg_id
        ON ( reg_id.post_id = {$wpdb->posts}.ID AND reg_id.meta_key = 'wc_pos_id_register' AND reg_id.meta_value = $rg_id )
        WHERE ({$wpdb->posts}.post_type='shop_order' || {$wpdb->posts}.post_type='shop_order_refund') AND ({$wpdb->posts}.post_date BETWEEN '$report_opened' AND '$report_closed') ";
$results = $wpdb->get_results($sql);
$payment_methods = array();
$payment_totals = array();
?>
<div id="sale_report_popup_inner">
    <h3><?php echo get_option('wc_pos_report_title', __('End of Day Report', 'wc_point_of_sale'));?></h3>
    <table class="wp-list-table widefat fixed striped posts endofday">
        <tr>
            <th class="first-col"><?php _e('Report ID:', 'wc_point_of_sale'); ?></th>
            <td>

                <?php
                $query = "SELECT id FROM {$wpdb->prefix}wc_point_of_sale_sale_reports 
                              WHERE opened = '{$report_opened}' AND closed = '{$report_closed}' AND register_id = {$rg_id}";
                $r_id = $wpdb->get_var($query, 0, 0);
                echo "#" . $r_id;
                ?>

            </td>
            <th class="first-col"><?php _e('Register:', 'wc_point_of_sale'); ?></th>
            <td><?php echo $data['name']; ?></td>
        </tr>
        <tr>
            <th class="first-col"><?php _e('Outlet:', 'wc_point_of_sale'); ?></th>
            <td><?php echo $outlet; ?></td>
            <?php if (!empty($data["detail"]["tax_number"])) : ?>
                <th class="first-col"><?php _e('Tax Number:', 'wc_point_of_sale'); ?></th>
                <td>
                    <?php echo $data["detail"]["tax_number"]; ?>
                </td>
            <?php endif; ?>

        </tr>
        <tr>
            <th class="first-col"><?php _e('Opened:', 'wc_point_of_sale'); ?></th>
            <td><?php
                echo date_i18n(__('jS F Y', 'woocommerce'), strtotime($data['opened'])) . "\n";
                _e(' at ', 'wc_point_of_sale');
                echo date_i18n(__('g:i:s A', 'woocommerce'), strtotime($data['opened'])) . "\n";
                ?></td>
            <th class="first-col"><?php _e('Closed:', 'wc_point_of_sale'); ?></th>
            <td><?php
                echo date_i18n(__('jS F Y', 'woocommerce'), strtotime($data['closed'])) . "\n";
                _e(' at ', 'wc_point_of_sale');
                echo date_i18n(__('g:i:s A', 'woocommerce'), strtotime($data['closed'])) . "\n";
                ?></td>
        </tr>

        <?php if (count($results)) : ?>
            <tr>
                <th class="first-col"><?php _e('First Order #:', 'wc_point_of_sale'); ?></th>
                <td>
                    <?php echo $results[0]->ID;?>
                </td>
                <th class="first-col"><?php _e('Last Order #:', 'wc_point_of_sale'); ?></th>
                <td>
                    <?php echo $results[count($results) - 1]->ID; ?>
                </td>
            </tr>
            <tr style="vertical-align: top">
                <th class="first-col"><?php _e('Number Of Orders:', 'wc_point_of_sale'); ?></th>
                <td colspan="3" style="padding-top: 8px;">

                    <?php
                    $selected_status = get_option('wc_pos_report_order_status', array());
                    if(!count($selected_status)){
                        $selected_status = array('processing');
                    }
                    $ids = array_map(function($result){
                        return $result->ID;
                    }, $results);
                    $selected_status = array_fill_keys($selected_status, 0);
                    foreach ($results as $result){
                        $key = str_replace('wc-', '', $result->post_status);
                        if(array_key_exists($key, $selected_status)){
                            $selected_status[$key]++;
                        }
                    }
                    $string = '';
                    foreach ($selected_status as $key => $val){
                        if($val < 1) continue;
                        $string .= $val . ' - ' . ucfirst($key) . '<br/>';
                    }
                    echo !empty($string) ? $string : count($results);
                    ?>

                </td>
            </tr>
        <?php endif; ?>

        <tr>
            <th class="first-col"><?php _e('Sales Value:', 'wc_point_of_sale'); ?></th>
            <td>

                <?php
                if(count($ids)){
                    $amount_cod = "pm1.meta_value - (CASE SIGN(pm2.meta_value) WHEN -1 THEN 0 ELSE pm2.meta_value END)";
                    $order_total_paid = "CASE {$amount_cod} WHEN 0 THEN pm.meta_value ELSE {$amount_cod} END";
                    $query = "SELECT ROUND(SUM({$order_total_paid}), 2) FROM {$wpdb->posts} p
                                  JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_order_total'
                                  JOIN {$wpdb->postmeta} pm1 ON p.ID = pm1.post_id AND pm1.meta_key = 'wc_pos_amount_pay'
                                  JOIN {$wpdb->postmeta} pm2 ON p.ID = pm2.post_id AND pm2.meta_key = 'wc_pos_amount_change'
                                  WHERE (p.post_type = 'shop_order' AND p.ID IN (".implode(",", $ids)."))
                                  OR (p.post_type = 'shop_order_refund' AND p.post_status != 'wc-refunded' AND p.post_parent IN (".implode(",", $ids)."))";
                    $result = $wpdb->get_var($query, 0, 0);
                    echo wc_price($result);
                }else{
                    echo "0";
                }
                ?>

            </td>
            <th class="first-col"><?php _e('Products Sold:', 'wc_point_of_sale'); ?></th>
            <td>

                <?php
                if(count($ids)){
                    $query = "SELECT SUM(woim.meta_value) FROM {$wpdb->posts} p
                                JOIN {$wpdb->prefix}woocommerce_order_items woi ON p.ID = woi.order_id AND woi.order_item_type = 'line_item'
                                JOIN {$wpdb->prefix}woocommerce_order_itemmeta woim ON woi.order_item_id = woim.order_item_id AND woim.meta_key = '_qty'
                                WHERE (p.post_type = 'shop_order' AND p.post_status != 'wc-refunded' AND p.ID IN (".implode(",", $ids)."))
                                OR (p.post_type = 'shop_order_refund' AND p.post_status != 'wc-refunded' AND p.post_parent IN (".implode(",", $ids)."))";
                    $total_product = $wpdb->get_var($query, 0, 0);
                    echo $total_product;
                }else{
                    echo "0";
                }
                ?>

            </td>
        </tr>

        <tr>
            <th class="first-col"><?php _e('Returned Value:', 'wc_point_of_sale'); ?></th>
            <td>

                <?php
                if(count($ids)){
                    $query = "SELECT ROUND(SUM(pm.meta_value), 2) FROM {$wpdb->posts} p
                                  JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_order_total'
                                  WHERE p.post_type = 'shop_order_refund' AND p.post_status != 'wc-refunded' AND p.post_parent IN (".implode(",", $ids).")";
                    $result = $wpdb->get_var($query, 0, 0);
                    echo wc_price($result);
                }else{
                    echo "0";
                }
                ?>

            </td>
            <th class="first-col"><?php _e('Products Returned:', 'wc_point_of_sale'); ?></th>
            <td>

                <?php
                if(count($ids)){
                    $query = "SELECT SUM(ABS(woim.meta_value)) FROM {$wpdb->posts} p
                                JOIN {$wpdb->prefix}woocommerce_order_items woi ON p.ID = woi.order_id AND woi.order_item_type = 'line_item'
                                JOIN {$wpdb->prefix}woocommerce_order_itemmeta woim ON woi.order_item_id = woim.order_item_id AND woim.meta_key = '_qty'
                                WHERE (p.post_type = 'shop_order' AND p.post_status = 'wc-refunded' AND p.ID IN (".implode(",", $ids)."))
                                OR (p.post_type = 'shop_order_refund' AND p.post_status != 'wc-refunded' AND p.post_parent IN (".implode(",", $ids)."))";
                    $total_product = $wpdb->get_var($query, 0, 0);
                    echo $total_product ? $total_product : 0;
                }else{
                    echo "0";
                }
                ?>

            </td>
        </tr>
        <tr>
            <th class="first-col"><?php _e('Discounts Value:', 'wc_point_of_sale'); ?></th>
            <td>

                <?php
                if(count($ids)){
                    $query = "SELECT ROUND(SUM(pm.meta_value + pm2.meta_value), 2) FROM {$wpdb->posts} p
                                  JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_cart_discount'
                                  JOIN {$wpdb->postmeta} pm2 ON p.ID = pm2.post_id AND pm2.meta_key = '_cart_discount_tax' 
                                  WHERE p.post_type = 'shop_order' AND p.post_status != 'wc-refunded' AND p.ID IN (".implode(",", $ids).")";
                    $result = $wpdb->get_var($query, 0, 0);
                    echo wc_price($result);
                }else{
                    echo "0";
                }
                ?>

            </td>
        </tr>
    </table>
    <?php
    $tax_sales = array();
    if(count($ids)){
        $query = "SELECT woim_2.meta_value as tax_label, woim.meta_value as tax_amount, woim_1.meta_value as tax_rate FROM {$wpdb->posts} p
                  JOIN {$wpdb->prefix}woocommerce_order_items woi ON p.ID = woi.order_id AND woi.order_item_type = 'tax'
                  JOIN {$wpdb->prefix}woocommerce_order_itemmeta woim ON woi.order_item_id = woim.order_item_id AND woim.meta_key = 'tax_amount'
                  JOIN {$wpdb->prefix}woocommerce_order_itemmeta woim_1 ON woi.order_item_id = woim_1.order_item_id AND woim_1.meta_key = 'rate_id'
                  JOIN {$wpdb->prefix}woocommerce_order_itemmeta woim_2 ON woi.order_item_id = woim_2.order_item_id AND woim_2.meta_key = 'label'
                  WHERE (p.post_type = 'shop_order' AND p.post_status != 'wc-refunded' AND p.ID IN (" .implode(",", $ids). "))
                  OR (p.post_type = 'shop_order_refund' AND p.post_status != 'wc-refunded' AND p.post_parent IN (" .implode(",", $ids). "))
                  GROUP BY woim.order_item_id";
        $tax_sales = $wpdb->get_results($query, ARRAY_A);
    }
    ?>
    <h3><?php _e('Sales', 'wc_point_of_sale'); ?></h3>
    <table class="wp-list-table widefat fixed striped posts">
        <thead>
        <tr>
            <th class="manage-column column-order_customer" scope="col">
                <?php _e('Order', 'wc_point_of_sale'); ?>
            </th>
            <th class="manage-column column-order-date" scope="col">
                <?php _e('Date', 'wc_point_of_sale'); ?>
            </th>
            <th class="manage-column column-order-time" scope="col">
                <?php _e('Time', 'wc_point_of_sale'); ?>
            </th>
            <th class="manage-column column-order_total" style="width: 25%;" scope="col">
                <?php _e('Total', 'wc_point_of_sale'); ?>
            </th>
        </tr>
        </thead>
        <tbody>
        <?php if ($results) {
            foreach ($results as $value) {
                if($value->post_status == 'wc-refunded'){
                    $refunded_orders[] = $value->ID;
                    continue;
                }
                if ($value->post_status == 'wc-cancelled') {
                    $canceled_orders[] = $value->ID;
                    continue;
                }
                if ($value->post_status == 'wc-' . $save_order_status) {
                    $saved_orders[] = $value->ID;
                    continue;
                }
                $the_order = new WC_Order($value->ID);
                ?>
                <tr>
                    <td>
                        <?php

                        echo '<div class="tips" >';

                        if ($the_order->get_user_id()) {
                            $user_info = get_userdata($the_order->get_user_id());
                        }

                        if ($the_order->get_user_id() && !empty($user_info)) {

                            $username = '<a href="user-edit.php?user_id=' . absint($user_info->ID) . '">';

                            if ($user_info->first_name || $user_info->last_name) {
                                $username .= esc_html(ucfirst($user_info->first_name) . ' ' . ucfirst($user_info->last_name));
                            } else {
                                $username .= esc_html(ucfirst($user_info->display_name));
                            }

                            $username .= '</a>';

                        } else {
                            if ($the_order->get_billing_first_name() || $the_order->get_billing_last_name()) {
                                $username = trim($the_order->get_billing_first_name() . ' ' . $the_order->get_billing_last_name());
                            } else {
                                $username = __('Guest', 'woocommerce');
                            }
                        }

                        printf(__('%s by %s', 'woocommerce'), '<a href="' . admin_url('post.php?post=' . absint($value->ID) . '&action=edit') . '"><strong>' . esc_attr($the_order->get_order_number()) . '</strong></a>', $username);
                        if ($the_order->get_billing_email()) {
                            echo '<small class="meta email"><a href="' . esc_url('mailto:' . $the_order->get_billing_email()) . '">' . esc_html($the_order->get_billing_email()) . '</a></small>';
                        }
                        echo '</div>';
                        ?>
                    </td>
                    <td>
                        <?php
                        echo date_i18n(__('jS F Y', 'woocommerce'), strtotime($the_order->get_date_created()->date('jS F Y'))) . "\n";
                        ?>
                    </td>
                    <td>
                        <?php
                        echo date_i18n(__('g:i:s A', 'woocommerce'), strtotime($the_order->get_date_created()->date('g:i:s A'))) . "\n";
                        ?>
                    </td>
                    <td><?php
                        $total = $the_order->get_total();
                        if($the_order->get_payment_method() == "cod"){
                            $paid = $the_order->get_meta('wc_pos_amount_pay');
                            $change = $the_order->get_meta('wc_pos_amount_change');
                            $amount_cod = floatval($paid) - floatval($change < 0 ? 0 : $change);
                            $total = !empty($amount_cod) ? $amount_cod : $total;
                        }
                        $the_order->set_total($total);
                        echo $the_order->get_formatted_order_total();
                        if ($the_order->get_payment_method_title()) {
                            if (!isset($payment_methods[$the_order->get_payment_method_title()])) {
                                $payment_methods[$the_order->get_payment_method_title()] = ($the_order->get_total() - $the_order->get_total_refunded());
                            }else{
                                $payment_methods[$the_order->get_payment_method_title()] += ($the_order->get_total() - $the_order->get_total_refunded());
                            }
                            if (!isset($payment_totals[$the_order->get_payment_method()]))
                                $payment_totals[$the_order->get_payment_method()] = ($the_order->get_total() - $the_order->get_total_refunded());
                            else
                                $payment_totals[$the_order->get_payment_method()] += ($the_order->get_total() - $the_order->get_total_refunded());
                        }
                        ?>
                    </td>
                </tr>
                <?php
            }
        } else {
            echo '<tr><td colspan="4"> No sales </td></tr>';
        } ?>
        </tbody>
    </table>
    <?php if (!empty($refunded_orders)) { ?>
        <h3><?php _e('Refunds', 'wc_point_of_sale'); ?></h3>
        <table class="wp-list-table widefat fixed striped posts">
            <thead>
            <tr>
                <th class="manage-column column-order_customer" scope="col">
                    <?php _e('Order', 'wc_point_of_sale'); ?>
                </th>
                <th class="manage-column column-order-date" scope="col">
                    <?php _e('Date', 'wc_point_of_sale'); ?>
                </th>
                <th class="manage-column column-order-time" scope="col">
                    <?php _e('Time', 'wc_point_of_sale'); ?>
                </th>
                <th class="manage-column column-order_total" style="width: 25%;" scope="col">
                    <?php _e('Total', 'wc_point_of_sale'); ?>
                </th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($refunded_orders as $ID) {
                $the_order = new WC_Order($ID);
                ?>
                <tr>
                    <td>
                        <?php

                        echo '<div class="tips" >';

                        if ($the_order->get_user_id()) {
                            $user_info = get_userdata($the_order->get_user_id());
                        }

                        if ($the_order->get_user_id() && !empty($user_info)) {

                            $username = '<a href="user-edit.php?user_id=' . absint($user_info->ID) . '">';

                            if ($user_info->first_name || $user_info->last_name) {
                                $username .= esc_html(ucfirst($user_info->first_name) . ' ' . ucfirst($user_info->last_name));
                            } else {
                                $username .= esc_html(ucfirst($user_info->display_name));
                            }

                            $username .= '</a>';

                        } else {
                            if ($the_order->get_billing_first_name() || $the_order->get_billing_last_name()) {
                                $username = trim($the_order->get_billing_first_name() . ' ' . $the_order->get_billing_last_name());
                            } else {
                                $username = __('Guest', 'woocommerce');
                            }
                        }

                        printf(__('%s by %s', 'woocommerce'), '<a href="' . admin_url('post.php?post=' . absint($value->ID) . '&action=edit') . '"><strong>' . esc_attr($the_order->get_order_number()) . '</strong></a>', $username);

                        if ($the_order->get_billing_email()) {
                            echo '<small class="meta email"><a href="' . esc_url('mailto:' . $the_order->get_billing_email()) . '">' . esc_html($the_order->get_billing_email()) . '</a></small>';
                        }

                        echo '</div>';
                        ?>
                    </td>
                    <td>
                        <?php
                        echo date_i18n(__('jS F Y', 'woocommerce'), strtotime($the_order->get_date_created())) . "\n";
                        ?>
                    </td>
                    <td>
                        <?php
                        echo date_i18n(__('g:i:s A', 'woocommerce'), strtotime($the_order->get_date_created())) . "\n";
                        ?>
                    </td>
                    <td><?php
                        echo esc_html(strip_tags($the_order->get_formatted_order_total('', false)));
                        ?>
                    </td>
                </tr>
                <?php
            } ?>
            </tbody>
        </table>
    <?php } ?>
    <?php if (!empty($canceled_orders)) { ?>
        <h3><?php _e('Cancelled', 'wc_point_of_sale'); ?></h3>
        <table class="wp-list-table widefat fixed striped posts">
            <thead>
            <tr>
                <th class="manage-column column-order_customer" scope="col">
                    <?php _e('Order', 'wc_point_of_sale'); ?>
                </th>
                <th class="manage-column column-order-date" scope="col">
                    <?php _e('Date', 'wc_point_of_sale'); ?>
                </th>
                <th class="manage-column column-order-time" scope="col">
                    <?php _e('Time', 'wc_point_of_sale'); ?>
                </th>
                <th class="manage-column column-order_total" style="width: 25%;" scope="col">
                    <?php _e('Total', 'wc_point_of_sale'); ?>
                </th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($canceled_orders as $ID) {
                $the_order = new WC_Order($ID);
                ?>
                <tr>
                    <td>
                        <?php

                        echo '<div class="tips" >';

                        if ($the_order->get_user_id()) {
                            $user_info = get_userdata($the_order->get_user_id());
                        }

                        if ($the_order->get_user_id() && !empty($user_info)) {

                            $username = '<a href="user-edit.php?user_id=' . absint($user_info->ID) . '">';

                            if ($user_info->first_name || $user_info->last_name) {
                                $username .= esc_html(ucfirst($user_info->first_name) . ' ' . ucfirst($user_info->last_name));
                            } else {
                                $username .= esc_html(ucfirst($user_info->display_name));
                            }

                            $username .= '</a>';

                        } else {
                            if ($the_order->get_billing_first_name() || $the_order->get_billing_last_name()) {
                                $username = trim($the_order->get_billing_first_name() . ' ' . $the_order->get_billing_last_name());
                            } else {
                                $username = __('Guest', 'woocommerce');
                            }
                        }

                        printf(__('%s by %s', 'woocommerce'), '<a href="' . admin_url('post.php?post=' . absint($value->ID) . '&action=edit') . '"><strong>' . esc_attr($the_order->get_order_number()) . '</strong></a>', $username);

                        if ($the_order->get_billing_email()) {
                            echo '<small class="meta email"><a href="' . esc_url('mailto:' . $the_order->get_billing_email()) . '">' . esc_html($the_order->get_billing_email()) . '</a></small>';
                        }

                        echo '</div>';
                        ?>
                    </td>
                    <td>
                        <?php
                        echo date_i18n(__('jS F Y', 'woocommerce'), strtotime($the_order->get_date_created())) . "\n";
                        ?>
                    </td>
                    <td>
                        <?php
                        echo date_i18n(__('g:i:s A', 'woocommerce'), strtotime($the_order->get_date_created())) . "\n";
                        ?>
                    </td>
                    <td><?php
                        echo esc_html(strip_tags($the_order->get_formatted_order_total()));

                        if ($the_order->get_payment_method_title()) {
                            if (!isset($payment_methods[$the_order->get_payment_method_title()]))
                                $payment_methods[$the_order->get_payment_method_title()] = $the_order->get_total();
                            else
                                $payment_methods[$the_order->get_payment_method_title()] += $the_order->get_total();
                            if (!isset($payment_totals[$the_order->get_payment_method()]))
                                $payment_totals[$the_order->get_payment_method()] = $the_order->get_total();
                            else
                                $payment_totals[$the_order->get_payment_method()] += $the_order->get_total();
                        }
                        ?>
                    </td>
                </tr>
                <?php
            } ?>
            </tbody>
        </table>
    <?php } ?>
    <?php if (!empty($saved_orders)) { ?>
        <h3><?php _e('Saved', 'wc_point_of_sale'); ?></h3>
        <table class="wp-list-table widefat fixed striped posts">
            <thead>
            <tr>
                <th class="manage-column column-order_customer" scope="col">
                    <?php _e('Order', 'wc_point_of_sale'); ?>
                </th>
                <th class="manage-column column-order-date" scope="col">
                    <?php _e('Date', 'wc_point_of_sale'); ?>
                </th>
                <th class="manage-column column-order-time" scope="col">
                    <?php _e('Time', 'wc_point_of_sale'); ?>
                </th>
                <th class="manage-column column-order_total" style="width: 25%;" scope="col">
                    <?php _e('Total', 'wc_point_of_sale'); ?>
                </th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($saved_orders as $ID) {
                $the_order = new WC_Order($ID);
                ?>
                <tr>
                    <td>
                        <?php

                        echo '<div class="tips">';

                        if ($the_order->get_user_id()) {
                            $user_info = get_userdata($the_order->get_user_id());
                        }

                        if ($the_order->get_user_id() && !empty($user_info)) {

                            $username = '<a href="user-edit.php?user_id=' . absint($user_info->ID) . '">';

                            if ($user_info->first_name || $user_info->last_name) {
                                $username .= esc_html(ucfirst($user_info->first_name) . ' ' . ucfirst($user_info->last_name));
                            } else {
                                $username .= esc_html(ucfirst($user_info->display_name));
                            }

                            $username .= '</a>';

                        } else {
                            if ($the_order->get_billing_first_name() || $the_order->get_billing_last_name()) {
                                $username = trim($the_order->get_billing_first_name() . ' ' . $the_order->get_billing_last_name());
                            } else {
                                $username = __('Guest', 'woocommerce');
                            }
                        }

                        printf(__('%s by %s', 'woocommerce'), '<a href="' . admin_url('post.php?post=' . absint($value->ID) . '&action=edit') . '"><strong>' . esc_attr($the_order->get_order_number()) . '</strong></a>', $username);

                        if ($the_order->get_billing_email()) {
                            echo '<small class="meta email"><a href="' . esc_url('mailto:' . $the_order->get_billing_email()) . '">' . esc_html($the_order->get_billing_email()) . '</a></small>';
                        }

                        echo '</div>';
                        ?>
                    </td>
                    <td>
                        <?php
                        echo date_i18n(__('jS F Y', 'woocommerce'), strtotime($the_order->get_date_created())) . "\n";
                        ?>
                    </td>
                    <td>
                        <?php
                        echo date_i18n(__('g:i:s A', 'woocommerce'), strtotime($the_order->get_date_created())) . "\n";
                        ?>
                    </td>
                    <td><?php
                        echo esc_html(strip_tags($the_order->get_formatted_order_total()));
                        if ($the_order->get_payment_method_title()) {
                            if (!isset($payment_methods[$the_order->get_payment_method_title()]))
                                $payment_methods[$the_order->get_payment_method_title()] = $the_order->get_total();
                            else
                                $payment_methods[$the_order->get_payment_method_title()] += $the_order->get_total();
                            if (!isset($payment_totals[$the_order->get_payment_method()]))
                                $payment_totals[$the_order->get_payment_method()] = $the_order->get_total();
                            else
                                $payment_totals[$the_order->get_payment_method()] += $the_order->get_total();
                        }
                        ?>
                    </td>
                </tr>
                <?php
            } ?>
            </tbody>
        </table>
    <?php } ?>

    <?php if (isset($data['detail']['float_cash_management']) && $data['detail']['float_cash_management']) {
        $cash = (isset($payment_totals['cod'])) ? $payment_totals['cod'] : 0;
        $cash_in = 0;
        $cash_out = 0;
        if (isset($data['detail']['opening_cash_amount']->amount) && $data['detail']['opening_cash_amount']->amount) {
            $cash_in = $cash_in + $data['detail']['opening_cash_amount']->amount;
        }
    } ?>
    
    <h3><?php _e('Tax Breakdown', 'wc_point_of_sale'); ?></h3>
    <table class="wp-list-table widefat fixed striped posts">
        <thead>
        <tr>
            <th class="manage-column column-order_customer" scope="col">
                <?php _e('Tax Rate', 'wc_point_of_sale'); ?>
            </th>
            <th class="manage-column column-order_customer" scope="col">
                <?php _e('Tax Name', 'wc_point_of_sale'); ?>
            </th>
            <th class="manage-column column-order_customer" scope="col">
                <?php _e('Tax', 'wc_point_of_sale'); ?>
            </th>
        </tr>
        </thead>
        <tbody>
        <?php
        $filtered = array();
        foreach ($tax_sales as $key => $tax_sale){
            if(empty($tax_sale['tax_rate'])){
                continue;
            }
            if(array_key_exists($tax_sale['tax_rate'], $filtered)){
                $filtered[$tax_sale['tax_rate']]['tax_amount'] += $tax_sale['tax_amount'];
            }else{
                $filtered[$tax_sale['tax_rate']] = array(
                    'tax_label' => $tax_sale['tax_label'],
                    'tax_amount' => $tax_sale['tax_amount']
                );
            }
        }
        ?>

        <?php foreach ($filtered as $tax_rate => $tax_item): ?>
            <tr>
                <td><?php echo WC_Tax::_get_tax_rate($tax_rate)['tax_rate']; ?></td>
                <td><?php echo ucfirst($tax_item['tax_label']); ?></td>
                <td><?php echo wc_price($tax_item['tax_amount']); ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php if (!empty($payment_methods)): ?>
        <h3><?php _e('Payments', 'wc_point_of_sale'); ?></h3>
        <table class="wp-list-table widefat fixed striped posts">
            <thead>
            <tr>
                <th class="manage-column column-payment_type" scope="col">
                    <?php _e('Type', 'wc_point_of_sale'); ?>
                </th>
                <th class="manage-column column-amount" scope="col">
                    <?php _e('Amount', 'wc_point_of_sale'); ?>
                </th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($payment_methods as $name => $amount) { ?>
                <?php /*if ($name == 'Cash on delivery' || $name == 'Cash') {
                    $cash = $cash + $amount;
                } */ ?>
                <tr>
                    <td><?php echo $name; ?></td>
                    <td><?php echo wc_price($amount); ?></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    <?php endif; ?>
    <?php if (isset($data['detail']['float_cash_management']) && $data['detail']['float_cash_management']) { ?>
        <h3><?php _e('Cash Summary', 'wc_point_of_sale'); ?></h3>
        <table class="wp-list-table widefat fixed striped posts">
            <thead>
            <tr>
                <th class="manage-column" scope="col">
                    <?php _e('Type', 'wc_point_of_sale'); ?>
                </th>
                <th class="manage-column" scope="col">
                    <?php _e('Amount', 'wc_point_of_sale'); ?>
                </th>
            </tr>
            </thead>
            <tbody>
            <?php if (isset($data['detail']['opening_cash_amount']) && $data['detail']['opening_cash_amount']->status && $data['detail']['opening_cash_amount']->amount) { ?>
                <tr>
                    <td><?php _e('Opening Cash', 'wc_point_of_sale'); ?><br>
                    <small class="meta"><?php echo $data['detail']['opening_cash_amount']->note ?></small></td>
                    <td><?php echo wc_price($data['detail']['opening_cash_amount']->amount); ?></td>
                </tr>
            <?php } ?>
            <?php if (isset($data['detail']['cash_management_actions']) && $data['detail']['cash_management_actions']) {
                foreach ($data['detail']['cash_management_actions'] as $cash_action) {
                    switch ($cash_action->type) {
                        case 'add-cash':
                            $cash_in = $cash_in + $cash_action->amount;
                            break;
                        case 'remove-cash':
                            $cash_out = $cash_out + $cash_action->amount;
                            break;
                    }
                } ?>
                <tr>
                    <td><?php _e('Cash In', 'wc_point_of_sale'); ?></td>
                    <td><?php echo wc_price($cash_in); ?></td>
                </tr>
                <tr>
                    <td><?php _e('Cash Out', 'wc_point_of_sale'); ?></td>
                    <td>-<?php echo wc_price($cash_out); ?></td>
                </tr>
            <?php } ?>

            <?php foreach ($payment_methods as $name => $amount) { ?>
                <tr>
                    <td><?php _e('Cash', 'wc_point_of_sale'); ?></td>
                    <td><?php echo wc_price($amount); ?></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
        <div class="cash-result">
            <h3><?php _e('Cash Totals', 'wc_point_of_sale'); ?></h3>
            <table class="wp-list-table widefat fixed posts">
                <tbody>
                <tr>
                    <td><?php _e('Drawer Cash:', 'wc_point_of_sale'); ?></td>
                    <td id="drawer-cash"
                        data-value="<?php echo $total_cash = $cash + $cash_in - $cash_out ?>"><?php echo wc_price($total_cash) ?></td>
                </tr>
                <tr>
                    <td><?php _e('Actual Cash:', 'wc_point_of_sale'); ?></td>
                    <td class="actual-cash"><a
                                class="button"><?php echo (isset($data['detail']['actual_cash'])) ? wc_price($data['detail']['actual_cash']) : __('Enter Remaining Cash', 'wc_point_of_sale'); ?></a>
                    </td>
                </tr>
                <?php if (!isset($_GET['print'])) { ?>
                    <tr id="cash-popup" class="cash-popup">
                        <td colspan="2"><p
                                    class="description"><?php _e('Enter the remaining cash found in the cash drawer. Use the denominations setup under Point of Sale > Settings > Denominations to enter these values:', 'wc_point_of_sale'); ?></p>
                        </td>
                    </tr>
                    <?php $nominals = get_option('wc_pos_cash_nominal') ?>
                    <?php if ($nominals) { ?>
                        <?php foreach ($nominals as $nominal) { ?>
                            <tr class="nominal-row cash-popup">
                                <td><?php echo wc_price($nominal) ?></td>
                                <td><input type="number" class="nominal" id="nominal-<?php echo $nominal ?>"
                                           data-value="<?php echo $nominal ?>"></td>
                            </tr>
                        <?php } ?>
                    <?php } ?>
                    <tr class="cash-popup">
                        <td colspan="2">
                            <a href="#"
                               class="button alignright"><?php _e('Enter Cash Values', 'wc_point_of_sale') ?></a></td>
                    </tr>
                <?php } ?>
                <tr>
                    <td><?php _e('Difference:', 'wc_point_of_sale'); ?></td>
                    <td class="cash-difference"><?php echo (isset($data['detail']['actual_cash'])) ? wc_price($data['detail']['actual_cash'] - $total_cash) : '' ?></td>
                </tr>
                </tbody>
            </table>
        </div>
    <?php } ?>
    <?php
    $pos_gateways = get_option('pos_enabled_gateways', array());
    $is_terminal = isset($data['detail']['paymentsense_terminal']) && $data['detail']['paymentsense_terminal'] != "none";
    if(in_array('wc_pos_paymentsense', $pos_gateways) && $is_terminal){
        echo '<p style="margin-top:1em;" class="description">' . __('Terminal End of Day reports can be printed from the assigned registers settings page.', 'wc_point_of_sale') . '</p>';
    }
    ?>
</div>
<?php if (isset($_GET['page'])): ?>
    <?php if($_GET['page'] == 'wc_pos-print'): ?>
        <script>
            window.print();
        </script>
    <?php endif; ?>
<?php endif; ?>