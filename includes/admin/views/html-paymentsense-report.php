<?php
/**
 * @param object $report_body
 */
?>
<div id="sale_report_overlay" class="overlay_order_popup" style="display: block;">
    <div id="sale_report_popup">
        <div class="media-frame-title">
            <h1><?php _e("Paymentsense Report", "wc_point_of_sale"); ?></h1>
        </div>
        <span class="close_popup"></span>
        <div id="sale_report_popup_inner">
            <h3><?php _e('Totals', 'wc_point_of_sale'); ?></h3>
            <table class='wp-list-table widefat fixed striped posts endofday'>
                <thead>
                    <tr>
                        <td><?php _e("Total Sales Count", "wc_point_of_sale"); ?></td>
                        <td><?php _e("Total Sales Amount", "wc_point_of_sale"); ?></td>
                        <td><?php _e("Total Refunds Amount", "wc_point_of_sale"); ?></td>
                        <td><?php _e("Total Cashback Amount", "wc_point_of_sale"); ?></td>
                        <td><?php _e("Total Amount", "wc_point_of_sale"); ?></td>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?php echo $report_body->balances->totalSalesCount; ?></td>
                        <td><?php echo wc_price(($report_body->balances->totalSalesAmount / 100)); ?></td>
                        <td><?php echo wc_price(($report_body->balances->totalRefundsAmount / 100)); ?></td>
                        <td><?php echo wc_price(($report_body->balances->totalCashbackAmount / 100)); ?></td>
                        <td><?php echo wc_price(($report_body->balances->totalAmount / 100)); ?></td>
                    </tr>
                </tbody>
            </table>
            <?php
            $bankings = get_object_vars($report_body->balances->issuerTotals);
            if (count($bankings)):
            ?>
            <h3><?php _e('Issuer Totals', 'wc_point_of_sale'); ?></h3>
            <table class='wp-list-table widefat fixed striped posts endofday'>
                <thead>
                    <tr>
                        <th><?php _e("Issuer", "wc_point_of_sale"); ?></th>
                        <?php
                        foreach ($bankings as $bank_name => $banking){
                            echo '<th>'. $bank_name .'</th>';
                        }
                        ?>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th><?php _e("Sales Count", "wc_point_of_sale"); ?></th>
                        <?php
                        foreach ($bankings as $bank_name => $banking){
                            echo '<td>'. $banking->totalSalesCount .'</td>';
                        }
                        ?>
                    </tr>
                    <tr>
                        <th><?php _e("Sales Amount", "wc_point_of_sale"); ?></th>
                        <?php
                        foreach ($bankings as $bank_name => $banking){
                            echo '<td>'. $banking->totalSalesAmount .'</td>';
                        }
                        ?>
                    </tr>
                    <tr>
                        <th><?php _e("Refunds Amount", "wc_point_of_sale"); ?></th>
                        <?php
                        foreach ($bankings as $bank_name => $banking){
                            echo '<td>'. $banking->totalRefundsAmount .'</td>';
                        }
                        ?>
                    </tr>
                    <tr>
                        <th><?php _e("Total Amount", "wc_point_of_sale"); ?></th>
                        <?php
                        foreach ($bankings as $bank_name => $banking){
                            echo '<td>'. $banking->totalAmount .'</td>';
                        }
                        ?>
                    </tr>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
        <div class="media-frame-footer"></div>
        </div>
</div>