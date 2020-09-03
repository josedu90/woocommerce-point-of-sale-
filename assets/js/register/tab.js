class Tab {
    constructor(id) {
        this.id = id;
        this._setDefaultData();
    }

    _setDefaultData() {
        this.cart = {
            cart_contents: {},
            coupons: {},
            applied_coupons: [],
            chosen_shipping_methods: {price: '', title: ''},
            fees: [],
            fee_total: 0,
            cart_session_data: {
                'cart_contents_total': 0,
                'cart_contents_weight': 0,
                'cart_contents_count': 0,
                'total': 0,
                'subtotal': 0,
                'subtotal_ex_tax': 0,
                'tax_total': 0,
                'taxes': {},
                'shipping_taxes': {},
                'discount_cart': 0,
                'discount_cart_tax': 0,
                'shipping_total': 0,
                'shipping_tax_total': 0,
                'coupon_discount_amounts': {},
                '_coupon_discount_amounts': {},
                'coupon_discount_tax_amounts': {},
                '_coupon_discount_tax_amounts': {},
                'fee_total': 0,
                'fees': []
            },
        };
        this.customer = false;
    }

    setCart(key, value) {
        this.cart[key] = value;
    }

    //Should be global cart variable
    getCart() {
        CART.cart_contents = this.cart.cart_contents;
        CART.coupons = this.cart.coupons;
        CART.applied_coupons = this.cart.applied_coupons;
        CART.chosen_shipping_methods = this.cart.chosen_shipping_methods;
        CART.fees = this.cart.fees;
        CART.fee_total = this.cart.fee_total;
        CART.cart_session_data = this.cart.cart_session_data;
        if (this.customer) {
            CUSTOMER = Object.assign({}, this.customer);
            APP.setCustomer(CUSTOMER.id)
        } else {
            APP.setGuest();
            CUSTOMER.id = 0; //TODO: Fix this. Should be in the customer file.
        }
        console.log(CART);
    }

    clear() {
        this._setDefaultData();
    }

    setCustomer(CUSTOMER) {
        this.customer = Object.assign({}, CUSTOMER);
    }
}

