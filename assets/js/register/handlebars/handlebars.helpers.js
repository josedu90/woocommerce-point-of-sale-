Handlebars.registerHelper('displayProductItemTitle', function() {
	var cart_item_data = this.cart_item_data;
	var product        = this.cart_item_data.data;
	var v_data         = this.cart_item_data.v_data;
	var variation_id   = this.cart_item_data.variation_id;
	var variation_id   = this.cart_item_data.variation_id;
	var title          = typeof product.title === 'undefined' ? product.name : product.title;

    if( wc_pos_params.user_can_edit_product == true && product.id != pos_custom_product.id ){
    	var edit_link =  (wc_pos_params.edit_link).replace('{{post_id}}', product.id);
      	title = '<a href="'+edit_link+'" class="product_title" target="_blank" >'+title+'</a>';
    }else{
    	title = '<span class="product_title" >'+title+'</span>';
    }
    
    if(variation_id > 0 && v_data){
    	if(v_data.sku != ''){
	      title = title + " <br><span class='sku'>" + v_data.sku + "</span>";
	    }
    }else if(product.sku != '') {
	      title = title + " <br><span class='sku'>" + product.sku + "</span>";
	}else if(product.id != pos_custom_product.id) {
	      title = title + " <br><span class='sku'></span>";
	}else {
	      title = title + " <br><span class='sku'>Custom Product</span>";
	}

    return new Handlebars.SafeString(title);
});

Handlebars.registerHelper('displayOrderTitle', function() {
	var title           = '';
	var customer        = this.customer_id;
	var order_number    = this.number;
	var billing_address = this.billing;

    if( wc_pos_params.user_can_edit_order == true ){
    	var edit_link =  (wc_pos_params.edit_link).replace('{{post_id}}', this.id);
    	title += '<a class="row-title" href="'+edit_link+'" target="_blank">';
	        title += '<strong>#'+order_number+'</strong>';
	    title += '</a>';
	    title += ' by ';
	    if(customer > 0 ){
	        title += '<a href="user-edit.php?user_id='+customer+'"  target="_blank">';
	        if(billing_address.first_name != '' || billing_address.last_name != ''){
	        	title += billing_address.first_name + ' ' + billing_address.last_name;
	        }else if( typeof customer.username != 'undefined' && customer.username != ''){
	        	title += customer.username;	        	
	        }
	        title += '</a>';
	    }else if( billing_address.first_name != '' || billing_address.last_name != ''){
	        title += billing_address.first_name + ' ' + billing_address.last_name;
	    }else{
        	title += 'Walk-in Customer';
        }
	}else{
        title += '<strong>#'+order_number+'</strong>';
	    title += ' by ';
	    if(customer.id > 0){
	        if(customer.first_name != '' || customer.last_name != ''){
	        	title += customer.first_name + ' ' + customer.last_name;
	        }else if( typeof customer.username != 'undefined' && customer.username != ''){
	        	title += customer.username;	        	
	        }
	    }else if( billing_address.first_name != '' || billing_address.last_name != ''){
	        title += billing_address.first_name + ' ' + billing_address.last_name;
	    }else{
        	title += 'Walk-in Customer';
        }
	}

    if( billing_address.email != '' ){
        title += '<small class="meta email">';
            title += '<a href="mailto:'+billing_address.email+'">';
                title += billing_address.email;
            title += '</a>';
        title += '</small>';
    }

    return new Handlebars.SafeString(title);
});

Handlebars.registerHelper('displayProductItemImage', function() {
	var cart_item_data = this.cart_item_data;
	var product        = this.cart_item_data.data;
	var v_data         = this.cart_item_data.v_data;
	var variation_id   = this.cart_item_data.variation_id;
	var string 		   = '';

	if(wc_pos_params.image_size == 'thumbnail'){
		if(product.thumbnail_src){
			string = '<img width="90" height="90" src="'+product.thumbnail_src+'" class="attachment-shop_thumbnail wp-post-image">';
		}else{
			if(product.images.length > 0){
				string = '<img width="90" height="90" src="'+product.images[0].src+'" class="attachment-shop_thumbnail wp-post-image">';
			}
		}
	}
	else if( product.featured_src !== false ){
		string = '<img width="90" height="90" src="'+product.featured_src+'" class="attachment-shop_thumbnail wp-post-image">';
	}
	
	if(variation_id > 0 && v_data ){
		if( wc_pos_params.image_size == 'thumbnail' && typeof v_data.thumbnail_src != 'undefined'){
			string = '<img width="90" height="90" src="'+v_data.thumbnail_src+'" class="attachment-shop_thumbnail wp-post-image">';
		}
		else {
			string = '<img width="90" height="90" src="'+v_data.featured_src+'" class="attachment-shop_thumbnail wp-post-image">';
		}
	}

	if(empty(string)){
		string = '<span class="customer_product_image"></span>';
	}

    return new Handlebars.SafeString(string);
});

