<?php
/**
 * REST API Products controller
 *
 * Handles requests to the /products endpoint.
 */

defined( 'ABSPATH' ) || exit;

/**
 * REST API Products controller class.
 *
 * @package WooCommerce POS
 * @extends WC_REST_Products_Controller
 */
class WC_API_POS_Products extends WC_REST_Products_Controller {
    /**
     * Route base.
     *
     * @var string
     */
    protected $rest_base = 'pos_products';

    /**
     * Prepare objects query.
     *
     * @param WP_REST_Request $request Full details about the request.
     *
     * @return array
     */
    protected function prepare_objects_query($request) {
        $args = parent::prepare_objects_query($request);

        // Exclude online_only products.
        $exclude = $this->get_online_only_products();

        // Exclude out of stock products.
        if (get_option('wc_pos_show_out_of_stock_products', 'no') !== 'yes') {
            $exclude = array_merge($exclude, $this->get_out_of_stock_products());
        }

        // Remove duplications.
        $args['post__not_in'] = array_map('intval', array_unique($exclude));

        return $args;
    }

    /**
     * Returns a list of online_only products.
     *
     * @return array
     */
    protected function get_online_only_products() {
        global $wpdb;

        $sql = "SELECT ID FROM $wpdb->posts p
                INNER JOIN $wpdb->postmeta pm
                ON p.ID = pm.post_id
                AND pm.meta_key = '_pos_visibility'
                WHERE pm.meta_value = 'online'
                AND p.post_type = 'product'";

        return $wpdb->get_col($sql);
    }

    /**
     * Returns a list of out of stock products.
     *
     * @return array
     */
    protected function get_out_of_stock_products() {
        global $wpdb;

        $sql = "SELECT ID FROM $wpdb->posts p
                INNER JOIN $wpdb->postmeta pm
                ON p.ID = pm.post_id
                AND pm.meta_key = '_stock_status'
                WHERE pm.meta_value = 'outofstock'
                AND p.post_type IN ('product', 'product_variation')";

        return $wpdb->get_col($sql);
    }
}