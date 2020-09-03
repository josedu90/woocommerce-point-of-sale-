jQuery(document).ready(function ($) {
    window.POS_CUSTOMER = {
        'customer': false,
        'id': 0,
        'create_account': false,
        "billing_address": {
            "first_name": '',
            "last_name": "",
            "address_1": "",
            "address_2": "",
            "city": "",
            "state": "",
            "postcode": "",
            "country": "",
            "email": "",
            "phone": ""
        },
        "shipping_address": {
            "first_name": '',
            "last_name": "",
            "address_1": "",
            "address_2": "",
            "city": "",
            "state": "",
            "postcode": "",
            "country": ""
        },
        'additional_fields': {},
        'custom_order_fields': {},
        'acf_fields': {},
        'is_vat_exempt': false,
        'calculated_shipping': false,
        'default_customer_html': '',
        get_tax_billing_address: function () {
            return {
                'country': this.billing_address.country,
                'state': this.billing_address.state,
                'postcode': this.billing_address.postcode,
                'city': this.billing_address.city
            };
        },
        get_tax_shipping_address: function () {
            return {
                'country': this.shipping_address.country,
                'state': this.shipping_address.state,
                'postcode': this.shipping_address.postcode,
                'city': this.shipping_address.city
            };
        },
        get_taxable_address: function () {
            var tax_based_on = pos_wc.pos_tax_based_on;
            var taxable_address = {};
            if (this.customer === true) {
                switch (tax_based_on) {
                    case 'billing':
                        taxable_address = this.get_tax_billing_address();
                        break;
                    case 'shipping':
                        taxable_address = this.get_tax_shipping_address();
                        break;
                }
            } else {
                var default_customer_addr = wc_pos_params.default_customer_addr;
                switch (default_customer_addr) {
                    case 'base':
                        taxable_address = pos_wc.shop_location;
                        break;
                    case 'outlet':
                        var address = {
                            country: pos_wc.outlet_location.contact.country,
                            state: pos_wc.outlet_location.contact.state,
                            city: pos_wc.outlet_location.contact.city,
                            postcode: pos_wc.outlet_location.contact.postcode
                        };
                        taxable_address = address;
                        break;
                }
            }

            return taxable_address;
        },
        get_defaults: function () {
            var _default = {
                'customer': false,
                'id': 0,
                'create_account': false,
                'avatar_url': wc_pos_params.avatar,
                "billing_address": {
                    "first_name": '',
                    "last_name": "",
                    "address_1": "",
                    "address_2": "",
                    "city": "",
                    "state": CUSTOMER.default_state,
                    "postcode": "",
                    "country": CUSTOMER.default_country,
                    "email": "",
                    "phone": ""
                },
                "shipping_address": {
                    "first_name": '',
                    "last_name": "",
                    "address_1": "",
                    "address_2": "",
                    "city": "",
                    "state": CUSTOMER.default_state,
                    "postcode": "",
                    "country": CUSTOMER.default_country
                },
                'additional_fields': {},
                'custom_order_fields': {},
                'acf_fields': {},
                'email': '',
                'first_name': '',
                'last_name': '',
                'role': '',
                'username': '',
                'fullname': '',
                'is_vat_exempt': false,
                'calculated_shipping': false,
                'points_balance': '',
                'account_funds': accountingPOS(0, 'formatMoney'),
                'user_meta': {}
            };
            return _default;
        },
        reset: function () {
            var _default = this.get_defaults();
            $.each(_default, function (index, val) {
                window.POS_CUSTOMER[index] = val;
            });
        },

        /**
         * Set default data for a customer
         */
        set_default_data: function (record) {
            var h = $('#wc-pos-register-data').height();
            if (record && $.type(record) === 'object') {
                var fullName = [record.first_name, record.last_name].join(" ");

                if (empty(trim(fullName))) {
                    fullName = record.username;
                }
                record.fullname = fullName;

                var source = $('#tmpl-cart-customer-item').html();
                var template = Handlebars.compile(source);
                var html = template(record);
                $('#customer_items_list').html(html);
                $('#customer_user').select2('val', '', false);
                CUSTOMER.customer = true;
                var _default = this.get_defaults();
                $.each(_default, function (index, val) {
                    if (index == 'acf_fields' && typeof record.meta_data !== 'undefined') {
                        var acf_fields = wc_pos_params.acf_fields;
                        var user_meta = record.meta_data;
                        // $.each(acf_fields, function (i, key) {
                        //     if (typeof user_meta[key] != 'undefined')
                        //         window.POS_CUSTOMER[index][key] = user_meta[key][0];
                        // });
                        $.each(user_meta, function (i, meta) {
                            if ( in_array(meta.key, acf_fields) ){
                                window.POS_CUSTOMER[index][meta.key] = meta.value;
                            }

                        });
                    } else if (typeof record[index] != 'undefined') {
                        window.POS_CUSTOMER[index] = record[index];
                    }
                });
                ADDONS.points.set_discount_for_redeeming_points();
            }else if(!empty(pos_register_data.default_customer) && parseInt(pos_register_data.default_customer) > 0){
                if(!empty(CUSTOMER.default_customer_html)){
                    $("#customer_items_list").html($(CUSTOMER.default_customer_html));
                }else{
                    $.ajax({
                        url: wc_pos_params.wc_api_url + 'customers/' + parseInt(pos_register_data.default_customer),
                        success: function(user){
                            APP.db.put('customers', user);

                            user.fullname = [user.first_name, user.last_name].join(' ');

                            var source = $('#tmpl-cart-customer-item').html();
                            var template = Handlebars.compile(source);
                            var html = template(user);

                            CUSTOMER.default_customer_html = html;
                            CUSTOMER.set_default_data(user);
                        }
                    })
                }
            } else {
                CUSTOMER.customer = false;
                var html = $('#tmpl-cart-guest-customer-item').html();
                $('#customer_items_list').html(html);
                h = $('#wc-pos-register-data').height();
            }

            var accountFunds =  $("#accountfunds");
            if(accountFunds.length){
                if(CUSTOMER.customer && !empty(CUSTOMER.account_funds)){
                    accountFunds.find(".woocommerce-Price-amount").html(accountingPOS(CUSTOMER.account_funds, 'formatMoney'));
                }else{
                    var symbol = accountFunds.find(".woocommerce-Price-currencySymbol");
                    accountFunds.find(".woocommerce-Price-amount").html(symbol).append("0");
                }
            }

            if (CUSTOMER['country'] == '') {
                CUSTOMER['country'] = CUSTOMER.get_default_country();
            }

            if (CUSTOMER['shipping_country'] == '') {
                CUSTOMER['shipping_country'] = CUSTOMER['country'];
            }

            if (CUSTOMER['state'] == '') {
                CUSTOMER['state'] = CUSTOMER.get_default_state();
            }

            if (CUSTOMER['shipping_state'] == '') {
                CUSTOMER['shipping_state'] = CUSTOMER['state'];
            }
            h = $('#wc-pos-register-data').height();
        },
        get_default_country: function () {
            var default_c = '';
            switch (wc_pos_params.default_customer_addr) {
                case 'base':
                    default_c = pos_wc.shop_location.country;
                    break;
                case 'outlet':
                    default_c = pos_wc.outlet_location.contact.country;
                    break;
            }
            return default_c;
        },
        get_default_state: function () {
            var default_c = '';
            switch (wc_pos_params.default_customer_addr) {
                case 'base':
                    default_c = pos_wc.shop_location.state;
                    break;
                case 'outlet':
                    default_c = pos_wc.outlet_location.contact.state;
                    break;
            }
            return default_c;
        }

    }
});

