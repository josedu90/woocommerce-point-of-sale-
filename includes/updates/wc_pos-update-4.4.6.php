<?php
/**
 * Update WC_POS to 4.4.6
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
$rows = $wpdb->query(
    $wpdb->prepare(
        "DELETE FROM {$wpdb->prefix}woocommerce_api_keys
        WHERE description like %s
		 AND user_id = %d",
        "%pos%", 0
    )
);
