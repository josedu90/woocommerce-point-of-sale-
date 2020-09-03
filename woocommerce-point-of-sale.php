<?php
/**
 * Plugin Name: WooCommerce Point of Sale |  VestaThemes.com
 * Plugin URI: http://codecanyon.net/item/woocommerce-point-of-sale-pos/7869665&ref=actualityextensions/
 * Description: An advanced toolkit for placing WooCommerce orders beautifully through a Point of Sale interface. Requires <a href="http://wordpress.org/plugins/woocommerce/">WooCommerce</a>.
 * Version: 4.5.33
 * Author: Actuality Extensions
 * Author URI: http://actualityextensions.com/
 * Tested up to: 5.2.1
 *
 * Text Domain: wc_point_of_sale
 * Domain Path: /lang
 *
 * Copyright: (c) 2013-2019 Actuality Extensions (info@actualityextensions.com)
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package     WC-Point-Of-Sale
 * @author      Actuality Extensions
 * @category    Plugin
 * @copyright   Copyright (c) 2013-2019, Actuality Extensions
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 *
 * WC requires at least: 3.6.0
 * WC tested up to: 3.6.4
 */
if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (function_exists('is_multisite') && is_multisite()) {
    include_once(ABSPATH . 'wp-admin/includes/plugin.php');
    if (!is_plugin_active('woocommerce/woocommerce.php'))
        return;
} else {
    if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins'))))
        return; // Check if WooCommerce is active    
}

// Load plugin class files
require_once('includes/class-wc-pos.php');

require 'updater/updater.php';
global $aebaseapi;
$aebaseapi->add_product(__FILE__);

add_filter('woocommerce_stock_amount', 'floatval', 1);
/**
 * Returns the main instance of WC_POS to prevent the need to use globals.
 *
 * @since    3.0.5
 * @return WC_POS
 */
function WC_POS()
{
    $instance = WC_POS::instance(__FILE__, '4.6.0');
    return $instance;
}

/*
 * Dequeue SCF script which cause errors
 */
function dequeue_acf_script()
{
    wp_dequeue_script('acf-input');
    wp_deregister_script('acf-input');
}

// Global for backwards compatibility.
global $wc_point_of_sale, $wc_pos_db_version;

$wc_pos_db_version = get_option('wc_pos_db_version');
$wc_point_of_sale = WC_POS();
$GLOBALS['wc_pos'] = WC_POS();