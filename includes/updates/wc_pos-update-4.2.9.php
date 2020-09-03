<?php
/**
 * Update WC_POS to 4.2.9
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
$table_name = $wpdb->prefix . "wc_poin_of_sale_tabs";
$wpdb->query("DROP TABLE IF EXISTS `{$table_name}`");
$wpdb->query("CREATE TABLE IF NOT EXISTS `{$table_name}` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `spend_limit` float DEFAULT NULL,
  `register_id` int(11) DEFAULT NULL,
  `order_id` int(11) DEFAULT NULL,
  `tab_number` int(11) DEFAULT NULL,
  `opened` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8");