jQuery(document).ready(function () {

    jQuery.fn.select2.amd.require([
        'select2/data/array',
        'select2/utils'
    ], function (ArrayData, Utils) {
        function CustomerData($element, options) {
            CustomerData.__super__.constructor.call(this, $element, options);
        }

        Utils.Extend(CustomerData, ArrayData);

        CustomerData.prototype.query = function (params, callback) {
            APP.search_customer_wc_3(params, callback);
        };

        jQuery("#tab-customer").select2({
            minimumInputLength: 3,
            multiple: false,
            dataAdapter: CustomerData
        }).change(function () {
            let customer_id = jQuery(this).val();
            jQuery('.box-tab.active').data('tab_customer', customer_id);
        });
    });

    jQuery('.box-tab').on('click', function () {
        jQuery('.select-tab').hide();
        jQuery('.tab-form').show();
        jQuery('.box-tab.active').removeClass('active');
        jQuery('#tab-title').val(jQuery(this).data('tab_title')).focus();
        jQuery('#tab-limit').val(jQuery(this).data('tab_limit'));
        var customer_id = jQuery(this).data('tab_customer');
        if (jQuery('#tab-customer option[value = "' + customer_id + '"]').length == 0) {
            jQuery.ajax({
                type: 'POST',
                url: wc_pos_params.ajax_url,
                data: {
                    action: 'wc_pos_get_customer_html',
                    customer_id: customer_id,
                },
                success: function (responce) {
                    jQuery('#tab-customer').append(responce);
                    jQuery('#tab-customer').val(customer_id).trigger('change');
                }
            });
        } else {
            jQuery('#tab-customer').val(customer_id).trigger('change');
        }
        if (jQuery(this).data('tab_register') != 0) {
            jQuery('#tab-register').prop('checked', true);
        } else {
            jQuery('#tab-register').prop('checked', false);
        }
        jQuery(this).addClass('active');
        jQuery('#modal-tabs .right-col .tab-number').text(jQuery(this).find('.tab-key').text());
    });

    jQuery('#open_tab').on('click', function () {
        var active_tab = jQuery('.box-tab.active');
        if (active_tab.length > 0) {
            if (jQuery('.tab-tabs .tab[data-tab_id="' + active_tab.data('tab_id') + '"]').length > 0) {
                APP.showNotice(pos_i18n[49], 'error');
                return false;
            }
            let title = jQuery('#tab-title').val();
            if (title.length < 3) {
                APP.showNotice(pos_i18n[52], 'error');
                return false;
            }
            let source = jQuery('#tmpl-tabs-head').html();
            let template = Handlebars.compile(source);
            active_tab.data('tab_limit', jQuery('#tab-limit').val());
            active_tab.data('tab_title', title);
            active_tab.find('.tab-title').text(title);
            active_tab.find('.tab-timer').timer();
            let data = {
                id: active_tab.data('tab_id'),
                title: title,
                customer: active_tab.data('tab_customer'),
                limit: active_tab.data('tab_limit'),
                order_id: active_tab.data('tab_order_id'),
                tab_number: active_tab.data('tab_number')
            };
            let html = template(data);
            jQuery('div.tab-tabs').append(html);
            source = jQuery('#tmpl-tabs-tab').html();
            template = Handlebars.compile(source);
            html = template(data);
            jQuery('#bill_screen .woocommerce_order_items_wrapper').append(html);
            jQuery('.md-close').click();
            jQuery('.tab[data-tab_id="' + data.id + '"]').click();
            if (active_tab.data('tab_order_id')) {
                APP.loadOrder(active_tab.data('tab_order_id'));
            }
            active_tab.find('span.status').text(pos_i18n[50]);
            jQuery('#tab-title').val('');
            jQuery('#tab-limit').val('');
        } else {
            APP.showNotice(pos_i18n[46], 'error');
        }
    });

    jQuery('.box-tab.saved').each(function () {
        jQuery(this).find('.tab-timer').timer({seconds: jQuery(this).data('time')})
    });
    jQuery(document).keypress(function (e) {
        let key = e.which;
        if (key == 13 && jQuery('#modal-tabs').hasClass('md-show') && jQuery('.box-tab.active').length > 0)  // the enter key code
        {
            jQuery('#open_tab').click();
            return false;
        }
    });
});

function closeActiveTab(saved) {
    jQuery('.select-tab').show();
    jQuery('.tab-form').hide();
    let tab_id = jQuery('.tab.active').data('tab_id');
    let tab_box = jQuery('.box-tab[data-tab_id="' + tab_id + '"]');
    jQuery('.tab.active:not(.main)').remove();
    tab_box.removeClass('active');
    if (!saved) {
        tab_box.find('.opened-amount').text('');
        tab_box.find('.tab-timer').timer('remove').text('');
        tab_box.find('.status').text(pos_i18n[51]);
        tab_box.find('.tab-title').text('');
        tab_box.data('tab_title', '');
    }
    jQuery('.tab:not(.active)').first().click();
}

function addSavedTab(tab_data) {
    let tab_box = jQuery('.box-tab[data-tab_id="' + tab_data.tab_id + '"]');
    tab_box.find('.tab-title').text(tab_data.title);
    tab_box.data('tab_title', tab_data.title);
    tab_box.data('tab_limit', tab_data.limit);
    tab_box.data('tab_order_id', tab_data.order_id);
    tab_box.addClass('saved');
    tab_box.find('.status').text(pos_i18n[50]);
    tab_box.find('.opened-amount').text(currency_symbol + CART.total.toFixed(2));
}

function removeSavedTab(tab_data) {
    let tab_box = jQuery('.box-tab[data-tab_order_id="' + tab_data.order_id + '"]');
    tab_box.find('.tab-title').text('');
    tab_box.data('tab_title', '');
    tab_box.data('tab_limit', '');
    tab_box.data('tab_order_id', '');
    tab_box.removeClass('saved');
    tab_box.find('.tab-timer').timer('remove').text('');
    tab_box.find('.status').text(pos_i18n[51]);
    tab_box.removeClass('active');
}
