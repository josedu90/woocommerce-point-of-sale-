<?php

if (!defined('ABSPATH')) exit;

class WC_POS
{

    /**
     * The single instance of WC_POS.
     * @var     object
     * @access  private
     * @since 1.9
     */
    private static $_instance = null;

    /*
     *
     * TODO: It is fix for double showing notices
    */
    private $show_notices = true;

    /**
     * The version number.
     * @var     string
     * @access  public
     * @since    3.0.5
     */
    public $_version;

    /**
     * @var string
     */
    public $db_version = '4.3.6';

    /**
     * The token.
     * @var     string
     * @access  public
     * @since    3.0.5
     */
    public $_token;

    /**
     * The main plugin file.
     * @var     string
     * @access  public
     * @since    3.0.5
     */
    public $file;

    /**
     * The main plugin directory.
     * @var     string
     * @access  public
     * @since    3.0.5
     */
    public $dir;

    /**
     * The plugin assets directory.
     * @var     string
     * @access  public
     * @since    3.0.5
     */
    public $assets_dir;

    /**
     * The plugin assets URL.
     * @var     string
     * @access  public
     * @since    3.0.5
     */
    public $assets_url;

    /**
     * Suffix for Javascripts.
     * @var     string
     * @access  public
     */
    public $script_suffix;

    /**
     * @var bool
     */
    public $is_pos = null;

    /**
     * @var bool
     */
    public $wc_api_is_active = false;

    /**
     * @var string
     */
    public $permalink_structure = '';

    public $users = null;
    /**
     * The plugin's ids
     * @var string
     */
    public $id = 'wc_point_of_sale';
    public $id_outlets = 'wc_pos_outlets';
    public $id_registers = 'wc_pos_registers';
    public $id_grids = 'wc_pos_grids';
    public $id_tiles = 'wc_pos_tiles';
    public $id_users = 'wc_pos_users';
    public $id_receipts = 'wc_pos_receipts';
    public $id_barcodes = 'wc_pos_barcodes';
    public $id_stock_c = 'wc_pos_stock_controller';
    public $id_settings = 'wc_pos_settings';
    public $id_session_reports = 'wc_pos_session_reports';

    /**
     * Constructor function.
     * @access  public
     * @return  void
     */
    public function __construct($file = '', $version = '1.0.0')
    {
        $this->tables = array();
        $this->_version = $version;
        $this->_token = 'wc_pos';

        // Load plugin environment variables
        $this->file = $file;
        $this->dir = dirname($this->file);
        $this->assets_dir = trailingslashit($this->dir) . 'assets';
        $this->assets_url = esc_url(trailingslashit(plugins_url('/assets/', $this->file)));

        $this->script_suffix = '';

        $this->define_constants();
        $this->load_plugin_textdomain();
        $this->includes();
        $this->init_hooks();

        $this->users = $this->user();
        do_action('woocommerce_poin_of_sale_loaded');
        add_action('wp_ajax_generate_rest_api', array($this, 'generate_rest_api'));
    } // End __construct ()

    public function pos_register_status($response, $data)
    {
        if ( empty( $data['pos_register_id'] ) ) {
            return $response;
        }

        $is_lock = pos_check_register_lock($data['pos_register_id']);
        if(!$is_lock){
            return $response;
        }

        $user_data = get_userdata($is_lock)->to_array();

        $response['register_status_data'] = array(
            "ID" => $user_data["ID"],
            "display_name" => $user_data["display_name"],
            "user_nicename" => $user_data["user_nicename"]
        );
        return $response;
    }

    /**
     * Define WC_POS Constants
     */
    private function define_constants()
    {
        $upload_dir = wp_upload_dir();

        $this->define('WC_POS_FILE', $this->file);
        $this->define('WC_POS_PLUGIN_FILE', $this->file);
        $this->define('WC_POS_BASENAME', plugin_basename($this->file));
        $this->define('WC_POS_DIR', $this->dir);
        $this->define('WC_POS_VERSION', $this->_version);
        $this->define('WC_POS_TOKEN', $this->_token);

    }

    /**
     * Define constant if not already set
     * @param  string $name
     * @param  string|bool $value
     */
    private function define($name, $value)
    {
        if (!defined($name)) {
            define($name, $value);
        }
    }

    /**
     * What type of request is this?
     * string $type ajax, frontend or admin
     * @return bool
     */
    private function is_request($type)
    {
        switch ($type) {
            case 'admin' :
                return is_admin();
            case 'ajax' :
                return defined('DOING_AJAX');
            case 'cron' :
                return defined('DOING_CRON');
            case 'frontend' :
                return (!is_admin() || defined('DOING_AJAX')) && !defined('DOING_CRON');
        }
    }

    /**
     * Include required core files used in admin and on the frontend.
     */
    public function includes()
    {
        include_once('class-wc-pos-autoloader.php');
        include_once('core-functions.php');
        include_once('grids-functions.php');
        include_once('tiles-functions.php');
        include_once('class-wc-pos-install.php');
        include_once('admin/class-wc-pos-admin.php');
        include_once('class-wc-pos-float-cash.php');
        include_once('class-wc-pos-bill-screen.php');

        include_once('class-wc-pos-cloud-print.php');
        if(get_option("wc_pos_enable_cloud_print", "disable") == "enable"){
            include_once('lib/tcpdf/tcpdf.php');
            include_once('class-wc-pos-tcpdf.php');
        }

        // frontend only
        if (!is_admin()) {
            include_once('class-wc-pos-sell.php');
            if(get_option("wc_pos_my_account", "no") == "yes"){
                include_once('class-wc-pos-my-account.php');
            }
        }

        if (defined('DOING_AJAX')) {
            $this->ajax_includes();
        }
    }

    /**
     * Include required ajax files.
     */
    public function ajax_includes()
    {
        include_once('class-wc-pos-ajax.php');         // Ajax functions for admin and the front-end
    }

    /**
     * Hook into actions and filters
     */
    public function init_hooks()
    {

        $this->wc_api_is_active = $this->check_api_active();
        $this->permalink_structure = get_option('permalink_structure');

        register_activation_hook($this->file, array($this, 'install'));
        register_deactivation_hook($this->file, array($this, 'wc_pos_deactivate'));

        add_action('upgrader_process_complete', array($this, 'update'), 10, 2);

        add_action('init', array($this, 'load_localisation'), 0);
        add_action('admin_init', array($this, 'print_report'), 100);
        add_action('init', array($this, 'check_pos_visibility_products'));
        //Pos only products
        add_action('init', array($this, 'wc_pos_visibility_action'));
        add_action('woocommerce_loaded', array($this, 'change_stock_amount'), 10);

        add_action('admin_notices', array($this, 'admin_notices'));

        if ((isset($_POST['register_id']) && !empty($_POST['register_id'])) || (isset($_GET['page']) && $_GET['page'] == 'wc_pos_registers' && isset($_GET['action']) && $_GET['action'] == 'view' && isset($_GET['id']) && !empty($_GET['action']))) {
            add_filter('woocommerce_customer_taxable_address', array($this, 'set_outlet_taxable_address'));
        }

        add_filter('woocommerce_attribute_label', array($this, 'tile_attribute_label'));
        add_filter('woocommerce_get_checkout_order_received_url', array($this, 'order_received_url'));


        add_filter('woocommerce_email_actions', array($this, 'woocommerce_email_actions'), 150, 1);


        add_filter('request', array($this, 'orders_by_order_type'));

        add_filter('woocommerce_admin_order_actions', array($this, 'order_actions_reprint_receipts'), 2, 20);
        add_filter('woocommerce_order_number', array($this, 'add_prefix_suffix_order_number'), 10, 2);

        add_action('woocommerce_loaded', array($this, 'woocommerce_delete_shop_order_transients'));
        add_action('admin_init', array($this, 'add_caps'), 20, 4);

        add_action('woocommerce_hidden_order_itemmeta', array($this, 'hidden_order_itemmeta'), 150, 1);

        //WC_Subscriptions Compatibility
        if (in_array('woocommerce-subscriptions/woocommerce-subscriptions.php', get_option('active_plugins'))) {
            add_filter('woocommerce_subscription_payment_method_to_display', array($this, 'get_subscription_payment_method'), 10, 2);
        }
        //Pos custom product
        add_action('pre_get_posts', array($this, 'hide_pos_custom_product'), 15, 1);

        if (get_option('wc_pos_visibility', 'no') == 'yes') {

            add_action('quick_edit_custom_box', array($this, 'quick_edit'), 10, 2);
            add_action( 'woocommerce_product_bulk_edit_end',  array($this, 'bulk_edit'), 10, 0 );
            add_action('save_post', array($this, 'save_visibility'));
            add_action('woocommerce_product_bulk_edit_save', array($this, 'save_bulk_visibility'), 15, 1);
        }

        // Load admin JS & CSS
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'), 10, 1);
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_styles'), 10, 1);
        add_action('wp_enqueue_scripts', array($this, 'frontend_enqueue_styles'), 10, 1);
        add_action('admin_print_scripts', array($this, 'admin_inline_js'));
        add_filter('woocommerce_pos_register_discount_presets', array($this, 'add_custom_discounts'));

        add_filter('woocommerce_screen_ids', array($this, 'screen_ids'), 10, 1);

        //todo: uncomment when pamyentsense gateway is ready
        add_action('plugins_loaded', array($this, 'init_paymentsense_gateway'), 1);
        add_filter( 'woocommerce_payment_gateways', array($this, 'add_paymentsense_gateway'), 100 );
        add_filter( 'heartbeat_received', array($this, 'pos_register_status'), 10, 2 );
        add_filter( 'woocommerce_order_get_payment_method', array($this, 'pos_payment_gateway_labels'), 10, 2);
        add_filter('woocommerce_valid_order_statuses_for_payment_complete', array($this, 'pos_order_status_for_payment_complete'), 10, 2);
        add_filter('woocommerce_payment_complete_order_status', array($this, 'pos_complete_order_status'), 99, 3);
        add_action('woocommerce_payment_complete', array($this, 'pos_transaction_complete'), 99, 1);
        add_action('woocommerce_order_status_changed', array($this, 'pos_order_status_changed'), 1 , 4);
    }


