<?php
/**
 * Update WC_POS to 4.5.9
 *
 * @author      Actuality Extensions
 * @category    Admin
 * @package     WC_POS/Admin
 * @version     1.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Delete options.
delete_option('wc_pos_enable_new_api');
delete_option('wc_pos_new_api_time');