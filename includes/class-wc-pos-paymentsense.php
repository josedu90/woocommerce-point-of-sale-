<?php

/**
 * Class WC_POS_PaymentSense_Gateway file.
 *
 * @package WC_POS\Includes
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class WC_POS_PaymentSense_Gateway extends WC_Payment_Gateway{

    private $api;
    private $terminals = array();

    public function __construct() {

        // Setup general properties.
        $this->setup_properties();

        // Load the settings.
        $this->init_form_fields();
        $this->init_settings();

        // Get settings.
        $this->title              = $this->get_option( 'title' );
        $this->description        = $this->get_option( 'description' );
        $this->instructions       = $this->get_option( 'instructions' );
        $this->enable_for_methods = $this->get_option( 'enable_for_methods', array() );
        $this->enable_for_virtual = $this->get_option( 'enable_for_virtual', 'yes' ) === 'yes';
        $this->api                = new WC_Pos_PaymentSense_API();

        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
        add_filter( 'woocommerce_payment_complete_order_status', array( $this, 'change_payment_complete_order_status' ), 10, 3 );

    }

    private function setup_properties() {
        $this->id                 = 'wc_pos_paymentsense';
        $this->icon               = apply_filters( 'wc_pos_paymentsense_icon', '' );
        $this->method_title       = __( 'Paymentsense', 'wc_point_of_sale' );
        $this->method_description = __( 'Take payments in person via EMV. More commonly known as chip & PIN.', 'wc_point_of_sale' );
        $this->has_fields         = true;
    }

    public function init_form_fields() {

        $this->form_fields = array(
            'enabled'            => array(
                'title'       => __( 'Enable/Disable', 'wc_point_of_sale' ),
                'label'       => __( 'Enable Payment Sense', 'wc_point_of_sale' ),
                'type'        => 'checkbox',
                'description' => '',
                'default'     => 'no',
            ),
            'title'              => array(
                'title'       => __( 'Title', 'wc_point_of_sale' ),
                'type'        => 'text',
                'description' => __( 'This controls the title which the user sees during checkout.', 'wc_point_of_sale' ),
                'default'     => __( 'Paymentsense', 'wc_point_of_sale' ),
                'desc_tip'    => true,
            ),
            'description'        => array(
                'title'       => __( 'Description', 'wc_point_of_sale' ),
                'type'        => 'textarea',
                'description' => __( 'Payment method description that the customer will see on your website.', 'wc_point_of_sale' ),
                'default'     => __( 'Pay with EMV terminal.', 'wc_point_of_sale' ),
                'desc_tip'    => true,
            ),
            'payment_sense_credentials'       => array(
                'title'       => __( 'Credentials', 'wc_point_of_sale' ),
                'type'        => 'title',
                'description' => __( 'Please enter the settings given to you by Paymentsense when setting up your account. This includes the Host Address and an API Key.', 'wc_point_of_sale' ),
            ),
            'payment_sense_url'        => array(
                'title'       => __( 'Host Address', 'wc_point_of_sale' ),
                'type'        => 'text',
                'description' => __( 'Enter the Paymentsense Host Address.', 'wc_point_of_sale' ),
                'desc_tip'    => true,
            ),
            'payment_sense_api_key'        => array(
                'title'       => __( 'API Key', 'wc_point_of_sale' ),
                'type'        => 'text',
                'description' => __( 'Enter the Paymentsense API Key.', 'wc_point_of_sale' ),
                'desc_tip'    => true,
            )
        );
    }

    /**
     * Check If The Gateway Is Available For Use.
     *
     * @return bool
     */
    public function is_available() {
        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        if(!$screen || $screen->id !== 'pos_page'){
            return false;
        }

        return parent::is_available();
    }


    /**
     * Process the payment and return the result.
     *
     * @param int $order_id Order ID.
     * @return array
     */
    public function process_payment( $order_id ) {
        global $woocommerce;
        $order = wc_get_order($order_id);

        $transaction_data = $_POST['paymentSenseData'];

        $order->payment_complete($transaction_data["transactionId"]);
        $order->add_order_note(__("Payment Sense payment complete (Transaction ID: ", "wc_point_of_sale") . $transaction_data["transactionId"]  .  ")");

        return array(
            'result' => 'success',
            'messages' => 'success'
        );

    }

    public function admin_options()
    {
        $this->test_connection();
        $this->display_errors();

        parent::admin_options();

        if(count($this->terminals)) : ?>
        <h3 class="wc-settings-sub-title "><?php _e("Available Terminals", "wc_point_of_sale") ?></h3>
        <ol>
        <?php
        foreach ($this->terminals as $terminal){
            echo "<li>".$terminal['tpi']."</li>";
        }
        ?>
        </ol>
        <?php endif;
    }

    public function test_connection()
    {
        $response = $this->api->pac_terminals(0);

        if(is_wp_error($response)){
            $this->add_error(__("An error occurred while checking the connection: ") . '<strong>' . $response->get_error_message() . '</strong>');
            return false;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        if(wp_remote_retrieve_response_code($response) != 200){
            $message = isset($body['messages']) && count($body['messages']) ? $body['messages']['error'][0] : wp_remote_retrieve_response_message($response);

            $this->add_error($message);
            return false;
        }

        if(isset($body["terminals"]) && count($body["terminals"])){
            $this->terminals = $body["terminals"];
        }

        return true;

    }

    public function get_polling_transaction($tpi, $transaction, $data)
    {
        $response = $this->api->pac_transactions($tpi, $transaction, $data);
        return $response;
    }

    /**
     * @param $status
     * @param $order_id
     * @param WC_Order $order
     * @return string
     */
    public function change_payment_complete_order_status($status, $order_id, $order)
    {
        if ( $order && 'wc_pos_paymentsense' === $order->get_payment_method() ) {
            $status = 'completed';
        }
        return $status;
    }
}