Handlebars.registerHelper('displayProductItemMeta', function() {
	var cart_item_data = this.cart_item_data;
	var product        = this.cart_item_data.data;
	var v_data         = this.cart_item_data.v_data;
	var variation      = this.cart_item_data.variation;
	var variation_id   = this.cart_item_data.variation_id;
	var string = '';

	if(variation_id > 0 && v_data){
		product = v_data;
	}
	var stock_quantity = parseInt(product.stock_quantity);
	if( wc_pos_params.decimal_quantity == 'yes' ){
		stock_quantity = parseFloat(product.stock_quantity);
	}
	if(wc_pos_params.show_stock == 'yes' ){
		stock_quantity = Math.round(stock_quantity * 100) / 100;
		if( !isNaN(stock_quantity) && stock_quantity > 0 ){
			string += '<span class="register_stock_indicator"><b>'+stock_quantity+'</b> '+pos_i18n[38]+' </span>';
		}else if(product.managing_stock  === true ){
			string += '<span class="register_stock_indicator out_of_stock">'+pos_i18n[39]+' </span>';
		}
	}
	
	
	if( product.managing_stock  === true && product.backorders_allowed === true && !isNaN(stock_quantity) && stock_quantity < cart_item_data.quantity ){
		string += '<span class="register_stock_indicator backorders_allowed">'+pos_i18n[40]+' </span>';
	}
	if( product.on_sale === true && !empty(product.sale_price)){
		regular_price = accountingPOS(product.regular_price, 'formatMoney');
		string += '<span class="register_sale_indicator">Sale <del>'+regular_price+'<del></span>';
    }
    var meta = '';

    for(var name in variation) { 
	   if (variation.hasOwnProperty(name)) {
	       var attr = variation[name];
		   var cls = name.toLowerCase() == 'item note' ? "item_note_meta" : "";
	       meta += '<li class="'+cls+'"><span class="meta_label">'+name+'</span><span class="meta_value">'+attr+'</span></li>';
	   }
	}
    if(meta != ''){
    	string += '<ul class="display_meta">'+meta+'</ul>';
    }
    return new Handlebars.SafeString(string);
});
Handlebars.registerHelper('missingAttributesOptions', function() {
	
	var string = '';
	[].forEach.call(this.options, function(val) {
		var slug = val.slug;
		var name = val.name;
		string += '<option value="'+slug+'">'+name+'</option>';				  
	});
    return new Handlebars.SafeString(string);
});
Handlebars.registerHelper('getCountItems', function() {
	
	var count = sizeof(this.line_items);
	if( count == 1 ){
		count += ' ' + pos_i18n[25][0];
	}else{
		count += ' ' + pos_i18n[25][1];
	}
    return new Handlebars.SafeString(count);
});

Handlebars.registerHelper('order_items_list', function() {
	var html = '';
	[].forEach.call(this.line_items, function(val) {
	 	html += '<tr>';
		 	html += '<td class="qty">'+val.quantity+'</td>';
		 	html += '<td class="name">';
		 	html += val.sku != '' ? val.sku + ' - ' : '';
				if( wc_pos_params.user_can_edit_product == true ){
			    	var edit_link =  (wc_pos_params.edit_link).replace('{{post_id}}', val.product_id);
			      	html += '<a title="'+val.name+'" href="'+edit_link+'" target="_blank" >'+val.name+'</a>';
			    }else{
			    	html += val.name;
			    }

			    if(sizeof(val.meta) > 0){
			    	var dat_tip = [];
			    	[].forEach.call(val.meta, function(meta) {
		    			dat_tip.push(meta.label + ': ' + meta.value );
			    	});
			    	
			    	html += '<a data-tip="' + ( dat_tip.join(', ') ) + '" class="tips" href="#">[?]</a>';
			    }			    

		 	html += '</td>';
	 	html += '</tr>';
	});
	return new Handlebars.SafeString(html);
});


