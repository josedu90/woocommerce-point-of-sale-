<?php
/**
 * Update WC_POS to 3.2.1
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
$table_name = $wpdb->prefix . "wc_point_of_sale_cache";
$wpdb->query("DROP TABLE IF EXISTS `{$table_name}`");
$wpdb->query("CREATE TABLE IF NOT EXISTS `{$table_name}` (
  `id` int(11) NOT NULL,
  `data` longtext NOT NULL,
  `pkey` longtext NOT NULL,
  `pos_id` int(11) DEFAULT NULL,
  `time` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");