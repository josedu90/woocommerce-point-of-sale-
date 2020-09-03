<?php
if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


class WC_Pos_ACF_Fields{

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
        add_filter('woocommerce_checkout_fields', array($this, 'acf_checkout_fields') );
        add_action('pos_admin_enqueue_scripts',   array($this, 'acf_admin_enqueue_scripts'));
        add_action('pos_admin_print_scripts',   array($this, 'acf_admin_print_scripts'));

//        Fix ACF bug with undefined post_id
        remove_action('acf/input/admin_head', array('acf_controller_input', 'input_admin_head'));
        add_action('admin_print_footer_scripts', array($this, 'input_admin_head'));

    }

    public function input_admin_head()
    {
        // global
        global $wp_version, $post;


        // vars
        $toolbars = apply_filters( 'acf/fields/wysiwyg/toolbars', array() );
        $post_id = 0;
        if( $post )
        {
            $post_id = intval( $post->ID );
        }


        // l10n
        $l10n = apply_filters( 'acf/input/admin_l10n', array(
            'core' => array(
                'expand_details' => __("Expand Details",'acf'),
                'collapse_details' => __("Collapse Details",'acf')
            ),
            'validation' => array(
                'error' => __("Validation Failed. One or more fields below are required.",'acf')
            )
        ));


        // options
        $o = array(
            'post_id'		=>	$post_id,
            'nonce'			=>	wp_create_nonce( 'acf_nonce' ),
            'admin_url'		=>	admin_url(),
            'ajaxurl'		=>	admin_url( 'admin-ajax.php' ),
            'wp_version'	=>	$wp_version
        );


        // toolbars
        $t = array();

        if( is_array($toolbars) ){ foreach( $toolbars as $label => $rows ){

            $label = sanitize_title( $label );
            $label = str_replace('-', '_', $label);

            $t[ $label ] = array();

            if( is_array($rows) ){ foreach( $rows as $k => $v ){

                $t[ $label ][ 'theme_advanced_buttons' . $k ] = implode(',', $v);

            }}
        }}


        ?>
        <script type="text/javascript">

            (function ($) {

                if(window.acf){
                    // vars
                    acf.post_id = <?php echo is_numeric( $post_id ) ? $post_id : '"' . $post_id . '"'; ?>;
                    acf.nonce = "<?php echo wp_create_nonce( 'acf_nonce' ); ?>";
                    acf.admin_url = "<?php echo admin_url(); ?>";
                    acf.ajaxurl = "<?php echo admin_url( 'admin-ajax.php' ); ?>";
                    acf.wp_version = "<?php echo $wp_version; ?>";


                    // new vars
                    acf.o = <?php echo json_encode( $o ); ?>;
                    acf.l10n = <?php echo json_encode( $l10n ); ?>;
                    acf.fields.wysiwyg.toolbars = <?php echo json_encode( $t ); ?>;
                }

            })(jQuery);

        </script>
        <?php
    }

	public function acf_checkout_fields($checkout_fields)
	{
		
		if( is_pos() && is_plugin_active( 'advanced-custom-fields/acf.php' )) {
//			add_filter('acf/location/rule_match/ef_crm_customers', '__return_true');
//			add_filter('acf/location/rule_match/post_type', '__return_false');

			$acfs = wc_pos_get_acf_field_groups(array(
                'user_form' => true,
                'user_role' => true,
                'ef_crm_customers' => true,
                'post_type' => 'shop_order'
            ));
			if( $acfs )
			{
				$checkout_fields['pos_acf'] = array();

				foreach( $acfs as $acf )
				{
                    $fields    = acf_get_fields($acf);
                    $wc_fields = array();
                    foreach ($fields as $field) {
                        $wc_fields['acf-field-'.$field['name']] = get_merged_acf_array($field);
                    }

                    $checkout_fields['pos_acf'][] = array(
                        'title'  => $acf['title'],
                        'fields' => $wc_fields,
                    );
				}
			}
			remove_filter('acf/location/rule_match/ef_crm_customers', '__return_true');
			remove_filter('acf/location/rule_match/post_type', '__return_false');
		}
		return $checkout_fields;
	}

	public function acf_admin_print_scripts()
	{
		global $post;
		if( !$post ){
			$post = (object)array();
		}
		$post->ID = 'user_';

        do_action('acf/enqueue_scripts');
        do_action('acf/admin_enqueue_scripts');
		do_action('acf/input/admin_head');
		do_action('acf/input/admin_enqueue_scripts');

	}
	public function acf_admin_enqueue_scripts()
	{
		global $typenow, $post;
		if( !$post ){
			$post = (object)array();
		}
		$post->ID = 'user_';
		wp_enqueue_style( 'wp-color-picker' );
	    wp_enqueue_script(
	        'iris',
	        admin_url( 'js/iris.min.js' ),
	        array( 'jquery-ui-draggable', 'jquery-ui-slider', 'jquery-touch-punch' ),
	        false,
	        1
	    );
	    wp_enqueue_script(
	        'wp-color-picker',
	        admin_url( 'js/color-picker.min.js' ),
	        array( 'iris' ),
	        false,
	        1
	    );
	    $colorpicker_l10n = array(
	        'clear' => __( 'Clear' ),
	        'defaultString' => __( 'Default' ),
	        'pick' => __( 'Select Color' ),
	        'current' => __( 'Current Color' ),
	    );
	    wp_localize_script( 'wp-color-picker', 'wpColorPickerL10n', $colorpicker_l10n );

		do_action('acf/input/admin_enqueue_scripts');

	}



    /**
	 * Main WC_Pos_Registers Instance
	 *
	 * Ensures only one instance of WC_Pos_Registers is loaded or can be loaded.
	 *
	 * @since 1.9
	 * @static
	 * @return WC_Pos_Registers Main instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.9
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'woocommerce' ), '1.9' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.9
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'woocommerce' ), '1.9' );
	}

}

return new WC_Pos_ACF_Fields();