<?php

class WC_POS_My_Account
{
    
    public function __construct()
    {
        add_action( 'init', array($this, 'pos_endpoint') );
        add_filter( 'query_vars', array($this, 'pos_query_vars'));
        add_filter( 'woocommerce_account_menu_items', array($this, 'pos_myaccount_tab') );
        add_action( 'woocommerce_account_point-of-sale_endpoint', array($this, 'pos_myaccount_content') );
    }

    public function pos_endpoint()
    {
        add_rewrite_endpoint( 'point-of-sale', EP_ROOT | EP_PAGES );
        flush_rewrite_rules();
    }

    public function pos_query_vars($vars)
    {
        $vars[] = 'point-of-sale';
        return $vars;
    }

    public function pos_myaccount_tab($items)
    {
        $new_items = array();
        foreach ($items as $key => $item){
            $new_items[$key] = $item;
            if($key == "edit-account"){
                $new_items["point-of-sale"] = __("Point of Sale", "wc_point_of_sale");
            }
        }

        return $new_items;
    }

    public function pos_myaccount_content()
    {
        include_once('views/html-my-account-tab.php');
    }
}

new WC_POS_My_Account();