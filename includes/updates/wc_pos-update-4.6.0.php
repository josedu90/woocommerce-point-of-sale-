<?php
/**
 * Update WC_POS to 4.6.0
 *
 * @author      Actuality Extensions
 * @version     4.6.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
global $wpdb;
$table_name = $wpdb->prefix . "wc_poin_of_sale_grids";
$wpdb->query("ALTER TABLE {$table_name}
ADD `auto_tiles_view` ENUM ('categories', 'products') DEFAULT 'products'");
