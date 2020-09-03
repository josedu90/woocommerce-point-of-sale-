<?php if ($this->register->opened > $this->register->closed) { ?>
    <script>
        <?php echo 'var register_cash_management = ' . json_encode($this) ?>
    </script>
    <div class="wrap">
        <h1 class="wp-heading-inline"><?php _e('Cash Management', 'wc_point_of_sale') ?></h1>
        <a class="page-title-action cash-button remove-cash"
           data-action="remove-cash"><?php _e('Remove Cash', 'wc_point_of_sale') ?></a>
        <a class="page-title-action cash-button add-cash"
           data-action="add-cash"><?php _e('Add Cash', 'wc_point_of_sale') ?></a>
        <p><strong><?php echo __('Register Name: ', 'wc_point_of_sale') ?></strong><?php echo $this->register->name . ' #' . $this->register->ID ?></p>
        <p><strong><?php echo __('Outlet Name: ', 'wc_point_of_sale') ?></strong><?php echo isset($this->outlet_name[$this->register->outlet]) ? $this->outlet_name[$this->register->outlet] . ' #' . $this->register->outlet : "N/A"; ?></p>
        <p id="cash-total"><strong><?php echo __('Current Cash Balance (inc. sales): ', 'wc_point_of_sale') ?></strong><?php echo wc_price($this->cash_balance) ?></p>
        <table class="wp-list-table widefat fixed striped posts cash-data" style="width: 100%;">
            <thead>
            <tr>
                <th class="time"><?php _e('Time', 'wc_point_of_sale') ?></th>
                <th class="user"><?php _e('Cashier', 'wc_point_of_sale') ?></th>
                <th class="reasons"><?php _e('Reason', 'wc_point_of_sale') ?></th>
                <th class="transaction"><?php _e('Transaction', 'wc_point_of_sale') ?></th>
            </tr>
            </thead>
            <tbody id="the-list">
            <?php
            usort($this->cash_data, function ($a, $b) {
                $time_1 = strtotime($a['time']);
                $time_2 = strtotime($b['time']);
                if ($time_1 == $time_2) return 0;
                return ($time_1 < $time_2) ? -1 : 1;
            });
            foreach ($this->cash_data as $row) { ?>
                <?php $author = get_user_by('id', $row['user']) ?>
                <?php include('html-float-cash-management-table-row.php') ?>
            <?php } ?>
            </tbody>
            <tfoot>
            <tr>
                <th class="time"><?php _e('Time', 'wc_point_of_sale') ?></th>
                <th class="user"><?php _e('Cashier', 'wc_point_of_sale') ?></th>
                <th class="reasons"><?php _e('Reasons', 'wc_point_of_sale') ?></th>
                <th class="transaction"><?php _e('Transaction', 'wc_point_of_sale') ?></th>
            </tr>
            </tfoot>
        </table>
    </div>
<?php } else { ?>
    <h2 class="closed-register"><?php _e('Register is closed.') ?></h2>
<?php } ?>
