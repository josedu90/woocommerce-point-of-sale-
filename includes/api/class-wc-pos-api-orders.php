<?php
/**
 * POS API Orders Class
 *
 * Handles requests to the /orders endpoint
 *
 * @class      WC_API_POS_Orders
 * @package   WooCommerce POS
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class WC_API_POS_Orders extends WC_REST_Orders_Controller
{

    /** @var string $base the route base */
    protected $rest_base = 'pos_orders';

    /**
     * @param WP_REST_Request $request
     * @param bool $creating
     * @return bool|WC_Data|WP_Error
     * @throws WC_API_Exception
     */
    protected function save_object( $request, $creating = false )
    {
        try {

            if (!current_user_can('view_register')) {
                return new WP_Error('woocommerce_api_user_cannot_create_order', __('You do not have permission to create orders', 'woocommerce'), 401);
            }

            $creating = $request["action"] == "create";

            $object = $this->prepare_object_for_database( $request, $creating );

            if ( is_wp_error( $object ) ) {
                return $object;
            }

            $this->init_cart($request);

            // Make sure gateways are loaded so hooks from gateways fire on save/create.
            WC()->payment_gateways();

            if ( ! is_null( $request['customer_id'] ) && 0 !== $request['customer_id'] ) {
                // Make sure customer exists.
                if ( false === get_user_by( 'id', $request['customer_id'] ) ) {
                    throw new WC_REST_Exception( 'woocommerce_rest_invalid_customer_id', __( 'Customer ID is invalid.', 'woocommerce' ), 400 );
                }

                // Make sure customer is part of blog.
                if ( is_multisite() && ! is_user_member_of_blog( $request['customer_id'] ) ) {
                    add_user_to_blog( get_current_blog_id(), $request['customer_id'], 'customer' );
                }

                if (isset($request['user_meta']) && is_array($request['user_meta']) && isset($request['customer_id'])) {
                    foreach ($request['user_meta'] as $key => $value) {
                        update_user_meta($request['customer_id'], $key, $value);
                    }
                }
            }

            if ( $creating ) {
                $object->set_created_via( 'POS' );
                $object->set_prices_include_tax( 'yes' === get_option( 'woocommerce_prices_include_tax' ) );
                $order_id = $object->save();

                $object = $this->calculate_totals($order_id, true);
            } else {
                // If items have changed, recalculate order totals.
                if ( isset( $request['billing'] ) || isset( $request['shipping'] ) || isset( $request['line_items'] ) || isset( $request['shipping_lines'] ) || isset( $request['fee_lines'] ) || isset( $request['coupon_lines'] ) ) {
                    $object->calculate_totals( true );
                    $object = $this->calculate_totals( $object->get_id(), true );
                }
            }

            // Set coupons.
            $this->calculate_coupons( $request, $object );

            // Set status.
            $object->set_status( $this->get_status($request) );

            $object->save();

            wp_update_post(array(
                'ID' => $object->get_id(),
                'post_type' => 'shop_order',
            ));

            // Actions for after the order is saved.
            if ( true === $request['set_paid'] ) {
                if ( $creating || $object->needs_payment() ) {
                    $this->process_payment($object->get_id(), $request);
                }
            }

            sentEmailReceipt($object->get_id());

            return $this->get_object( $object->get_id() );
        } catch ( WC_Data_Exception $e ) {
            $this->purge( $object, $creating );
            return new WP_Error( $e->getErrorCode(), $e->getMessage(), $e->getErrorData() );
        } catch ( WC_REST_Exception $e ) {
            $this->purge( $object, $creating );
            return new WP_Error( $e->getErrorCode(), $e->getMessage(), array( 'status' => $e->getCode() ) );
        }
    }


    /**
     * @param WP_REST_Request $request
     * @param bool $creating
     * @return WC_Order $order
     * @throws WC_Data_Exception
     * @throws WC_REST_Exception
     */
    protected function prepare_object_for_database($request, $creating = false ) {
        $id        = isset( $request['id'] ) ? absint( $request['id'] ) : 0;
        $order     = new WC_Order( $id );
        $schema    = $this->get_item_schema();
        $data_keys = array_keys( array_filter( $schema['properties'], array( $this, 'filter_writable_props' ) ) );

        // in case of error items will be saved. So again when we do the order it may double up items.
        // therefore we remove all items before proceed.
        if($creating) $order->remove_order_items();

        // POS data modifications
        if (isset($request['create_post']) && is_array($request['create_post'])) {
            foreach ($request['create_post'] as $post) {
                if (is_array($post)) {
                    foreach ($post as $key => $value) {
                        $_POST[$key] = $value;
                    }
                }
            }
        }

        $new_meta_data = $request->get_param('meta_data');

        $new_meta_data[] = array(
            'key' => '_order_number',
            'value' => isset($request['order_number']) ? $request['order_number'] : $order->get_order_number()
        );

        $served_by = get_userdata(get_current_user_id());
        $served_by_name = '';
        if ($served_by) {
            $served_by_name = $served_by->display_name;
        }

        $new_meta_data[] = array(
            'key' => 'wc_pos_served_by_name',
            'value' => $served_by_name
        );
        $new_meta_data[] = array(
            'key' => 'wc_pos_served_by',
            'value' => get_current_user_id()
        );

        if(isset($request['custom_order_meta'])){
            foreach ($request['custom_order_meta'] as $meta_key => $meta_value) {
                if (is_string($meta_key)) {
                    $new_meta_data[] = array(
                        'key' => $meta_key,
                        'value' => $meta_value
                    );
                }
            }
        }

        $request->set_param('customer_id', (int) $this->get_customer_id($request));
        $request->set_param('meta_data', $new_meta_data);

        // Handle all writable props.
        foreach ( $data_keys as $key ) {
            $value = $request[ $key ];

            if ( ! is_null( $value ) ) {
                switch ( $key ) {
                    case 'coupon_lines':
                    case 'status':
                        // Change should be done later so transitions have new data.
                        break;
                    case 'billing':
                    case 'shipping':
                        $this->update_address( $order, $value, $key );
                        $this->update_user_data( $request['customer_id'], $value, $key );
                        break;
                    case 'line_items':
                    case 'shipping_lines':
                    case 'fee_lines':
                        if ( is_array( $value ) ) {
                            foreach ( $value as $item ) {
                                if ( is_array( $item ) ) {
                                    if ( $this->item_is_null( $item ) || ( isset( $item['quantity'] ) && 0 === $item['quantity'] ) ) {
                                        $order->remove_item( $item['id'] );
                                    } else {
                                        $this->set_item( $order, $key, $item );
                                    }
                                }
                            }
                        }
                        break;
                    case 'meta_data':
                        if ( is_array( $value ) ) {
                            foreach ( $value as $meta ) {
                                $order->update_meta_data( $meta['key'], $meta['value'], isset( $meta['id'] ) ? $meta['id'] : '' );
                            }
                        }
                        break;
                    default:
                        if ( is_callable( array( $order, "set_{$key}" ) ) ) {
                            $order->{"set_{$key}"}( $value );
                        }
                        break;
                }
            }
        }

        if($creating){
            $order->set_date_created(new WC_DateTime());
        }

        /**
         * Filters an object before it is inserted via the REST API.
         *
         * The dynamic portion of the hook name, `$this->post_type`,
         * refers to the object type slug.
         *
         * @param WC_Data         $order    Object object.
         * @param WP_REST_Request $request  Request object.
         * @param bool            $creating If is creating a new object.
         */
        return apply_filters( "woocommerce_rest_pre_insert_{$this->post_type}_object", $order, $request, $creating );
    }


    /**
     * @param WP_REST_Request $request
     * @param WC_Order $order
     * @return bool
     * @throws WC_REST_Exception
     */
    protected function calculate_coupons($request, $order ) {
        if ( ! isset( $request['coupon_lines'] ) || ! is_array( $request['coupon_lines'] ) ) {
            return false;
        }

        // Remove all coupons first to ensure calculation is correct.
        foreach ( $order->get_items( 'coupon' ) as $coupon_id => $coupon ) {
            if(strtolower($coupon->get_code()) == 'pos discount'){
                $order->remove_item($coupon_id);
            }else{
                $order->remove_coupon( $coupon->get_code() );
            }
        }

        foreach ( $request['coupon_lines'] as $item ) {
            if ( is_array( $item ) ) {
                if ( empty( $item['id'] ) ) {
                    if ( empty( $item['code'] ) ) {
                        throw new WC_REST_Exception( 'woocommerce_rest_invalid_coupon', __( 'Coupon code is required.', 'woocommerce' ), 400 );
                    }

                    if (strtolower($item['code']) == 'pos discount') {
                        $coupon_item = new WC_Order_Item_Coupon();
                        $coupon_item->set_code($item['code']);
                        $coupon_item->set_discount($item['amount']);
                        $coupon_item->add_meta_data('wc_pos_discount_reason', $item['reason']);

                        if(isset($item['type']) && $item['type'] == 'percent' && isset($item['pamount'])) {
                            $coupon_item->add_meta_data('discount_amount_percent', $item['pamount']);
                        }

                        $order->add_item($coupon_item);
                    } elseif ($item['code'] == 'WC_POINTS_REDEMPTION' && class_exists('WC_Points_Rewards')) {
                        global $wc_points_rewards;
                        $discount_amount = $item['amount'];
                        $points_redeemed = WC_Points_Rewards_Manager::calculate_points_for_discount($discount_amount);

                        // deduct points
                        WC_Points_Rewards_Manager::decrease_points($order->get_user_id(), $points_redeemed, 'order-redeem', array('discount_code' => $item['code'], 'discount_amount' => $discount_amount), $order->get_id());

                        update_post_meta($order->get_id(), '_wc_points_redeemed', $points_redeemed);

                        // add order note
                        $order->add_order_note(sprintf(__('%d %s redeemed for a %s discount.', 'woocommerce-points-and-rewards'), $points_redeemed, $wc_points_rewards->get_points_label($points_redeemed), wc_price($discount_amount)));
                    } else {
                        $results = $order->apply_coupon( wc_clean( $item['code'] ) );

                        if ( is_wp_error( $results ) ) {
                            throw new WC_REST_Exception( 'woocommerce_rest_' . $results->get_error_code(), $results->get_error_message(), 400 );
                        }
                    }
                }
            }
        }

        return true;
    }


    /**
     * Wrapper method to create/update order items.
     * When updating, the item ID provided is checked to ensure it is associated
     * with the order.
     *
     * @param WC_Order $order order object.
     * @param string   $item_type The item type.
     * @param array    $posted item provided in the request body.
     * @throws WC_REST_Exception If item ID is not associated with order.
     */
    protected function set_item( $order, $item_type, $posted ) {
        global $wpdb;

        if ( ! empty( $posted['id'] ) ) {
            $action = 'update';
        } else {
            $action = 'create';
        }

        $method = 'prepare_' . $item_type;
        $item   = null;

        // Verify provided line item ID is associated with order.
        if ( 'update' === $action ) {
            $item = $order->get_item( absint( $posted['id'] ), false );

            if ( ! $item ) {
                throw new WC_REST_Exception( 'woocommerce_rest_invalid_item_id', __( 'Order item ID provided is not associated with order.', 'woocommerce' ), 400 );
            }
        }

        // Prepare item data.
        $item = $this->$method( $posted, $action, $item );

        do_action( 'woocommerce_rest_set_order_item', $item, $posted );

        $product = wc_get_product($posted['product_id']);
        $item_id = $item->save();

        if (isset($posted['hidden_fields']) && isset($posted['hidden_fields']['booking'])) {

            parse_str($posted['hidden_fields']['booking'], $booking_data);

            if (isset($booking_data['booking_id'])) {
                $new_booking = get_wc_booking($booking_data['booking_id']);
                $new_booking->update_status('in-cart');
                $new_booking->set_order_id($order->get_id(), $item_id);
            } else {
                $booking_form = new WC_Booking_Form($product);
                $cart_item_meta = array();
                $cart_item_meta['booking'] = $booking_form->get_posted_data($booking_data);
                $cart_item_meta['booking']['_cost'] = $booking_form->calculate_booking_cost($booking_data);
                $cart_item_meta['booking']['_order_item_id'] = $item_id;

                // Create the new booking
                $new_booking = $this->create_booking_from_cart_data($cart_item_meta, $product->get_id());
                $new_booking->set_customer_id($order->get_customer_id());
                $new_booking->set_order_id($order->get_id());
                $new_booking->save();
            }

            // Schedule this item to be removed from the cart if the user is inactive
            $this->schedule_cart_removal($new_booking->get_id());
        }

        // If creating the order, add the item to it.
        if ( 'create' === $action ) {
            $order->add_item( $item );
        } else {
            $item->save();
        }
    }


    /**
     * @param array $posted
     * @param string $action
     * @param null $item
     * @return WC_Order_Item_Product|null
     * @throws WC_REST_Exception
     */
    protected function prepare_line_items($posted, $action = 'create', $item = null)
    {
        $item    = is_null( $item ) ? new WC_Order_Item_Product( ! empty( $posted['id'] ) ? $posted['id'] : '' ) : $item;
        $product = wc_get_product( $this->get_product_id( $posted ) );

        if ( $product !== $item->get_product() ) {
            $item->set_product( $product );

            if ( 'create' === $action ) {
                $quantity = isset( $posted['quantity'] ) ? $posted['quantity'] : 1;
                $total    = wc_get_price_excluding_tax( $product, array( 'qty' => $quantity ) );
                $item->set_total( $total );
                $item->set_subtotal( $total );
            }
        }
        $this->maybe_set_item_props( $item, array( 'name', 'quantity', 'total', 'subtotal', 'tax_class', 'taxes' ), $posted );
        $this->maybe_set_item_meta_data( $item, $posted );

        return $item;
    }

    /**
     * Process payment
     * @param $order_id
     * @param $data
     * @throws Exception
     */
    public function process_payment($order_id, $data)
    {
        $object = wc_get_order($order_id);
        if (in_array($data['payment_method'], array("pos_chip_pin", "pos_chip_pin2", "pos_chip_pin3", "cod", "bacs", "cheque"))) {
            $object->payment_complete();
            return;
        }

        if (isset($data['paymentSense'])){
            $object->payment_complete($data['paymentSense']['transactionId']);
            return;
        }

        // some gateways check if a user is signed in, so let's switch to customer
        $logged_in_user = get_current_user_id();
        $customer_id = isset($data['customer_id']) ? $data['customer_id'] : 0;
        wp_set_current_user($customer_id);

        // load the gateways & process payment
        $gateway_id = $data['payment_method'];
        switch ($gateway_id) {
            case 'stripe':
                $_POST['stripe_source'] = $data['stripe_source'];
                break;
            case 'simplify_commerce':
                $_POST['simplify_token'] = $data['simplify_token'];
                break;
            case 'telematika_secure_acceptance_sop':
                $docompleteorder = 1;
                break;
            case 'cybersource_secure_acceptance_sop':
                $docompleteorder = 1;
                break;
        }

        $gateways = WC()->payment_gateways->get_available_payment_gateways();
        if (isset($_POST['all_fields'])) {
            $_POST['all_fields'] = $_POST['all_fields'] . '&terms=1';
        }

        if($gateway_id === 'authorize_net_cim_credit_card'){
            add_filter('wc_authorize_net_cim_api_request_data', function ($data, $order, $xml){
                if(isset($data['createTransactionRequest']['transactionRequest']['payment'])){
                    $card_data = wp_parse_args($_POST, array(
                        'wc-authorize-net-cim-credit-card-account-number' => '',
                        'wc-authorize-net-cim-credit-card-expiry' => '',
                        'wc-authorize-net-cim-credit-card-csc' => ''
                    ));
                    $data['createTransactionRequest']['transactionRequest']['payment'] = array(
                        'creditCard' => array(
                            'cardNumber' => $card_data['wc-authorize-net-cim-credit-card-account-number'],
                            'expirationDate' => $card_data['wc-authorize-net-cim-credit-card-expiry'],
                            'cardCode' => $card_data['wc-authorize-net-cim-credit-card-csc'],
                        )
                    );
                }
                return $data;
            }, 10, 3);
        }

        $result = $gateways[$gateway_id]->process_payment($order_id);
        if(isset($result['result']) && $result['result'] != "success"){
            throw new WC_REST_Exception( 'woocommerce_rest_payment_error', __( 'An error occurred while processing the card.', 'woocommerce' ), 400 );
        }

        wp_set_current_user($logged_in_user);
    }

    /**
     * Create booking from cart data
     */
    private function create_booking_from_cart_data($cart_item_meta, $product_id, $status = 'in-cart')
    {
        // Create the new booking
        $new_booking_data = array(
            'product_id' => $product_id, // Booking ID
            'cost' => $cart_item_meta['booking']['_cost'], // Cost of this booking
            'start_date' => $cart_item_meta['booking']['_start_date'],
            'end_date' => $cart_item_meta['booking']['_end_date'],
            'all_day' => $cart_item_meta['booking']['_all_day'],
            'order_item_id' => $cart_item_meta['booking']['_order_item_id']
        );

        // Check if the booking has resources
        if (isset($cart_item_meta['booking']['_resource_id'])) {
            $new_booking_data['resource_id'] = $cart_item_meta['booking']['_resource_id']; // ID of the resource
        }

        // Checks if the booking allows persons
        if (isset($cart_item_meta['booking']['_persons'])) {
            $new_booking_data['persons'] = $cart_item_meta['booking']['_persons']; // Count of persons making booking
        }

        $new_booking = get_wc_booking($new_booking_data);
        $new_booking->create($status);

        return $new_booking;
    }

    /**
     * Schedule booking to be deleted if inactive
     */
    public function schedule_cart_removal($booking_id)
    {
        wp_clear_scheduled_hook('wc-booking-remove-inactive-cart', array($booking_id));
        wp_schedule_single_event(apply_filters('woocommerce_bookings_remove_inactive_cart_time', time() + (60 * 15)), 'wc-booking-remove-inactive-cart', array($booking_id));
    }

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

            if(isset($data['refund_payment'])){
                $api_refund = false;
                $this->set_refund_data($data, $refund);
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

    public function get_tax_location($order_id, $id_register = 0, $args = array())
    {
        $order = wc_get_order($order_id);
        $tax_based_on = get_option('woocommerce_pos_calculate_tax_based_on', 'outlet');
        if (empty($tax_based_on) || $tax_based_on == 'default') {
            $tax_based_on = get_option('woocommerce_tax_based_on');
        }

        if ( 'shipping' === $tax_based_on && ! $order->get_shipping_country() ) {
            $tax_based_on = 'billing';
        }

        $args = wp_parse_args(
            $args,
            array(
                'country'  => 'billing' === $tax_based_on ? $order->get_billing_country() : $order->get_shipping_country(),
                'state'    => 'billing' === $tax_based_on ? $order->get_billing_state() : $order->get_shipping_state(),
                'postcode' => 'billing' === $tax_based_on ? $order->get_billing_postcode() : $order->get_shipping_postcode(),
                'city'     => 'billing' === $tax_based_on ? $order->get_billing_city() : $order->get_shipping_city(),
            )
        );

        if(in_array($tax_based_on, array('shipping', 'billing')) && !$order->get_customer_id()){
            $default_customer_addr = get_option('woocommerce_pos_tax_default_customer_address', 'outlet');
            switch ($default_customer_addr) {
                case 'base':
                    $default = wc_get_base_location();
                    $args['country']  = $default['country'];
                    $args['state']    = $default['state'];
                    $args['postcode'] = '';
                    $args['city']     = '';
                    break;
                case 'outlet':
                    $default = wc_pos_get_outlet_location($id_register);
                    $args['country']  = $default['contact']['country'];
                    $args['state']    = $default['contact']['state'];
                    $args['postcode'] = $default['contact']['postcode'];
                    $args['city']     = $default['contact']['city'];
                    break;
            }
        }else if ( 'base' === $tax_based_on) {
            $default          = wc_get_base_location();
            $args['country']  = $default['country'];
            $args['state']    = $default['state'];
            $args['postcode'] = '';
            $args['city']     = '';
        }else if ('outlet' === $tax_based_on) {
            $default = wc_pos_get_outlet_location($id_register);
            $args['country']  = $default['contact']['country'];
            $args['state']    = $default['contact']['state'];
            $args['postcode'] = $default['contact']['postcode'];
            $args['city']     = $default['contact']['city'];
        }

        return $args;
    }

    /**
     * @param array $refund_data
     * @param WC_Order_Refund $refund_object
     */
    public function set_refund_data($refund_data, $refund_object)
    {
        $payment_data = $refund_data['pos_refund_payment'];
        $order = wc_get_order($refund_data['order_id']);

        if(!$order){
            return;
        }

        $refund_message = isset($payment_data['transaction_id']) ?
            sprintf(
                __( 'Refunded %1$s - Refund ID: %2$s - Reason: %3$s', 'wc_point_of_sale' ),
                $refund_object->get_amount(),
                $payment_data["transaction_id"],
                $refund_object->get_reason()
            )
            :   __('Refunded %1$s', $refund_object->get_formatted_refund_amount());

        $order->add_order_note( $refund_message );
    }

    protected function query_orders( $args ) {

        // set base query arguments
        $query_args = array(
            'fields'      => 'ids',
            'post_type'   => $this->post_type,
            'post_status' => array_keys( wc_get_order_statuses() ),
        );

        // add status argument
        if ( ! empty( $args['status'] ) ) {
            $statuses                  = 'wc-' . str_replace( ',', ',wc-', $args['status'] );
            $statuses                  = explode( ',', $statuses );
            $query_args['post_status'] = $statuses;

            unset( $args['status'] );
        }

        if ( ! empty( $args['customer_id'] ) ) {
            $query_args['meta_query'] = array(
                array(
                    'key'     => '_customer_user',
                    'value'   => absint( $args['customer_id'] ),
                    'compare' => '=',
                ),
            );
        }

        $query_args = $this->merge_query_args( $query_args, $args );

        return new WP_Query( $query_args );
    }

    public function get_orders( $fields = null, $filter = array(), $status = null, $page = 1 ) {

        if ( ! empty( $status ) ) {
            $filter['status'] = $status;
        }

        $filter['page'] = $page;

        $query = $this->query_orders( $filter );

        $orders = array();

        foreach ( $query->posts as $order_id ) {
            if ( ! $this->is_readable( $order_id ) ) {
                continue;
            }

            $orders[] = current( $this->get_order( $order_id, $fields, $filter ) );
        }

        $this->server->add_pagination_headers( $query );

        return array( 'orders' => $orders );
    }

    public function get_order( $id, $fields = null, $filter = array() ) {

        // Ensure order ID is valid & user has permission to read.
        $id = $this->validate_request( $id, $this->post_type, 'read' );

        if ( is_wp_error( $id ) ) {
            return $id;
        }

        // Get the decimal precession.
        $dp     = ( isset( $filter['dp'] ) ? intval( $filter['dp'] ) : 2 );
        $order  = wc_get_order( $id );
        $expand = array();

        if ( ! empty( $filter['expand'] ) ) {
            $expand = explode( ',', $filter['expand'] );
        }

        $order_data = array(
            'id'                        => $order->get_id(),
            'order_number'              => $order->get_order_number(),
            'order_key'                 => $order->get_order_key(),
            'created_at'                => $this->server->format_datetime( $order->get_date_created() ? $order->get_date_created()->getTimestamp() : 0, false, false ), // API gives UTC times.
            'updated_at'                => $this->server->format_datetime( $order->get_date_modified() ? $order->get_date_modified()->getTimestamp() : 0, false, false ), // API gives UTC times.
            'completed_at'              => $this->server->format_datetime( $order->get_date_completed() ? $order->get_date_completed()->getTimestamp() : 0, false, false ), // API gives UTC times.
            'status'                    => $order->get_status(),
            'currency'                  => $order->get_currency(),
            'total'                     => wc_format_decimal( $order->get_total(), $dp ),
            'subtotal'                  => wc_format_decimal( $order->get_subtotal(), $dp ),
            'total_line_items_quantity' => $order->get_item_count(),
            'total_tax'                 => wc_format_decimal( $order->get_total_tax(), $dp ),
            'total_shipping'            => wc_format_decimal( $order->get_shipping_total(), $dp ),
            'cart_tax'                  => wc_format_decimal( $order->get_cart_tax(), $dp ),
            'shipping_tax'              => wc_format_decimal( $order->get_shipping_tax(), $dp ),
            'total_discount'            => wc_format_decimal( $order->get_total_discount(), $dp ),
            'shipping_methods'          => $order->get_shipping_method(),
            'payment_details' => array(
                'method_id'    => $order->get_payment_method(),
                'method_title' => $order->get_payment_method_title(),
                'paid'         => ! is_null( $order->get_date_paid() ),
            ),
            'billing_address' => array(
                'first_name' => $order->get_billing_first_name(),
                'last_name'  => $order->get_billing_last_name(),
                'company'    => $order->get_billing_company(),
                'address_1'  => $order->get_billing_address_1(),
                'address_2'  => $order->get_billing_address_2(),
                'city'       => $order->get_billing_city(),
                'state'      => $order->get_billing_state(),
                'postcode'   => $order->get_billing_postcode(),
                'country'    => $order->get_billing_country(),
                'email'      => $order->get_billing_email(),
                'phone'      => $order->get_billing_phone(),
            ),
            'shipping_address' => array(
                'first_name' => $order->get_shipping_first_name(),
                'last_name'  => $order->get_shipping_last_name(),
                'company'    => $order->get_shipping_company(),
                'address_1'  => $order->get_shipping_address_1(),
                'address_2'  => $order->get_shipping_address_2(),
                'city'       => $order->get_shipping_city(),
                'state'      => $order->get_shipping_state(),
                'postcode'   => $order->get_shipping_postcode(),
                'country'    => $order->get_shipping_country(),
            ),
            'note'                      => $order->get_customer_note(),
            'customer_ip'               => $order->get_customer_ip_address(),
            'customer_user_agent'       => $order->get_customer_user_agent(),
            'customer_id'               => $order->get_user_id(),
            'view_order_url'            => $order->get_view_order_url(),
            'line_items'                => array(),
            'shipping_lines'            => array(),
            'tax_lines'                 => array(),
            'fee_lines'                 => array(),
            'coupon_lines'              => array(),
        );

        // Add line items.
        foreach ( $order->get_items() as $item_id => $item ) {
            $product    = $item->get_product();
            $hideprefix = ( isset( $filter['all_item_meta'] ) && 'true' === $filter['all_item_meta'] ) ? null : '_';
            $item_meta  = $item->get_formatted_meta_data( $hideprefix, true );

            foreach ( $item_meta as $key => $values ) {
                $item_meta[ $key ]->label = $values->display_key;
                unset( $item_meta[ $key ]->display_key );
                unset( $item_meta[ $key ]->display_value );
            }

            $line_item = array(
                'id'           => $item_id,
                'subtotal'     => wc_format_decimal( $order->get_line_subtotal( $item, false, false ), $dp ),
                'subtotal_tax' => wc_format_decimal( $item->get_subtotal_tax(), $dp ),
                'total'        => wc_format_decimal( $order->get_line_total( $item, false, false ), $dp ),
                'total_tax'    => wc_format_decimal( $item->get_total_tax(), $dp ),
                'price'        => wc_format_decimal( $order->get_item_total( $item, false, false ), $dp ),
                'quantity'     => $item->get_quantity(),
                'tax_class'    => $item->get_tax_class(),
                'name'         => $item->get_name(),
                'product_id'   => $item->get_variation_id() ? $item->get_variation_id() : $item->get_product_id(),
                'sku'          => is_object( $product ) ? $product->get_sku() : null,
                'meta'         => array_values( $item_meta ),
            );

            if ( in_array( 'products', $expand ) && is_object( $product ) ) {
                $_product_data = WC()->api->WC_API_Products->get_product( $product->get_id() );

                if ( isset( $_product_data['product'] ) ) {
                    $line_item['product_data'] = $_product_data['product'];
                }
            }

            $order_data['line_items'][] = $line_item;
        }

        // Add shipping.
        foreach ( $order->get_shipping_methods() as $shipping_item_id => $shipping_item ) {
            $order_data['shipping_lines'][] = array(
                'id'           => $shipping_item_id,
                'method_id'    => $shipping_item->get_method_id(),
                'method_title' => $shipping_item->get_name(),
                'total'        => wc_format_decimal( $shipping_item->get_total(), $dp ),
            );
        }

        // Add taxes.
        foreach ( $order->get_tax_totals() as $tax_code => $tax ) {
            $tax_line = array(
                'id'       => $tax->id,
                'rate_id'  => $tax->rate_id,
                'code'     => $tax_code,
                'title'    => $tax->label,
                'total'    => wc_format_decimal( $tax->amount, $dp ),
                'compound' => (bool) $tax->is_compound,
            );

            if ( in_array( 'taxes', $expand ) ) {
                $_rate_data = WC()->api->WC_API_Taxes->get_tax( $tax->rate_id );

                if ( isset( $_rate_data['tax'] ) ) {
                    $tax_line['rate_data'] = $_rate_data['tax'];
                }
            }

            $order_data['tax_lines'][] = $tax_line;
        }

        // Add fees.
        foreach ( $order->get_fees() as $fee_item_id => $fee_item ) {
            $order_data['fee_lines'][] = array(
                'id'        => $fee_item_id,
                'title'     => $fee_item->get_name(),
                'tax_class' => $fee_item->get_tax_class(),
                'total'     => wc_format_decimal( $order->get_line_total( $fee_item ), $dp ),
                'total_tax' => wc_format_decimal( $order->get_line_tax( $fee_item ), $dp ),
            );
        }

        // Add coupons.
        foreach ( $order->get_items( 'coupon' ) as $coupon_item_id => $coupon_item ) {
            $coupon_line = array(
                'id'     => $coupon_item_id,
                'code'   => $coupon_item->get_code(),
                'amount' => wc_format_decimal( $coupon_item->get_discount(), $dp ),
            );

            if ( in_array( 'coupons', $expand ) ) {
                $_coupon_data = WC()->api->WC_API_Coupons->get_coupon_by_code( $coupon_item->get_code() );

                if ( ! is_wp_error( $_coupon_data ) && isset( $_coupon_data['coupon'] ) ) {
                    $coupon_line['coupon_data'] = $_coupon_data['coupon'];
                }
            }

            $order_data['coupon_lines'][] = $coupon_line;
        }

        return array( 'order' => apply_filters( 'woocommerce_api_order_response', $order_data, $order, $fields, $this->server ) );
    }

    /**
     * @param WP_REST_Request $data
     * @return int|WC_REST_Exception
     */
    public function get_customer_id($data)
    {
        global $wpdb;

        if ($data['create_account'] === true) {

            $billing_data = $data['billing'];

            $username_opt = get_option('woocommerce_pos_end_of_sale_username_add_customer');
            $wc_reg_generate_username_opt = get_option('woocommerce_registration_generate_username');
            $wc_reg_generate_pass_opt = get_option('woocommerce_registration_generate_password');
            if ($wc_reg_generate_username_opt == 'yes') {
                switch ($username_opt) {
                    case 2:
                        $username = str_replace(' ', '', strtolower($billing_data['first_name'])) . '-' . str_replace(' ', '', strtolower($billing_data['last_name']));
                        break;
                    case 3:
                        $username = $billing_data['email'];
                        break;
                    default:
                        $username = str_replace(' ', '', strtolower($billing_data['first_name'])) . str_replace(' ', '', strtolower($billing_data['last_name']));
                        break;
                }
            } else {
                $username = $billing_data['account_username'];
            }

            $username = _truncate_post_slug($username, 60);
            $check_sql = "SELECT user_login FROM {$wpdb->users} WHERE user_login = '%s' LIMIT 1";

            $user_name_check = $wpdb->get_var($wpdb->prepare($check_sql, $username));

            if ($user_name_check) {
                $suffix = 1;
                do {
                    $alt_user_name = _truncate_post_slug($username, 60 - (strlen($suffix) + 1)) . "-$suffix";
                    $user_name_check = $wpdb->get_var($wpdb->prepare($check_sql, $alt_user_name));
                    $suffix++;
                } while ($user_name_check);
                $username = $alt_user_name;
            }

            add_filter('pre_option_woocommerce_registration_generate_password', 'pos_enable_generate_password');
            $password = '';
            if ($wc_reg_generate_pass_opt == 'yes') {
                $password = isset($billing_data['account_password']) ? $billing_data['account_password'] : '';
            }
            $new_customer = wc_create_new_customer($billing_data['email'], $username, $password);
            remove_filter('pre_option_woocommerce_registration_generate_password', 'pos_enable_generate_password');

            if (is_wp_error($new_customer)) {
                return new WC_REST_Exception('woocommerce_api_cannot_create_customer_account', $new_customer->get_error_message(), 400);
            }

            // Add customer info from other billing fields
            if ($billing_data['first_name'] && apply_filters('wc_pos_checkout_update_customer_data', true, $this)) {
                $userdata = array(
                    'ID' => $new_customer,
                    'first_name' => $billing_data['first_name'] ? $billing_data['first_name'] : '',
                    'last_name' => $billing_data['last_name'] ? $billing_data['last_name'] : '',
                    'display_name' => $billing_data['first_name'] ? $billing_data['first_name'] : ''
                );
                wp_update_user(apply_filters('wc_pos_checkout_customer_userdata', $userdata, $this));
            }

            return $new_customer;
        }else{
            return $data['customer_id'];
        }
    }

    /**
     * @param WP_REST_Request $data
     * @return string
     */
    public function get_status($data)
    {
        $save_order_status = get_option('wc_pos_save_order_status', 'pending');
        if (empty($save_order_status)) {
            $save_order_status = 'pending';
        } else if (strpos($save_order_status, 'wc-') === 0) {
            $save_order_status = substr($save_order_status, 3);
        }

        if ($save_order_status && $data["action"] == "creating") {
            return $save_order_status;
        }

        return $data['status'];
    }

    public function get_item_schema()
    {
        $schema = parent::get_item_schema();

        $schema['properties']['line_items']['items']['properties']['quantity']['type'] = 'number';

        return $schema;
    }

    public function calculate_totals( $order_id = 0, $and_taxes = true ) {
        $order = wc_get_order($order_id);

        do_action( 'woocommerce_order_before_calculate_totals', $and_taxes, $order );

        $cart_subtotal     = 0;
        $cart_total        = 0;
        $fees_total         = 0;
        $shipping_total    = 0;
        $cart_subtotal_tax = 0;
        $cart_total_tax    = 0;

        // Sum line item costs.
        foreach ( $order->get_items() as $item ) {
            $cart_subtotal += round( $item->get_subtotal(), wc_get_price_decimals() );
            $cart_total    += round( $item->get_total(), wc_get_price_decimals() );
        }

        // Sum shipping costs.
        foreach ( $order->get_shipping_methods() as $shipping ) {
            $shipping_total += round( $shipping->get_total(), wc_get_price_decimals() );
        }

        $order->set_shipping_total( $shipping_total );

        // Sum fee costs.
        foreach ( $order->get_fees() as $item ) {
            $fee_total = $item->get_total();

            if ( 0 > $fee_total ) {
                $max_discount = round( $cart_total + $fees_total + $shipping_total, wc_get_price_decimals() ) * -1;

                if ( $fee_total < $max_discount ) {
                    $item->set_total( $max_discount );
                }
            }

            $fees_total += $item->get_total();
        }

        $order->save();

        // Calculate taxes for items, shipping, discounts. Note; this also triggers save().
        if ( $and_taxes ) {
            $this->calculate_taxes($order_id);
        }

        $order = wc_get_order($order_id);

        // Sum taxes again so we can work out how much tax was discounted. This uses original values, not those possibly rounded to 2dp.
        foreach ( $order->get_items() as $item ) {
            $taxes = $item->get_taxes();

            foreach ( $taxes['total'] as $tax_rate_id => $tax ) {
                $cart_total_tax += (float) $tax;
            }

            foreach ( $taxes['subtotal'] as $tax_rate_id => $tax ) {
                $cart_subtotal_tax += (float) $tax;
            }
        }

        $order->set_discount_total( $cart_subtotal - $cart_total );
        $order->set_discount_tax( wc_round_tax_total( $cart_subtotal_tax - $cart_total_tax ) );
        $order->set_total( round( $cart_total + $fees_total + $order->get_shipping_total() + $order->get_cart_tax() + $order->get_shipping_tax(), wc_get_price_decimals() ) );

        do_action( 'woocommerce_order_after_calculate_totals', $and_taxes, $order );

        $order_id = $order->save();

        return wc_get_order($order_id);
    }

    public function calculate_taxes( $order_id, $args = array() ) {

        $order = wc_get_order($order_id);

        do_action( 'woocommerce_order_before_calculate_taxes', $args, $order );

        $calculate_tax_for  = $this->get_tax_location( $order_id, $order->get_meta('wc_pos_id_register', true), $args );
        $shipping_tax_class = get_option( 'woocommerce_shipping_tax_class' );

        if ( 'inherit' === $shipping_tax_class ) {
            $found_classes      = array_intersect( array_merge( array( '' ), WC_Tax::get_tax_class_slugs() ), $order->get_items_tax_classes() );
            $shipping_tax_class = count( $found_classes ) ? current( $found_classes ) : false;
        }

        $is_vat_exempt = apply_filters( 'woocommerce_order_is_vat_exempt', 'yes' === $order->get_meta( 'is_vat_exempt' ), $this );

        // Trigger tax recalculation for all items.
        foreach ( $order->get_items( array( 'line_item', 'fee' ) ) as $item_id => $item ) {
            if ( ! $is_vat_exempt ) {
                $item->calculate_taxes( $calculate_tax_for );
            } else {
                $item->set_taxes( false );
            }
        }

        foreach ( $order->get_shipping_methods() as $item_id => $item ) {
            if ( false !== $shipping_tax_class && ! $is_vat_exempt ) {
                $item->calculate_taxes( array_merge( $calculate_tax_for, array( 'tax_class' => $shipping_tax_class ) ) );
            } else {
                $item->set_taxes( false );
            }
        }

        $order->update_taxes();
    }

    public function update_user_data($user_id, $posted, $type = 'billing')
    {
        foreach ( $posted as $key => $value ) {
            update_user_meta($user_id, $type . '_' . $key, $value);
        }
    }

    public function init_cart($request)
    {
        /*
         * @todo find a best solution
         * this is a temporary solution as payment gateways try to empty the cart.
         * but as we are doing virtual cart we cant provide cart.
         * therefore we have not initialized cart without any items.
         * Then gateways will empty the cart.
         * */
        include_once WC_ABSPATH . 'includes/wc-cart-functions.php';
        include_once WC_ABSPATH . 'includes/wc-notice-functions.php';

        $session_class = apply_filters( 'woocommerce_session_handler', 'WC_Session_Handler' );
        WC()->session = new $session_class();
        WC()->session->init();
        WC()->customer = new WC_Customer( isset($request['customer_id']) ? $request['customer_id'] : 0 );
        WC()->cart = new WC_Cart();
    }

}
