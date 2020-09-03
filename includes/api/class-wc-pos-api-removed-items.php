<?php
/**
 * POS API Removed Items Class
 *
 * Handles requests to the /removed endpoint. 
 *
 * @class 	  WC_API_POS_Orders
 * @package   WooCommerce POS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WC_API_POS_Removed extends WC_REST_CRUD_Controller {

    /** @var string $namespace the route namespace */
    protected $namespace = 'wc/v3';

	/** @var string $rest_base the route base */
	protected $rest_base = 'pos_removed';

	/** @var string $post_type the custom post type */
	protected $post_type = 'shop_order';

	/**
	 * Register the routes for this class
	 *
	 * GET/pos_removed
	 *
	 * @since 2.1
	 * @param array $routes
	 * @return array
	 */
	public function register_routes() {

        register_rest_route(
            $this->namespace, '/' . $this->rest_base, array(
                array(
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => array( $this, 'get_removed_items' ),
                    'permission_callback' => array( $this, 'get_items_permissions_check' ),
                    'args'                => $this->get_collection_params(),
                ),
                'schema' => array( $this, 'get_public_item_schema' ),
            )
        );

	}


	/**
	 * Get all removed items
	 *
	 * @since 2.1
	 * @param string $fields
	 * @param array $filter
	 * @param string $status
	 * @param int $page
	 * @return WP_REST_Response
	 */
	public function get_removed_items( $fields = null, $filter = array(), $status = null, $page = 1 ) {

		try {
			if ( ! current_user_can( 'read_private_shop_orders' ) ) {
				throw new WC_REST_Exception( 'woocommerce_api_user_cannot_read_orders_count', __( 'You do not have permission to read the orders', 'wc_point_of_sale' ), 401 );
			}

			$post_ids = get_option( 'pos_removed_posts_ids', array() );

			$user_ids = get_option( 'pos_removed_user_ids', array() );

            $response = rest_ensure_response(array('post_ids' => (array)$post_ids, 'user_ids' => (array)$user_ids));

			return $response;

		} catch ( WC_REST_Exception $e ) {
			return new WP_Error( $e->getErrorCode(), $e->getMessage(), array( 'status' => $e->getCode() ) );
		}
	}
}