Handlebars.registerHelper("switch", function(value, options) {
    this._switch_value_ = value;
    var html = options.fn(this); // Process the body of the switch block
    delete this._switch_value_;
    return html;
});
Handlebars.registerHelper("case", function() {
    // Convert "arguments" to a real array - stackoverflow.com/a/4775938
    var args = Array.prototype.slice.call(arguments);

    var options    = args.pop();
    var caseValues = args;

    if (caseValues.indexOf(this._switch_value_) === -1) {
        return '';
    } else {
        return options.fn(this);
    }
});

Handlebars.registerHelper("date", function(format, timestamp) {
    var html =  date(format, timestamp);
    return html;
});

Handlebars.registerHelper("accountingPOS", function(price, format) {
	if( price != '' ){
		if( typeof format !== 'string' ) format = 'formatMoney';
	    price = ' (' +  accountingPOS(price, format) + ')';
	    return new Handlebars.SafeString(price);
	}
	return price;
});


Handlebars.registerHelper('json', function(context) {
    return JSON.stringify(context);
});



Handlebars.registerHelper('product_addons_maxlength', function(addon_data) {
	var string = '';
	if( addon_data.max != ''){
		string += 'maxlength="'+addon_data.max+'"';
	}
    return new Handlebars.SafeString(string);
});

Handlebars.registerHelper('product_addons_max', function(addon_data) {
	
	var string = '';
	if( addon_data.max != ''){
		string += 'max="'+addon_data.max+'"';
	}
    return new Handlebars.SafeString(string);
});

Handlebars.registerHelper('product_addons_min', function(addon_data) {
	
	var string = '';
	if( addon_data.min != ''){
		string += 'min="'+addon_data.min+'"';
	}
    return new Handlebars.SafeString(string);
});

Handlebars.registerHelper('getAddonsCustomField', function(index, prIndex, type, options) {

    var field = '';
    switch (this.restrictions_type) {
		case 'email':
            field = '<input type="email" data-raw-price="'+this.price+'" placeholder=""\n' +
                'data-price="'+this.price+'"\n' +
                'pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\\.[a-z]{2,4}$"\n' +
                'class="input-text addon addon-custom addon-custom-email '+type+'_'+prIndex+' '+type+'_'+prIndex+'_'+index+'">';
            break;
		case 'only_numbers':
			field = '<input type="text" data-raw-price="{{this.price}}" data-price="{{this.price}}" pattern="[0-9]*"\n' +
                'placeholder=""\n' +
                'class="input-text addon addon-custom addon-custom-pattern '+type+'_'+prIndex+' '+type+'_'+prIndex+'_'+index+'"\n' +
                '{{product_addons_maxlength}}>';
            field = Handlebars.compile(field);
            field = field(this);
            break;
		case 'only_letters':
            field = '<input type="text" data-raw-price="{{price}}" data-price="{{price}}" pattern="[A-Za-z]*"\n' +
                'placeholder=""\n' +
                'class="input-text addon addon-custom addon-custom-pattern '+type+'_'+prIndex+' '+type+'_'+prIndex+'_'+index+'"\n' +
                '{{product_addons_maxlength}}>';
            break;
		case 'only_letters_numbers':
            field = '<input type="text" data-raw-price="{{price}}" data-price="{{price}}" pattern="[A-Za-z0-9]*"\n' +
                'placeholder=""\n' +
                'class="input-text addon addon-custom addon-custom-pattern '+type+'_'+prIndex+' '+type+'_'+prIndex+'_'+index+'"\n' +
                '{{product_addons_maxlength}}>';
            break;
		default:
            field = '<input type="text" data-raw-price="{{price}}" data-price="{{price}}"\n' +
                'placeholder=""\n' +
                'class="input-text addon addon-custom '+type+'_'+prIndex+' '+type+'_'+prIndex+'_'+index+'"\n' +
                '{{product_addons_maxlength}}>';
            field = Handlebars.compile(field);
            field = field(null, this);
            break;
    }
    return new Handlebars.SafeString(field);
});

Handlebars.registerHelper('getItemNote', function(addon_data) {
	var variation = this.cart_item_data.variation;
	var string = '';
	if(typeof variation["Item Note"] != "undefined"){
		string = variation["Item Note"];
	}
	return new Handlebars.SafeString(string);
});

Handlebars.registerHelper('ifEquals', function(arg1, arg2, options) {
	return (arg1 == arg2) ? options.fn(this) : options.inverse(this);
});