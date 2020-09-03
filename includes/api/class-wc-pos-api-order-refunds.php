<?php
/**
 * POS API Order Refunds Class
 *
 * Handles requests to the /pos_orders/<order_id>/refunds endpoint
 *
 * @class      WC_API_POS_Order_Refunds
 * @package   WooCommerce POS
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class WC_API_POS_Order_Refunds extends WC_REST_Order_Refunds_Controller
{

    /** @var string $base the route base */
    protected $rest_base = 'pos_orders/(?P<order_id>[\d]+)/refunds';

    public function create_order_refund($order_id, $data, $api_refund = true)
    {
        try {

            if(get_option('wc_pos_refund_approval', 'no') == 'yes'){
                $user_approval = get_user_meta(get_current_user_id(), 'approve_refunds', true);
                if(!$user_approval || $user_approval == 'no'){
                    throw new WC_API_Exception( 'wc_point_of_sale_no_permission_for_refund', __('You do not have permission to process refunds', 'wc_point_of_sale'), 400 );
                }
            }

            if ( ! isset( $data['order_refund'] ) ) {
                throw new WC_API_Exception( 'woocommerce_api_missing_order_refund_data', sprintf( __( 'No %1$s data specified to create %1$s', 'woocommerce' ), 'order_refund' ), 400 );
            }

            $data = $data['order_refund'];

            // Permission check
            if ( ! current_user_can( 'publish_shop_orders' ) ) {
                throw new WC_API_Exception( 'woocommerce_api_user_cannot_create_order_refund', __( 'You do not have permission to create order refunds', 'woocommerce' ), 401 );
            }

            $order_id = absint( $order_id );

            if ( empty( $order_id ) ) {
                throw new WC_API_Exception( 'woocommerce_api_invalid_order_id', __( 'Order ID is invalid', 'woocommerce' ), 400 );
            }

            $data = apply_filters( 'woocommerce_api_create_order_refund_data', $data, $order_id, $this );

            // Refund amount is required
            if ( ! isset( $data['amount'] ) ) {
                throw new WC_API_Exception( 'woocommerce_api_invalid_order_refund', __( 'Refund amount is required.', 'woocommerce' ), 400 );
            } elseif ( 0 > $data['amount'] ) {
                throw new WC_API_Exception( 'woocommerce_api_invalid_order_refund', __( 'Refund amount must be positive.', 'woocommerce' ), 400 );
            }

            $data['order_id']  = $order_id;
            $data['refund_id'] = 0;

            // Create the refund
            $refund = wc_create_refund( $data );

            if(is_wp_error($refund)){
                throw new WC_API_Exception( 'woocommerce_api_cannot_create_order_refund', $refund->get_error_message(), 400);
            }

            if ( ! $refund ) {
                throw new WC_API_Exception( 'woocommerce_api_cannot_create_order_refund', __( 'Cannot create order refund, please try again.', 'woocommerce' ), 500 );
            }

            // Refund via API
            if ( $api_refund ) {
                if ( WC()->payment_gateways() ) {
                    $payment_gateways = WC()->payment_gateways->payment_gateways();
                }

                $order = wc_get_order( $order_id );

                if ( isset( $payment_gateways[ $order->get_payment_method() ] ) && $payment_gateways[ $order->get_payment_method() ]->supports( 'refunds' ) ) {
                    $result = $payment_gateways[ $order->get_payment_method() ]->process_refund( $order_id, $refund->get_amount(), $refund->get_reason() );

                    if ( is_wp_error( $result ) ) {
                        return $result;
                    } elseif ( ! $result ) {
                        throw new WC_API_Exception( 'woocommerce_api_create_order_refund_api_failed', __( 'An error occurred while attempting to create the refund using the payment gateway API.', 'woocommerce' ), 500 );
                    }
                }
            }

            // HTTP 201 Created
            $this->server->send_status( 201 );

            do_action( 'woocommerce_api_create_order_refund', $refund->get_id(), $order_id, $this );

            $refund_data =  parent::get_order_refund( $order_id, $refund->get_id());
            $refund_data["order_refund"]["refund_receipt"] = admin_url("admin.php?print_pos_receipt=true&order_id=" . $refund_data['order_refund']['id'] . "&_wpnonce=" . wp_create_nonce("print_pos_receipt"));

            return $refund_data;
        } catch ( WC_Data_Exception $e ) {
            return new WP_Error( $e->getErrorCode(), $e->getMessage(), array( 'status' => 400 ) );
        } catch ( WC_API_Exception $e ) {
            return new WP_Error( $e->getErrorCode(), $e->getMessage(), array( 'status' => $e->getCode() ) );
        }
    }

    protected function prepare_object_for_database($request, $creating = false)
    {
        if(get_option('wc_pos_refund_approval', 'no') == 'yes'){
            $user_approval = get_user_meta(get_current_user_id(), 'approve_refunds', true);
            if(!$user_approval || $user_approval == 'no'){
                return new WP_Error( 'wc_point_of_sale_no_permission_for_refund', __('You do not have permission to process refunds', 'wc_point_of_sale'), 401 );
            }
        }

        if ( ! isset( $request['order_refund'] ) ) {
            return new WP_Error( 'woocommerce_api_missing_order_refund_data', sprintf( __( 'No %1$s data specified to create %1$s', 'woocommerce' ), 'order_refund' ), 400 );
        }

        $order = wc_get_order( (int) $request['order_id'] );

        if ( ! $order ) {
            return new WP_Error( 'woocommerce_rest_invalid_order_id', __( 'Invalid order ID.', 'woocommerce' ), 404 );
        }

        $request_data = $request['order_refund'];

        if ( 0 > $request_data['amount'] ) {
            return new WP_Error( 'woocommerce_rest_invalid_order_refund', __( 'Refund amount must be greater than zero.', 'woocommerce' ), 400 );
        }

        // Create the refund.
        $refund = wc_create_refund(
            array(
                'order_id'       => $order->get_id(),
                'amount'         => $request_data['amount'],
                'reason'         => empty( $request_data['reason'] ) ? null : $request_data['reason'],
                'line_items'     => empty( $request_data['line_items'] ) ? array() : $request_data['line_items'],
                'refund_payment' => is_bool( $request_data['api_refund'] ) ? $request_data['api_refund'] : true,
                'restock_items'  => $request_data['restock_items'],
            )
        );

        if ( is_wp_error( $refund ) ) {
            return new WP_Error( 'woocommerce_rest_cannot_create_order_refund', $refund->get_error_message(), 500 );
        }

        if ( ! $refund ) {
            return new WP_Error( 'woocommerce_rest_cannot_create_order_refund', __( 'Cannot create order refund, please try again.', 'woocommerce' ), 500 );
        }

        if ( ! empty( $request['meta_data'] ) && is_array( $request['meta_data'] ) ) {
            foreach ( $request['meta_data'] as $meta ) {
                $refund->update_meta_data( $meta['key'], $meta['value'], isset( $meta['id'] ) ? $meta['id'] : '' );
            }
            $refund->save_meta_data();
        }

        /**
         * Filters an object before it is inserted via the REST API.
         *
         * The dynamic portion of the hook name, `$this->post_type`,
         * refers to the object type slug.
         *
         * @param WC_Data         $coupon   Object object.
         * @param WP_REST_Request $request  Request object.
         * @param bool            $creating If is creating a new object.
         */
        return apply_filters( "woocommerce_rest_pre_insert_{$this->post_type}_object", $refund, $request, $creating );

    }

}