    public function screen_ids($ids)
    {
        $ids[] = 'point-of-sale';
        return $ids;
    }

    function wc_pos_visibility_action()
    {
        if (get_option('wc_pos_visibility', 'no') == 'yes') {
            add_action('pre_get_posts', array($this, 'pos_only_products'), 15, 1);
            add_filter('views_edit-product', array($this, 'add_pos_only_filter'));
        }
    }

    public function quick_edit($column_name, $post_type)
    {
        global $post;

        if ('thumb' == $column_name && 'product' == $post_type) {
            include_once($this->plugin_path() . '/includes/admin/views/html-quick-edit-product-status.php');
        }

    }

    public function bulk_edit()
    {
        global $post;
        ?>
        <div class="inline-edit-group">
            <label class="alignleft">
                <span class="title"><?php esc_html_e( 'POS Status', 'wc_point_of_sale' ); ?></span>
                <span class="input-text-wrap">
                    <select class="pos_visibility" name="_pos_bulk_visibility">
                    <?php
                    $visibility_options = apply_filters('woocommerce_pos_visibility_options', array(
                        '' => __('— No Change —', 'wc_point_of_sale'),
                        'pos_online' => __('POS & Online', 'wc_point_of_sale'),
                        'pos' => __('POS Only', 'wc_point_of_sale'),
                        'online' => __('Online Only', 'wc_point_of_sale'),
                    ));
                    foreach ( $visibility_options as $key => $value ) {
                        echo "<option value='" . esc_attr( $key ) . "'>" . esc_html( $value ) . "</option>";
                    }
                    ?>
                    </select>
                </span>
            </label>
        </div>
        <?php
    }

    public function save_visibility($post_id)
    {

        if (!isset($_POST['post_type'])) {
            return;
        }

        if ('product' !== $_POST['post_type']) {
            return;
        }
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        update_post_meta($_POST['post_ID'], '_pos_visibility', $_POST['_pos_visibility']);
    }

    /**
     * @param $product
     */
    public function save_bulk_visibility($product)
    {
        $product_id = $product->get_id();

        if (!current_user_can('edit_post', $product_id)) {
            return;
        }

        update_post_meta($product_id, '_pos_visibility', $_REQUEST['_pos_bulk_visibility']);

    }

    function admin_inline_js()
    {
        echo "<script type='text/javascript'>\n";
        echo 'var wc_version = ' . intval(WC_VERSION) . ';';
        echo "\n</script>";
    }


    /**
     * @param WP_Query $query
     */
    public function hide_pos_custom_product($query)
    {
        $post__not_in = $query->get('post__not_in', array());

        if(!is_array($post__not_in)){
            $post__not_in = array($post__not_in);
        }

        $post__not_in[] = (int)get_option('wc_pos_custom_product_id');
        $query->set('post__not_in', $post__not_in);
    }

    public function pos_only_products($query)
    {
        if ( !isset($_GET['filter']['updated_at_min']) && !is_admin() && strpos($_SERVER['REQUEST_URI'], 'wp-json/wc') === false &&
            (isset($query->query_vars['post_type']) && $query->query_vars['post_type'] == 'product') ||
            (is_product_category() && !isset($query->query_vars['post_type'])) ||
            (is_product_tag() && !isset($query->query_vars['post_type'])) ) {

            $query->query_vars['meta_query']['pos_visibility'] = array(
                'key' => '_pos_visibility',
                'value' => 'pos',
                'compare' => '!=',
            );
            $query->query_vars['meta_query']['relation'] = 'AND';

            $query->set('meta_query', $query->query_vars['meta_query']);
        }
        if (isset($query->query_vars['post_type']) && $query->query_vars['post_type'] == 'product' && isset($_GET['pos_only'])) {
            $query->query_vars['meta_query']['pos_visibility'] = array(
                'key' => '_pos_visibility',
                'value' => 'pos',
                'compare' => '=',
            );
            $query->query_vars['meta_query']['relation'] = 'AND';

            $query->set('meta_query', $query->query_vars['meta_query']);
        }
        if (isset($query->query_vars['post_type']) && $query->query_vars['post_type'] == 'product' && isset($_GET['online_only'])) {

            $query->query_vars['meta_query']['pos_visibility'] = array(

                'key' => '_pos_visibility',
                'value' => 'online',
                'compare' => '=',

            );
            $query->query_vars['meta_query']['relation'] = 'AND';

            $query->set('meta_query', $query->query_vars['meta_query']);
        }
        if(!is_admin() && isset($query->query_vars['s']) && !empty($query->query_vars['s'])){
            $query->query_vars['meta_query']['pos_visibility'] = array(
                'key' => '_pos_visibility',
                'value' => 'pos',
                'compare' => '!=',
            );
            $query->query_vars['meta_query']['relation'] = 'AND';

            $query->set('meta_query', $query->query_vars['meta_query']);
        }

    }

    function add_pos_only_filter($views)
    {
        global $post_type_object;
        $post_type = $post_type_object->name;
        global $wpdb;
        //Pos only count
        $sql = "SELECT COUNT(post_id) FROM $wpdb->postmeta WHERE meta_key = '_pos_visibility' AND meta_value = 'pos'";
        $count = ($count = $wpdb->get_var($sql)) ? $count : 0;
        if ($count) {
            $class = (isset($_GET['pos_only'])) ? 'current' : '';
            $views['pos_only'] = "<a href='edit.php?post_type=$post_type&pos_only=1' class='$class'>" . __('POS Only', 'wc_point_of_sale') . " ({$count}) " . "</a>";
        }
        //Online only count
        $sql = "SELECT COUNT(post_id) FROM $wpdb->postmeta WHERE meta_key = '_pos_visibility' AND meta_value = 'online'";
        $count = ($count = $wpdb->get_var($sql)) ? $count : 0;
        if ($count) {
            $class = (isset($_GET['online_only'])) ? 'current' : '';
            $views['online_only'] = "<a href='edit.php?post_type=$post_type&online_only=1' class='$class'>" . __('Online Only', 'wc_point_of_sale') . " ({$count}) " . "</a>";
        }
        return $views;
    }

