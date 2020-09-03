<?php
/**
 * Update WC_POS to 4.3.6
 *
 * @author      Actuality Extensions
 * @category    Admin
 * @package     WC_POS/Admin
 * @version     1.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
global $wpdb;
$wpdb->hide_errors();
if (!($wpdb->get_results("SELECT *
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE table_name = '{$wpdb->prefix}wc_poin_of_sale_receipts'
            AND table_schema = '{$wpdb->dbname}'
            AND column_name = 'print_by_pos_printer'"))
) {
    $result['tax_summary'] = $wpdb->query("ALTER TABLE {$wpdb->prefix}wc_poin_of_sale_receipts ADD `print_by_pos_printer` VARCHAR (255) DEFAULT '' NOT NULL");
}
