<?php

class WC_Pos_PaymentSense_API
{
    public $base_url;
    public $settings;
    public $username;
    public $key;

    public function __construct()
    {
        $this->init();
    }

    /**
     * void init
     */
    protected function init()
    {
        $this->init_settings();

        $this->base_url = $this->settings["payment_sense_url"];
        $this->username = 'user';
        $this->key = $this->settings["payment_sense_api_key"];
    }

    /**
     * @return string
     */
    protected function get_base_url(){

        $url = esc_url($this->base_url);
        if(empty($url)){
            return '';
        }

        $parsed = parse_url($url);
        if($parsed["scheme"] == "http"){
            $url = str_replace("http", "https", $url);
        }

        return trailingslashit($url);
    }

    /**
     * @return array
     */
    protected function init_settings(){

        $this->settings = get_option("woocommerce_wc_pos_paymentsense_settings", array(
            'payment_sense_url' => '',
            'payment_sense_api_key' => ''
        ));
        return $this->settings;
    }

    /**
     * @return array
     */
    protected function get_headers(){
        return array(
            'Content-Type' => 'application/json',
            'Accept'       => 'application/connect.v1+json',
            'Authorization' => 'Basic ' . base64_encode($this->username . ":" . $this->key)
        );
    }

    /**
     * @param array $data
     * @return array|WP_Error
     */
    protected function request($url, $data = array()){

        $data['headers'] = $this->get_headers();

        return wp_remote_request($url, $data);
    }

    /**
     * @param int $single
     * @return array|WP_Error
     */
    public function pac_terminals($single = 0)
    {
        $url = $this->get_base_url() . 'pac/terminals';
        if(!empty($single)){
            $url .= "/" . $single;
        }

        return $this->request($url, array());
    }

    public function pac_terminals_response($single = 0)
    {
        $response = $this->pac_terminals($single);
        if($response instanceof WP_Error){
            return array();
        }

        return json_decode(wp_remote_retrieve_body($response), true);
    }

    /**
     * @param string $tpi
     * @param string $single
     * @param array $data
     * @return array|WP_Error
     */
    public function pac_transactions($tpi = "0", $single = '', $data = array())
    {
        $url = $this->get_base_url() . "pac/terminals/$tpi/transactions";
        if(!empty($single)){
            $url .= "/" . $single;
        }

        return $this->request($url, $data);
    }

    /**
     * @param string $tpi
     * @param int $single
     * @param array $data
     * @return array|WP_Error
     */
    public function pac_reports($tpi = "0", $single = 0, $data = array()){
        $url = $this->get_base_url() . "pac/terminals/$tpi/reports";
        if(!empty($single)){
            $url .= "/" . $single;
        }

        return $this->request($url, $data);
    }

    public function get_first_error_message($response){
        $message = __("An error occurred", "wc_point_of_sale");
        if(!isset($response["messages"]) || !count($response["messages"])){
            return $message;
        }

        foreach ($response["messages"] as $error){
            $message = $error[0];
            break;
        }

        return $message;
    }
}