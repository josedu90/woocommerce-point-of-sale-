<tr>
    <td>
        <?php echo $row['time']  . "\n"; ?>
        <small class="meta"><?php echo date_i18n(__('jS F Y', 'woocommerce'), strtotime($row['time'])) . "\n"; ?></small>
    </td>
    <td>
        <?php echo ($author) ? $author->first_name . ' ' . $author->last_name : '' ?>
    </td>
    <td>
        <?php echo $row['title'] ?>
        <?php echo ($row['note']) ? '</br><small class="meta">' . $row['note'] . '</small>' : '' ?>
    </td>
    <td class="<?php echo $row['type'] ?>">
        <?php echo wc_price($row['amount']) ?>
    </td>
    <input type="hidden" class="et-date" value="<?php echo date("Y-m-d G:i:s", strtotime($row['time'])) ?>">
</tr>