    /**
     * Load admin CSS.
     * @access  public
     * @return  void
     */
    public function admin_enqueue_styles($hook = '')
    {

        $wc_pos_version = $this->_version;
        wp_enqueue_style('wc-pos-fonts', $this->plugin_url() . '/assets/css/fonts.css', array(), $wc_pos_version);
        if (pos_admin_page()) {
            /****** START STYLE *****/
            wp_enqueue_style('thickbox');
            wp_enqueue_style('jquery-style', '//ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');

            wp_enqueue_style('woocommerce_frontend_styles', WC()->plugin_url() . '/assets/css/admin.css');

            wp_enqueue_style('woocommerce-style', WC()->plugin_url() . '/assets/css/woocommerce-layout.css', array(), $wc_pos_version);
            wp_enqueue_style('wc-pos-jquery-editable', $this->plugin_url() . '/assets/css/jquery-editable.css', array(), $wc_pos_version);
        }
        if (pos_barcodes_admin_page()) {
            wp_enqueue_style('wc-pos-barcode-options', $this->plugin_url() . '/assets/css/barcode-options.css', array(), $wc_pos_version);
        }
        if (pos_shop_order_page()) {
            wp_enqueue_style('wc-pos-style', $this->plugin_url() . '/assets/css/admin.css', array(), $wc_pos_version);
        }
        if (pos_receipts_admin_page() && isset($_GET['action']) && ($_GET['action'] == 'edit' || $_GET['action'] == 'add')) {
            wp_enqueue_style('codemirror-css', $this->plugin_url() . '/assets/plugins/codemirror/codemirror.css', array(), $wc_pos_version);
        }
        wp_enqueue_style('wc-pos-print', $this->plugin_url() . '/assets/css/print.css', array(), $wc_pos_version);
        wp_enqueue_style('wc-pos-style', $this->plugin_url() . '/assets/css/admin.css', array(), $wc_pos_version);

    } // End admin_enqueue_styles ()


    public function frontend_enqueue_styles()
    {
        wp_enqueue_style('wc-pos-frontend-style', $this->plugin_url() . '/assets/css/frontend.css', array(), $this->_version);
    }

    /**
     * Load admin Javascript.
     * @access  public
     * @return  void
     */
    public function admin_enqueue_scripts($hook = '')
    {
        global $post_type;

        $wc_pos_version = $this->_version;
        $scripts = array('jquery', 'wc-enhanced-select', 'jquery-blockui', 'jquery-tiptip');
        if(wp_script_is('woocommerce_admin')){
            $scripts[] = "woocommerce_admin";
        }
        if (pos_admin_page()) {
            wp_enqueue_script(array('jquery', 'editor', 'thickbox', 'jquery-ui-core', 'jquery-ui-datepicker'));

            wp_enqueue_script('postbox_', admin_url() . '/js/postbox.min.js', array(), '2.66');

            if (pos_tiles_admin_page()) {
                wp_enqueue_media();
                wp_enqueue_script('custom-background');
                wp_enqueue_style('wp-color-picker');
                wp_enqueue_script('jquery_cycle', $this->plugin_url() . '/assets/plugins/jquery.cycle.all.js', array('jquery'), $wc_pos_version);
                wp_enqueue_script('pos-colormin', $this->plugin_url() . '/assets/js/colormin.js', array('jquery'), $wc_pos_version);

                wp_enqueue_script('pos-script-tile-ordering', $this->plugin_url() . '/assets/js/tile-ordering.js', array('jquery'), $wc_pos_version);

            }

            if (pos_receipts_admin_page() && isset($_GET['action']) && ($_GET['action'] == 'edit' || $_GET['action'] == 'add')) {
                wp_enqueue_media();
                wp_enqueue_script('postbox');

                $deps = array('jquery', 'codemirror', 'codemirror-css');

                wp_register_script('codemirror', $this->plugin_url() . '/assets/plugins/codemirror/codemirror.js', array(), $wc_pos_version);
                wp_register_script('codemirror-css', $this->plugin_url() . '/assets/plugins/codemirror/css.js', array(), $wc_pos_version);

                wp_enqueue_script('pos-script-receipt_options', $this->plugin_url() . '/assets/js/receipt_options.js', $deps, $wc_pos_version);
                wp_localize_script('pos-script-receipt_options', 'wc_pos_receipt', array(
                    'pos_receipt_style' => $this->receipt()->get_style_templates()
                ));

            }
            if (pos_barcodes_admin_page()) {
                wp_enqueue_script('pos-script-barcode_options', $this->plugin_url() . '/assets/js/barcode-options.js', array('jquery'), $wc_pos_version);
                wp_localize_script('pos-script-barcode_options', 'wc_pos_barcode', array(
                    'ajax_url' => WC()->ajax_url(),
                    'barcode_url' => $this->barcode_url(),
                    'product_for_barcode_nonce' => wp_create_nonce('product_for_barcode'),
                    'remove_item_notice' => __('Are you sure you want to remove the selected items?', 'wc_point_of_sale'),
                    'select_placeholder_category' => __('Search for a category&hellip;', 'wc_point_of_sale'),
                ));
            }
            if (pos_settings_admin_page()) {
                wp_enqueue_media();
            }

            wp_enqueue_script('wc-pos-handlebars-admin', $this->plugin_url() . '/assets/js/register/handlebars/handlebars.min.js', $scripts, $wc_pos_version);
            wp_enqueue_script('wc-pos-script-admin', $this->plugin_url() . '/assets/js/admin.js', $scripts, $wc_pos_version);


            wp_enqueue_script('wc-pos-jquery-editable', $this->plugin_url() . '/assets/js/jquery-editable-poshytip.min.js', $scripts, time());

            pos_localize_script('wc-pos-script-admin');

        }
        if (pos_shop_order_page()) {
            if (!wp_script_is('jquery', 'enqueued'))
                wp_enqueue_script('jquery');

            wp_enqueue_script('wc-pos-functions', $this->plugin_url() . '/assets/js/register/functions.js', array(), null, true);
            wp_enqueue_script('jquery_barcodelistener', $this->plugin_url() . '/assets/plugins/anysearch.js', array('jquery'), $wc_pos_version);
            pos_localize_script('wc-pos-script-admin');
            wp_enqueue_script('wc-pos-shop-order-page-script', $this->plugin_url() . '/assets/js/shop-order-page-script.js', array('jquery'), $wc_pos_version);
        }
        if (isset($_GET['page']) && $_GET['page'] == $this->id_stock_c) {
            wp_enqueue_script('jquery_barcodelistener', $this->plugin_url() . '/assets/plugins/anysearch.js', array('jquery'), $wc_pos_version);
        }

        //Barcode and QR-code
        wp_enqueue_script('wc-pos-script-admin', $this->plugin_url() . '/assets/js/admin.js', $scripts, time()); // R1 Software - Scan Orders Fix
        wp_enqueue_script('wc-pos-script-cardswipe', $this->plugin_url() . '/assets/plugins/jquery.cardswipe.js', $scripts, time()); // R1 Software - Scan Orders Fix
        wp_enqueue_script('wc-pos-js-barcode', $this->plugin_url() . '/assets/plugins/JsBarcode.all.min.js', array('jquery'), $wc_pos_version);
        wp_enqueue_script('wc-pos-js-qr-code', $this->plugin_url() . '/assets/plugins/qrcode.min.js', array('jquery'), $wc_pos_version);


    } // End admin_enqueue_scripts ()

    /**
     * Load plugin localisation
     * @access  public
     * @return  void
     */
    public function load_localisation()
    {
        load_plugin_textdomain('wc_point_of_sale', false, dirname(plugin_basename($this->file)) . '/lang/');
    } // End load_localisation ()

    /**
     * Load plugin textdomain
     * @access  public
     * @return  void
     */
    public function load_plugin_textdomain()
    {
        $domain = 'wc_point_of_sale';
        $locale = is_admin() && function_exists('get_user_locale') ? get_user_locale() : get_locale();
        $locale = apply_filters( 'plugin_locale', $locale, $domain );
        $mofile = $domain . '-' . $locale . '.mo';

        load_textdomain( $domain, WP_LANG_DIR . '/plugins/' . $mofile );
        load_plugin_textdomain( $domain, false, $this->plugin_path() . '/lang/' . $mofile );
    }

