<div class="md-modal md-dynamicmodal md-message" id="modal-lost-connection">
    <div class="md-content">
        <div>
	        <div class="md-message-icon">
        		<span class="dashicons dashicons-admin-plugins"></span>
			</div>
		    <p><?php _e( "This device is not connected to the internet and orders will not be synchronised with the shops database until an internet connection is available.", 'wc_point_of_sale'); ?></p>
		    <p><strong><?php _e( "This device will store orders locally until the connection is resumed", 'wc_point_of_sale'); ?></strong>
		    	<span class="dot-one">.</span>
		    	<span class="dot-two">.</span>
		    	<span class="dot-three">.</span>
	    	</p>
        </div>
        <div class="wrap-button">	            
	        <button class="button button-primary md-close" style="float: right;" type="button" >
	            <?php _e('I Understand', 'wc_point_of_sale'); ?>
	        </button>
	    </div>
    </div>
</div>
<div class="md-modal md-dynamicmodal md-message" id="modal-reconnected-successfuly">
    <div class="md-content">
        <div>
            <div class="md-message-icon">
        		<span class="dashicons dashicons-cloud"></span>
			</div>
		    <p><?php _e( "Your device is now connected to the internet.", 'wc_point_of_sale'); ?></p>
        </div>
        <div class="wrap-button">	            
	        <button class="button button-primary md-close" style="float: right;" type="button" >
	            <?php _e('Continue', 'wc_point_of_sale'); ?>
	        </button>
	    </div>
    </div>
</div>