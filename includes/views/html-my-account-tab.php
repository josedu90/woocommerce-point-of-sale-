<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
?>

<table class="shop_table shop_table_responsive">
    <thead>
    <tr>
        <th class=""><?php _e("Register", "wc_point_of_Sale"); ?></th>
        <th class=""><?php _e("Outlet", "wc_point_of_Sale"); ?></th>
        <th class=""><?php _e("Actions", "wc_point_of_Sale"); ?></th>
    </tr>
    </thead>
    <tbody>
    <?php
    $registers = WC_Pos_Registers::instance()->get_data();
    $count = 0;
    foreach ($registers as $register){
        if(pos_check_user_can_open_register($register["ID"]) && !pos_check_register_lock($register["ID"])){
            $count++;
            $outlet = WC_Pos_Outlets::instance()->get_data($register["outlet"])[0];
            $pos_url = get_site_url(null, "/point-of-sale/" . sanitize_title($outlet["name"]) . "/" . $register["slug"]);
            ?>
            <tr class="">
                <td data-title="<?php _e("Register", "wc_point_of_Sale"); ?>">
                    <?php printf('<a href="%1$s" target="_blank">%2$s</a>',
                        $pos_url,
                        ucfirst($register["name"]));
                    ?>
                </td>
                <td data-title="<?php _e("Outlet", "wc_point_of_Sale"); ?>">
                    <?php echo ucfirst($outlet["name"]); ?>
                </td>
                <td data-title="<?php _e("Outlet", "wc_point_of_Sale"); ?>">
                    <?php
                    $button_text = pos_check_register_is_open($register['ID']) ? __("Enter", "wc_point_of_sale") : __("Open", "wc_point_of_sale");
                    printf('<a href="%1$s" class="woocommerce-button button" target="_blank">%2$s</a>', $pos_url, $button_text); ?>
                </td>
            </tr>
        <?php
        }
    }

    if($count < 1): ?>
        <tr>
            <td colspan="3"><span class="no-rows-found"><?php _e("No registers found.", "wc_point_of_sale"); ?></span></td>
        </tr>
    <?php endif; ?>
    </tbody>
</table>