    /**
     * Main WC_POS Instance
     *
     * Ensures only one instance of WC_POS is loaded or can be loaded.
     *
     * @static
     * @see WC_POS()
     * @return WC_POS instance
     * @since 1.9
     */
    public static function instance($file = '', $version = '1.0.0')
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self($file, $version);
        }
        return self::$_instance;
    } // End instance ()

    /**
     * Cloning is forbidden.
     *
     */
    public function __clone()
    {
        _doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?'), $this->_version);
    } // End __clone ()

    /**
     * Unserializing instances of this class is forbidden.
     *
     */
    public function __wakeup()
    {
        _doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?'), $this->_version);
    } // End __wakeup ()

    /**
     * Installation. Runs on activation.
     * @access  public
     * @return  void
     */
    public function install($networkwide)
    {
        global $wpdb;

        if (function_exists('is_multisite') && is_multisite()) {
            // check if it is a network activation - if so, run the activation function for each blog id
            if ($networkwide) {
                $old_blog = $wpdb->blogid;
                // Get all blog ids
                $blogids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
                foreach ($blogids as $blog_id) {
                    switch_to_blog($blog_id);
                    WC_POS_Install::install();
                }
                switch_to_blog($old_blog);
                return;
            } else {
                WC_POS_Install::install();
            }
        } else {
            WC_POS_Install::install();
        }
    } // End install ()


    public function update($updater, $options)
    {
        if($options['action'] == 'update' && $options['type'] == 'plugin' && isset( $options['plugins'] )){
            if(in_array(plugin_basename($this->file), $options['plugins'])){
                WC_POS_Install::install();
            }
        }
    }


    /**
     * Log the plugin version number.
     * @access  public
     * @return  void
     */
    private function _log_version_number()
    {
        update_option($this->_token . '_version', $this->_version);
    } // End _log_version_number ()


    /**
     * Check if current page is pos screen
     *
     * @return boolean
     */
    public function is_pos_page()
    {
        global $post_type;
        if ($post_type == 'product')
            return true;
        if (isset($_GET['page']) && (
                $_GET['page'] == 'wc_pos_settings' ||
                $_GET['page'] == 'wc_pos_barcodesr' ||
                $_GET['page'] == 'wc_pos_receipts' ||
                $_GET['page'] == 'wc_pos_users' ||
                $_GET['page'] == 'wc_pos_tiles' ||
                $_GET['page'] == 'wc_pos_grids' ||
                $_GET['page'] == 'wc_pos_outlets' ||
                $_GET['page'] == 'wc_pos_registers' ||
                $_GET['page'] == 'wc_pos_stock_controller' ||
                $_GET['page'] == 'wc_pos_cash_management' ||
                $_GET['page'] == 'wc_pos_bill_screen'
            )
        ) {
            return true;
        }
        return false;
    }

    public function change_stock_amount()
    {
        $decimal_quantity = get_option('wc_pos_decimal_quantity');

        if ($decimal_quantity == 'yes') {
            remove_filter('woocommerce_stock_amount', 'intval');
            add_filter('woocommerce_stock_amount', 'floatval');
            add_filter('woocommerce_quantity_input_step', array($this, 'quantity_input_step'), 80, 2);
        }
    }

    public function quantity_input_step($step, $_product)
    {
        return 'any';
    }

    /**
     * Check API is active
     * @return boolean
     */
    public function check_api_active()
    {
        return true;
    }

    public function admin_notices()
    {
        if (!$this->wc_api_is_active) {
            ?>
            <div class="error">
                <p><?php _e('Your REST API for WooCommerce is not enabled. Please enable the ', 'wc_point_of_sale'); ?>
                    <a href="<?php echo admin_url('admin.php?page=wc-settings&tab=advanced&section=legacy_api'); ?>"><?php _e('Legacy API', 'wc_point_of_sale'); ?></a>
                    <?php _e(' box in the WooCommerce settings to fix this.', 'wc_point_of_sale'); ?>
                </p>
            </div>
            <?php
        }
        if ($this->permalink_structure == '') {
            ?>
            <div class="error">
                <p><?php _e('Incorrect Permalinks Structure.', 'wc_point_of_sale'); ?> <a
                            href="<?php echo admin_url('options-permalink.php'); ?>"><?php _e('Change Permalinks', 'wc_point_of_sale'); ?></a>
                </p>
            </div>
            <?php
        }

        $purchase_codes = get_option(AEBaseApi::PURCHASE_CODES_OPTION_KEY, array());
        if(empty($purchase_codes['woocommerce-point-of-sale'])):
            global $current_screen;
            if($current_screen->id == 'point-of-sale_page_wc_pos_settings'){
                return;
            }
            ?>
           
        <?php endif;
    }

    function tile_attribute_label($label)
    {
        if (isset($_GET['page']) && $_GET['page'] == $this->id_tiles && isset($_GET['grid_id']))
            return '<strong>' . $label . '</strong>';
        else return $label;
    }

    function order_received_url($order_received_url)
    {
        if (isset($_GET['page']) && $_GET['page'] == 'wc_pos_registers' && isset($_GET['reg']) && !empty($_GET['reg']) && isset($_GET['outlet']) && !empty($_GET['outlet'])) {
            $register = $_GET['reg'];
            $outlet = $_GET['outlet'];

            setcookie("wc_point_of_sale_register", $register, time() - 3600 * 24 * 120, '/');
            $register_url = get_home_url() . "/point-of-sale/$outlet/$register";

            if (is_ssl() || get_option('woocommerce_pos_force_ssl_checkout') == 'yes') {
                $register_url = str_replace('http:', 'https:', $register_url);
            }

            return $register_url;
        } else {
            return $order_received_url;
        }
    }

    public function orders_by_order_type($vars)
    {
        global $typenow, $wp_query;
        if ($typenow == 'shop_order') {

            if (isset($_GET['shop_order_wc_pos_order_type']) && $_GET['shop_order_wc_pos_order_type'] != '') {

                if ($_GET['shop_order_wc_pos_order_type'] == 'POS') {
                    $vars['meta_query'][] = array(
                        'key' => 'wc_pos_order_type',
                        'value' => 'POS',
                        'compare' => '=',
                    );
                } elseif ($_GET['shop_order_wc_pos_order_type'] == 'online') {
                    $vars['meta_query'][] = array(
                        'key' => 'wc_pos_order_type',
                        'compare' => 'NOT EXISTS'
                    );
                }

            }

            if (isset($_GET['shop_order_wc_pos_filter_register']) && $_GET['shop_order_wc_pos_filter_register'] != '') {
                $vars['meta_query'][] = array(
                    'key' => 'wc_pos_id_register',
                    'value' => $_GET['shop_order_wc_pos_filter_register'],
                    'compare' => '=',
                );

            }
            if (isset($_GET['shop_order_wc_pos_filter_outlet']) && $_GET['shop_order_wc_pos_filter_outlet'] != '') {
                $registers = pos_get_registers_by_outlet($_GET['shop_order_wc_pos_filter_outlet']);
                $vars['meta_query'][] = array(
                    'key' => 'wc_pos_id_register',
                    'value' => $registers,
                    'compare' => 'IN',
                );

            }

        }

        return $vars;
    }

    function order_actions_reprint_receipts($actions, $the_order)
    {
        $amount_change = get_post_meta($the_order->get_id(), 'wc_pos_order_type', true);
        $id_register = get_post_meta($the_order->get_id(), 'wc_pos_id_register', true);
        if ($amount_change && $id_register) {
            $data = $this->register()->get_data($id_register);
            if (!empty($data) && !empty($data[0])) {
                $data = $data[0];
                $actions['reprint_receipts'] = array(
                    'url' => wp_nonce_url(admin_url('admin.php?print_pos_receipt=true&print_from_wc=true&order_id=' . $the_order->get_id()), 'print_pos_receipt'),
                    'name' => __('Reprints receipts', 'wc_point_of_sale'),
                    'action' => "reprint_receipts",
                    'target' => "_parent"
                );
            }

        }

        return $actions;
    }

    function add_prefix_suffix_order_number($order_id, $order)
    {
        if (!$order instanceof WC_Order) {
            return $order_id;
        }
        $redister_id = get_post_meta($order->get_id(), 'wc_pos_id_register', true);

        if ($redister_id) {
            $_order_id = get_post_meta($order->get_id(), 'wc_pos_prefix_suffix_order_number', true);
            if ($_order_id == '') {
                $reg = $this->register()->get_data($redister_id);
                if ($reg) {
                    $reg = $reg[0];
                    $_order_id = $reg['detail']['prefix'] . $order->get_id() . $reg['detail']['suffix'];
                    add_post_meta($order->get_id(), 'wc_pos_prefix_suffix_order_number', $_order_id, true);
                    add_post_meta($order->get_id(), 'wc_pos_order_tax_number', $reg['detail']['tax_number'], true);
                }
            }
            $order_id = str_replace('#', '', $_order_id);
        }
        return $order_id;
    }

    function sv_change_email_tax_label($label)
    {
        $label = '';
        return $label;
    }

    function print_report()
    {
        if(!isset($_GET['print_pos_receipt']) || empty($_GET['print_pos_receipt'])){
            return;
        }

        if(!isset($_GET['order_id']) && !isset($_GET['refund_id'])){
            return;
        }

        if(empty($_GET['order_id']) && empty($_GET['refund_id'])){
            return;
        }

        $order_id = isset($_GET['order_id']) ? $_GET['order_id'] : $_GET['refund_id'];
        $post = get_post($order_id);
        $order = $post->post_type == "shop_order" ? wc_get_order($post->ID) : wc_get_order($post->post_parent);

        $nonce = $_REQUEST['_wpnonce'];
        if (!wp_verify_nonce($nonce, 'print_pos_receipt') || !is_user_logged_in()) die('You are not allowed to view this page.');
        $register_ID = get_post_meta($order->get_id(), 'wc_pos_id_register', true);

        $register = $this->register()->get_data($register_ID);
        $register = $register[0];
        $register_name = $register['name'];

        $receipt_ID = $register['detail']['receipt_template'];
        $outlet_ID = $register['outlet'];

        $preview = false;

        $order = wc_get_order($order_id);
        $receipt_options = WC_POS()->receipt()->get_data($receipt_ID);
        $receipt_style = WC_POS()->receipt()->get_style_templates();
        $receipt_options = $receipt_options[0];
        $attachment_image_logo = wp_get_attachment_image_src($receipt_options['logo'], 'full');


        $outlet = $this->outlet()->get_data($outlet_ID);
        $outlet = $outlet[0];
        $address = $outlet['contact'];
        $address['first_name'] = '';
        $address['last_name'] = '';
        $address['company'] = '';
        $outlet_address = WC()->countries->get_formatted_address($address);
        if (isset($_GET['tab_id'])) {
            $tab = str_replace('tab_', '', $_GET['tab_id']);
        }
        remove_action('wp_footer', 'wp_admin_bar_render', 1000);

        if($post->post_type == "shop_order"){
            include_once($this->plugin_views_path() . '/html-print-receipt.php');
        }else{
            $refund = new WC_Order_Refund($order->get_id());
            $order = wc_get_order($refund->get_parent_id());
            include_once($this->plugin_views_path() . '/html-print-refund-receipt.php');
        }

    }

    private function html_receipt_to_text($receipt_options, $outlet_address, $outlet, $order ,$register, $register_name ) {
        ob_start();
         ?>

        <?php _e('Receipt', 'woocommerce-point-of-sale'); ?>"\n"

        <?php

        for ($rc = 1; $rc <= $receipt_options['print_copies_count']; $rc++) { ?>
"\n"
                <?php echo $receipt_options['receipt_title']; ?>
"\n"

"\n"
                    <?php if ($receipt_options['show_site_name'] == 'yes') { ?>
                        <?php echo bloginfo('name'); ?>
                    <?php } ?>
"\n"
                <?php if ($receipt_options['show_outlet'] == 'yes') { ?>
                    <?php echo $outlet['name']; ?>
                <?php } ?>
            "\n"
                <?php
                if ($receipt_options['print_outlet_address'] == 'yes') { ?>
"\n"
                    <?php echo $outlet_address; ?>
                <?php } ?>
"\n"
                <?php if ($receipt_options['print_outlet_contact_details'] == 'yes') { ?>
                    <?php if ($outlet['social']['phone']) {
                        if ($receipt_options['telephone_label']) echo $receipt_options['telephone_label'] . ': ';
                        echo $outlet['social']['phone'] . '';
                    }
                    ?>
                    <?php if ($outlet['social']['fax']) {
                        if ($receipt_options['fax_label']) echo $receipt_options['fax_label'] . ': ';
                        echo $outlet['social']['fax'] . '';
                    }
                    ?>
                    <?php if ($outlet['social']['email']) {
                        if ($receipt_options['email_label']) echo $receipt_options['email_label'] . ': ';
                        echo $outlet['social']['email'] . '';
                    }
                    ?>
                    <?php if ($outlet['social']['website']) {
                        if ($receipt_options['website_label']) echo $receipt_options['website_label'] . ': ';
                        echo $outlet['social']['website'];
                    }
                    ?>
                <?php } ?>
"\n"
            <?php if ($receipt_options['socials_display_option'] != 'none' && $receipt_options['socials_display_option'] == 'header') { ?>

                    <?php if ($receipt_options['show_twitter'] == 'yes') { ?>
                        "\n"<?php echo __('Twitter: ', 'wc_point_of_sale') . $outlet['social']['twitter'] ?>"\n"
                    <?php } ?>
                    <?php if ($receipt_options['show_facebook'] == 'yes') { ?>
                       "\n"<?php echo __('Facebook: ', 'wc_point_of_sale') . $outlet['social']['facebook'] ?>"\n"
                    <?php } ?>
                    <?php if ($receipt_options['show_instagram'] == 'yes') { ?>
                        "\n"<?php echo __('Instagram: ', 'wc_point_of_sale') . $outlet['social']['instagram'] ?>"\n"
                    <?php } ?>
                    <?php if ($receipt_options['show_snapchat'] == 'yes') { ?>
                       "\n"<?php echo __('Snapchat: ', 'wc_point_of_sale') . $outlet['social']['snapchat'] ?>"\n"
                    <?php } ?>

            <?php } ?>
           "\n"
                <?php if ($receipt_options['print_tax_number'] == 'yes') { ?>
                   "\n"<?php echo $receipt_options['tax_number_label'] . ': '; ?>"\n"
                    <?php
                    $tax_number = get_post_meta($order->get_id(), 'wc_pos_order_tax_number', true);
                    if ($tax_number == '')
                        echo isset($register['detail']['tax_number']) ? $register['detail']['tax_number'] : '[tax-number]';
                    else
                        echo $tax_number;
                    ?>
                <?php } ?>
           "\n"
                <?php echo stripslashes($receipt_options['header_text']); ?>
            "\n"
                    <?php if ($receipt_options['order_number_label']) { ?>
                      "\n"<?php echo $receipt_options['order_number_label']; ?>
                            <?php echo $order->get_order_number(); ?>
                       "\n"
                    <?php } else {
                        echo $order->get_order_number();
                    } ?>
                    <?php if ($receipt_options['print_order_time'] == 'yes') { ?>
                       "\n"
                           "\n"<?php echo $receipt_options['order_date_label']; ?>"\n"
                            <?php if ($receipt_options['order_date_label']) {
                                    $order_date = explode(' ', $order->get_date_created());
                                    echo date_i18n($receipt_options['order_date_format'], strtotime($order_date[0]) + (get_option('gmt_offset') * HOUR_IN_SECONDS)); ?>
                                    at  <?php echo date_i18n('H:i', strtotime($order_date[0]) + (get_option('gmt_offset') * HOUR_IN_SECONDS)); ?>
                                <?php } ?>
"\n"
                    <?php } ?>
                    <?php if ($receipt_options['print_customer_name'] == 'yes' && ($order->get_billing_first_name() || $order->get_billing_first_name())) { ?>
"\n"<?php echo $receipt_options['customer_name_label']; ?>"\n"
                            "\n"
                                <?php echo esc_html($order->get_billing_first_name()); ?> <?php echo esc_html($order->get_billing_last_name()); ?>
                           "\n"
                    <?php } ?>
                    <?php if ($receipt_options['print_customer_email'] == 'yes' && $order->get_billing_email()) { ?>
                        "\n"<?php echo $receipt_options['customer_email_label']; ?>
                            <?php echo esc_html($order->get_billing_email()); ?>
                        "\n"
                    <?php } ?>
                    <?php if ($receipt_options['print_customer_phone'] == 'yes' && $order->get_billing_phone()) { ?>
                        "\n"<?php echo $receipt_options['customer_phone_label']; ?>
                            <?php echo esc_html($order->get_billing_phone()); ?>
                        "\n"
                    <?php } ?>
                    <?php if ($receipt_options['print_customer_ship_address'] == 'yes' && $order->get_shipping_methods() && $order->get_shipping_address_1()) { ?>
                       "\n"<?php echo $receipt_options['customer_ship_address_label']; ?>

                                <?php echo ($address = $order->get_formatted_shipping_address()) ? $address : __('N/A', 'woocommerce'); ?>
                            "\n"
                    <?php } ?>
                    <?php if ($receipt_options['print_server'] == 'yes') {
                        $post_author = get_current_user_id();
                        $served_by = get_userdata($post_author);
                        if ($served_by) {
                            switch ($receipt_options['served_by_type']) {
                                case 'nickname':
                                    $served_by_name = $served_by->nickname;
                                    break;
                                case 'display_name':
                                    $served_by_name = $served_by->display_name;
                                    break;
                                default:
                                    $served_by_name = $served_by->user_nicename;
                                    break;
                            }
                        } else {
                            $served_by_name = get_post_meta($order->get_id(), 'wc_pos_served_by_name', true);
                        }
                        ?>
                        "\n"<?php echo $receipt_options['served_by_label']; ?>
                            <?php echo $served_by_name; ?>
                                <?php if ($receipt_options['show_register'] == 'yes') { ?>
                                on <?php echo $register_name; ?></td>
                            <?php } ?>
                        "\n"
                    <?php } ?>
                    <?php if ($receipt_options['print_order_notes'] == 'yes' && $order->get_customer_note()) { ?>
                        "\n"<?php echo $receipt_options['order_notes_label']; ?>"\n"
                            <?php echo wptexturize($order->get_customer_note()); ?>
                "\n"
                    <?php } ?>

                    <?php if (get_option('wc_pos_print_diner_option', 'no') == 'yes') { ?>
                        <?php if (get_post_meta($order->get_id(), 'wc_pos_dining_option', true) != 'None'): ?>
                          "\n"<?php _e('Dining options', 'wc_point_of_sale'); ?>
                                <?php echo get_post_meta($order->get_id(), 'wc_pos_dining_option', true); ?>
                            "\n"
                        <?php endif; ?>
                    <?php } ?>

                    <?php if (isset($tab)) { ?>
                        "\n"<?php echo __('Tab', 'wc_point_of_sale') ?></th>
                           <?php echo $tab ?>
                "\n"
                    <?php } elseif (isset($order) && $order->get_meta('order_tab')) { ?>
                        "\n"
                <?php echo __('Tab', 'wc_point_of_sale') ?>
                            <td><?php echo $order->get_meta('order_tab') ?>"\n"
                    <?php } ?>
                   "\n"
            "\n"
                       <?php _e('Qty', 'wc_point_of_sale'); ?>

                        <?php _e('Product', 'wc_point_of_sale'); ?>
                        <?php echo ($receipt_options['show_cost'] == 'yes') ? __('Cost', 'wc_point_of_sale') : '' ?>
                        <?php _e('Total', 'wc_point_of_sale'); ?>
                   "\n"
                    <?php
                    $items = $order->get_items('line_item');
                    $_items = array();
                    $_items_nosku = array();
                    $_items_sku = array();
                    $_cart_subtotal = 0;
                    add_filter('woocommerce_countries_ex_tax_or_vat', array($this, 'sv_change_email_tax_label') );
                    foreach ($items as $item_id => $item) {

                        $_product = $order->get_product_from_item($item);
                        if ($_product) {
                            $sku = $_product->get_sku();
                        } else {
                            $sku = '';
                        }
                        ob_start();
                        ?>
                        "\n"
                        <?php echo $item['qty']; ?>


                               <?php echo $name = esc_html($item['name']); ?>
                                <?php echo ($_product && $_product->get_sku() && $receipt_options['show_sku'] == 'yes') ? '' . esc_html($_product->get_sku()) : ''; ?>
                                <?php

                                if ($metadata = wc_get_order_item_meta($item_id, '')) {
                                    $meta_list = array();
                                    foreach ($metadata as $key => $meta) {

                                        // Skip hidden core fields
                                        if (in_array($key, apply_filters('woocommerce_hidden_order_itemmeta', array(
                                            '_qty',
                                            '_tax_class',
                                            '_product_id',
                                            '_variation_id',
                                            '_line_subtotal',
                                            '_line_subtotal_tax',
                                            '_line_total',
                                            '_line_tax',
                                        )))) {
                                            continue;
                                        }

                                        // Skip serialised meta
                                        if (is_serialized($meta[0])) {
                                            continue;
                                        }

                                        // Get attribute data
                                        if (taxonomy_exists(wc_sanitize_taxonomy_name($key))) {
                                            $term = get_term_by('slug', $meta[0], wc_sanitize_taxonomy_name($key));
                                            $meta['meta_key'] = wc_attribute_label(wc_sanitize_taxonomy_name($key));
                                            $meta['meta_value'] = isset($term->name) ? $term->name : $meta[0];
                                        } else {
                                            $meta['meta_key'] = apply_filters('woocommerce_attribute_label', wc_attribute_label($key, $_product), $key);
                                        }

                                        $meta_list[] = wp_kses_post(rawurldecode($key)) . ': ' . wp_kses_post(make_clickable(rawurldecode($meta[0])));
                                    }

                                }
                                ?>


                                <?php
                                if ($receipt_options['show_cost'] == 'yes') {

                                    $product = new WC_Product($item->get_product_id());
                                    $tax_display = $order->get_prices_include_tax();

                                    if ($receipt_options['show_discount'] == 'yes' && ($product->get_regular_price() != $order->get_item_subtotal($item, $tax_display, true))){
                                        echo wc_price($product->get_regular_price(), array('currency' => $order->get_currency()));
                                    }

                                    if (isset($item['line_total'])) {
                                        echo wc_price($order->get_item_subtotal($item, $tax_display, true), array('currency' => $order->get_currency()));
                                    }
                                }
                                ?>

                                <?php ?>
                                <?php
                                if (isset($item['line_total'])) {
                                    echo $order->get_formatted_line_subtotal($item);
                                }

                                if ($refunded = $order->get_total_refunded_for_item($item_id)) {
                                    echo wc_price($refunded, array('currency' => $order->get_currency()));
                                }
                                ?>
    "\n"
                        <?php
                        if (empty($sku)) {
                            $_items_nosku[$item_id] = $name;
                        } else {
                            $_items_sku[$item_id] = $sku . $name;
                        }

                        $_items[$item_id] = ob_get_contents();

                        ob_end_clean();
                    }
                    asort($_items_sku);
                    foreach ($_items_sku as $key => $_item) {
                        echo $_items[$key];
                    }
                    asort($_items_nosku);
                    foreach ($_items_nosku as $key => $_item) {
                        echo $_items[$key];
                    }
                    ?>
                    "\n"
                    <?php
                    if (($totals = $order->get_order_item_totals())) {
                    $i = 0;
                    $total_order = 0;
                    foreach ($totals as $total_key => $total) {
                    switch ($total_key) {
                        case 'cart_subtotal':
                            $total_label = __('Subtotal', 'wc_point_of_sale');
                            break;
                        case 'order_total':
                            $total_label = '<span id="print-total_label">' . __('Total', 'wc_point_of_sale') . '</span>';
                            $total_order = $total['value'];
                            break;
                        case 'discount':
                            $total_label = __('Discount', 'wc_point_of_sale');;
                            break;
                        case 'shipping':
                            $total_label = __('Shipping', 'wc_point_of_sale');
                            break;
                        case 'payment_method':
                            continue 2;
                            break;
                        default :
                            $total_label = $total['label'];
                            break;
                    }
                    $i++;
                    if ($total_key == 'order_total') {
                    // Tax for tax exclusive prices
                    $tax_display = $order->get_prices_include_tax();
                    if ($tax_display) {
                        if (get_option('woocommerce_tax_total_display') == 'itemized') {
                            foreach ($order->get_tax_totals() as $code => $tax) {
                                $total_rows[] = array(
                                    'label' => $tax->label,
                                    'value' => $tax->formatted_amount
                                );
                            }
                        } else {
                            $total_rows[] = array(
                                'label' => WC()->countries->tax_or_vat(),
                                'value' => wc_price($order->get_total_tax(), array('currency' => $order->get_currency()))
                            );
                        }
                    }

                    }
                    ?>
                  "\n"


                            <?php echo rtrim($total_label, ":"); ?>

                            <?php echo $total['value']; ?>

                    <?php
                    }
                    ?>
                    "\n"


                            <?php echo $order->get_payment_method_title(); ?><?php echo $receipt_options['payment_label']; ?>


                            <?php
                            $amount_pay = get_post_meta($order->get_id(), 'wc_pos_amount_pay', true);
                            if ($amount_pay) {
                                echo wc_price($amount_pay, array('currency' => $order->get_currency()));
                            } else {
                                echo $total_order;
                            }
                            ?>

                    <?php if ($order->get_payment_method() == 'cod') { ?>
                       "\n"


                                <?php _e('Change', 'wc_point_of_sale'); ?>

                                <?php
                                $amount_change = get_post_meta($order->get_id(), 'wc_pos_amount_change', true);
                                if ($amount_change) {
                                    echo wc_price($amount_change, array('currency' => $order->get_currency
                                    ()));
                                } else {
                                    echo wc_price(0, array('currency' => $order->get_currency()));
                                }
                                ?>

                    <?php } ?>
                    <?php if ( $receipt_options['print_number_items'] == 'yes') { ?>
                        "\n"

                            <?php echo $receipt_options['items_label']; ?>

                                <?php echo $order->get_item_count(); ?>

                    <?php } ?>
                    <?php
                    }
                    ?>
"\n"
            <?php if (isset($receipt_options['tax_summary']) && $receipt_options['tax_summary'] == 'yes') { ?>
               "\n"<?php echo $receipt_options['tax_label']; ?><?php _e(' Summary', 'wc_point_of_sale'); ?>
                        "\n"
                <?php echo $receipt_options['tax_label']; ?><?php _e(' Name', 'wc_point_of_sale'); ?>
                <?php echo $receipt_options['tax_label']; ?><?php _e(' Rate', 'wc_point_of_sale'); ?>
                <?php echo $receipt_options['tax_label']; ?>

                        <?php
                        $tax_display = $order->get_prices_include_tax();
                        $order_taxes = $order->get_taxes();
                        if (!empty($order_taxes)) {
                            foreach ($order_taxes as $row) {
                                $tax_rate = WC_Tax::_get_tax_rate($row->get_rate_id());
                                ?>
                               "\n"
                                    <?php echo $row->get_label() ?>
                                    <?php echo number_format($tax_rate['tax_rate'], 2) ?>
                                    <?php echo wc_price($row->get_tax_total()) ?>

                                <?php
                            }
                        }
                        ?>

            <?php } ?>
            <?php if ($receipt_options['socials_display_option'] != 'none' && $receipt_options['socials_display_option'] == 'footer') { ?>
                "\n"
                    <?php if ($receipt_options['show_twitter'] == 'yes') { ?>
                       <?php echo __('Twitter: ', 'wc_point_of_sale') . $outlet['social']['twitter'] ?>
                    <?php } ?>
                    <?php if ($receipt_options['show_facebook'] == 'yes') { ?>
                        <?php echo __('Facebook: ', 'wc_point_of_sale') . $outlet['social']['facebook'] ?>
                    <?php } ?>
                    <?php if ($receipt_options['show_instagram'] == 'yes') { ?>
                        <?php echo __('Instagram: ', 'wc_point_of_sale') . $outlet['social']['instagram'] ?>
                    <?php } ?>
                    <?php if ($receipt_options['show_snapchat'] == 'yes') { ?>
                        <?php echo __('Snapchat: ', 'wc_point_of_sale') . $outlet['social']['snapchat'] ?>
                    <?php } ?>

            <?php } ?>

            "\n"
                <?php echo stripslashes($receipt_options['footer_text']); ?>


            <?php } ?>

        <?php
        $out = ob_get_contents();

        return $out;
    }

    /**
     * Check if page is POS Register
     * @since 1.9
     * @return bool
     */
    function is_pos()
    {
        global $wp_query;
        if (isset($this->is_pos) && !is_null($this->is_pos)) {
            return $this->is_pos;
        } else {
            $q = $wp_query->query;
            if (isset($q['page']) && $q['page'] == 'wc_pos_registers' && isset($q['action']) && $q['action'] == 'view') {
                $this->is_pos = true;
            } else {
                $this->is_pos = false;
            }
            return $this->is_pos;
        }
    }

    public function woocommerce_delete_shop_order_transients()
    {
        $transients_to_clear = array(
            'wc_pos_report_sales_by_register',
            'wc_pos_report_sales_by_outlet',
            'wc_pos_report_sales_by_cashier'
        );
        // Clear transients where we have names
        foreach ($transients_to_clear as $transient) {
            delete_transient($transient);
        }
    }

    public function add_caps()
    {
        $role = get_role('shop_manager');
        $role->add_cap('read_private_products');
    }


    public function hidden_order_itemmeta($meta_keys = array())
    {
        $meta_keys[] = '_pos_custom_product';
        $meta_keys[] = '_price';
        return $meta_keys;
    }

    //TODO: Note - is_pos() function don't work for some reason, be carefull
    public function woocommerce_email_actions($email_actions)
    {
        if ((is_pos_referer() === true )|| is_pos() || (strpos($_SERVER['REQUEST_URI'], 'pos_orders') !== false) ) {

            foreach ($email_actions as $key => $action) {
                if (strpos($action, 'woocommerce_order_status_') === 0) {
                    if($action === "woocommerce_order_status_changed"){
                        continue;
                    }
                    unset($email_actions[$key]);
                }
            }
            $aenc = get_option('wc_pos_automatic_emails');
            if ($aenc != 'yes') {
                $new_actions = array();
                foreach ($email_actions as $action) {
                    if ($action == 'woocommerce_created_customer'){
                        continue;
                    }

                    $new_actions[] = $action;
                }
                $email_actions = $new_actions;
            }
        }

        return $email_actions;
    }


    /** Helper functions ******************************************************/

    /**
     * Get WooCommerce API endpoint.
     *
     * @return string
     */
    public function wc_api_url()
    {
        return get_home_url(null, "wp-json/wc/v3/", is_ssl() ? 'https' : 'http');
    }

    /**
     * Get the plugin file.
     *
     * @return string
     */
    public function plugin_file()
    {
        return WC_POS_FILE;
    }


    /**
     * Get the plugin url.
     *
     * @return string
     */
    public function plugin_url()
    {
        return untrailingslashit(plugins_url('/', WC_POS_FILE));
    }

    /**
     * Get the plugin barcode url.
     *
     * @return string
     */
    public function barcode_url()
    {
        return untrailingslashit(plugins_url('includes/lib/barcode/image.php', WC_POS_FILE) . '?filetype=PNG&dpi=72&scale=1&rotation=0&font_family=0&thickness=60&start=NULL&code=BCGcode128');
    }

    /**
     * Get the plugin path.
     *
     * @return string
     */
    public function plugin_path()
    {
        return untrailingslashit(plugin_dir_path(WC_POS_FILE));
    }


    /**
     * Get the plugin path.
     *
     * @return string
     */
    public function plugin_views_path()
    {
        return untrailingslashit(plugin_dir_path(WC_POS_FILE) . 'includes/views');
    }

    /**
     * Get the plugin assets path.
     *
     * @return string
     */
    public function plugin_assets_path()
    {
        return untrailingslashit(plugin_dir_path(WC_POS_FILE) . 'assets');
    }

    /**
     * Get the sound url.
     *
     * @return string
     */
    public function plugin_sound_url()
    {
        return untrailingslashit(plugins_url('/assets/plugins/ion.sound/sounds', WC_POS_FILE));
    }

    /**
     * Get Outlets class
     *
     * @since 1.9
     * @return WC_Pos_Outlets
     */
    public function outlet()
    {
        return WC_Pos_Outlets::instance();
    }

    /**
     * Get Outlets table class
     *
     * @since 1.9
     * @return WC_Pos_Table_Outlets
     */
    public function outlet_table()
    {
        return new WC_Pos_Table_Outlets;
    }

    /**
     * Get Registers class
     *
     * @since 1.9
     * @return WC_Pos_Registers
     */
    public function register()
    {
        return WC_Pos_Registers::instance();
    }

    /**
     * Get Registers Table class
     *
     * @since 1.9
     * @return WC_Pos_Table_Registers
     */
    public function registers_table()
    {
        return new WC_Pos_Table_Registers;
    }


    /**
     * Get Grids class
     *
     * @since 1.9
     * @return WC_Pos_Grids
     */
    public function grid()
    {
        return WC_Pos_Grids::instance();
    }

    /**
     * Get Grids Table class
     *
     * @since 1.9
     * @return WC_Pos_Table_Grids
     */
    public function grids_table()
    {
        return new WC_Pos_Table_Grids;
    }

    /**
     * Get Tiles class
     *
     * @since 1.9
     * @return WC_Pos_Tiles
     */
    public function tile()
    {
        return WC_Pos_Tiles::instance();
    }

    /**
     * Get Tiles Table class
     *
     * @since 1.9
     * @return WC_Pos_Table_Tiles
     */
    public function tiles_table()
    {
        return new WC_Pos_Table_Tiles;
    }

    /**
     * Get Users class
     *
     * @since 1.9
     * @return WC_Pos_Users
     */
    public function user()
    {
        return WC_Pos_Users::instance();
    }

    /**
     * Get Users Table class
     *
     * @since 1.9
     * @return WC_Pos_Table_Users
     */
    public function users_table()
    {
        return new WC_Pos_Table_Users;
    }

    /**
     * Get Receipts class
     *
     * @since 1.9
     * @return WC_Pos_Receipts
     */
    public function receipt()
    {
        return WC_Pos_Receipts::instance();
    }

    /**
     * Get Receipts Table class
     *
     * @since 1.9
     * @return WC_Pos_Table_Receipts
     */
    public function receipts_table()
    {
        return new WC_Pos_Table_Receipts();
    }

    /**
     * Get Session Reports class
     *
     * @since 1.9
     * @return WC_Pos_Session_Reports
     */
    public function session_reports()
    {
        return new WC_Pos_Session_Reports;
    }

    /**
     * Get Session Reports class
     *
     * @since 1.9
     * @return WC_Pos_Bill_Screen
     */
    public function bill_screen($reg_id)
    {
        return new WC_Pos_Bill_Screen($reg_id);
    }

    /**
     * Get Sessions Table class
     *
     * @since 1.9
     * @return WC_Pos_Table_Sessions
     */
    public function sessions_table()
    {
        return new WC_Pos_Table_Sessions;
    }

    /**
     * Get Barcodes class
     *
     * @since 1.9
     * @return WC_Pos_Barcodes
     */
    public function barcode()
    {
        return WC_Pos_Barcodes::instance();
    }

    /**
     * Get Stock class
     *
     * @since 3.0.0
     * @return WC_Pos_Stock
     */
    public function stock()
    {
        return WC_Pos_Stocks::instance();
    }

    /**
     * Get Float_cash class
     *
     * @since 3.1.8.1
     * @return WC_Float_Cash
     */
    public function float_cash()
    {
        return WC_Float_Cash::instance();
    }

    public function get_subscription_payment_method($payment_method, $subscription)
    {

        if (get_post_meta($subscription->get_order_number(), 'wc_pos_order_type', true) == 'POS') {
            $payment_method = get_post_meta($subscription->get_order_number(), '_payment_method_title', true);
        }
        return $payment_method;
    }


    public function check_pos_visibility_products()
    {
        global $wpdb;
        //get products without pos_visibility
        $sql = "SELECT DISTINCT p.`ID` FROM {$wpdb->posts} p
                    WHERE `post_type` = 'product'
                    AND NOT EXISTS (
                    SELECT * FROM {$wpdb->postmeta} WHERE `meta_key` = '_pos_visibility' 
                    AND `post_id` = p.`ID`
                    )";
        $result = $wpdb->get_results($sql);
        if ($result) {
            foreach ($result as $res) {
                $wpdb->insert(
                    $wpdb->postmeta,
                    array(
                        'post_id' => $res->ID,
                        'meta_key' => '_pos_visibility',
                        'meta_value' => 'pos_online'
                    )
                );
            }
        }
    }

    public function add_custom_discounts($default)
    {
        $discounts = get_option('woocommerce_pos_register_discount_presets', []);
        foreach ($discounts as $key => $value) {
            if (array_key_exists($value, $default)) {
                continue;
            }
            $default[$value] = $value . __('%', 'wc_point_of_sale');
        }
        return $default;
    }

    public function wc_pos_deactivate()
    {
        wp_delete_post((int)get_option('wc_pos_custom_product_id'), true);
    }

    public function generate_rest_api()
    {

        $result = wp_remote_post(
            admin_url( 'admin-ajax.php' ),
            array(
                'method' => 'POST',
                'body' => array(
                    'action' => 'woocommerce_update_api_key',
                    'security' => $_POST['security'],
                    'description' => $_POST['description'],
                    'key_id' => '0',
                    'user' => $_POST['user'],
                    'permissions' => $_POST['permissions']
                ),
            )
        );

//        $data = wp_remote_post( admin_url('admin-ajax.php'), [
//            'method' => 'POST',
//            'body' => [
//                'action' => 'woocommerce_update_api_key',
//                'security' => $_POST['security'],
//                'description' => $_POST['description'],
//                'user' => $_POST['user'],
//                'permissions' => $_POST['permissions']
//            ]
//        ]);

        wp_send_json($result);
    }

    public static function init_paymentsense_gateway()
    {
        include_once('api/class-wc-pos-paymentsense-api.php');
        include_once('class-wc-pos-paymentsense.php');
    }

    public function add_paymentsense_gateway( $methods ) {
        $methods[] = 'WC_POS_PaymentSense_Gateway';
        return $methods;
    }

    public function pos_payment_gateway_labels( $value, $data )
    {
        global $current_screen;
        $screen = $current_screen ? $current_screen->id : null;
        $gateways = wc_pos_get_available_payment_gateways();
        $pos_gateways = array('pos_chip_pin', 'pos_chip_pin2', 'pos_chip_pin3');
        if(in_array($value, $pos_gateways) && $screen == "shop_order"){
            foreach ($gateways as $gateway){
                if($gateway->id == $value){
                    $value = $gateway->title;
                    break;
                }
            }
        }

        return $value;
    }


    /**
     * @param array $statuses
     * @param WC_Order $order
     * @return array
     */
    public function pos_order_status_for_payment_complete($statuses, $order)
    {
        if(is_pos_referer()){
            $pos_status = get_option('woocommerce_pos_end_of_sale_order_status', 'processing');
            if(!in_array($pos_status, $statuses)){
                $statuses[] = $pos_status;
            }
        }

        return $statuses;
    }

    /**
     * @param string $status
     * @param integer $order_id
     * @param WC_Order $order
     * @return mixed|string|void
     */
    public function pos_complete_order_status($status, $order_id, $order)
    {
        if(is_pos_referer()){
            $pos_status = get_option('woocommerce_pos_end_of_sale_order_status', 'processing');
            if($status != $pos_status){
                $status = $pos_status;
            }
        }

        return $status;
    }

    /**
     * @param integer $order_id
     */
    public function pos_transaction_complete($order_id)
    {
        if(is_pos_referer()){
            $order = wc_get_order($order_id);
            $order->add_order_note(__('Point of Sale transaction completed.', 'wc_point_of_sale'));
        }
    }

    /**
     * @param integer $id
     * @param string $from
     * @param string $to
     * @param WC_Order $order
     */
    public function pos_order_status_changed($id, $from, $to, $order)
    {
        if(!empty($to) && !empty($from)){
            update_post_meta($id, 'pos_status_transition', array(
                'from' => $from,
                'to' => $to
            ));
        }
    }
}