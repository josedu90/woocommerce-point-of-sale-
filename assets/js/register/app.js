var APP = null;
var TAX = null;
var CUSTOMER = null;
var SHIPPING = null;
var ADDONS = null;
var BOOKING = null;
var SUBSCRIPTION = null;
var PR_ADDONS = null;
var POS_TRANSIENT = {};
var resizeCart = null;
var change_price_timer, changeProductPrice = null;
var changeProductQuantity = null;
var subtotals_height = 0; //TODO: need find better solution
var total_height = 0;
const store = {
    products: [],
    orders: [],
    coupons: [],
    customers: []
};

jQuery(document).ready(function ($) {

    var paymentSenseDeferred = $.Deferred(),
        paymentSenseTimeout = null,
        paymentSenseNotifications = [],
        paymentSenseTransaction = true,
        paymentSenseSignature = false;

    $(window).load(function () {
        $('#preloader').hide(0, function () {
            $(this).remove();
        });
    });

    $('#force_open_reg').on('click', function (e) {
        e.preventDefault();

        var confirmForce = confirm("This process will log the existing user out of this register. Are you sure want to take over this register?");

        if (confirmForce) {
            $('.modal-locked-register, #post-body').block({
                overlayCSS: {
                    background: '#fff',
                    opacity: 0.6
                }
            });

            $.ajax({
                type: 'POST',
                url: wc_pos_params.ajax_url,
                data: {
                    action: 'wc_pos_force_register',
                    register_id: pos_register_data.ID
                },
                success: function (response) {
                    location.reload();
                },
                error: function (error) {
                    console.log(error);
                },
                complete: function () {
                    $('.modal-locked-register, #post-body').unblock();
                }
            });
        }

    });

    $(document).on('heartbeat-send', function (event, data) {
        data.pos_register_id = pos_register_data.ID;
    }).on('heartbeat-tick', function (event, data) {
        APP.checkPOSUserLogin(data);
    }).on('visibilitychange', function (event) {

        if (wc_pos_params.auto_logout_session == "0") {
            return;
        }

        if (APP.isTabVisible()) {
            APP.startInactivity();
        } else {
            clearTimeout(APP.sessionTimeout);
        }
    });

    var page = 1,
        layout = $("#grid_layout_cycle"),
        product_list = layout.children('ul'),
        tile_limit = getLimit();
    layout.on('scroll', function (e) {
        //if (((layout.scrollTop() === layout.children('ul').outerHeight(true) - layout.height())) || APP.getOffSet() < 1 || APP.getOffSet() < getLimit()) {
        if (!APP.is_request && APP.is_not_list() && APP.isOnline()) {
            var product_ids = [];
            var categoryId = $('.cat_title:not(#wc-pos-register-grids-title)').last().data('parent');

            if (APP.fullLoadedCategories.indexOf(categoryId) === -1) {
                APP.is_request = true;
                product_list.next('.product-loader').remove();
                product_list.after("<div class='product-loader'></div>");
                product_list.next(".product-loader").block({
                    message: "Loading Products",
                    overlayCSS: {
                        background: '#fff',
                        opacity: 0.6
                    }
                });
                $('#tile_go_back, .open_category').addClass('loading');
                $.when(APP.getServerProducts(APP.getRequestLimit(), APP.getOffSet(), categoryId, APP.getIncludes())).then(function (products, status, jqXHR) {
                    if (products.length > 0) {

                        $.each(products, function (i, product) {

                            if (disable_sale_prices) {
                                product.price = product.regular_price;
                                product.on_sale = false;
                            }

                            APP.db.put('products', product);

                            if (product.variations.length > 0) {
                                $.each(product.variations, function (i, variation) {

                                    if(disable_sale_prices){
                                        variation.price = variation.regular_price;
                                        variation.on_sale = false;
                                    }

                                    APP.db.put('variations', {
                                        id: variation.id,
                                        prod_id: product.id,
                                        title: product.name,
                                        sku: variation.sku
                                    });
                                });
                            }

                        });

                        APP.setOffSet(products.length);
                        if (products.length < APP.getRequestLimit()) {
                            APP.fullLoadedCategories.push(categoryId);
                        }
                    } else {
                        //TODO: No products message
                        /*product_list.next('.product-loader').remove();
                        product_list.after("<div class='product-loader'></div>");
                        product_list.next(".product-loader").block({
                            message: "No products or categories found",
                            overlayCSS: {
                                background: '#fff',
                                opacity: 0.6
                            }
                        });*/
                    }
                    if (jqXHR) {
                        var serverLimit = parseInt(jqXHR.getResponseHeader("x-wp-total"));
                        pos_grid.tile_limit = serverLimit < pos_grid.tile_limit ? serverLimit : pos_grid.tile_limit;
                    }

                    $.each(products, function (i, product) {
                        product_ids.push(product.id);
                    });
                }).always(function (a) {
                    if (product_ids.length > 0) {
                        APP.is_request = false;

                        APP.db.values('products', product_ids).done(function (products) {
                            $.each(products, function (i, product) {
                                if ($('#product_' + product.id).length > 0) {
                                    let productEl = $('#product_' + product.id);
                                    let productCategories = productEl.data('root-category');

                                    if($.inArray(categoryId, productCategories) === -1){
                                        productCategories.push(categoryId);
                                        productEl.data('root-category', productCategories);
                                    }

                                    return;
                                }
                                if (wc_pos_params.image_size == 'thumbnail') {
                                    if (product.thumbnail_src) {
                                        product.featured_src = product.thumbnail_src;
                                    } else {
                                        if (product.images.length > 0) {
                                            product.featured_src = product.images[0].src;
                                        }
                                    }
                                }

                                $.each(product.attributes, function (index, attribute) {
                                    if (!attribute.slug && !attribute.is_taxonomy) {
                                        product.attributes[index].slug = str_replace(' ', '_', attribute.name).toLowerCase();
                                    }
                                });

                                if (typeof product.type != 'undefined' && product.type == 'variable' && product.variations.length > 0 && pos_grid.tile_variables == 'tiles') {
                                    var $li = $('<li id="product_' + product.id + '" class="title_product open_variantion category_cycle" data-id="' + product.id + '" data-root-category="[' + categoryId + ']" title="' + product.name + '"><span></span><span class="price"></span></li>');
                                } else {
                                    var $li = $('<li id="product_' + product.id + '" class="title_product add_grid_tile category_cycle" data-id="' + product.id + '" data-root-category="[' + categoryId + ']" title="' + product.name + '"><span></span><span class="price"></span></li>');
                                }
                                $li.find('span').first().html(product.name);
                                var price = pos_get_price_html(product);
                                $li.find('span').last().html(price);

                                if (typeof pos_grid.tile_styles[product.id] != 'undefined' && pos_grid.tile_styles[product.id].style == 'colour' && !pos_grid.hide_text) {
                                    $li.addClass("colour_tile");
                                    $li.append('<span class="colour_label" style="background-color: #' + pos_grid.tile_styles[product.id].background + '"></span>');
                                } else {
                                    if (typeof product.featured_src != 'undefined' && product.featured_src) {
                                        $li.css({
                                            'background-image': 'url(' + product.featured_src + ')'
                                        });
                                    } else {
                                        $li.css({
                                            'background-image': 'url(' + wc_pos_params.def_img + ')'
                                        });
                                    }
                                }

                                //store.products.push(product);
                                pos_grid.products_sort.push(product.id);
                                if (APP.is_not_list(product)) {
                                    product_list.append($li);
                                }
                            });
                            $(".title_product").each(function () {
                                let productCategories = $(this).data('root-category');
                                if($.inArray(categoryId, productCategories) !== -1){
                                    $(this).show();
                                }
                            });
                        });
                    } else {
                        APP.fullLoadedCategories.push(categoryId);
                    }
                    product_list.next('.product-loader').remove();
                    $('#tile_go_back, .open_category').removeClass('loading');
                });
            }
        }
        //}
    });

    var wc_pos_dining_option = $('.dining-option-selector.checked').find('span').text();
    var wc_pos_dining_option_default = wc_pos_dining_option === '' ? 'None' : wc_pos_dining_option;

    localStorage.setItem('register_status_' + pos_register_data.ID, 'open');
    jQuery('#close_register').on('click', function (e) {
        var result = confirm(pos_i18n[48]);
        if (result) {
            localStorage.setItem('register_status_' + pos_register_data.ID, 'close');
        } else {
            e.preventDefault();
        }
    });
    // ajaxSetup is global, but we use it to ensure JSON is valid once returned.temp@_@admintemp
    $.ajaxSetup({
        headers: {
            "X-WP-Nonce": wc_pos_params.rest_nonce
        },
        dataFilter: function (raw_response, dataType) {
            // We only want to work with JSON
            if ('json' !== dataType) {
                return raw_response;
            }
            try {
                // Check for valid JSON
                var data = $.parseJSON(raw_response);
                if (data && 'object' === typeof data) {

                    // Valid - return it so it can be parsed by Ajax handler
                    return raw_response;
                }

            } catch (e) {
                // Attempt to fix the malformed JSON
                var matches = new Array();
                matches.push(raw_response.match(/{"count.*}/));
                matches.push(raw_response.match(/{"order.*}/));
                matches.push(raw_response.match(/{"customer.*}/));
                matches.push(raw_response.match(/{"product.*}/));
                matches.push(raw_response.match(/{"coupon.*}/));
                matches.push(raw_response.match(/{"posts_ids.*}/));


                var valid_json = null;
                for (var i = 0; i < matches.length; i++) {
                    var m = matches[i];
                    if (m !== null) {
                        valid_json = m;
                    }
                }

                if (null === valid_json) {
                    console.log('Unable to fix malformed JSON');
                } else {
                    console.log('Fixed malformed JSON. Original:');
                    //console.log(valid_json[0]);
                    raw_response = valid_json[0];
                }
            }
            return raw_response;
        }
    });

    toastr.options = {
        "closeButton": false,
        "debug": false,
        "newestOnTop": false,
        "progressBar": false,
        "positionClass": "toast-bottom-left",
        "preventDuplicates": false,
        "onclick": null,
        "showDuration": "300",
        "hideDuration": "1000",
        "timeOut": "3000",
        "extendedTimeOut": "1000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
    }
    window.openwin = false;
    CART = window.POS_CART;
    TAX = window.POS_TAX;
    CUSTOMER = window.POS_CUSTOMER;
    SHIPPING = window.POS_SHIPPING;
    ADDONS = window.POS_ADDONS;
    BOOKING = window.POS_BOOKING;
    SUBSCRIPTION = window.POS_SUBSCRIPTION;
    PR_ADDONS = window.POS_PR_ADDONS;
    APP = window.POS_APP = {
        //version          : Math.random(),
        version: '32',
        customerVersion: '2',
        db: null,
        initialized: false,
        lastUpdate: 'null',
        lastOffset: {
            allProducts: 0,
            productGrids: 0,
            customers: 0,
            coupons: 0
        },
        fullLoadedCategories: [],
        limits: {},
        lastUpdateCoupon: 'null',
        lastOffsetCoupon: 0,
        productCount: 0,
        lastUpdateCustomer: 'null',
        lastOffsetCustomer: 0,
        signature: ['null', 'null'],
        is_request: false,
        tmp: {
            product_item: {},
            products: {},
            productscans: {},
        },
        coupons: {},
        active_tab: 'main',
        tabs: {},
        spend_limit: 0,
        sessionTimeout: null,
        schema: {
            stores: [{
                name: 'products',
                keyPath: 'id',
                indexes: [
                    {
                        name: 'name',
                        keyPath: 'name',
                        multiEntry: true,
                    },
                    {
                        name: 'sku',
                        keyPath: 'sku'
                    },
                    {
                        name: 'skutitle',
                        generator: function (obj) {
                            var sku = '';
                            if (obj.sku != '') {
                                sku = obj.sku + ' ';
                            }
                            var search_str = sku + obj.title;
                            search_str = search_str.toLowerCase();
                            return search_str;
                        }
                    },
                ]
            },
                {
                    name: 'variations',
                    keyPath: 'id',
                    indexes: [
                        {
                            name: 'prod_id',
                            keyPath: 'prod_id',
                        },
                        {
                            name: 'sku',
                            keyPath: 'sku',
                        },
                        {
                            name: 'name',
                            keyPath: 'name',
                        },
                    ]
                },
                {
                    name: 'orders',
                    keyPath: 'id'
                },
                {
                    name: 'coupons',
                    keyPath: 'code',
                },
                {
                    name: 'customers',
                    keyPath: 'id',
                    indexes: [
                        {
                            name: 'email',
                            keyPath: 'email',
                            multiEntry: true,
                        },
                        {
                            name: 'username',
                            keyPath: 'username',
                        },
                        {
                            name: 'phone',
                            generator: function (obj) {
                                var phonenumber = obj.billing_address['phone'];
                                var search_str = phonenumber;
                                if (search_str == '') {
                                    search_str = obj.username;
                                }
                                if (typeof search_str != 'string') {
                                    search_str = '';
                                }
                                search_str = search_str.toLowerCase();
                                return search_str;
                            }
                        },
                        {
                            name: 'fullname',
                            generator: function (obj) {
                                var fullname = [obj.first_name, obj.last_name]
                                var search_str = fullname.join(' ').trim();
                                if (search_str == '') {
                                    search_str = obj.username;
                                }
                                if (typeof search_str != 'string') {
                                    search_str = '';
                                }
                                search_str = search_str.toLowerCase();
                                return search_str;
                            }
                        },
                        {
                            name: 'lastfirst',
                            generator: function (obj) {
                                var lastfirst = [obj.last_name, obj.first_name]
                                var search_str = lastfirst.join(' ').trim();
                                if (search_str == '') {
                                    search_str = obj.username;
                                }
                                if (typeof search_str != 'string') {
                                    search_str = '';
                                }
                                search_str = search_str.toLowerCase();
                                return search_str;
                            }
                        },
                    ]
                },
                {
                    name: 'offline_orders',
                    keyPath: 'id'
                }
            ]
        },
        processing_payment: false,
        interval: 800 * 1000,
        sync_status: {
            'product': false,
            'coupon': false,
            'customer': false
        },
        category_que: [],
        category_request: false,


        init: function () {
            if (wc_pos_params.default_country) {
                CUSTOMER.default_country = wc_pos_params.default_country;
                CUSTOMER.default_state = '';
                if (CUSTOMER.default_country.indexOf(':') !== false) {
                    var location = CUSTOMER.default_country.split(':');
                    CUSTOMER.default_country = location[0];
                    CUSTOMER.default_state = location[1];
                }
                CUSTOMER.billing_address.country = CUSTOMER.default_country;
                CUSTOMER.billing_address.state = CUSTOMER.default_state;
                CUSTOMER.shipping_address.country = CUSTOMER.default_country;
                CUSTOMER.shipping_address.state = CUSTOMER.default_state;
            }

            var mechanisms = ['indexeddb', 'websql'];
            var ua = navigator.userAgent.toLowerCase();
            if (ua.indexOf('safari') != -1) {
                if (ua.indexOf('chrome') > -1) {
                    // Chrome
                } else {
                    mechanisms = ['websql', 'indexeddb']; // Safari
                }
            }

            APP.initialized = true;
            APP.checkCookieVersion();

            APP.debug("Open Database. Version " + APP.version);
            APP.db = new ydn.db.Storage('WC-POS', APP.schema, {mechanisms: mechanisms});
            APP.db.clear();

            if (typeof BOOKING != 'undefined') {
                BOOKING.init();
            }
            if (typeof SUBSCRIPTION != 'undefined') {
                SUBSCRIPTION.init();
            }
            if (typeof CART != 'undefined') {
                CART.init();
            }
            if (typeof PR_ADDONS != 'undefined') {
                PR_ADDONS.init();
            }
            APP.updateSearchList(0);
            APP.sync_data(true);
            APP.addGrid();
            layout.trigger('scroll');

            APP.ready();
            APP.setCookieVersion();

            wp.heartbeat.interval(15);

            if (wc_pos_params.autoupdate_interval != '') {
                APP.interval = wc_pos_params.autoupdate_interval * 1000;
            }
            if (wc_pos_params.autoupdate_stock == 'yes') {
                setInterval(APP.sync_data, APP.interval);
            }

            if (sizeof(custom_fees) > 0) {
                $.each(custom_fees, function (k, fee) {
                    //TODO: change to bollean by default
                    if (fee.taxable === 'yes') {
                        fee.taxable = true;
                    } else {
                        fee.taxable = false;
                    }
                    fee.amount = floatval(fee.value);
                    CART.add_custom_fee(fee);
                });
            }
            if (wc_pos_params.tabs_management) {
                let active_tab = $('.tbc.tab.active').data('tab_id');
                APP.active_tab = active_tab;
                APP.tabs[active_tab] = new Tab(active_tab);
            }

            $('body').on('click', '#cancel-pm-process', function (e) {
                paymentSenseTransaction = false;
                $(this).remove();
            });

            if (wc_pos_params.offline_mode) {
                $('#modal-clone-window').clone().attr('id', 'offline-mode-progress').insertBefore('#modal-clone-window');

                var offline_mode = $("#offline-mode-progress");
                offline_mode.find('.md-content p').html('<strong>' + pos_i18n[58] + '</strong>');
                offline_mode.find('a.button').remove();
                offline_mode.find('button.button').attr('onclick', "closeModal('offline-mode-progress')").text('Close');

                setTimeout(function () {
                    openModal('offline-mode-progress');
                }, 2000);
            }
        },
        sync_data: function (block) {
            $('#last_sync_time').attr('title', APP.lastUpdate).timeago();
            APP.sync_ProductData(block);
            APP.sync_CouponData(block);
            if (wc_pos_params.offline_mode) {
                APP.sync_CustomerData(block);
            }
        },
        sync_ProductData: function (block) {
            if (APP.processing_payment === true || APP.sync_status.product === true) return;
            if ($('#grid_layout_cycle').length && block === true && pos_grid.second_column_layout == 'product_grids') {
                var h = parseFloat($('#wc-pos-register-grids').height()) - 80;
                $('#grid_layout_cycle').height(h);
            }
            APP.debug('Checking new products');
            APP.sync_status.product = true;
            APP.debug(APP.productCount + ' new products', false);
            //APP.insertUpdate(50, APP.lastOffset, APP.productCount);
        },
        sync_CouponData: function (block) {
            if (APP.processing_payment === true || APP.sync_status.coupon === true) return;
            APP.sync_status.coupon = true;
            $.when(APP.getServerCoupons(100, APP.lastOffsetCoupon)).then(function (Coupons, status, jqXHR) {
                if (typeof jqXHR === "undefined") {
                    return;
                }
                var CouponsCount = jqXHR.getResponseHeader("x-wp-total");
                if (CouponsCount && parseInt(CouponsCount) > 0) {
                    if (Coupons.length > 0) {
                        APP.db.putAll('coupons', Coupons).fail(function (e) {
                            throw e;
                        });
                    }
                    APP.lastOffsetCoupon += Coupons.length;
                    APP.setCookie('pos_LastOffsetCoupon', APP.lastOffsetCoupon, 5);

                    if (CouponsCount >= APP.lastOffsetCoupon) {
                        APP.sync_CouponData();
                    }
                    else {
                        APP.lastUpdateCoupon = APP._formatLastUpdateFilter('pos_lastUpdateCoupon');
                        APP.sync_status.coupon = false;
                    }

                } else {
                    APP.lastUpdateCoupon = APP._formatLastUpdateFilter('pos_lastUpdateCoupon');
                    APP.lastOffsetCoupon = 0;
                    APP.setCookie('pos_LastOffsetCoupon', APP.lastOffsetCoupon, 5);
                    APP.sync_status.coupon = false;
                }
            });
        },
        sync_CustomerData: function (block) {
            if (APP.processing_payment === true || APP.sync_status.customer === true) return;
            if (block === true) {
                $('#wc-pos-customer-data').block({
                    message: null,
                    overlayCSS: {
                        background: '#fff',
                        opacity: 0.6
                    }
                });
            }
            APP.sync_status.customer = true;
            APP.customersUpdate(100, APP.getOffSet('customers'));
        },
        check_RemovedItems: function () {
            $.when(APP.getServerRemovedItems()).then(function (items) {
                if (parseInt(items.post_ids.length) > 0) {
                    $.each(items.post_ids, function (index, post_id) {
                        APP.db.remove('products', post_id);
                        APP.db.remove('variations', post_id);
                        APP.db.remove('orders', post_id);
                        APP.db.remove('coupons', post_id);
                    });
                }
                if (parseInt(items.user_ids.length) > 0) {
                    $.each(items.user_ids, function (index, user_id) {
                        APP.db.remove('customers', user_id);
                    });
                }

            });
        },
        updateCustomersCompleted: function () {
            APP.sync_status.customer = false;
            APP.lastUpdateCustomer = APP._formatLastUpdateFilter('pos_lastUpdateCustomer');
            $('#wc-pos-customer-data').unblock();
        },
        insertUpdate: function (limit, offset, ProductsCount) {
            limit = pos_grid.tile_limit <= 50 ? pos_grid.tile_limit : limit;

            if ($("#grid_layout_cycle ul li").length >= pos_grid.tile_limit) {
                return;
            }

            var d = limit + offset;
            if (ProductsCount < d)
                d = ProductsCount;

            $.when(APP.getServerProducts(limit, offset)).then(function (productsData) {
                if (productsData.length > 0) {
                    $.each(productsData, function (k, product) {

                        pos_grid.products_sort.push(product.id);

                        if (disable_sale_prices) {
                            product.price = product.regular_price;
                            product.on_sale = false;
                            if (product.variations.length > 0) {
                                $.each(product.variations, function (i, variation) {
                                    variation.price = variation.regular_price;
                                    variation.on_sale = false;
                                });
                            }
                        }
                    });
                    $.each(productsData, function (index, product) {
                        APP.insertSearchListItem(product);
                        if (product.variations.length > 0) {
                            $.each(product.variations, function (i, variation) {
                                APP.db.put('variations', {
                                    id: variation.id,
                                    prod_id: product.id,
                                    title: product.name,
                                    sku: variation.sku
                                });
                            });
                        }
                        APP.insertRelationships(product);
                    });
                }
                APP.setOffSet(offset + productsData.length);
                APP.setCookie('pos_LastOffset', APP.lastOffset, 5);

                APP.debug('Loaded ' + d + ' of ' + ProductsCount + ' products');

                if (ProductsCount >= limit + offset) {
                    APP.insertUpdate(limit, limit + offset, ProductsCount);
                    APP.canHide();
                } else {
                    APP.sync_status.product = false;
                    APP.lastUpdate = APP._formatLastUpdateFilter();
                    APP.addGrid();
                }

                if (APP.is_request === true) {
                    APP.is_request = false;
                }
            });
        },
        getServerProductsCount: function () {
            var v = APP.makeid();
            var filter = "?filter[updated_at_min]=" + APP.lastUpdate + "&v=" + v;
            var e = $.getJSON(wc_pos_params.wc_api_url + 'products/count/' + filter);
            return e;
        },
        getServerProduct: function (find, by) {
            var e = $.getJSON(wc_pos_params.ajax_url, {
                action: 'wc_pos_get_product_by',
                find: find,
                by: by,
            });
            return e;
        },
        getServerProducts: function (limit, offset, category, includes) {
            var e = $.get(wc_pos_params.wc_api_url + 'pos_products/',
                {
                    per_page: limit,
                    offset: offset ? offset : 0,
                    category: category ? category : null,
                    include: includes ? includes : [],
                    orderby: APP.getServerProductSortingArgs(false),
                    order: APP.getServerProductSortingArgs(true)
                }
            );
            return e;
        },
        getServerProductSortingArgs: function (type) {
            var sorting = pos_grid.tile_sorting,
                sorting_type = "desc";
            switch (sorting) {
                case "price-desc":
                case "popularity":
                case "rating":
                    break;
                case "price":
                    sorting = "price";
                    sorting_type = "asc";
                    break;
                case "date":
                    sorting_type = "desc";
                    break;
                case "title-asc":
                case "menu_order":
                default:
                    sorting = "title";
                    sorting_type = "asc";
                    break;
            }

            if (type) {
                return sorting_type;
            }

            return sorting;
        },
        getServerOrdersCount: function (reg_id, search) {
            var filter = {};
            if (reg_id == 'all') {
                if (!wc_pos_params.load_web_order) {
                    filter['meta_key'] = 'wc_pos_id_register';
                    filter['meta_value'] = '';
                    filter['meta_compare'] = '!=';
                }
            } else {
                filter['meta_key'] = 'wc_pos_id_register';
                filter['meta_value'] = reg_id;
            }

            if (typeof search != 'undefined') {
                filter['q'] = search;
            }
            var v = APP.makeid();
            var e = $.getJSON(wc_pos_params.wc_api_url + 'orders/count/?v=' + v, {
                action: "wc_pos_json_api",
                reg_id: reg_id,
                filter: filter,
                status: wc_pos_params.load_order_status
            });
            return e;
        },
        getServerOrders: function (reg_id, search, status) {
            var e = $.getJSON(wc_pos_params.wc_api_url + 'orders/', {
                status: status ? status : wc_pos_params.load_order_status,
                reg_id: reg_id,
                search: search
            });
            return e;
        },
        couponsUpdate: function (limit, offset, CouponsCount) {
            var d = limit + offset;
            if (CouponsCount < d)
                d = CouponsCount;

            $.when(APP.getServerCoupons(limit, offset)).then(function (couponsData) {
                if (couponsData.coupons.length > 0) {
                    APP.db.putAll('coupons', couponsData.coupons).fail(function (e) {
                        throw e;
                    });
                }
                APP.lastOffsetCoupon = offset;
                APP.setCookie('pos_LastOffsetCoupon', APP.lastOffsetCoupon, 5);

                if (CouponsCount >= limit + offset) {
                    APP.couponsUpdate(limit, limit + offset, CouponsCount);
                }
                else {
                    APP.lastUpdateCoupon = APP._formatLastUpdateFilter('pos_lastUpdateCoupon');
                    APP.sync_status.coupon = false;
                }
            });
        },
        getServerCoupons: function (limit, offset) {
            var v = APP.makeid();
            var e = $.get(wc_pos_params.wc_api_url + 'coupons/', {
                per_page: limit,
                offset: offset
            });
            return e;
        },
        customersUpdate: function (limit, offset) {
            $.when(APP.getServerCustomers(limit, offset)).then(function (customers, status, jqxhr) {
                if (customers.length > 0) {
                    APP.db.putAll('customers', customers);
                    APP.lastOffset['customers'] += customers.length;
                }
                var serverCount = jqxhr.getResponseHeader("x-wp-total");
                APP.db.count('customers').done(function (count) {
                    if (serverCount > count) {
                        APP.customersUpdate(limit, APP.getOffSet('customers'));
                    } else {
                        APP.updateCustomersCompleted();
                    }
                    $('#wc-pos-customer-data').unblock();
                });
            });
        },
        getServerCustomersCount: function () {
            var v = APP.makeid();
            var filter = "?filter[updated_at_min]=" + APP.lastUpdateCustomer + "&v=" + v;
            var e = $.getJSON(wc_pos_params.wc_api_url + 'customers/count/' + filter, {
                action: "wc_pos_json_api",
                role: "all",
            });
            return e;
        },
        getServerCustomers: function (limit, offset) {
            var e = $.getJSON(wc_pos_params.wc_api_url + 'customers/', {
                per_page: limit,
                role: "all",
                offset: offset
            });
            return e;
        },
        getServerCustomer: function (id) {
            var e = $.getJSON(wc_pos_params.wc_api_url + 'customers/' + id);
            return e;
        },
        getServerGridOptions: function () {
            var e = $.getJSON(wc_pos_params.ajax_url, {
                action: "wc_pos_get_grid_options",
                reg: wc_pos_register_id
            });
            return e;
        },
        getServerRemovedItems: function () {
            var v = APP.makeid();
            return jQuery.getJSON(wc_pos_params.wc_api_url + 'pos_removed/?v=' + v);
        },
        canHide: function () {
            APP.addGrid();
            $('#modal-1 .md-close').show();
        },
        loadOrder: function (order_id, items) {
            if (typeof items === "undefined") {
                items = [];
            }
            APP.db.get('orders', order_id).done(function (order) {
                CART.empty_cart(false);

                POS_TRANSIENT.order_id = order.id;
                $.each(order.line_items, function (index, item) {

                    if (items.length) {
                        var met = false;
                        $.each(items, function (i, it) {
                            if (it.id === item.id) {
                                met = true;
                                item.quantity = it.quantity;
                                return false;
                            }
                        });
                        if (!met) {
                            return true;
                        }
                    }

                    item.stock_reduced = order.stock_reduced;

                    var quantity = parseInt(item.quantity);
                    if (wc_pos_params.decimal_quantity == 'yes') {
                        quantity = parseFloat(item.quantity);
                    }
                    var variation_id = 0;
                    var variation = {};

                    if (typeof item.variation_id != 'undefined') {
                        variation_id = item.variation_id;
                    }
                    if (item.meta_data.length > 0) {
                        $.each(item.meta_data, function (index, val) {
                            variation[val.key] = val.value;
                        });
                    }
                    if (pos_custom_product.id == item.product_id || item.product_id == null) {
                        var adding_to_cart = clone(pos_custom_product);
                        adding_to_cart.title = item.name;
                        adding_to_cart.price = item.price;

                        adding_to_cart.regular_price = adding_to_cart.price;

                        var subtotal_tax = parseFloat(item.subtotal_tax);
                        if (item.tax_class != null || subtotal_tax > 0) {
                            adding_to_cart.tax_class = item.tax_class != null ? item.tax_class : '';
                            adding_to_cart.taxable = true;
                            adding_to_cart.tax_status = 'taxable';
                        } else {
                            adding_to_cart.tax_status = 'none';
                            adding_to_cart.taxable = false;
                        }
                        adding_to_cart.item_id = item.id;

                        CART.addToCart(adding_to_cart, adding_to_cart.id, quantity, 0, variation, item);

                    } else {

                        APP.db.get('products', item.product_id).then(function (product) {
                            if (product) {
                                APP.addToCart(item.product_id, quantity, variation_id, variation, 0, item.id, item, true);
                            } else {
                                var blocker = $("#postbox-container-1");
                                blocker.block({
                                    message: null,
                                    overlayCSS: {
                                        background: '#fff',
                                        opacity: 0.6
                                    }
                                });

                                $.when(APP.getServerProducts(1, 0, null, [item.product_id])).then(function (response) {
                                    $.each(response, function (i, product) {
                                        if (product.variations.length > 0) {
                                            $.each(product.variations, function (i, variation) {
                                                APP.db.put('variations', {
                                                    id: variation.id,
                                                    prod_id: product.id,
                                                    title: product.name,
                                                    sku: variation.sku
                                                });
                                            });
                                        }
                                    });
                                    APP.db.putAll('products', response).done(function () {
                                        APP.addToCart(item.product_id, quantity, variation_id, variation, 0, item.id, item, true);
                                    });
                                }).done(function () {
                                    blocker.unblock();
                                });
                            }
                        });
                    }
                });

                $.each(order.shipping_lines, function (index, method) {
                    var price = parseFloat(method.total);
                    CART.chosen_shipping_methods = {
                        id: method.method_id,
                        title: method.method_title,
                        price: max(0, price),
                    };
                });
                $.each(order.coupon_lines, function (index, coupon) {
                    var amount = parseFloat(coupon.discount);
                    if (coupon.code.toLowerCase() === 'pos discount') {
                        var is_percent = false,
                            percent = 0,
                            reason = "";

                        $.each(coupon.meta_data, function (id, meta) {
                            if (meta.key === "discount_amount_percent" && !empty(meta.value)) {
                                is_percent = true;
                                percent = meta.value;
                            }
                            if (meta.key === "wc_pos_discount_reason") {
                                reason = meta.value;
                            }
                        });
                        if (is_percent === true) {
                            var amount = parseFloat(percent);
                            CART.add_custom_discount(amount, 'percent', coupon.code, reason);
                        } else {
                            CART.add_custom_discount(amount, 'fixed_cart', coupon.code, reason);
                        }
                    } else {
                        CART.add_discount(coupon.code)
                    }
                });


                CART.customer_note = order.customer_note;
                $('#order_comments').val(order.customer_note);
                if (order.customer_note != '') {
                    openModal('modal-order_comments');
                }

                if (order.customer_id > 0) {
                    APP.setCustomer(order.customer_id);
                } else {
                    APP.setGuest();
                    CUSTOMER.id = 0;
                }
                var arr = ['country', 'first_name', 'last_name', 'company', 'address_1', 'address_2', 'city', 'state', 'postcode', 'email', 'phone'];
                $.each(arr, function (index, key) {
                    if (typeof order.billing[key] != 'undefined') {
                        CUSTOMER.billing_address[key] = order.billing[key];
                    }
                    if (typeof order.shipping[key] != 'undefined') {
                        CUSTOMER.shipping_address[key] = order.shipping[key];
                    }
                });
                if (CUSTOMER.first_name == '' && CUSTOMER.billing_address['first_name'] != '') {
                    CUSTOMER.first_name = CUSTOMER.billing_address['first_name'];
                }
                if (CUSTOMER.last_name == '' && CUSTOMER.billing_address['last_name'] != '') {
                    CUSTOMER.last_name = CUSTOMER.billing_address['last_name'];
                }
                if (CUSTOMER.email == '' && CUSTOMER.billing_address['email'] != '') {
                    CUSTOMER.email = CUSTOMER.billing_address['email'];
                }

                var fullname = [CUSTOMER.first_name, CUSTOMER.last_name];
                fullname = fullname.join(' ').trim();

                if (fullname == '') {
                    fullname = clone(CUSTOMER.username);
                }
                if (fullname != '') {
                    CUSTOMER.fullname = fullname;
                }
                CUSTOMER.points_balance = 0;

                $('#createaccount').prop('checked', false);
                CUSTOMER.create_account = false;

                if (fullname != '' || CUSTOMER.email != '') {
                    var source = $('#tmpl-cart-customer-item').html();
                    var template = Handlebars.compile(source);
                    var html = template(CUSTOMER);
                    $('tbody#customer_items_list').html(html);
                }
                CART.calculate_totals();
                var pos_btns = $('#pos_register_buttons');
                pos_btns.find('.order-remove').remove();
                pos_btns.append('<a class="order-remove page-title-action load-order">Order #' + order_id + '</a>');
                $('.order-remove').on('click', function (e) {
                    e.preventDefault();
                    $('.load-order').remove();
                    CART.empty_cart();
                    delete POS_TRANSIENT.order_id;
                    APP.destroy_refund();
                });
            });
        },
        /**
         * Add a product/variation into IndexedDB and optionally add it to cart.
         *
         * @param {object}  product   The product object {id, vid, variation, quantity}. Only id is mandatory.
         * @param {boolean} addToCart Whether to the product to cart after indexing.
         */
        indexProduct: function (product, addToCart) {
            if (typeof product.id === 'undefined' || product.id === '') {
                return;
            }

            var product_id = product.id;
            var variation_id = (typeof product.vid !== 'undefined' ? product.vid : 0);
            var variation = (typeof product.variation !== 'undefined' ? product.variation : {});
            var quantity = (typeof product.quantity !== 'undefined' ? product.quantity : wc_pos_params.decimal_quantity_value);

            jQuery.when(APP.db.get('products', product_id)).then(function (dbProduct) {
                if (dbProduct === undefined) {
                    jQuery.when(APP.getServerProduct(product_id, 'id')).then(function (serverProduct, status, jqXHR) {
                        if (serverProduct.success) {
                            if (serverProduct.data.parent_id === 0) {
                                APP.db.put('products', serverProduct.data);
                            } else {
                                APP.db.put('variations', {
                                    id: serverProduct.data.id,
                                    prod_id: serverProduct.data.parent_id,
                                    title: serverProduct.data.name,
                                    sku: serverProduct.data.sku
                                });
                            }

                            if (addToCart) {
                                APP.addToCart(product_id, quantity, variation_id, variation);
                            }
                        } else {
                            APP.showNotice(pos_i18n[62], 'error');
                        }
                    });
                } else {
                    if (addToCart) {
                        APP.addToCart(product_id, quantity, variation_id, variation);
                    }
                }
            });
        },
        addToCart: function (product_id, quantity, variation_id, variation, cart_item_data, item_id, item, loaded) {
            if (typeof loaded === 'undefined') {
                loaded = false;
            }
            product_id = parseInt(product_id);
            product_id = wp.hooks.applyFilters('wc_pos_add_to_cart_product_id', product_id);
            var was_added_to_cart = false;

            if (typeof quantity == 'undefined') {
                quantity = wc_pos_params.decimal_quantity_value;
            }
            if (typeof variation_id == 'undefined') {
                variation_id = 0;
            }
            if (typeof variation == 'undefined') {
                variation = {};
            }
            if (typeof cart_item_data != 'object') {
                cart_item_data = {};
            }

            APP.db.get('products', product_id).done(function (record) {
                var adding_to_cart = record;
                if (!adding_to_cart) {
                    return;
                }
                if (typeof item_id != 'undefined') {
                    adding_to_cart.item_id = item_id;
                }
                if (typeof item != 'undefined') {
                    adding_to_cart.loaded_price = item.price;
                    if (record.managing_stock === true && item.stock_reduced) {
                        adding_to_cart.in_stock = true;
                        adding_to_cart.stock_quantity += quantity;
                    }
                    if (typeof item.hidden_fields != 'undefined') {
                        adding_to_cart.hidden_fields = item.hidden_fields;
                    }
                }

                var add_to_cart_handler = wp.hooks.applyFilters('wc_pos_add_to_cart_handler', adding_to_cart.type, adding_to_cart);

                APP.tmp.product_item = {
                    product_id: product_id,
                    adding_to_cart: adding_to_cart,
                    quantity: quantity,
                    variation_id: variation_id,
                    variation: variation,
                    cart_item_data: cart_item_data
                }
                var handler_action_name = 'wc_pos_add_to_cart_handler_' + add_to_cart_handler;

                // Variable product handling
                if ('variable' === add_to_cart_handler || 'variable-subscription' === add_to_cart_handler) {
                    was_added_to_cart = APP.add_to_cart_handler_variable(product_id, adding_to_cart, quantity, variation_id, variation, cart_item_data);
                    // Grouped Products
                    /*} else if ( 'grouped' === add_to_cart_handler ) {
                     was_added_to_cart = APP.add_to_cart_handler_grouped( product_id, adding_to_cart, quantity, variation_id, variation, cart_item_data );
                     */
                    // Custom Handler
                } else if (wp.hooks.hasFilter(handler_action_name)) {
                    was_added_to_cart = wp.hooks.applyFilters(handler_action_name, false, product_id, adding_to_cart, quantity, variation_id, variation, cart_item_data);

                    // Simple Products
                } else {
                    was_added_to_cart = APP.add_to_cart_handler_simple(product_id, adding_to_cart, quantity, variation_id, variation, cart_item_data);
                }
                // If we added the product to the cart we can now optionally do a reset.
                if (was_added_to_cart) {
                    APP.tmp.product_item = {};
                    runTips();
                    //var cart_item_key = CART.addToCart(adding_to_cart, product_id, quantity, variation_id, variation, cart_item_data);
                    var msg = wp.hooks.applyFilters('wc_pos_added_to_cart_message', '', product_id, adding_to_cart, quantity, variation_id, variation, cart_item_data);
                    APP.showNotice(msg, 'basket_addition');
                } else if (loaded) {
                    CART.addToCart(adding_to_cart, product_id, quantity, variation_id, variation, cart_item_data)
                }
            });

            if (wc_pos_params.tabs_management) {
                if (typeof APP.tabs[APP.active_tab] != 'undefined') {
                    APP.tabs[APP.active_tab].setCart('cart_contents', CART.get_cart());
                } else {
                    APP.tabs[APP.active_tab] = new Tab(APP.active_tab);
                }
            }
        },
        add_to_cart_handler_simple: function (product_id, adding_to_cart, quantity, variation_id, variation, cart_item_data) {
            var missing = wp.hooks.applyFilters('wc_pos_validate_missing_attributes', false, adding_to_cart, product_id, quantity, variation_id, variation, cart_item_data);
            if (missing === true) {
                if (window.openwin === false && typeof adding_to_cart.item_id == 'undefined') {
                    openModal('modal-missing-attributes', true);

                    if (adding_to_cart.type !== 'variable') {
                        $('#missing-attributes-select').trigger('found_variation', [{data: adding_to_cart}]);
                    } else {
                        $('#selected-variation-data, #reset_selected_variation').hide();
                    }
                    return false;
                }
            } else if (wc_pos_params.instant_quantity == 'yes' && $('#modal-qt-product').length && window.openwin === false && typeof adding_to_cart.item_id == 'undefined') {
                jQuery('#modal-qt-product .keypad-clear').click();
                openModal('modal-qt-product', true);
                return false;
            }
            window.openwin = false;
            //TODO: Commented by load order variations bug 11.05.17 - filter don't work
            //var passed_validation = wp.hooks.applyFilters('wc_pos_add_to_cart_validation', true, adding_to_cart, product_id, quantity);
            var passed_validation = true;
            var cart_item_key = '';
            if (passed_validation && (cart_item_key = CART.addToCart(adding_to_cart, product_id, quantity, variation_id, variation, cart_item_data))) {
                return true;
            }
            return false;
        },
        add_to_cart_handler_variable: function (product_id, adding_to_cart, quantity, variation_id, selected_attr, cart_item_data) {
            var missing = false;
            var missing_attributes = {};
            var variations = {};
            var attributes = adding_to_cart.attributes;
            var variation = null;
            APP.tmp.product_item.product_variations = [];
            if (typeof selected_attr != 'undefined' && sizeof(selected_attr) > 0) {
                variations = selected_attr;
            } else {
                selected_attr = {};
            }
            $.each(adding_to_cart.variations, function (index, val) {
                var attributes = {};
                $.each(val.attributes, function (i, attr) {
                    var slug = i;
                    attributes[slug] = attr;
                });
                APP.tmp.product_item.product_variations[index] = {attributes: attributes};
                APP.tmp.product_item.product_variations[index]['variation_is_active'] = true;
                APP.tmp.product_item.product_variations[index]['variation_id'] = val.id;
                APP.tmp.product_item.product_variations[index]['data'] = val;

                if (val.id == variation_id) {
                    if (val.attributes) {
                        $.each(val.attributes, function (i, attr) {
                            if (!empty(attr)) {
                                selected_attr[i] = attr;
                            }
                        });
                    }
                    variation = val;
                    return;
                }
            });
            if (typeof adding_to_cart.item_id == 'undefined') {
                $.each(attributes, function (index, attribute) {
                    var taxonomy = !empty(attribute['slug']) ? attribute['slug'] : str_replace(' ', '_', attribute['name']).toLowerCase();
                    if (attribute.variation == true) {
                        if (typeof selected_attr[taxonomy] != 'undefined' && variation != null) {
                            // Get value
                            variations[taxonomy] = selected_attr[taxonomy];
                        } else {
                            missing = true;
                        }
                        missing_attributes[taxonomy] = attribute;
                    }
                });
            }

            missing = wp.hooks.applyFilters('wc_pos_validate_missing_attributes', missing, adding_to_cart, product_id, quantity, variation_id, selected_attr, cart_item_data);
            if (missing === true) {
                var source = $('#tmpl-missing-attributes').html();
                var template = Handlebars.compile(source);
                var html = template({attr: missing_attributes});
                $html = $(html);
                $.each(variations, function (i, opt) {
                    i = i.replace(/[\s!@#$%^&*();:]/g, '');
                    $html.find("select.attribute_" + i).val(opt);
                });

                $('#modal-missing-attributes').addClass('missing-attributes md-close-by-overlay');
                $('#missing-attributes-select').html($html);

                if (window.openwin === false && typeof adding_to_cart.item_id == 'undefined') {
                    $('#selected-variation-data, #reset_selected_variation').hide();
                    openModal('modal-missing-attributes', true);
                    $.each(default_variations[adding_to_cart.id], function (k, v) {
                        var taxonomy = k.replace('pa_', '');
                        $("[data-taxonomy=" + taxonomy + "] [value='" + v + "']").attr('selected', true);
                        $("[data-taxonomy=" + taxonomy + "]").change();
                    });
                }
            } else if (typeof variation_id == 'undefined') {
                APP.showNotice(pos_i18n[2], 'error');
            }
            else {
                /*if (wc_pos_params.instant_quantity == 'yes' && $('#modal-qt-product').length && window.openwin === false && typeof adding_to_cart.item_id == 'undefined') {
                 openModal('modal-qt-product', true);
                 return false;
                 }
                 window.openwin = false;
                 var passed_validation = wp.hooks.applyFilters('wc_pos_add_to_cart_validation', true, adding_to_cart, product_id, quantity, variation_id, variations);
                 var cart_item_key = '';

                 if (passed_validation && (cart_item_key = CART.addToCart(adding_to_cart, product_id, quantity, variation_id, variations, cart_item_data) )) {
                 return true;
                 }*/
                return APP.add_to_cart_handler_simple(product_id, adding_to_cart, quantity, variation_id, variations, cart_item_data);
            }
            return false;
        },
        voidRegister: function (notice) {
            if (typeof POS_TRANSIENT.order_id != 'undefined' && POS_TRANSIENT.order_id > 0) {
                $('#post-body').block({
                    message: null,
                    overlayCSS: {
                        background: '#fff',
                        opacity: 0.6
                    }
                });
                var order_id = POS_TRANSIENT.order_id;
                var register_id = wc_pos_register_id;
                $.ajax({
                    type: 'POST',
                    url: wc_pos_params.ajax_url,
                    data: {
                        action: 'wc_pos_void_register',
                        security: wc_pos_params.void_register_nonce,
                        order_id: order_id,
                        register_id: register_id,
                    },
                    success: function (response) {
                        if (notice !== false) {
                            APP.showNotice(pos_i18n[13]);
                        }
                        CART.empty_cart();
                        delete POS_TRANSIENT.order_id;
                    },

                })
                    .always(function (response) {
                        $('#post-body').unblock();
                    });
            } else {
                CART.empty_cart();
                if (notice !== false) {
                    APP.showNotice(pos_i18n[13]);
                }
                delete POS_TRANSIENT.order_id;
            }
        },
        setCustomer: function (customer, open) {
            open = typeof open === 'undefined' ? false : open;
            var customer_id = is_object(customer) ? parseInt(customer.id) : parseInt(customer);

            if (typeof customer_id === 'undefined' || empty(customer_id)) {
                return;
            }
            customer_id = parseInt(customer_id);

            // Reset current customer if any.
            CUSTOMER.reset();

            // Look for customer in IndexedDB records.
            APP.db.get('customers', customer_id).done(function (_customer) {
                if (typeof _customer === 'undefined') {
                    $.when(APP.getServerCustomer(customer_id)).then(function (c, status, jqxhr) {
                        if (typeof c !== 'undefined') {
                            APP.db.put('customers', c);
                            _customer = c;
                        }

                        _customer = _customer ? _customer : customer;
                        CUSTOMER.set_default_data(_customer);
                        APP.setUpCustomerModal(wc_pos_params.load_customer, true);
                        CART.calculate_totals();
                        if (open == true) {
                            $('a.show_customer_popup').trigger('click');
                        }
                    });
                } else {
                    CUSTOMER.set_default_data(customer);
                    APP.setUpCustomerModal(wc_pos_params.load_customer, true);
                    CART.calculate_totals();
                    if (open == true) {
                        $('a.show_customer_popup').trigger('click');
                    }
                }
            });
        },
        setGuest: function () {
            CUSTOMER.reset();
            CUSTOMER.set_default_data();
            CART.calculate_totals();
            runTips();
            return false;
        },
        searchByTerm: function (term) {
            var _term = term.toLowerCase();
            //var q = APP.db.from( 'products' ).where('sku', '^', term);//.where('title', '^', term, '^', _term)
            /*var q = APP.db.from( 'products' ).where('title', '^', term);
             var limit = 10000;
             var result = [];
             q.list( limit ).done( function( objs ) {
             result = objs;
             });
             var result = [];
             APP.db.count('products').done(function(x) {
             d('Number of authors: ' + x);
             APP.db.from('products').order('sku').list(x).done(function(records) {

             result = $.grep(records, function(e){
             var sku  = e.sku;
             var title = e.title;
             title = title.toLowerCase();
             return title.indexOf(_term) >= 0 || sku.indexOf(_term) >= 0;
             });

             console.log(result);

             });
             });

             return result;*/
        },
        debug: function (msg, type) {
            if ($('#process_loding').length) {
                if (typeof msg == 'string' && msg != '') {
                    if (type == false)
                        $('#process_loding').append('<p>' + msg + '</p>');
                    else
                        $('#process_loding').append('<p>' + msg + '<span class="dot one">.</span><span class="dot two">.</span><span class="dot three">.</span>​</p>');
                }
                $('#process_loding').scrollTop($('#process_loding')[0].scrollHeight);
            }
        },
        setCookie: function (cname, cvalue, exdays) {
            var d = new Date();
            d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
            var expires = "expires=" + d.toGMTString();
            document.cookie = cname + "=" + cvalue + "; " + expires;
        },
        getCookie: function (cname) {
            var name = cname + "=";
            var ca = document.cookie.split(';');
            for (var i = 0; i < ca.length; i++) {
                var c = ca[i];
                while (c.charAt(0) == ' ') c = c.substring(1);
                if (c.indexOf(name) != -1) {
                    return c.substring(name.length, c.length);
                }
            }
            return "";
        },
        checkCookie: function (name) {
            if (typeof name == 'undefined') {
                name = 'pos_lastUpdate';
            }
            var LU = APP.getCookie(name);
            if (LU != "") {
                return LU;
            }
            return 'null';
        },
        checkCookieOffset: function (name) {
            if (typeof name == 'undefined') {
                name = 'pos_LastOffset';
            }
            var LO = APP.getCookie(name);
            if (LO != "") {
                return parseInt(LO);
            }
            return 0;
        },
        checkCookieVersion: function () {
            var v = APP.getCookie("pos_Version");
            var cv = APP.getCookie("pos_ustomerVersion");
            if (v != APP.version) {
                APP.resetCookieVersion();
            } else if (cv != APP.customerVersion) {
                APP.setCookie('pos_LastOffsetCustomer', '', 5);
                APP.setCookie('pos_lastUpdateCustomer', '', 5);
            }
        },
        resetCookieVersion: function () {
            APP.setCookie('pos_LastOffset', '', 5);
            APP.setCookie('pos_lastUpdate', '', 5);
            APP.setCookie('pos_LastOffsetCoupon', '', 5);
            APP.setCookie('pos_lastUpdateCoupon', '', 5);
            APP.setCookie('pos_LastOffsetCustomer', '', 5);
            APP.setCookie('pos_lastUpdateCustomer', '', 5);
        },
        setCookieVersion: function () {
            APP.setCookie("pos_Version", APP.version, 365);
            APP.setCookie("pos_ustomerVersion", APP.customerVersion, 365);
        },
        updateSearchList: function (x) {
            if (x > 0) {
                APP.db.from('products').list(x).done(function (records) {

                    for (i = 0; i < x; i++) {
                        var obj = records[i];
                        APP.insertSearchListItem(obj);
                    }
                });
            }
        },
        insertSearchListItem: function (obj) {
            var val = JSON.stringify({id: obj.id});
            var t = obj.name;
            if (!empty(obj.sku)) {
                t = obj.sku + ' - ' + obj.name;
            }
            APP.tmp.products[obj.id] = {id: val, text: t, post_meta: obj.post_meta};

            if (wc_pos_params.scan_field && !empty(wc_pos_params.scan_field)) {

                $.each(obj.meta, function (meta_key, meta_data) {
                    if (meta_data.key == wc_pos_params.scan_field && !empty(meta_data.value)) {
                        APP.tmp.productscans[meta_data.value] = {id: obj.id};
                    }
                });
            }

            if (obj.variations.length > 0) {
                for (var j = 0; j < obj.variations.length; j++) {
                    var name = t;
                    var v = obj.variations[j];

                    if (v.sku != '') {
                        name = v.sku + ' - ' + obj.name;
                    }
                    var selected_attr = {};

                    $.each(v.attributes, function (k, a) {
                        if (!empty(a)) {
                            selected_attr[k] = a;
                            name += ' - ' + a;
                        }
                    });
                    var val = JSON.stringify({id: obj.id, vid: v.id, variation: selected_attr});
                    APP.tmp.products[v.id] = {id: val, text: name, post_meta: obj.post_meta};

                    if (wc_pos_params.scan_field && !empty(wc_pos_params.scan_field)) {

                        $.each(v.meta_data, function (meta_key, meta_data) {
                            if (meta_data.key == wc_pos_params.scan_field && !empty(meta_data.value)) {
                                APP.tmp.productscans[meta_data.value] = {id: obj.id, vid: v.id};
                            }
                        });
                    }

                }
            }
        },
        _formatLastUpdateFilter: function (name) {
            if (typeof name == 'undefined') {
                name = 'pos_lastUpdate';
            }
            var t = new Date();
            if (t.getTime() > 0) {
                var r = t.getUTCFullYear(),
                    i = t.getUTCMonth() + 1,
                    s = t.getUTCDate(),
                    o = t.getUTCHours(),
                    u = t.getUTCMinutes(),
                    a = t.getUTCSeconds();
                var dd = r + "-" + i + "-" + s + "T" + o + ":" + u + ":" + a + "Z";
                APP.setCookie(name, dd, 5);
                return dd;
            }
            $('#last_sync_time').html('');
            return null
        },
        showNotice: function (msg, type) {
            if (typeof type == 'undefined') {
                type = 'success';
            }
            if (typeof msg != 'undefined') {
                switch (type) {
                    case 'error':
                        toastr.error(msg);
                        if (!wc_pos_params.disable_sound_notifications) {
                            ion.sound.play("error");
                        }
                        break;
                    case 'success':
                        toastr.success(msg);
                        if (!wc_pos_params.disable_sound_notifications) {
                            ion.sound.play("succesful_order");
                        }
                        break;
                    case 'info':
                        toastr.info(msg);
                        if (!wc_pos_params.disable_sound_notifications) {
                            ion.sound.play("succesful_order");
                        }
                        break;
                    case 'basket_addition':
                        if (msg == '') {
                            msg = pos_i18n[6];
                        }
                        toastr.info(msg);
                        if (!wc_pos_params.disable_sound_notifications) {
                            ion.sound.play("basket_addition");
                        }
                        break;
                    case 'succesful_order':
                        toastr.success(msg);
                        if (!wc_pos_params.disable_sound_notifications) {
                            ion.sound.play("succesful_order");
                        }
                        break;
                }
            }
        },
        addGrid: function (offset) {
            if (typeof offset === "undefined") {
                offset = 0;
            }
            if (pos_grid.second_column_layout == 'product_grids') {
                if (pos_grid.grid_id == 'categories') {
                    var cats = Object.keys(pos_grid.categories).slice(offset, offset + 15);
                    $.each(cats, function (i, cat) {
                        if (typeof pos_grid.categories[cat] === "undefined") {
                            return true;
                        }
                        cat = pos_grid.categories[cat];
                        if(pos_grid.auto_tiles){
                            cat.parent = 0;
                        }
                        var $li = $('<li id="category_' + cat.term_id + '" class="title_category open_category category_cycle" data-catid="' + cat.term_id + '" data-parent="' + cat.parent + '" data-title="' + cat.name + '"><span></span></li>');
                        $li.data('title', cat.name).addClass('loading-cat');

                        if (pos_grid.grid_id == "categories" && layout.data('parent') !== 0) {
                            $li.hide();
                        }
                        product_list.append($li);
                        if (cat.parent == 0) {
                            APP.lastOffset[cat.term_id] = 0;
                            if (!in_array(cat.term_id, APP.category_que)) {
                                APP.category_que.push(cat.term_id);
                            }
                        } else {
                            $li.css({
                                'background-image': 'url(' + cat.image + ')'
                            }).removeClass('loading-cat');
                            $li.find('span').html(cat.name);
                        }
                        if (Object.keys(pos_grid.categories).length >= (cats.length + offset)) {
                            if ((cats.length - 1) == i) {
                                APP.addGrid(cats.length + offset);
                            }
                        }
                    });
                    APP.process_que();
                }
                resizeGrid();
            } else {
                layout.unblock();
            }
        },
        find_matching_variations: function (product_variations, settings) {
            var matching = [];
            $.each(product_variations, function (i, val) {
                var variation = val;
                if (APP.variations_match(variation.attributes, settings)) {
                    matching.push(variation);
                }
            });
            return matching;
        },
        variations_match: function (attrs1, attrs2) {
            var match = true;
            for (var attr_name in attrs1) {
                if (attrs1.hasOwnProperty(attr_name)) {
                    var val1 = attrs1[attr_name];
                    var val2 = attrs2[attr_name];

                    if (val1 !== undefined && val2 !== undefined && val1.length !== 0 && val2.length !== 0 && val1 != val2) {
                        match = false;
                    }
                }
            }

            return match;
        },
        paymentProcessBeforeCheckout: function () {
            var selectedPM = $('.payment_methods.active .select_payment_method:not(:disabled)').val(),
                deferredObject = $.Deferred();
            if (selectedPM !== "wc_pos_paymentsense") {
                deferredObject.reject({
                    showPaymentMethods: false
                });
                return deferredObject.promise();
            }

            $('#modal-order_payment, #post-body').block({
                message: '<div class="payment_sense_box">Connecting to EMV terminal. Please follow instructions on terminal.<button class="button" id="cancel-pm-process">Cancel</button></div>',
                overlayCSS: {
                    background: '#fff',
                    opacity: 0.6
                }
            });

            $.ajax({
                url: wc_pos_params.payment_sense_url + '/pac/terminals/' + pos_register_data.paymentsense_terminal + '/transactions',
                type: "POST",
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/connect.v1+json',
                    'Authorization': 'Basic ' + btoa('user :' + wc_pos_params.payment_sense_api_key),
                },
                data: JSON.stringify({
                    transactionType: "SALE",
                    amount: CART.total * 100,
                    currency: "GBP"
                }),
                success: function (response) {
                    if (!response.requestId) {
                        response["showPaymentMethods"] = true;
                        deferredObject.reject(response);
                    } else {
                        $.when(APP.getRequestData(response)).then(function (result) {
                            deferredObject.resolve(result);
                        }, function (error) {
                            deferredObject.reject(error);
                        });
                    }
                },
                error: function (error) {
                    error["showPaymentMethods"] = true;
                    deferredObject.reject(error);
                }
            });

            return deferredObject.promise();
        },
        processRefundAmountBeforeRefunds: function () {
            var refundOrder = POS_CART.pos_refund.order,
                deferredObject = $.Deferred();
            if (!refundOrder || empty(wc_pos_params.payment_sense_url) || refundOrder.payment_method !== "wc_pos_paymentsense") {
                deferredObject.reject();
                return deferredObject.promise();
            }

            $('#modal-order_payment, #post-body').block({
                message: '<div class="payment_sense_box">Connecting to EMV processor. Please follow instructions on terminal.<button class="button" id="cancel-pm-process">Cancel</button></div>',
                overlayCSS: {
                    background: '#fff',
                    opacity: 0.6
                }
            });

            $.ajax({
                url: wc_pos_params.payment_sense_url + '/pac/terminals/' + pos_register_data.paymentsense_terminal + '/transactions',
                type: "POST",
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/connect.v1+json',
                    'Authorization': 'Basic ' + btoa('user :' + wc_pos_params.payment_sense_api_key),
                },
                data: JSON.stringify({
                    transactionType: "REFUND",
                    amount: Math.abs(CART.total) * 100,
                    currency: "GBP"
                }),
                success: function (response) {
                    if (!response.requestId) {
                        response["showPaymentMethods"] = true;
                        deferredObject.reject(response);
                    } else {
                        $.when(APP.getRequestData(response)).then(function (result) {
                            deferredObject.resolve(result);
                        }, function (error) {
                            deferredObject.reject(error);
                        });
                    }
                },
                error: function (error) {
                    deferredObject.reject(error);
                }
            });

            return deferredObject.promise();
        },
        getRequestData: function (result) {

            var transactionCallback = function () {
                var request = $.ajax({
                    url: wc_pos_params.payment_sense_url + '/pac/terminals/' + pos_register_data.paymentsense_terminal + '/transactions/' + result.requestId,
                    type: "GET",
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/connect.v1+json',
                        'Authorization': 'Basic ' + btoa('user :' + wc_pos_params.payment_sense_api_key),
                    }
                });

                request.then(function (response) {
                    if (!response.transactionResult) {
                        var notifications = response.notifications ? response.notifications : [];

                        if (in_array('SIGNATURE_VERIFICATION', response.notifications) && 'SIGNATURE_VERIFICATION' === response.notifications[0] && paymentSenseSignature === false) {
                            paymentSenseSignature = true;
                            var confirmed = $.Deferred(),
                                signatureAccepted = false;
                            openConfirm({
                                content: '<h3>Is signature verification success?</h3><strong>NOTE:</strong> Signature verification will be gone through after 80 seconds of inactivity',
                                confirm: function () {
                                    signatureAccepted = true;
                                    confirmed.resolve(signatureAccepted);
                                },
                                cancel: function () {
                                    confirmed.resolve(signatureAccepted);
                                },
                                notSign: true
                            });
                            $.when(confirmed).then(function (signature) {
                                APP.updateRequest(result.requestId, signature).then(function (signResponse) {

                                }, function (error) {
                                    var message = JSON.parse(error.responseText);
                                    toastr.options.timeOut = 5000;
                                    if (message.messages) {
                                        APP.showNotice(message.messages.error[0], 'error');
                                    } else {
                                        APP.showNotice(error.responseText, "error");
                                    }
                                    toastr.options.timeOut = 2000;
                                }).always(function (responseData) {
                                    paymentSenseTimeout = setTimeout(function () {
                                        APP.getRequestData(result);
                                    }, 1000);
                                });
                            });
                        }
                        paymentSenseTimeout = setTimeout(function () {
                            APP.getRequestData(result);
                        }, 1000);
                        APP.showPSNotification(notifications);
                    } else if (paymentSenseTimeout) {
                        clearTimeout(paymentSenseTimeout);
                        if ($("#modal-confirm-box").hasClass("md-show")) {
                            toastr.options.timeOut = 5000;
                            APP.showNotice("Signature verification succeed automatically due to timeout", "error");
                            closeModal("modal-confirm-box");
                            toastr.options.timeOut = 2000;
                        }
                        paymentSenseDeferred.resolve(response);
                    }
                });

                request.fail(function (error) {
                    error["showPaymentMethods"] = true;
                    paymentSenseDeferred.reject(error);
                });
            };

            if (!paymentSenseTransaction) {
                var cancelRequest = $.ajax({
                    url: wc_pos_params.payment_sense_url + '/pac/terminals/' + pos_register_data.paymentsense_terminal + '/transactions/' + result.requestId,
                    type: "DELETE",
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/connect.v1+json',
                        'Authorization': 'Basic ' + btoa('user :' + wc_pos_params.payment_sense_api_key),
                    },
                });
                cancelRequest.then(function (success) {
                    paymentSenseDeferred.reject({
                        status: 2,
                        showPaymentMethods: true,
                        message: "Transaction cancelled."
                    });
                    clearTimeout(paymentSenseTimeout);
                    return paymentSenseDeferred.promise();
                });
                cancelRequest.fail(function (err) {
                    if (err.responseText) {
                        var message = JSON.parse(err.responseText);
                        APP.showNotice(message.messages.error[0], 'error');
                    }
                    paymentSenseTransaction = true;
                    transactionCallback();
                })
            } else {
                transactionCallback();
            }

            return paymentSenseDeferred.promise();
        },
        updateRequest: function (requestId, accepted) {
            accepted = typeof accepted === "undefined" ? false : accepted;
            return $.ajax({
                url: wc_pos_params.payment_sense_url + '/pac/terminals/' + pos_register_data.paymentsense_terminal + '/transactions/' + requestId + '/signature',
                type: "PUT",
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/connect.v1+json',
                    'Authorization': 'Basic ' + btoa('user :' + wc_pos_params.payment_sense_api_key),
                },
                data: JSON.stringify({
                    "accepted": accepted
                })
            });
        },
        showPSNotification: function (notifications) {
            var yetToNotice = array_diff(notifications, paymentSenseNotifications);
            if (yetToNotice.length > 0) {
                toastr.remove();
                APP.showNotice(str_replace('_', ' ', yetToNotice[0]), 'info');
                paymentSenseNotifications.push(yetToNotice[0]);
                setTimeout(function () {
                    APP.showPSNotification(notifications);
                }, 300);
            }
        },
        createOrder: function (paid, paymentSense) {
            if (typeof paid === 'undefined') {
                paid = false;
            }

            if (typeof paymentSense === "undefined") {
                paymentSense = {};
            }

            var cart_contents = CART.cart_contents;

            var line_items = [];
            $.each(cart_contents, function (index, item) {
                var _item = {};
                _item.id = typeof(item.item_id) != 'undefined' ? item.item_id : 0;
                _item.product_id = item.product_id;
                _item.price = item.price;
                _item.variation_id = item.variation_id;
                _item.quantity = item.quantity;
                _item.variations = item.variation;
                _item.total = item.line_total.toString();
                _item.subtotal = item.line_subtotal.toString();
                _item.name = typeof item.data.title === 'undefined' ? item.data.name : item.data.title;
                _item.tax_class = item.data.tax_class;
                _item.tax_status = item.data.tax_status;

                if (typeof item.data.hidden_fields != 'undefined') {
                    _item.hidden_fields = item.data.hidden_fields;
                }

                if (item.variation_id > 0) {
                    _item.tax_class = item.v_data.tax_class;
                    _item.tax_status = item.v_data.tax_status;
                }
                if (_item.tax_status == 'none') {
                    _item.tax_class = '0';
                } else {
                    _item.subtotal_tax = item.line_subtotal_tax ? item.line_subtotal_tax.toString() : "0";
                    _item.taxes = item.taxes;
                    _item.total_tax = item.line_tax ? item.line_tax.toString() : "0";
                }
                if (!$.isEmptyObject(item.variation)) {
                    var attribute_summary = {};
                    _item.meta_data = [];

                    if(item.v_data){
                        var summary = item.v_data.attribute_summary.split(",");
                        $.each(summary, function(i, attr){
                            var attr_pair = attr.split(":");
                            if(attr_pair.length === 2){
                                attribute_summary[attr_pair[0].trim()] = attr_pair[1].trim();
                            }
                        });
                    }

                    $.each(item.variation, function (k, v) {

                        if(pos_custom_product.id !== item.product_id && typeof attribute_summary[k] !== "undefined"){
                            return true;
                        }

                        _item.meta_data.push({
                            key: k,
                            value: v,
                        });
                    });
                }
                line_items.push(_item);
            });
            var cart = {
                "action": 'create',
                "status": paid ? wc_pos_params.complete_order_status : wc_pos_params.save_order_status,
                "line_items": line_items,
                "customer_note": CART.customer_note,
                "billing": CUSTOMER.billing_address.first_name != '' ? CUSTOMER.billing_address : {},
                "shipping": CUSTOMER.shipping_address.first_name != '' ? CUSTOMER.shipping_address : {},
                "additional_fields": CUSTOMER.additional_fields,
                "customer_id": CUSTOMER.id,
                "create_account": CUSTOMER.create_account,
                "user_meta": CUSTOMER.acf_fields,
                "custom_order_meta": CUSTOMER.custom_order_fields,
                "fee_lines": CART.fees.map(function (fee, i) {
                    fee.total = fee.total.toString();
                    return fee;
                }),
                "meta_data": [
                    {
                        "key": "wc_pos_order_saved",
                        "value": false
                    },
                    {
                        "key": "wc_pos_amount_change",
                        "value": ""
                    },
                    {
                        "key": "wc_pos_amount_pay",
                        "value": ""
                    },
                    {
                        "key": "wc_pos_id_register",
                        "value": wc_pos_register_id
                    },
                    {
                        "key": "wc_pos_order_tax_number",
                        "value": ""
                    },
                    {
                        "key": "wc_pos_order_type",
                        "value": "POS"
                    },
                    {
                        "key": "wc_pos_dining_option",
                        "value": wc_pos_dining_option
                    },
                    {
                        "key": "wc_pos_signature",
                        "value": APP.signature[1]
                    },
                    {
                        "key": "wc_pos_prefix_suffix_order_number",
                        "value": pos_register_data.prefix + String(pos_register_data.order_id) + pos_register_data.suffix
                    },
                ]
            };

            if (!$.isEmptyObject(paymentSense)) {
                cart.paymentSense = paymentSense;
            }

            if ($("#payment_switch").bootstrapSwitch('state')) {
                cart.print_receipt = true;
            }

            $.each(CUSTOMER.additional_fields, function (index, val) {
                cart.meta_data.push({
                    "key": index,
                    "value": val
                });
            });
            if (wc_pos_params.tabs_management) {
                cart.meta_data.push({
                    "key": "order_tab",
                    "value": jQuery('.woocommerce_order_items_wrapper .tab.active').data('tab_number')
                });
            }
            var acf_order_fields = wc_pos_params.acf_order_fields;
            $.each(wc_pos_params.acf_order_fields, function (index, key) {
                $el = $('#customer_details #pos_order_fields :input[id^="acf-field-' + key + '"]');
                if ($el.length) {
                    var _val;
                    if ($el.first().is(':checkbox')) {
                        $el.filter(':checked').each(function (index, el) {
                            _val = [];
                            _val.push($(el).val());
                        });
                    } else if ($el.first().is(':radio')) {
                        _val = $el.filter(':checked').val();
                    } else {
                        _val = $el.val();
                    }
                    cart.custom_order_meta[key] = _val;
                }
            });

            if (typeof POS_TRANSIENT.order_id != 'undefined' && POS_TRANSIENT.order_id > 0) {
                cart.action = 'update';
                cart.meta_data.push({
                    "key": "wc_pos_prefix_suffix_order_number",
                    "value": pos_register_data.prefix + String(POS_TRANSIENT.order_id) + pos_register_data.suffix
                });
            }
            if (paid) {
                var selected_pm = $('.select_payment_method:checked:not(:disabled)').val();
                var selected_pm_t = $('a.payment_method_' + selected_pm).text();
                cart.payment_method = selected_pm;
                cart.payment_method_title = selected_pm_t.trim();
                cart.set_paid = paid;
                if (selected_pm == 'cod') {
                    if (wc_pos_params.wc_pos_rounding) {
                        cart.meta_data.push(
                            {
                                "key": "wc_pos_order_rounding",
                                "value": "yes"
                            },
                            {
                                "key": "wc_pos_rounding_total",
                                "value": CART.total
                            }
                        );
                    }
                    $.each(cart.meta_data, function (index, meta_data) {
                        if (meta_data.key === "wc_pos_amount_change") {
                            cart.meta_data[index].value = $('#amount_change_cod').val()
                        }
                        if (meta_data.key === "wc_pos_amount_pay") {
                            cart.meta_data[index].value = $('#amount_pay_cod').val()
                        }
                    });
                }
                cart.meta_data.push(
                    {
                        "key": "wc_pos_dining_option",
                        "value": wc_pos_dining_option
                    },
                    {
                        "key": "wc_pos_signature",
                        "value": APP.signature[1]
                    },
                );
            } else {
                cart.meta_data.push({
                    "key": "wc_pos_order_saved",
                    "value": true
                });
            }
            ;
            //shipping_lines
            if (CART.needs_shipping()) {
                var taxes = [];
                $.each(CART.shipping_taxes, function (i, tax) {
                    taxes.push({
                        id: i,
                        total: tax.toFixed(2)
                    });
                });
                cart.shipping_lines = [{
                    method_id: CART.chosen_shipping_methods.id,
                    method_title: CART.chosen_shipping_methods.title,
                    total: CART.shipping_total.toFixed(2),
                    taxes: taxes,
                }];
            }
            if (Object.size(CART.applied_coupons) > 0) {
                cart.coupon_lines = [];
                $.each(CART.applied_coupons, function (index, coupon_code) {
                    var amount = CART.get_coupon_discount_amount(coupon_code, (!pos_wc.prices_include_tax));
                    if (amount) {
                        var c_data = {'amount': amount, 'code': coupon_code};
                        var coupon = CART.coupons[coupon_code];
                        c_data.reason = coupon.data.text ? coupon.data.text : "none";
                        if (coupon_code.toLowerCase() == 'pos discount') {
                            var type = coupon.data.type;
                            if (type == 'percent') {
                                c_data.type = type;
                                c_data.pamount = coupon.data.amount;
                            }
                        }
                        cart.coupon_lines.push(c_data);
                    }
                });
                if (sizeof(cart.coupon_lines) == 0) {
                    delete cart.coupon_lines;
                }
                ;
            }

            $('#modal-order_payment, #post-body').block({
                message: null,
                overlayCSS: {
                    background: '#fff',
                    opacity: 0.6
                }
            });

            var selected_pm = $('.select_payment_method:checked:not(:disabled)').val();
            if (paid) {
                $.when(wp.hooks.applyFilters('wc_pos_process_payment', cart, selected_pm)).then(function (cart) {
                    if (cart) {
                        if (APP.isOnline()) {
                            APP.processPayment(cart, true);
                        } else {
                            cart.id = Date.now();
                            var order_number = Math.floor(Math.random() * 900000) + 100000;
                            cart.meta_data.push({
                                "key": "wc_pos_prefix_suffix_order_number",
                                "value": pos_register_data.prefix + order_number + pos_register_data.suffix + '-OFFLINE'
                            });
                            APP.db.put('offline_orders', cart).fail(function (e) {
                                throw e;
                            });
                            closeModal('modal-order_payment');
                            APP.showNotice(pos_i18n[43], 'succesful_order');
                            CART.empty_cart();
                            jQuery('.blockOverlay').css('display', 'none');
                        }
                    }
                });
            } else {
                APP.processPayment(cart, false, pos_register_data.settings.note_request, pos_register_data.settings.print_receipt);
            }
            if (wc_pos_params.tabs_management) {
                APP.tabs[APP.active_tab].clear();
            }
        },
        create_refund: function () {
            $('#modal-order_payment, #post-body').block({
                message: null,
                overlayCSS: {
                    background: '#fff',
                    opacity: 0.6
                }
            });

            var wc_api_url = wc_pos_params.wc_api_url + 'orders/' + CART.pos_refund.order.id + "/refunds";
            delete CART.pos_refund.order.meta_data;

            var line_items = {},
                total = 0;
            $.each(CART.pos_refund.items, function (index, item) {
                var id = item.id,
                    item_total = (parseFloat(item.total) / item.quantity) * item.qty;

                line_items[id] = {};
                line_items[id]["refund_tax"] = {};
                line_items[id]["qty"] = Math.abs(item.qty);
                line_items[id]["refund_total"] = item_total;

                $.each(item.taxes, function (i, tax) {
                    var tax_val = (parseFloat(tax.total) / item.quantity) * item.qty;
                    line_items[id]["refund_tax"][tax.id] = tax_val;
                    total += tax_val;
                });

                total += item_total;
            });

            $.ajax({
                url: wc_api_url,
                type: 'POST',
                dataType: 'json',
                contentType: 'application/json',
                processData: false,
                data: JSON.stringify({
                    amount: parseFloat(total).toFixed(2),
                    api_refund: false,
                    line_items: line_items
                }),
                success: function (response) {
                    if (!response) {
                        APP.showNotice("There is an error while refunding order.", "error");
                    } else {
                        APP.showNotice("Order refund successful", "success");
                        openConfirm({
                            content: '<div><h3>Do you want to print refund receipt?</h3></div>'
                        });
                        var modalConfirm = $("#modal-confirm-box-content");
                        modalConfirm.on("click", "#confirm-button", function () {
                            closeModal("modal-confirm-box");
                            posPrintReceipt(response.refund_receipt);
                            modalConfirm.off("click", "#confirm-button");
                        });
                    }
                },
                error: function (response) {
                    if (response.responseJSON.errors && response.responseJSON.errors.length) {
                        APP.showNotice(response.responseJSON.errors[0].message, "error");
                        return
                    }
                    APP.showNotice("There is an error while refunding order.", "error");
                }
            }).always(function () {
                APP.db.remove("orders", CART.pos_refund.order.id);
                APP.destroy_refund();
                $('.blockUI').remove();
            });

        },
        destroy_refund: function () {
            CART.pos_refund.items = [];
            CART.pos_refund.order = null;
            CART.pos_refund.refund = false;
            CART.empty_cart();
            delete POS_TRANSIENT.order_id;
            APP.setup_refund_elements(true);
        },
        processPayment: function (cart, paid, show_message, print_receipt) {
            show_message = (typeof show_message !== 'undefined' && show_message !== 0) ? show_message : true;
            print_receipt = (typeof print_receipt !== 'undefined' && print_receipt !== 0) ? print_receipt : true;
            var v = APP.makeid();
            var wc_api_url = wc_pos_params.wc_api_url + 'pos_orders/';
            if (typeof POS_TRANSIENT.order_id != 'undefined' && POS_TRANSIENT.order_id > 0) {
                wc_api_url += POS_TRANSIENT.order_id;
            } else {
                wc_api_url += pos_register_data.order_id;
            }
            wc_api_url += '/?v=' + v;
            APP.processing_payment = true;
            $.ajax({
                url: wc_api_url + '/',
                type: 'POST',
                dataType: 'json',
                contentType: 'application/json',
                processData: false,
                data: JSON.stringify(cart),
                success: function (data) {
                    var success = true;
                    var receiptPrinter = pos_register_data.receipt_printer;
                    if (paid) {
                        if (typeof data != 'undefined' && typeof data['payment_result'] != 'undefined' && typeof data['payment_result']['result'] != 'undefined') {
                            if (data['payment_result']['result'] == 'error') {
                                var message = data['payment_result']['message'] != "undefined" ? data['payment_result']['message'] : data['payment_result']['messages'];
                                APP.showNotice(message, 'error');
                                success = false;
                            } else {
                                if (typeof data['payment_result']['redirect'] != 'undefined' && data['payment_result']['redirect'] != '') {
                                    $('#modal-redirect-payment #payment_result_message').html(data['payment_result']['messages']);
                                    if (typeof data['payment_result']['is_need_barcode'] != 'undefined' && data['payment_result']['is_need_barcode'] != '') {
                                        openModal('modal-redirect-payment');
                                    } else {
                                        openModal('modal-redirect-payment');
                                        setTimeout(function () {
                                            window.location.href = data['payment_result']['redirect'];
                                        }, 1000);
                                    }
                                } else {
                                    //APP.showNotice( data['payment_result']['messages'], 'success');
                                    if ($('#payment_switch').bootstrapSwitch('state')) {
                                        if (print_receipt != 0 && (!isValidMac(receiptPrinter) || data.print_type === "html")) {
                                            posPrintReceipt(data.print_url, $('#payment_print_gift_receipt').bootstrapSwitch('state'));
                                        }
                                    } else {
                                        if (change_user) {
                                            APP.user_logout();
                                        }
                                        wp.heartbeat.connectNow();
                                    }
                                }
                            }
                        } else if ($('#payment_switch').bootstrapSwitch('state')) {
                            if (print_receipt != 0 && (!isValidMac(receiptPrinter) || data.print_type === "html")) {
                                posPrintReceipt(data.print_url, $('#payment_print_gift_receipt').bootstrapSwitch('state'));
                            }
                        } else {
                            if (change_user) {
                                APP.user_logout();
                            }
                            wp.heartbeat.connectNow();
                        }
                        //Delete tab data if order have same ID like saved
                        if (wc_pos_params.tabs_management) {
                            if (jQuery('.woocommerce_order_items_wrapper .tab.active').data('order_id') == data.id) {
                                let tab_data = {
                                    action: 'wc_pos_delete_tab',
                                    order_id: data.id
                                };
                                jQuery.ajax({
                                    type: 'POST',
                                    url: wc_pos_params.ajax_url,
                                    data: tab_data,
                                    success: function (responce) {
                                        removeSavedTab(tab_data);
                                    }
                                });
                            }
                        }
                        //$('#payment_switch').bootstrapSwitch('state', print_receipt);
                        //$('#payment_email_receipt').bootstrapSwitch('state', email_receipt);
                    } else {
                        //Save tab data if tabs active
                        if (wc_pos_params.tabs_management) {
                            let active_box = jQuery('.woocommerce_order_items_wrapper .tab.active');
                            let title = jQuery('div.tab-tabs .tab.active').text();
                            let tab_number = active_box.data('tab_number');
                            var tab_data = {
                                action: 'wc_pos_save_tab',
                                title: title,
                                limit: active_box.data('spend_limit'),
                                tab_id: active_box.data('tab_id'),
                                reg_id: pos_register_data.ID,
                                order_id: data.id,
                                tab_number: tab_number,
                                seconds: jQuery('.box-tab[data-tab_number="' + tab_number + '"] .tab-timer').data('seconds')
                            };
                            jQuery.ajax({
                                type: 'POST',
                                url: wc_pos_params.ajax_url,
                                data: tab_data,
                                success: function (responce) {

                                }
                            });
                            //Update saved order data
                            let opt = {reg_id: 'all'};
                            $.when(APP.getServerOrders(opt)).then(function (ordersData) {
                                APP.db.putAll('orders', ordersData.orders).fail(function (e) {
                                    throw e;
                                });
                            });
                            //addSavedTab(tab_data);
                        }
                        if (print_receipt != 0 && (!isValidMac(receiptPrinter) || data.print_type === "html")) {
                            posPrintReceipt(data.print_url);
                        }
                    }
                    if (success) {
                        if (show_message) {
                            if (paid) {
                                APP.showNotice(pos_i18n[12], 'succesful_order');
                            } else {
                                APP.showNotice(pos_i18n[14], 'succesful_order');
                            }
                        }
                        if (wc_pos_params.tabs_management) {
                            let saved = false;
                            if (tab_data) {
                                addSavedTab(tab_data);
                                saved = true;
                            }
                            closeActiveTab(saved);
                        }
                        CART.empty_cart();
                        closeModal('modal-order_payment');
                        $('.load-order').remove();

                        $('#discount-reason').prop('selectedIndex', 0);
                        $('#createaccount').attr('checked', false);
                        $('.keypad-discount_val2').html('');
                        $('.keypad-discount_val1').html('');
                        $('#fee-name').val('');
                        $('.keypad-fee_val').html('');

                        var $row = $('.fee-tr');
                        var fee = $row.data('fee');
                        if (CART.remove_fee(fee)) {
                            $row.remove();
                        }

                        if (typeof data.new_order != 'undefined') {
                            pos_register_data.order_id = data.new_order;
                        }
                        delete POS_TRANSIENT.order_id;
                        ADDONS.crlearCardfields();
                        APP.processing_payment = false;
                        APP.sync_data(true);

                        if (pos_default_customer > 0) {
                            APP.setCustomer(pos_default_customer);
                        } else {
                            APP.setGuest();
                        }

                        $.each(data.line_items, function(i, line_item){
                            APP.db.get('products', line_item.product_id).then(function (product) {
                                if(product && product.manage_stock === true){
                                    product.stock_quantity = product.stock_quantity - line_item.quantity;
                                    APP.db.put('products', product).done(function () {
                                        if (product.backorders_allowed === false && product.stock_quantity < 1) {
                                            $("#product_" + product.id).remove();
                                        }
                                    });
                                }
                            });
                        });
                    }
                },
                error: function (data) {
                    if(typeof data.message != "undefined"){
                        APP.showNotice(data.message, "error");
                        return;
                    }

                    if(typeof data.responseJSON != "undefined" && typeof data.responseJSON.message != "undefined"){
                        APP.showNotice(data.responseJSON.message, "error");
                        return;
                    }

                    var data = $.parseJSON(data.responseText);
                    if (typeof data.errors != 'undefined') {
                        $.each(data.errors, function (index, val) {
                            APP.showNotice(val.message, 'error');
                        });
                    } else {
                        APP.showNotice("An error occured.", "error");
                    }
                }
            }).always(function (response) {
                APP.processing_payment = false;
                $('#modal-order_payment, #post-body, form.woocommerce-checkout').unblock();
                jQuery(document.body).trigger('updated_checkout');
                $('.dining-option-selector').removeClass('checked');
                $('.dining-option-selector[data-option=' + wc_pos_dining_option_default + ']').addClass('checked');
                $('.selected-dining').html(wc_pos_dining_option_default);
                wc_pos_dining_option = wc_pos_dining_option_default;

            });
            POS_TRANSIENT.save_order = false;

        },
        getOrdersListContent: function (opt) {
            $('#retrieve-sales-wrapper .box_content').hide();
            $('#retrieve_sales_popup_inner').html('');
            $('#modal-retrieve_sales .wrap-button').html('');
            $('#retrieve-sales-wrapper').block({
                message: null,
                overlayCSS: {
                    background: '#fff',
                    opacity: 0.6
                }
            });
            $.when(APP.getServerOrders(opt)).then(function (ordersData) {
                APP.db.putAll('orders', ordersData.orders).fail(function (e) {
                    throw e;
                });
                var pager = getOrdersListPager(opt);
                $('#modal-retrieve_sales .wrap-button').html(pager);

                var source = $('#tmpl-retrieve-sales-orders-list').html();
                var template = Handlebars.compile(source);
                var html = template(ordersData.orders);

                $('#retrieve-sales-wrapper .box_content').css('visibility', 'hidden').show();
                $('#retrieve_sales_popup_inner').html(html);

                var table_h = $('#retrieve_sales_popup_inner table').height();
                var wrapper_h = $('#retrieve-sales-wrapper .box_content').height();
                var nav_h = $('#retrieve-sales-wrapper .tablenav_wrap_top').height();

                if (table_h > (wrapper_h - nav_h)) {
                    $('#retrieve-sales-wrapper').addClass('big-size');
                } else {
                    $('#retrieve-sales-wrapper').removeClass('big-size');
                }
                $('#retrieve-sales-wrapper .box_content').removeAttr('style');
                runTips();
                $('#retrieve-sales-wrapper').unblock();
            });
            return false;
        },
        checkStock: function (product_data, quantity, cart_item_key) {
            try {
                var product_id = typeof product_data.variation_id != 'undefined' ? parseInt(product_data.variation_id) : parseInt(product_data.product_id);

                if (product_data.stock_status != "instock" && product_data.backorders_allowed === false) {
                    throw new Error(sprintf(pos_i18n[3], product_data.title));
                }
                if (CART.has_enough_stock(product_data, quantity) === false) {
                    throw new Error(sprintf(pos_i18n[4], product_data.name, product_data.stock_quantity));
                }
                // Stock check - this time accounting for whats already in-cart
                if (product_data.managing_stock === true) {
                    var managing_stock = product_data.managing_stock;
                    var products_qty_in_cart = CART.get_cart_item_quantities();
                    var check_qty = typeof products_qty_in_cart[product_id] != 'undefined' ? products_qty_in_cart[product_id] : 0;

                    /**
                     * Check stock based on all items in the cart
                     */
                    if (CART.has_enough_stock(product_data, check_qty + quantity) === false) {
                        throw new Error(sprintf(pos_i18n[5], product_data.stock_quantity, check_qty));
                    }

                    if (product_data.stock_quantity < check_qty + quantity && product_data.backorders_allowed) {
                        if (cart_item_key != 'undefined') {
                            var view = $('tr#' + cart_item_key + ' td.name .view');
                            if (!view.find('.backorders_allowed').length) {
                                view.append('<span class="register_stock_indicator backorders_allowed">' + pos_i18n[40] + ' </span>');
                            }
                        }
                    } else if (cart_item_key != 'undefined') {
                        $('tr#' + cart_item_key + ' td.name .view .backorders_allowed').remove();
                    }
                }
                return true;
            } catch (e) {
                console.log(e);
                APP.showNotice(e.message, 'error');
                return false;
            }
        },
        makeid: function () {
            var text = "";
            var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

            for (var i = 0; i < 5; i++)
                text += possible.charAt(Math.floor(Math.random() * possible.length));

            return text;
        },
        add_customer_item_to_result: function (customer) {
            var source = $('#tmpl-search-customer-result').html();
            var template = Handlebars.compile(source);
            var html = template(customer);
            $('#customer_search_result').append(html);
        },
        search_customer: function (query) {
            $('#customer_search_result').html('');
            var term = query.term;
            var _term = POS_TRANSIENT.searching_term = term.toLowerCase();
            var data = {results: []};
            var result = [];
            var chk = {};

            $.each(store.customers, function (i, customer) {

                if (POS_TRANSIENT.searching_term !== _term) {
                    return false;
                }

                if (typeof chk[customer.id] != 'undefined') {
                    return true;
                }

                var fullName = [customer.first_name, customer.last_name].join(' ');
                if (fullName.toLowerCase().indexOf(_term) > -1 || customer.email.indexOf(_term) > -1 || customer.billing_address.phone.indexOf(_term) > -1) {
                    if (empty(fullName)) {
                        fullName = customer.username;
                    }
                    var email_phone = !empty(customer.billing_address.phone) ? customer.email + ' / ' + customer.billing_address.phone : customer.email;
                    fullName += ' (' + email_phone + ')';

                    var data_pr = {id: customer.id, text: fullName};
                    chk[customer.id] = fullName;
                    result.push(data_pr);
                    if (typeof query.callback == 'undefined') {
                        fullName = fullName.replace(/(cows)/g, '<span class="smallcaps">$1</span>');
                        APP.add_customer_item_to_result({
                            id: customer.id,
                            avatar_url: customer.avatar_url,
                            fullname: fullName
                        });
                    }
                }
            });
            data.results = result;
            if (typeof query.callback != 'undefined') {
                query.callback(data);
            }
        },
        //WC 3.0 search customer function
        search_customer_wc_3: function (query, callback) {
            $('#customer_search_result').html('');
            var term = query.term;
            var _term = '';
            if (term) {
                _term = POS_TRANSIENT.searching_term = term.toLowerCase();
            }
            var data = {results: []};
            var result = [];
            var chk = {};

            APP.db.count('customers').done(function (count) {
                APP.db.values('customers', null, count, 0).done(function (customers) {
                    $.each(customers, function (i, customer) {

                        if (POS_TRANSIENT.searching_term !== _term) {
                            return false;
                        }

                        if (typeof chk[customer.id] != 'undefined') {
                            return true;
                        }

                        var fullName = [customer.first_name, customer.last_name].join(' ');
                        if (fullName.toLowerCase().indexOf(_term) > -1 || customer.email.indexOf(_term) > -1 || customer.billing_address.phone.indexOf(_term) > -1) {
                            if (empty(trim(fullName))) {
                                fullName = customer.username;
                            }
                            var email_phone = !empty(customer.billing_address.phone) ? customer.email + ' / ' + customer.billing_address.phone : customer.email;
                            fullName += ' (' + email_phone + ')';

                            var data_pr = {id: customer.id, text: fullName};
                            chk[customer.id] = fullName;
                            result.push(data_pr);
                            if (typeof callback == 'undefined') {
                                fullName = fullName.replace(/(cows)/g, '<span class="smallcaps">$1</span>');
                                APP.add_customer_item_to_result({
                                    id: customer.id,
                                    avatar_url: customer.avatar_url,
                                    fullname: fullName
                                });
                            }
                        }
                    });

                    data.results = result;
                    if (typeof callback != 'undefined') {
                        callback(data);
                    }
                });
            });

            // $.each(store.customers, function (i, customer) {
            //
            //     if (POS_TRANSIENT.searching_term !== _term) {
            //         return false;
            //     }
            //
            //     if (typeof chk[customer.id] != 'undefined'){
            //         return true;
            //     }
            //
            //     var fullName = [customer.first_name, customer.last_name].join(' ');
            //     if(fullName.toLowerCase().indexOf(_term) > -1 || customer.email.indexOf(_term) > -1 || customer.billing_address.phone.indexOf(_term) > -1){
            //         if (empty(trim(fullName))) {
            //             fullName = customer.username;
            //         }
            //         var email_phone = !empty(customer.billing_address.phone) ? customer.email + ' / ' + customer.billing_address.phone : customer.email;
            //         fullName += ' (' + email_phone + ')';
            //
            //         var data_pr = {id: customer.id, text: fullName};
            //         chk[customer.id] = fullName;
            //         result.push(data_pr);
            //         if (typeof callback == 'undefined') {
            //             fullName = fullName.replace(/(cows)/g, '<span class="smallcaps">$1</span>');
            //             APP.add_customer_item_to_result({id: customer.id, avatar_url: customer.avatar_url, fullname: fullName});
            //         }
            //     }
            // });
            // data.results = result;
            // if (typeof callback != 'undefined') {
            //     callback(data);
            // }
        },
        display_variation_price_sku: function (event, variation) {

            var price_html = pos_get_price_html(variation.data);
            var stock_quantity = parseInt(variation.data.stock_quantity);
            price = '<span class="price">' + price_html + '</span>';

            $('#selected-variation-data .selected-variation-price').html(price);
            $('#selected-variation-data .selected-variation-sku').html(variation.data.sku);

            if (!isNaN(stock_quantity)) {
                if (variation.data.stock_status != "instock") {
                    $('#selected-variation-data .selected-variation-stock').html(pos_i18n[39]).closest('li').show();
                } else {
                    $('#selected-variation-data .selected-variation-stock').html(variation.data.stock_quantity + ' ' + pos_i18n[38]).closest('li').show();
                }
            } else {
                $stock = variation.data.stock_status == "instock" ? pos_i18n[38] : pos_i18n[39];
                $('#selected-variation-data .selected-variation-stock').html($stock).closest('li').hide();
            }

            if (!$('#selected-variation-data').is(':visible')) {
                $('#selected-variation-data').slideToggle(0);
            }
        },
        user_logout: function () {
            $.ajax({
                type: 'POST',
                url: wc_pos_params.ajax_url,
                data: {
                    action: 'wc_pos_logout',
                    register_id: pos_register_data.ID
                },
                success: function (responce) {
                    APP_auth_show();
                }
            });
        },
        ready: function () {
            var request = null;
            Ladda.bind('#sync_data', {
                callback: function (instance) {
                    var progress = 0;
                    var interval = setInterval(function () {
                        progress = Math.min(progress + Math.random() * 0.1, 1);
                        instance.setProgress(progress);
                        if (progress === 1) {
                            instance.stop();
                            clearInterval(interval);
                        }
                    }, 200);
                }
            });
            setInterval(function () {
                if (APP.lastUpdate != 'null') {
                    jQuery('#last_sync_time').attr('title', APP.lastUpdate).timeago('updateFromDOM');
                }
            }, 1000);
            $('#sync_data').click(function () {
                var attr = $(this).attr('disabled');
                if (typeof attr === typeof undefined) {
                    APP.db.clear();
                    APP.sync_data(true);
                    location.reload();
                }
                return false;
            });
            if (wc_version >= 3) {

                $.fn.select2.amd.define("ProductsResultsAdapter", [
                    'select2/results',
                    'select2/utils'
                ], function (Results, Utils) {
                    function ProductResults($element, options, dataAdapter) {
                        ProductResults.__super__.constructor.call(this, $element, options, dataAdapter);
                    }

                    Utils.Extend(ProductResults, Results);

                    ProductResults.prototype.showLoading = function (params) {

                        this.clear();
                        this.hideLoading();

                        if (empty(params.term)) {
                            return;
                        }

                        var loadingMore = pos_i18n[57] + ' ' + params.term;

                        var loading = {
                            disabled: true,
                            loading: true,
                            text: loadingMore
                        };
                        var $loading = this.option(loading);
                        $loading.className += ' loading-results';

                        this.$results.prepend($loading);
                    };

                    return ProductResults;
                });


                $.fn.select2.amd.require([
                    'select2/data/array',
                    'select2/utils'
                ], function (ArrayData, Utils) {
                    function ProductData($element, options) {
                        ProductData.__super__.constructor.call(this, $element, options);
                    }

                    Utils.Extend(ProductData, ArrayData);

                    ProductData.prototype.query = function (params, callback) {
                        var term = params.term,
                            result = [],
                            title;

                        if (empty(params.term)) {
                            return;
                        }

                        if (term) {
                            var _term = term.toLowerCase();
                        }

                        if (request != null) {
                            request.abort();
                        }

                        request = $.get({
                            url: wc_pos_params.wc_api_url + 'products',
                            data: {
                                search: term,
                                per_page: 15,
                                wc_pos_search: true
                            },
                            success: function (response) {
                                var ids = [];
                                APP.db.putAll('products', response);
                                $.each(response, function (i, product) {
                                    var met = false;
                                    ids.push(product.id);
                                    if (product.variations.length > 0) {
                                        $.each(product.variations, function (i, variation) {
                                            APP.db.put('variations', {
                                                id: variation.id,
                                                prod_id: product.id,
                                                title: product.name,
                                                sku: variation.sku
                                            });
                                        });
                                    }
                                    if (product.type == 'variation') {
                                        APP.db.put('variations', {
                                            id: product.id,
                                            prod_id: product.parent_id,
                                            title: product.name,
                                            sku: product.sku
                                        });
                                    }

                                    if (typeof product.post_meta[wc_pos_params.scan_field] != 'undefined' && wc_pos_params.ready_to_scan !== 'no') {
                                        if (typeof product.post_meta[wc_pos_params.scan_field] == 'object') {
                                            title = product.post_meta[wc_pos_params.scan_field][0].toLowerCase();
                                        }
                                    }

                                    title = !empty(title) ? title : product.name.toLowerCase();

                                    if (title.indexOf(_term) >= 0) {
                                        met = true;
                                    } else {
                                        title = product.name.toLowerCase();
                                        if (title.indexOf(_term) >= 0) {
                                            met = true;
                                        } else {
                                            title = product.sku.toLowerCase();
                                            if (title.indexOf(_term) >= 0) {
                                                met = true;
                                            }
                                        }
                                    }

                                    if (met) {
                                        title = !empty(product.sku) ? product.sku + ' - ' : '';
                                        title += product.name;

                                        result.push({
                                            id: product.type == 'variation' ? product.parent_id : product.id,
                                            text: title,
                                            post_meta: product.post_meta,
                                            vid: product.type == 'variation' ? product.id : 0,
                                        });
                                    }
                                    APP.insertRelationships(product);
                                });
                            },
                            complete: function () {
                                request = null;
                                callback({results: result});
                            }
                        });

                    };

                    $('#add_product_id').select2({
                        ajax: {
                            type: 'post',
                            delay: 250,
                            url: wc_pos_params.ajax_url,
                            data: function (params) {
                                return {
                                    q: params.term,
                                    action: 'wc_pos_find_products',
                                };
                            },
                            processResults: function (data, params) {
                                data.items = data.items.map(function (item) {
                                    var title = item.title ? '<span class="result-title">' + item.title + '</span>' : '';
                                    var price = item.price ? '<span class="result-price">' + item.price + '</span>' : '';
                                    var sku = item.sku ? '<span class="result-sku">' + pos_i18n[60] + ' ' + item.sku + '</span>' : '';
                                    var stock = item.stock ? '<span class="result-stock">' + pos_i18n[61] + ' ' + item.stock + '</span>' : '';

                                    var firstRow =  '<div class="result-row first">' + title + price + '</div>';
                                    var secondRow = '<div class="result-row second">' + sku + stock + '</div>';
                                    item.text = firstRow + secondRow;

                                    return item;
                                });
                                return {
                                    results: data.items,
                                };
                            },
                            cache: true,
                        },
                        escapeMarkup: function (markup) {
                            return markup;
                        },
                        minimumInputLength: 3,
                        cache: true,
                        multiple: true,
                    }).change(function () {
                        var val = $(this).select2('data');
                        $(this).html('');
                        if (!empty(val)) {
                            val = is_array(val) ? val[0] : val;

                            // Index the product if not indexed, then add it to cart.
                            APP.indexProduct({
                                id: val.id,
                                vid: (typeof val.vid != 'undefined' ? val.vid : 0),
                                variation: (typeof val.variation != 'undefined' ? val.variation : {}),
                                quantity: wc_pos_params.decimal_quantity_value,
                            }, true);
                        }
                    });
                });
            } else {
                $('#add_product_id').select2({
                    minimumInputLength: 3,
                    multiple: true,

                    query: function (query) {
                        var term = query.term;
                        var _term = term.toLowerCase();
                        var result = [];
                        $.each(APP.tmp.products, function (index, o) {
                            var title = o.text.toLowerCase();
                            if (title.indexOf(_term) >= 0) {
                                result.push(o);
                            }
                        });
                        query.callback({results: result});
                    },

                }).change(function () {
                    var val = $('#add_product_id').val();
                    $('#add_product_id').select2('val', '', false);
                    if (val != '') {
                        val = JSON.parse(val);
                        var product_id = val.id;
                        var variation_id = (typeof val.vid != 'undefined' ? val.vid : 0);
                        var variation = (typeof val.variation != 'undefined' ? val.variation : {});

                        var quantity = wc_pos_params.decimal_quantity_value;
                        APP.addToCart(product_id, quantity, variation_id, variation);
                    }
                });
            }
            if ($('#customer_user').length) {
                if (wc_pos_params.offline_mode) {
                    if (wc_version >= 3) {
                        $.fn.select2.amd.require([
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

                            $("#customer_user").select2({
                                minimumInputLength: 3,
                                multiple: true,
                                dataAdapter: CustomerData
                            }).change(function () {
                                var customer_id = $(this).val();
                                $("#customer_user").html('');
                                APP.setCustomer(customer_id, wc_pos_params.load_customer);
                            });
                        });
                    } else {
                        $('#customer_user').select2({
                            minimumInputLength: 3,
                            multiple: false,
                            query: function (query) {
                                APP.search_customer(query);
                            }
                        }).change(function () {
                            var customer_id = $(this).val();
                            APP.setCustomer(customer_id, wc_pos_params.load_customer);
                        });
                    }
                } else {
                    $('#customer_user').select2({
                        minimumInputLength: 3,
                        multiple: false,
                        ajax: {
                            url: wc_pos_params.wc_api_url + 'customers/',
                            dataType: 'json',
                            delay: 500,
                            data: function (params) {
                                return {
                                    search: params.term,
                                    role: "all"
                                }
                            },
                            processResults: function (data) {
                                var result = [];
                                var select_data = {results: []};
                                if (data.length) {
                                    $.each(data, function (index, val) {
                                        var fullname = [val.first_name, val.last_name];
                                        var fullname = fullname.join(' ').trim();
                                        if (empty(fullname)) {
                                            fullname = val.username;
                                        }
                                        fullname += ' (' + val.email + ')';
                                        var data_pr = {id: val.id, text: fullname, data: val};
                                        result.push(data_pr);
                                        if (typeof callback == 'undefined') {
                                            APP.add_customer_item_to_result({
                                                id: val.id,
                                                avatar_url: val.avatar_url,
                                                fullname: fullname
                                            });
                                        }
                                    });
                                    select_data.results = result;
                                }
                                return select_data;
                            },
                        }
                    }).change(function () {
                        var customer = $(this).select2('data')[0];
                        APP.setCustomer(customer.data, wc_pos_params.load_customer);
                        $("#customer_user").html('');
                    });
                }
            }

            if ($('#search_customer_to_register').length) {
                $('#search_customer_to_register').click(function (event) {
                    openModal('modal-search-customer');
                    $('#search-customer-input').focus();
                });
                $('#search-customer-input').on('keyup', function (event) {
                    if ($(this).val().length >= 3) {
                        APP.search_customer({term: $(this).val()});
                    } else {
                        $('#customer_search_result').html('');
                    }
                });

                $('#customer_search_result').on('click', '.user-item', function (event) {
                    var customer_id = $(this).data('id');
                    APP.setCustomer(customer_id, wc_pos_params.load_customer);
                    closeModal('modal-search-customer');
                    $('#customer_search_result').html('');
                    $('#search-customer-input').val('');
                });
            }

            if ($('.payment_method_cod').length) {
                $('.payment_method_cod').click(function (event) {
                    $('#amount_pay_cod').focus();
                });
            }

            var accountFunds = $("#accountfunds");
            if (accountFunds.length) {
                accountFunds.find(".woocommerce-Price-amount").html(accountingPOS(0, 'formatMoney'));
            }


            if (pos_default_customer) {
                APP.setCustomer(pos_default_customer);
            }
            $('#wc-pos-customer-data').on('click', '.remove_customer_row', function () {
                APP.setGuest();
                return false;
            });
            $('body').on('click', '#clear_shipping', function (event) {
                CART.chosen_shipping_methods = {id: '', title: '', price: ''};
                if (wc_pos_params.tabs_management) {
                    APP.tabs[APP.active_tab].setCart('chosen_shipping_methods', CART.chosen_shipping_methods);
                }
                CART.calculate_totals();
            });

            $(document).on('click', 'a.show_customer_popup', function (event) {
                var source = $('#tmpl-form-add-customer').html();
                var template = Handlebars.compile(source);
                var html = template(CUSTOMER);
                html = $(html);

                if (CUSTOMER.create_account === true) {
                    $('#modal-order_customer').find('#createaccount').prop('checked', 'checked');
                }

                if (CUSTOMER.id > 0) {
                    $('#modal-order_customer').find('label[for="createaccount"]').hide();
                } else {
                    $('#modal-order_customer').find('label[for="createaccount"]').show();
                }

                if (CUSTOMER.customer) {
                    $('button#save_customer').text(pos_i18n[55]);
                } else {
                    $('button#save_customer').text(pos_i18n[54]);
                }

                $('#customer_details').html(html);
                $('.shipping_address #shipping_country').select2({width: '100%'});
                $('#modal-order_customer .nav-tab-wrapper a').first().trigger('click');
                wc_country_select_select2();
                openModal('modal-order_customer');
                jQuery(document).trigger('acf/setup_fields', [jQuery('#pos_custom_fields')]);
                //$(document.body).trigger('wc-enhanced-select-init');
                if (sizeof(wc_country_select_params.allowed_countries) > 1) {
                    $('#shipping_country').val(CUSTOMER.shipping_address.country).trigger('change');
                    $('#billing_country').val(CUSTOMER.billing_address.country).trigger('change');
                }

                $.each(CUSTOMER.acf_fields, function (key, val) {
                    var field = Array.isArray(val) ? '[name = "acf-field-' + key + '[]"]' : '#acf-field-' + key;
                    var a_el = $('#customer_details #pos_custom_fields ' + field );
                    if (a_el.length) {
                        if (a_el.first().is(':radio') || a_el.first().is(':checkbox')) {
                            a_el.each(function (index, el) {
                                if ($(el).val() == val) {
                                    $(el).attr('checked', 'checked').trigger('change');
                                }
                            });
                        } else {
                            a_el.val(val).trigger('change');
                        }
                    }
                });

                $.each(CUSTOMER.additional_fields, function (key, val) {
                    var a_el = $('#customer_details #pos_additional_fields #' + key);
                    if (a_el.length) {
                        if (a_el.first().is(':radio') || a_el.first().is(':checkbox')) {
                            a_el.each(function (index, el) {
                                if ($(el).val() == val) {
                                    $(el).attr('checked', 'checked').trigger('change');
                                }
                            });
                        } else {
                            a_el.val(val).trigger('change');
                        }
                    }
                });
                $.each(CUSTOMER.custom_order_fields, function (key, val) {
                    var a_el = $('#order_details #pos_order_fields #' + key);
                    if (a_el.length) {
                        if (a_el.first().is(':radio') || a_el.first().is(':checkbox')) {
                            a_el.each(function (index, el) {
                                if ($(el).val() == val) {
                                    $(el).attr('checked', 'checked').trigger('change');
                                }
                            });
                        } else {
                            a_el.val(val).trigger('change');
                        }
                    }
                });
                $.each(CUSTOMER.billing_address, function (key, val) {
                    var a_el = $('#customer_details #pos_billing_details #' + key);
                    if (a_el.length) {
                        if (a_el.first().is(':radio') || a_el.first().is(':checkbox')) {

                            a_el.each(function (index, el) {
                                if ($(el).val() == val) {
                                    $(el).attr('checked', 'checked').trigger('change');
                                }
                            });
                        } else {
                            a_el.val(val).trigger('change');
                        }
                    }
                });
                $.each(CUSTOMER.shipping_address, function (key, val) {
                    var a_el = $('#customer_details #pos_shipping_details #' + key);

                    if (a_el.length) {
                        if (a_el.first().is(':radio') || a_el.first().is(':checkbox')) {
                            a_el.each(function (index, el) {
                                if ($(el).val() == val) {
                                    $(el).attr('checked', 'checked').trigger('change');
                                }
                            });
                        } else {
                            a_el.val(val).trigger('change');
                        }
                    }
                });
                $("#add_customer_to_register").click();
                runTips();
                return false;
            });
            $('body').on('click', '#add_customer_to_register', function (event) {
                APP.setUpCustomerModal(true, false, event);
            });
            $('body').on('click', '#billing-same-as-shipping', function (event) {
                if ($('.wc-address-validation-address-type[value="shipping"]').length >= 1) {
                    var postcode = $('.wc-address-validation-billing-field [name="wc_address_validation_postcode_lookup_postcode"]').val();
                    $('.wc-address-validation-shipping-field [name="wc_address_validation_postcode_lookup_postcode"]').val(postcode);
                    $('.shipping_address .wc-address-validation-shipping-field a').click();
                } else {
                    var ar = ['first_name', 'last_name', 'company', 'address_1', 'address_2', 'city', 'state', 'postcode'];
                    $('#shipping_country').val($('#billing_country').val());
                    $('#shipping_country').trigger('change');
                    $.each(ar, function (index, val) {
                        if ($('#billing_' + val).length && $('#shipping_' + val)) {
                            var v = $('#billing_' + val).val();
                            $('#shipping_' + val).val(v);
                        }
                    });
                }
                $('#shipping_state').trigger('change');
            });
            $('#edit_wc_pos_registers').on('click', '.add_grid_tile', function (event) {
                if (CART.pos_refund.refund == true) {
                    APP.showNotice("You can't add product while refund an order", "error");
                    return false;
                }
                var product_id = $(this).data('id');
                var variation_id = $(this).data('varid');
                if (variation_id == 'undefined') {
                    variation_id = 0;
                }
                var quantity = wc_pos_params.decimal_quantity_value;
                if (product_id && !empty(product_id)) {
                    APP.addToCart(product_id, quantity, variation_id);
                }
            });
            $('#save_customer').on('click', function () {
                var err = 0;
                if (jQuery('#billing_account_password').val() != jQuery('#billing_password_confirm').val()) {
                    APP.showNotice(pos_i18n[41], 'error');
                    err++;
                }
                $('#customer_details .woocommerce-billing-fields .validate-required input, #customer_details .woocommerce-billing-fields .validate-required select').each(function (index, el) {
                    if (err == 0) {
                        if ($(this).hasClass('select2-offscreen')) {
                            return;
                        }
                        if ($(this).closest('.form-row').css('display') != 'none' && !$(this).closest('.select2-container').length) {

                            var val = $(this).val();
                            if (val == '') {
                                APP.showNotice(pos_i18n[15], 'error');
                                err++;
                            }
                        }
                    }
                });
                if (err > 0) {
                    return;
                }
                $('#customer_details .woocommerce-shipping-fields .validate-required input, #customer_details .woocommerce-shipping-fields .validate-required select').each(function (index, el) {
                    if (err == 0) {
                        if ($(this).hasClass('select2-offscreen')) {
                            return;
                        }
                        ;
                        if ($(this).closest('.form-row').css('display') != 'none' && !$(this).closest('.select2-container').length) {
                            var val = $(this).val();
                            if (val == '') {
                                APP.showNotice(pos_i18n[16], 'error');
                                err++;
                            }
                        }
                    }
                });
                if (err > 0) {
                    return;
                }
                $('#customer_details .woocommerce-additional-fields .validate-required input, #customer_details .woocommerce-additional-fields .validate-required select').each(function (index, el) {
                    if (err == 0) {
                        if ($(this).hasClass('select2-offscreen')) {
                            return;
                        }
                        ;
                        var val = $(this).val();
                        if (val == '' && !$(this).closest('.select2-container').length) {
                            APP.showNotice(pos_i18n[17], 'error');
                            err++;
                        }
                    }
                });
                if (err > 0) {
                    return;
                }
                $('#customer_details .woocommerce-custom-fields .validate-required input, #customer_details .woocommerce-custom-fields .validate-required select').each(function (index, el) {
                    if (err == 0) {
                        if ($(this).hasClass('select2-offscreen')) {
                            return;
                        }
                        ;
                        var val = $(this).val();
                        if (val == '' && !$(this).closest('.select2-container').length) {
                            APP.showNotice(pos_i18n[35], 'error');
                            err++;
                        }
                    }
                });
                if (err > 0) {
                    return;
                }
                if (err == 0) {
                    var new_customer = $('#customer_details_id').val() == '' ? true : false;
                    if (new_customer) {
                        CUSTOMER.reset();
                    }
                    var arr = ['account_username', 'account_password', 'country', 'first_name', 'last_name', 'company', 'address_1', 'address_2', 'city', 'state', 'postcode', 'email', 'phone'];
                    $.each(arr, function (index, key) {
                        var b_key = 'billing_' + key;
                        var s_key = 'shipping_' + key;
                        var b_el = $('#customer_details #' + b_key);
                        var s_el = $('#customer_details #' + s_key);
                        if (b_el.length) {
                            CUSTOMER.billing_address[key] = escapeHtml(b_el.val());
                        }
                        if (s_el.length) {
                            CUSTOMER.shipping_address[key] = escapeHtml(s_el.val());
                        }
                    });

                    var acf_fields = wc_pos_params.acf_fields;
                    $.each(acf_fields, function (index, key) {
                        if (key == '') return true;

                        var a_custom_f = $('#acf-' + key + '[data-field_name="' + key + '"]');
                        if (a_custom_f.length && a_custom_f.hasClass('field_type-relationship')) {
                            var _val = [];
                            var a_el = a_custom_f.find('.relationship_right li input');
                            a_el.each(function (index, el) {
                                _val.push($(el).val());
                            });
                            CUSTOMER.acf_fields[key] = _val;
                        } else if (a_custom_f.length && a_custom_f.hasClass('field_type-google_map')) {
                            var _val = {};
                            _val['address'] = a_custom_f.find('.input-address').val();
                            _val['lat'] = a_custom_f.find('.input-lat').val();
                            _val['lng'] = a_custom_f.find('.input-lng').val();
                            CUSTOMER.acf_fields[key] = _val;
                        } else if (a_custom_f.length && a_custom_f.hasClass('field_type-date_picker')) {
                            CUSTOMER.acf_fields[key] = a_custom_f.find('.input-alt').val();
                        } else {
                            var a_el_id = $('#customer_details #pos_custom_fields :input[id^="acf-field-' + key + '"]');
                            var a_el = $('#customer_details #pos_custom_fields :input#acf-field-' + key);
                            var a_el = a_el.length ? a_el : a_el_id;


                            if (a_el.length) {
                                if (a_el.first().is(':checkbox')) {
                                    var _val = [];
                                    a_el.filter(':checked').each(function (index, el) {
                                        _val.push($(el).val());
                                    });
                                    CUSTOMER.acf_fields[key] = _val;
                                } else if (a_el.first().is(':radio')) {
                                    CUSTOMER.acf_fields[key] = a_el.filter(':checked').val();
                                } else {
                                    CUSTOMER.acf_fields[key] = a_el.val();
                                }
                            }
                        }

                    });

                    var additional_fields = wc_pos_params.additional_fields;
                    $.each(additional_fields, function (index, key) {
                        var a_el_id = $('#customer_details #pos_additional_fields :input[id^="' + key + '"]');
                        var a_el = $('#customer_details #pos_additional_fields :input#' + key);
                        var a_el = a_el.length ? a_el : a_el_id;

                        if (a_el.length) {
                            if (a_el.first().is(':checkbox')) {
                                var _val = [];
                                a_el.filter(':checked').each(function (index, el) {
                                    _val.push($(el).val());
                                });
                                CUSTOMER.additional_fields[key] = _val;
                            } else if (a_el.first().is(':radio')) {
                                CUSTOMER.additional_fields[key] = a_el.filter(':checked').val();
                            } else {
                                CUSTOMER.additional_fields[key] = a_el.val();
                            }
                        }

                    });
                    var note = $("#pos_additional_fields #order_comments").val();
                    if (note != "") {
                        CUSTOMER.additional_fields["order_comments"] = note;
                        CART.customer_note = note;
                    }

                    $.each(wc_pos_params.a_billing_fields, function (index, key) {
                        var a_el_id = $('#customer_details #pos_billing_details :input[id^="' + key + '"]');
                        var a_el = $('#customer_details #pos_billing_details :input#' + key);
                        var a_el = a_el.length ? a_el : a_el_id;

                        if (a_el.length) {
                            if (a_el.first().is(':checkbox')) {
                                var _val = [];
                                a_el.filter(':checked').each(function (index, el) {
                                    _val.push($(el).val());
                                });
                                CUSTOMER.additional_fields[key] = _val;
                                CUSTOMER.billing_address[key] = _val;
                            } else if (a_el.first().is(':radio')) {
                                CUSTOMER.additional_fields[key] = a_el.filter(':checked').val();
                                CUSTOMER.billing_address[key] = a_el.filter(':checked').val();
                            } else {
                                CUSTOMER.additional_fields[key] = a_el.val();
                                CUSTOMER.billing_address[key] = a_el.val();
                            }
                        }

                    });
                    $.each(wc_pos_params.a_shipping_fields, function (index, key) {

                        var a_el_id = $('#customer_details #pos_shipping_details :input[id^="' + key + '"]');
                        var a_el = $('#customer_details #pos_shipping_details :input#' + key);
                        var a_el = a_el.length ? a_el : a_el_id;

                        if (a_el.length) {
                            if (a_el.first().is(':checkbox')) {
                                var _val = [];
                                a_el.filter(':checked').each(function (index, el) {
                                    _val.push($(el).val());
                                });
                                CUSTOMER.additional_fields[key] = _val;
                                CUSTOMER.billing_address[key] = _val;
                            } else if (a_el.first().is(':radio')) {
                                CUSTOMER.additional_fields[key] = a_el.filter(':checked').val();
                                CUSTOMER.billing_address[key] = a_el.filter(':checked').val();
                            } else {
                                CUSTOMER.additional_fields[key] = a_el.val();
                                CUSTOMER.billing_address[key] = a_el.val();
                            }
                        }
                    });

                    CUSTOMER.first_name = CUSTOMER.billing_address['first_name'];
                    CUSTOMER.last_name = CUSTOMER.billing_address['last_name'];
                    CUSTOMER.email = CUSTOMER.billing_address['email'];

                    if (!CUSTOMER.avatar_url) CUSTOMER.avatar_url = wc_pos_params.avatar;

                    var fullname = [CUSTOMER.first_name, CUSTOMER.last_name];
                    fullname = fullname.join(' ');

                    if (fullname == '') {
                        fullname = clone(CUSTOMER.username);
                    }
                    CUSTOMER.fullname = fullname;

                    if ($('#createaccount').is(':checked')) {
                        CUSTOMER.create_account = true;
                    }

                    CUSTOMER.customer = true;
                    CUSTOMER.points_n_rewards = 0;

                    var source = $('#tmpl-cart-customer-item').html();
                    var template = Handlebars.compile(source);
                    var html = template(CUSTOMER);
                    $('tbody#customer_items_list').html(html);
                    CART.calculate_totals();
                    closeModal('modal-order_customer');
                }
            });

            $("#save_order_fields").on('click', function (e) {
                var custom_order_fields = wc_pos_params.acf_order_fields;
                $.each(custom_order_fields, function (index, key) {

                    var a_el_id = $('#order_details #pos_order_fields :input[id^="acf-field-' + key + '"]');
                    var a_el = $('#order_details #pos_order_fields :input#acf-field-' + key);
                    var a_el = a_el.length ? a_el : a_el_id;

                    if (a_el.length) {
                        if (a_el.first().is(':checkbox')) {
                            var _val = [];
                            a_el.filter(':checked').each(function (index, el) {
                                _val.push($(el).val());
                            });
                            CUSTOMER.custom_order_fields[key] = _val;
                        } else if (a_el.first().is(':radio')) {
                            CUSTOMER.custom_order_fields[key] = a_el.filter(':checked').val();
                        } else {
                            CUSTOMER.custom_order_fields[key] = a_el.val();
                        }
                    }
                });
                closeModal("modal-acf_order_information");
            });

            $('#wc-pos-register-data').on('click', '.remove_order_item', function () {
                var $el = $(this).closest('tr');
                var id = $el.attr('id');
                $el.remove();
                $('#item_note-' + id).remove();
                $('#tiptip_holder').hide().css({margin: '-100px 0 0 -100px'});
                CART.remove_cart_item(id);
                return false;
            });
            $('#modal-order_customer').on('click', '.nav-tab-wrapper a', function (event) {
                $('#modal-order_customer .nav-tab-wrapper a').removeClass('nav-tab-active');
                $(this).addClass('nav-tab-active');
                var id = $(this).attr('href');
                $('#customer_details .pos-customer-details-tab').hide();
                $('#customer_details ' + id).show();
                return false;
            }).on('click', "#scan_card_button", function (e) {
                var button = $(this);
                openModal("modal-customer-search-card", false, true);
                var parent = $("#pos_card_fields");
                $.cardswipe({
                    firstLineOnly: false,
                    success: function (data) {
                        $.ajax({
                            type: 'POST',
                            url: wc_pos_params.ajax_url,
                            data: {
                                action: 'wc_pos_get_user_by_card_number',
                                code: data.line2,
                            },
                            beforeSend: function () {
                                parent.find('.result').html('');
                                parent.parents('.md-modal').block({
                                    message: null,
                                    overlayCSS: {
                                        background: '#fff',
                                        opacity: 0.6
                                    }
                                });
                            },
                            success: function (response) {
                                if (response.success && response.data) {
                                    for (var key in response.data.billing) {
                                        $('#billing_' + key).val(response.data.billing[key])
                                    }
                                    for (var key in response.data.shipping) {
                                        $('#shipping_' + key).val(response.data.shipping[key])
                                    }
                                    parent.find('.result').append('<p class="message">Customer found</p>');
                                    APP.setCustomer(response.data.id, false);
                                    parent.prev().children('.md-close').click();
                                    button.siblings("#save_customer").click();

                                } else {
                                    parent.find('.result').append('<p class="message">Customer not found</p>');
                                }
                            },
                        }).always(function () {
                            parent.parents('.md-modal').unblock();
                        });
                    },
                    parsers: ["generic"],
                    failure: function () {
                        console.log("error");
                    },
                    debug: false
                });
            });
            $('#custom-add-shipping-details').change(function (event) {
                if ($(this).is(':checked')) {
                    $('#custom-shipping-details-wrap').show();
                    wc_country_select_select2();
                    if (sizeof(wc_country_select_params.allowed_countries) > 1) {
                        $('#custom_shipping_country').val(CUSTOMER.shipping_address.country).trigger('change');
                    }
                    $('select#custom_shipping_state').val(CUSTOMER.shipping_address.state).trigger('change');

                } else {
                    $('#custom-shipping-details-wrap').hide();
                }
            });

            $('#add_shipping_to_register').click(function (event) {
                var modal = $(this).attr('data-modal');
                var source = $('#tmpl-custom-shipping-method-title-price').html();
                var template = Handlebars.compile(source);
                var html = template(CART.chosen_shipping_methods);
                $('#custom_shipping_table tbody').html(html);
                if ($('#custom_shipping_table tbody #custom_shipping_price').length > 0) {
                    $('#custom_shipping_table tbody #custom_shipping_price').keypad('destroy');
                    calculateShippingPrice();
                }
                if (CUSTOMER.customer) {
                    $('#custom-add-shipping-details').prop('checked', true).trigger('change');
                } else {
                    $('#custom-add-shipping-details').prop('checked', false).trigger('change');
                }

                var source = $('#tmpl-custom-shipping-shippingaddress').html();
                var template = Handlebars.compile(source);
                var html = template(CUSTOMER);
                $('#custom-shipping-shippingaddress').html(html);
                $('#custom-shipping-shippingaddress #custom_shipping_country').select2({width: '100%'});
                openModal(modal);
                wc_country_select_select2();

                if (sizeof(wc_country_select_params.allowed_countries) >= 1) {
                    $('#custom_shipping_country').val(CUSTOMER.shipping_address.country).trigger('change');
                    $('select#custom_shipping_state').val(CUSTOMER.shipping_address.state).trigger('change');
                    if (sizeof(wc_country_select_params.allowed_countries) == 1) {
                        $('#custom_shipping_country_field .select2-container').attr('style', 'display: none;');
                    }
                }

                runTips();

                if (typeof pos_wc.outlet_location.contact.default_shipping_method != 'undefined' && pos_wc.outlet_location.contact.default_shipping_method) {
                    jQuery('#select-shipping-method').val(pos_wc.outlet_location.contact.default_shipping_method).trigger('change')
                }
            });
            $('#add_custom_shipping').click(function (event) {
                var err = 0;
                if ($('#custom-add-shipping-details').is(':checked')) {
                    $('#custom-shipping-shippingaddress .validate-required input, #custom-shipping-shippingaddress .validate-required select').each(function (index, el) {
                        if ($(this).hasClass('select2-offscreen')) {
                            return;
                        }
                        ;
                        var val = $(this).val();
                        if (val == '' && !$(this).closest('.select2-container').length) {
                            APP.showNotice(pos_i18n[16], 'error');
                            err++;
                            return false;
                        }
                    });
                    if (err > 0) return;
                }

                $('#custom_shipping_title, #custom_shipping_price').each(function (index, el) {
                    var val = $(this).val();
                    if (val == '' && err == 0) {
                        APP.showNotice(pos_i18n[18 + index], 'error');
                        err++;
                        return false;
                    }
                });
                if (err == 0) {
                    var arr = ['country', 'first_name', 'last_name', 'company', 'address_1', 'address_2', 'city', 'state', 'postcode'];
                    $.each(arr, function (index, key) {
                        var s_key = 'custom_shipping_' + key;
                        var s_el = $('#custom-shipping-shippingaddress #' + s_key);
                        if (s_el.length) {
                            CUSTOMER.shipping_address[key] = s_el.val();
                        }
                    });
                    var price = $('#custom_shipping_price').val();
                    CART.chosen_shipping_methods = {
                        id: $('#select-shipping-method').val(),
                        title: $('#custom_shipping_title').val(),
                        price: max(0, price),
                    };
                    if (wc_pos_params.tabs_management) {
                        APP.tabs[APP.active_tab].setCart('chosen_shipping_methods', CART.chosen_shipping_methods);
                    }
                    CART.calculate_totals();
                    closeModal('modal-add_custom_shipping');
                }
            });

            $('#add_custom_product_meta').click(function () {
                var meta = [{meta_key: '', meta_v: ''}];
                var source = $('#tmpl-add-custom-item-meta').html();
                var template = Handlebars.compile(source);
                var html = template(meta);
                $('#product_custom_meta_table tbody').html(html);
                $('#custom_product_meta_table tbody').append(html);
                $('#custom_product_meta_table, #custom_product_meta_label').show();
                runTips();
            });


            if (wc_pos_params.instant_quantity_keypad == 'yes') {
                runQuantityKeypad($('.inline_quantity'), 'full', false);
            } else if (wc_pos_params.instant_quantity == 'yes') {
                runQuantityKeypad($('.inline_quantity'), 'min', false);
            }

            $('body').on('update_variation_values', '#missing-attributes-select', function (event, variations) {
                $variation_form = $(this);

                // Loop through selects and disable/enable options based on selections
                $variation_form.find('select').each(function (index, el) {
                    var current_attr_name, current_attr_select = $(el);

                    // Reset options
                    if (!current_attr_select.data('attribute_options')) {
                        current_attr_select.data('attribute_options', current_attr_select.find('option:gt(0)').get());
                    }

                    current_attr_select.find('option:gt(0)').remove();
                    current_attr_select.append(current_attr_select.data('attribute_options'));
                    current_attr_select.find('option:gt(0)').removeClass('attached');
                    current_attr_select.find('option:gt(0)').removeClass('enabled');
                    current_attr_select.find('option:gt(0)').removeAttr('disabled');

                    // Get name from data-attribute_name, or from input name if it doesn't exist
                    current_attr_name = current_attr_select.data('taxonomy');

                    // Loop through variations
                    for (var num in variations) {

                        if (typeof(variations[num]) !== 'undefined') {

                            var attributes = variations[num].attributes;

                            for (var attr_name in attributes) {
                                if (attributes.hasOwnProperty(attr_name)) {
                                    var attr_val = attributes[attr_name];

                                    if (attr_name === current_attr_name) {

                                        var variation_active = '';

                                        if (variations[num].variation_is_active) {
                                            variation_active = 'enabled';
                                        }

                                        if (attr_val) {

                                            // Decode entities
                                            attr_val = $('<div/>').html(attr_val).text();

                                            // Add slashes
                                            attr_val = attr_val.replace(/'/g, '\\\'');
                                            attr_val = attr_val.replace(/"/g, '\\\"');

                                            // Compare the meerkat
                                            current_attr_select.find('option[value="' + attr_val + '"]').addClass('attached ' + variation_active);

                                        } else {

                                            current_attr_select.find('option:gt(0)').addClass('attached ' + variation_active);

                                        }
                                    }
                                }
                            }
                        }
                    }

                    // Detach unattached
                    current_attr_select.find('option:gt(0):not(.attached)').remove();

                    // Grey out disabled
                    current_attr_select.find('option:gt(0):not(.enabled)').attr('disabled', 'disabled');
                });

            });


            $('body').on('found_variation', '#missing-attributes-select', this.display_variation_price_sku);
            $('body').on('check_variations', '#missing-attributes-select', function (event, exclude, focus) {

                var all_attributes_chosen = true,
                    some_attributes_chosen = false,
                    current_settings = {},
                    $form = $(this),
                    product_id = parseInt(APP.tmp.product_item.product_id),
                    $product_variations = APP.tmp.product_item.product_variations;


                $form.find('select').each(function () {
                    var attribute_name = $(this).data('taxonomy');

                    if ($(this).val().length === 0) {
                        all_attributes_chosen = false;
                    } else {
                        some_attributes_chosen = true;
                    }

                    if (exclude && attribute_name === exclude) {
                        all_attributes_chosen = false;
                        current_settings[attribute_name] = '';
                    } else {
                        // Add to settings array
                        current_settings[attribute_name] = $(this).val();
                    }
                });


                var matching_variations = APP.find_matching_variations($product_variations, current_settings);

                if (all_attributes_chosen) {

                    var variation = matching_variations.shift();

                    if (variation) {
                        APP.tmp.product_item.variation_id = variation.variation_id;
                        $form.trigger('found_variation', [variation]);
                    } else {
                        if ($('#selected-variation-data').is(':visible')) {
                            $('#selected-variation-data').slideToggle(0);
                        }
                        // Nothing found - reset fields
                        $form.find('select').val('');

                        if (!focus) {
                            $form.trigger('reset_data');
                        }
                    }

                } else {

                    $form.trigger('update_variation_values', [matching_variations]);
                }

            });
            $('body').on('click', '#reset_selected_variation', function () {
                $('#missing-attributes-select select').val('').first().trigger('change');
                return false;
            });
            $('body').on('change', '#missing-attributes-select select', function () {

                var $form = $(this).closest('#missing-attributes-select');

                var all_attributes_chosen = true,
                    some_attributes_chosen = false;

                $form.find('select').each(function () {

                    if ($(this).val().length === 0) {
                        all_attributes_chosen = false;
                    } else {
                        some_attributes_chosen = true;
                    }

                });
                if (!all_attributes_chosen && $('#selected-variation-data').is(':visible')) {
                    $('#selected-variation-data').slideToggle(0);
                }

                if (some_attributes_chosen) {
                    if (!$('#reset_selected_variation').is(':visible')) {
                        $('#reset_selected_variation').slideToggle(0);
                    }
                } else {
                    if ($('#reset_selected_variation').is(':visible')) {
                        $('#reset_selected_variation').slideToggle(0);
                    }
                }

                $form.trigger('check_variations', ['', false]);
                $(this).blur();

            });
            $('body').on('focusin touchstart', '#missing-attributes-select select', function () {

                $form = $(this).closest('#missing-attributes-select');
                $form.trigger('check_variations', [$(this).data('taxonomy'), true]);

            });
            $('.product-add-btn').click(function (event) {

                var $parent = $(this).closest('.md-modal');
                var product_id = APP.tmp.product_item.product_id;
                var quantity = APP.tmp.product_item.quantity;

                if ($parent.find('.inline_quantity').length) {
                    quantity = $parent.find('.keypad-keyentry').val();
                    $parent.find('.keypad-keyentry').val('');
                    $parent.find('.keypad-inpval').text(1);

                    if (empty(quantity)) {
                        quantity = $parent.find('.keypad-inpval').text();
                    }
                }
                if (empty(quantity)) {
                    quantity = wc_pos_params.decimal_quantity_value;
                }

                if (wc_pos_params.decimal_quantity == 'yes') {
                    //quantity = parseFloat(quantity).toFixed(3);
                    quantity = parseFloat(quantity);
                } else {
                    quantity = parseInt(quantity);
                }
                if (quantity <= 0) {
                    quantity = wc_pos_params.decimal_quantity_value;
                }

                var valid = wp.hooks.applyFilters('wc_pos_before_add_to_cart_validation', true, APP.tmp.product_item.adding_to_cart, product_id, quantity, APP.tmp.product_item.variation_id, APP.tmp.product_item.variation);

                if ($parent.find('#missing-attributes-select table').length) {
                    $parent.find('#missing-attributes-select table select').each(function (index, el) {
                        var taxonomy = $(this).data('taxonomy');
                        var value = $(this).val();
                        if (value == '') {
                            valid = false;
                            return;
                        } else {
                            APP.tmp.product_item.variation[taxonomy] = value;
                        }
                    });
                }

                if (valid === true) {
                    APP.addToCart(product_id, quantity, APP.tmp.product_item.variation_id, APP.tmp.product_item.variation, APP.tmp.product_item.cart_item_data);
                    var modalid = $parent.attr('id');
                    closeModal(modalid);
                } else {
                    APP.showNotice(pos_i18n[7], 'error');
                }
            });

            $('.wc_pos_show_tiles').on('click', function () {
                $('#wc-pos-register-grids').css('visibility', 'visible');
            });
            $('.close_product_grids').on('click', function () {
                $('#wc-pos-register-grids').css('visibility', 'hidden');
            });
            $('.wc_pos_register_void').on('click', function () {

                var register_void = function () {
                    var args = {
                        content: $('#tmpl-confirm-void-register').html(),
                        confirm: APP.voidRegister,
                        notSign: true
                    };
                    openConfirm(args);
                };

                if (wc_pos_params.signature_panel == true && wc_pos_params.signature_required == true && wc_pos_params.signature_required_on.indexOf('void') != -1) {
                    var args = {
                        content: $('#tmpl-signature').html(),
                        confirm: APP.voidRegister
                    };
                    openConfirm(args);
                } else {
                    register_void();
                }

            });
            $('#wc-pos-register-data').on('click', '.add_custom_meta', function () {
                var item_key = $(this).closest('tr').attr('id');
                var cart_contents = CART.cart_contents;

                if (typeof cart_contents[item_key] != 'undefined') {
                    var product = cart_contents[item_key];
                    var variation = product.variation;
                    var meta = [];
                    if (Object.size(variation) === 0) {
                        meta.push({meta_key: '', meta_v: ''});
                    } else {
                        $.each(variation, function (meta_key, meta_v) {
                            var _meta = {'meta_key': meta_key, 'meta_v': meta_v};
                            meta.push(_meta);
                        });
                    }

                    var source = $('#tmpl-add-custom-item-meta').html();
                    var template = Handlebars.compile(source);
                    var html = template(meta);
                    $('#product_custom_meta_table tbody').html(html);
                    $('#add_custom_meta_product_id').val(item_key);

                    var item_title = product.data['name'];
                    var original_title = product.data['name'];
                    if (typeof product.data['original_title'] == 'undefined') {
                        CART.cart_contents[item_key]['data']['original_title'] = clone(product.data['title']);
                    } else {
                        original_title = product.data['original_title'];
                    }
                    $('#original_product_title').html(original_title);
                    $('#product_new_custom_title').val(item_title);

                    var is_taxable = product.data['tax_status'] === "taxable";
                    var tax_class = product.data['tax_class'];

                    if (product.variation_id > 0) {
                        is_taxable = product.v_data['tax_status'] === "taxable";
                        tax_class = product.v_data['tax_class'];
                    }

                    $('#product_new_is_taxable').prop('checked', is_taxable).trigger('change');
                    $('#product_new_tax_class').val(tax_class);

                    openModal('modal-add_product_custom_meta');
                    runTips();
                }
                return false;
            });
            $('#save_product_custom_meta').click(function () {
                var modalid = $(this).closest('div.md-modal').attr('id');
                var item_key = $('#add_custom_meta_product_id').val();
                var cart = CART.get_cart();
                if (typeof cart[item_key] != 'undefined') {
                    var title = $('#product_new_custom_title').val();
                    var variation = {};
                    var meta = '';
                    $('tr#' + item_key + ' td.name span > .product_title').html(title);
                    cart[item_key]['data']['title'] = title;

                    var is_taxable = $('#product_new_is_taxable').is(':checked');
                    var tax_class = $('#product_new_tax_class').val();
                    var _key = 'data';
                    if (cart[item_key].variation_id > 0) {
                        _key = 'v_data';
                    }
                    cart[item_key][_key]['taxable'] = is_taxable;
                    if (is_taxable) {
                        cart[item_key][_key]['tax_status'] = 'taxable';
                    } else {
                        cart[item_key][_key]['tax_status'] = 'none';
                    }
                    cart[item_key][_key]['tax_class'] = tax_class;
                    $('#product_custom_meta_table tbody tr').each(function (index, el) {
                        var meta_label = $(this).find('.meta_label_value').val();
                        var meta_attribute = $(this).find('.meta_attribute_value').val();
                        if (meta_label != '' && meta_attribute != '') {
                            variation[meta_label] = meta_attribute;
                            meta += '<li><span class="meta_label">' + meta_label + '</span><span class="meta_value">' + meta_attribute + '</span></li>';
                        }
                    });
                    cart[item_key]['variation'] = variation;
                    if (meta != '') {
                        meta = '<ul class="display_meta">' + meta + '</ul>';
                    }
                    var display_meta = $('tr#' + item_key + ' td.name .display_meta');
                    if (display_meta.length) {
                        display_meta.replaceWith(meta);
                    } else {
                        $('tr#' + item_key + ' td.name .view').append(meta);
                    }

                    wp.hooks.doAction('wc_pos_save_product_custom_meta', item_key);
                    CART.calculate_totals();
                }
                $('#add_custom_meta_product_id').val('');
                closeModal(modalid);
            });

            $('#product_new_is_taxable').change(function (e) {
                if (!$(this).is(':checked')) {
                    $('#product_new_tax_class').attr('disabled', 'disabled');
                } else {
                    $('#product_new_tax_class').removeAttr('disabled');
                }
            });
            $('#add_product_custom_meta').click(function () {
                var meta = [{meta_key: '', meta_v: ''}];
                var source = $('#tmpl-add-custom-item-meta').html();
                var template = Handlebars.compile(source);
                var html = template(meta);
                $('#product_custom_meta_table tbody').append(html);
                runTips();
            });
            $('body').on('click', '.remove_custom_product_meta', function () {
                var $tbody = $(this).closest('tbody');
                var id = $(this).closest('table').attr('id');
                $(this).closest('tr').remove();
                var count = $tbody.find('tr').length;

                if (!count) {
                    if (id == 'custom_product_meta_table') {
                        $('#custom_product_meta_label, #custom_product_meta_table').hide();
                    } else {
                        var meta = [{meta_key: '', meta_v: ''}];
                        var source = $('#tmpl-add-custom-item-meta').html();
                        var template = Handlebars.compile(source);
                        var html = template(meta);
                        $tbody.append(html);
                        runTips();
                    }
                }
                return false;
            });
            $('#add_custom_product').click(function () {
                var err = 0;
                $('#custom_product_title, #custom_product_price, #custom_product_quantity').each(function (index, el) {
                    if ($(this).val() == '') {
                        APP.showNotice(pos_i18n[20 + index], 'error');
                        err++;
                        return false;
                    }
                });
                if (err > 0)
                    return false;

                var adding_to_cart = clone(pos_custom_product);
                adding_to_cart.name = $('#custom_product_table input#custom_product_title').val();
                adding_to_cart.price = $('#custom_product_table input#custom_product_price').val();
                adding_to_cart.regular_price = adding_to_cart.price;

                var quantity = parseInt($('#custom_product_table input#custom_product_quantity').val());
                if (wc_pos_params.decimal_quantity == 'yes') {
                    quantity = parseFloat($('#custom_product_table input#custom_product_quantity').val());
                }
                var variation = {};

                $('#custom_product_meta_table tbody tr').each(function (index, el) {
                    var meta_label = $(el).find('.meta_label_value').val();
                    var meta_attribute = $(el).find('.meta_attribute_value').val();
                    variation[meta_label] = meta_attribute;
                });

                CART.addToCart(adding_to_cart, adding_to_cart.id, quantity, 0, variation);
                closeModal('modal-add_custom_product');

            });
            $('#add_product_to_register').click(function (event) {
                $('#custom_product_meta_label, #custom_product_meta_table').hide();
                $('#custom_product_meta_table tbody').html('');
                $('#custom_product_title, #custom_product_price, #custom_product_quantity').val('');
                $('#custom_product_quantity').val(1);
                openModal('modal-add_custom_product');
                $('#custom_product_title').focus();
            });
            $('#acf_order_info').click(function (event) {
                var source = $('#tmpl-order-info').html();
                var template = Handlebars.compile(source);
                var html = template(CUSTOMER);
                $("#order_details").html(html);
                $.each(CUSTOMER.custom_order_fields, function (key, val) {
                    var a_el = $('#order_details #pos_order_fields :input[id^="acf-field-' + key + '"]');
                    if (a_el.length) {
                        if (a_el.first().is(':radio') || a_el.first().is(':checkbox')) {
                            a_el.each(function (index, el) {
                                if (Array.isArray(val)) {
                                    if (val.indexOf($(el).val()) > -1) {
                                        $(el).attr('checked', 'checked').trigger('change');
                                    }
                                } else if ($(el).val() == val) {
                                    $(el).attr('checked', 'checked').trigger('change');
                                }
                            });
                        } else {
                            a_el.val(val).trigger('change');
                        }
                    }
                });
                openModal('modal-acf_order_information');
            });
            $('#add_dining_option').click(function (event) {
                openModal('modal-dining_option');
            });
            $('.wc_pos_register_notes').on('click', function () {
                var customer_note = CART.customer_note;
                $('#order_comments').val(customer_note);
                openModal('modal-order_comments');
                $('#order_comments').focus();
            });
            $('#save_order_comments').on('click', function () {
                var note = $('#order_comments').val();
                if (CART.customer_note == '' && note != '') {
                    APP.showNotice(pos_i18n[30], 'success');
                } else if (CART.customer_note != '' && note == '') {
                    APP.showNotice(pos_i18n[32], 'success');
                } else if (CART.customer_note != '' && note != '') {
                    APP.showNotice(pos_i18n[31], 'success');
                }
                CART.customer_note = note;
                CUSTOMER.additional_fields["order_comments"] = note;
                if (typeof POS_TRANSIENT.save_order != 'undefined' && POS_TRANSIENT.save_order === true) {
                    APP.createOrder(false);
                }
                closeModal('modal-order_comments');
            });
            $('.close-order-comments').on('click', function () {
                POS_TRANSIENT.save_order = false;
            });
            $('#save_order_discount, #inline_order_discount .keypad-close').click(function (e) {
                var $keyentry = $('#inline_order_discount .keypad-keyentry');
                var discount_prev = $keyentry.val();
                if (!discount_prev) {
                    discount_prev = $('.keypad-discount_val2').text();
                }
                var amount = parseFloat(discount_prev);
                var symbol = $('#order_discount_symbol').val();
                if (symbol == 'percent_symbol') {
                    var type = 'percent';
                } else {
                    var type = 'fixed_cart';
                }
                let reason = $('#discount-reason').val();
                var text = false;
                if (reason != 'none') {
                    text = $('#discount-reason option[value="' + reason + '"]').text();
                }
                CART.add_custom_discount(amount, type, false, text);
                closeModal('modal-order_discount');
                return false;
            });
            $('#apply_coupon_btn').click(function (e) {
                var coupon_code = $('#coupon_code').val();
                CART.add_discount(coupon_code.toLowerCase().trim());
                $('#coupon_code').val('');
                return false;
            });
            $('#coupon_code').keypress(function (event) {
                if (event.which == 13) {
                    var coupon_code = $('#coupon_code').val();
                    CART.add_discount(coupon_code.toLowerCase().trim());
                    $('#coupon_code').val('');
                    return false;
                }
            });
            $('#retrieve_sales').click(function () {
                retrieve_sales();
                return false;
            });

            $('#tabs_page').on('click', function () {
                openModal('modal-tabs');
                return false;
            });

            $('#btn_retrieve_from').click(function () {
                var register_id = $('#bulk-action-retrieve_sales').val();
                var register_name = $('#bulk-action-retrieve_sales option:selected').data('name');
                retrieve_sales(register_id, register_name);
                return false;
            });
            $('#orders-search-submit').click(function () {
                var register_id = $('#bulk-action-retrieve_sales').val();
                var register_name = $('#bulk-action-retrieve_sales option:selected').data('name');
                var search = $('#orders-search-input').val();
                retrieve_sales(register_id, register_name, search);
                return false;
            });
            $('#modal-retrieve_sales').on('keypress', '#orders-search-input', function (e) {
                if (e.which == 13) {
                    var register_id = $('#bulk-action-retrieve_sales').val();
                    var register_name = $('#bulk-action-retrieve_sales option:selected').data('name');
                    var search = $('#orders-search-input').val();
                    retrieve_sales(register_id, register_name, search);
                    return false;
                }
            });
            $('#modal-retrieve_sales').on('keypress', '#current-page-selector', function (e) {
                if (e.which == 13) {
                    var count = parseInt($(this).data('count'));
                    var reg_id = $(this).data('reg_id');
                    var page = parseInt($(this).val());
                    var max_c = Math.ceil(count / 20);
                    if (page > max_c) {
                        page = max_c;
                    } else if (page <= 0) {
                        page = 1;
                    }
                    APP.getOrdersListContent({count: count, currentpage: page, reg_id: reg_id})
                    return false;
                }
            });
            $(document.body).on('click', '.show_order_items', function () {
                $(this).closest('td').find('table').toggle();
                return false;
            });
            $(document.body).on('click', '.load_order_data', function () {
                var order_id = parseInt($(this).attr('href'));
                APP.loadOrder(order_id);
                closeModal('modal-retrieve_sales');
                return false;
            });

            $(document.body).on('click', 'a.reprint_receipts', function () {
                var url = $(this).attr("href");
                var start_print = false;
                var print_document = '';
                $.get(url + '#print_receipt', function (data) {
                    print_document = data;
                    if ($('#printable').length)
                        $('#printable').remove();
                    var newHTML = $('<div id="printable">' + print_document + '</div>');

                    $('body').addClass('print_receipt').append(newHTML);
                    if ($('#print_barcode img').length) {
                        var src = $('#print_barcode img').attr('src');
                        if (src != '') {
                            $("<img>").load(function () {
                                window.print();
                                $('#printing_receipt').hide();
                            }).attr('src', src);
                        } else {
                            window.print();
                            $('#printing_receipt').hide();
                        }
                    }
                    else if ($('#print_receipt_logo').length) {
                        var src = $('#print_receipt_logo').attr('src');
                        if (src != '') {
                            $("<img>").load(function () {
                                window.print();
                                $('#printing_receipt').hide();
                            }).attr('src', src);
                        } else {
                            window.print();
                            $('#printing_receipt').hide();
                        }
                    }
                    else {
                        window.print();
                        $('#printing_receipt').hide();
                    }
                });
                return false;
            });

            $('.wc_pos_register_save').on('click', function (e) {

                e.preventDefault();

                if (CART.is_empty()) {
                    APP.showNotice(pos_i18n[9], 'error');
                    return false;
                }

                if (wc_pos_params.order_prompt === true) {


                    if (wc_pos_params.signature_panel == true && wc_pos_params.signature_required == true && wc_pos_params.signature_required_on.indexOf('save') != -1) {
                        var args = {
                            content: $('#tmpl-signature').html(),
                            confirm: APP.createOrder
                        };
                        openConfirm(args);
                    } else {
                        var args = {
                            content: $('#tmpl-confirm-save-order').html(),
                            confirm: APP.createOrder
                        };
                        openConfirm(args);
                    }


                } else if (CART.customer_note == '' && typeof note_request !== 'undefined' && note_request == 1) {

                    openModal('modal-order_comments');
                    $('#order_comments').focus();
                    POS_TRANSIENT.save_order = true;

                } else {

                    if (wc_pos_params.signature_panel == true && wc_pos_params.signature_required == true && wc_pos_params.signature_required_on.indexOf('save') != -1) {
                        var args = {
                            content: $('#tmpl-signature').html(),
                            confirm: APP.createOrder
                        };
                        openConfirm(args);
                    } else {
                        APP.createOrder(false);
                    }
                }
            });
            $('#wc-pos-actions').on('click', '.wc_pos_register_discount', function () {
                openModal('modal-order_discount');
            });
            $('#wc-pos-actions').on('click', '#wc_pos_create_refund', function () {
                openModal('modal-refund');
            });
            $('#wc-pos-actions').on('click', '.wc_pos_register_coupon', function () {
                openModal('modal-order_coupon');
            });
            $('#wc-pos-actions').on('click', '.wc_pos_register_custom_fee', function () {
                openModal('modal-add_custom_fee');
                $('#fee-name').focus();
            });
            $('.wc_pos_register_pay').on('click', function () {
                $('#less-amount-notice').hide();
                var cart_total = CART.total;
                if (cart_total > CART.spend_limit) {
                    let error = new Error(sprintf(pos_i18n[47], CART.spend_limit));
                    APP.showNotice(error + currency_symbol, 'error');
                    return false;
                }
                if (CART.is_empty()) {
                    APP.showNotice(pos_i18n[9], 'error');
                    return false;
                } else if (!wc_pos_params.guest_checkout && !CUSTOMER.customer && empty(CUSTOMER.email)) {
                    APP.showNotice(pos_i18n[42], 'error');
                    return false;
                } else if (CUSTOMER.customer) {
                    var requiredFields = wc_pos_params.customer_required_fields;

                    for (var i = 0; i < requiredFields.length; i++) {
                        var field, type;
                        if (requiredFields[i].indexOf('billing_') === 0) {
                            field = requiredFields[i].replace('billing_', '');
                            type = 'billing';
                        } else if (requiredFields[i].indexOf('shipping_') === 0) {
                            field = requiredFields[i].replace('shipping_', '');
                            type = 'shipping';
                        }
                        if (CUSTOMER[type + '_address'][field] == '') {
                            APP.showNotice(pos_i18n[63], 'error');
                            return false;
                        }
                    }
                }

                var wc_pay = function () {
                    $('#modal-order_payment input.select_payment_method').removeAttr('disabled');
                    $('#modal-order_payment .media-menu a').first().click();

                    var switches = $('#payment_switch_wrap .payment_switch');
                    if (switches.length) {
                        switches.bootstrapSwitch();
                    }

                    var pos_chip_pin = $('.pos_chip_pin_order_generate');
                    pos_chip_pin.find('span').remove();
                    pos_chip_pin.find('#generate_order_id').show();
                    if (parseInt(POS_TRANSIENT.order_id) > 0) {
                        pos_chip_pin.append('<span>' + POS_TRANSIENT.order_id + '</span>');
                        pos_chip_pin.find('#generate_order_id').hide();
                    }
                    pos_chip_pin.find('#generate_order_id').one('click', function (e) {
                        var blocker = $("#modal-order_payment");
                        blocker.block({
                            message: null,
                            overlayCSS: {
                                background: '#fff',
                                opacity: 0.6
                            }
                        });

                        $.ajax({
                            url: wc_pos_params.ajax_url,
                            type: 'POST',
                            data: {
                                action: 'wc_pos_generate_order_id',
                                register_id: pos_register_data.ID
                            },
                            success: function (response) {
                                if (typeof POS_TRANSIENT.order_id != 'undefined') {
                                    POS_TRANSIENT.order_id = response.data.order_id
                                }
                                pos_register_data.order_id = response.data.order_id;
                            },
                            error: function (error) {
                                console.log(error);
                            },
                            complete: function () {
                                blocker.unblock();

                                var chip_pin_order_id = pos_register_data.prefix + String(pos_register_data.order_id) + pos_register_data.suffix;
                                if (typeof POS_TRANSIENT.order_id != 'undefined' && POS_TRANSIENT.order_id > 0) {
                                    chip_pin_order_id = pos_register_data.prefix + String(POS_TRANSIENT.order_id) + pos_register_data.suffix;
                                }
                                pos_chip_pin.find('span').remove();
                                pos_chip_pin.append('<span>' + chip_pin_order_id + '</span>').find('#generate_order_id').hide();
                            }
                        });

                    });

                    // if ($('.pos_chip_pin_order_id').length) {
                    //     var chip_pin_order_id = pos_register_data.prefix + String(pos_register_data.order_id) + pos_register_data.suffix;
                    //     if (typeof POS_TRANSIENT.order_id != 'undefined' && POS_TRANSIENT.order_id > 0) {
                    //         chip_pin_order_id = pos_register_data.prefix + String(POS_TRANSIENT.order_id) + pos_register_data.suffix;
                    //     }
                    //     $('.pos_chip_pin_order_id').text(chip_pin_order_id);
                    // }

                    $('#amount_pay_cod, #amount_change_cod').val('');

                    var round_total = cart_total;
                    var h = cart_total % wc_pos_params.wc_pos_rounding_value;
                    if (wc_pos_params.wc_pos_rounding) {
                        if (h >= wc_pos_params.wc_pos_rounding_value / 2) {
                            round_total = cart_total - h + parseFloat(wc_pos_params.wc_pos_rounding_value);
                        } else {
                            round_total = cart_total - h;
                        }
                    }
                    if (wc_pos_params.wc_pos_rounding) {
                        CART.total = round_total;
                        $('#show_total_amt_inp').val(round_total);
                        var total_text = $('#show_total_amt .amount').text();
                        var old_value = total_text.replace(/[0-9.,]+/, cart_total.toFixed(2));
                        var new_value = total_text.replace(/[0-9.,]+/, round_total.toFixed(2));
                        $('#show_total_amt .amount').text(new_value);
                        $('.payment_methods').on('click', function () {
                            var bind = $(this).data('bind');
                            if (bind != 'cod') {
                                $('#show_total_amt_inp').val(cart_total);
                                $('#show_total_amt .amount').text(old_value);
                                CART.total = cart_total;
                            } else {
                                $('#show_total_amt_inp').val(round_total);
                                $('#show_total_amt .amount').text(new_value);
                                CART.total = round_total;
                            }
                        });
                    } else {
                        $('#show_total_amt_inp').val(CART.total);
                    }
                    if (CART.customer_note == '' && typeof note_request !== 'undefined' && note_request == 2) {
                        openModal('modal-order_comments');
                        $('#order_comments').focus();
                        return;
                    }
                    $('#payment_switch').bootstrapSwitch('state', print_receipt);
                    var _email_receipt = false;
                    switch (email_receipt) {
                        case 1:
                            _email_receipt = true;
                            break;
                        case 2:
                            _email_receipt = !CUSTOMER.customer ? false : true;
                            break;
                    }
                    $('#payment_email_receipt').bootstrapSwitch('state', _email_receipt);
                    jQuery('#modal-order_payment .payment_method_cod .keypad-clear').click();
                    openModal('modal-order_payment');
                }

                if (wc_pos_params.signature_panel == true && wc_pos_params.signature_required == true && wc_pos_params.signature_required_on.indexOf('pay') != -1) {

                    var args = {
                        content: $('#tmpl-signature').html(),
                        confirm: wc_pay
                    };
                    openConfirm(args);
                } else {
                    wc_pay();
                }
            });
            $('#modal-order_payment').on('click', 'input.go_payment', function () {
                var selected_pm = $('.select_payment_method:checked').val();

                if (selected_pm == 'pos_chip_pin' && jQuery("#generate_order_id:visible").length) {
                    APP.showNotice(pos_i18n[59], 'error');
                    return false;
                }

                if (selected_pm == '' || selected_pm == undefined) {
                    APP.showNotice(pos_i18n[10], 'error');
                    return false;
                }
                var total_amount = parseFloat($("#show_total_amt_inp").val());
                var amount_pay = parseFloat($('#amount_pay_cod').val());

                if (($('#amount_pay_cod').val() == '' || parseFloat(amount_pay.toFixed(2)) < parseFloat(total_amount.toFixed(2))) && selected_pm == 'cod' && !$('#less-amount-notice').data('approve')) {
                    var difference = total_amount.toFixed(2) - amount_pay.toFixed(2);
                    jQuery('#amount_change_cod').val(-difference.toFixed(2)).addClass('error');
                    $('#less-amount-notice').fadeIn();
                    //APP.showNotice(pos_i18n[11], 'error');
                    return false;
                } else if (selected_pm === 'wc_pos_paymentsense') {
                    if (empty(wc_pos_params.payment_sense_url)) {
                        APP.showNotice('Invalid Paymentsense Host Address.', 'error');
                        return false;
                    }
                    if (empty(wc_pos_params.payment_sense_api_key)) {
                        APP.showNotice('Invalid Paymentsense API Key.', 'error');
                        return false;
                    }
                    if (empty(pos_register_data.paymentsense_terminal) || pos_register_data.paymentsense_terminal == "none") {
                        APP.showNotice('No terminal configured for this register.', 'error');
                        return false;
                    }
                } else if(selected_pm == 'accountfunds'){
                    var accountFunds = !CUSTOMER.account_funds ? 0 : parseFloat(CUSTOMER.account_funds);
                    if(accountFunds < CART.total){
                        APP.showNotice('Account fund insufficient.', 'error');
                        return false;
                    }
                } else {
                    jQuery('#amount_change_cod').removeClass('error');
                    var res = ADDONS.validatePayment(selected_pm);
                    if (!res) {
                        APP.showNotice(pos_i18n[34], 'error');
                        return false;
                    }
                }
                var paymentSenseData = {},
                    doOrder = true,
                    isTimedOut = false;

                delete $.ajaxSettings.headers["X-WP-Nonce"];

                $.when(APP.paymentProcessBeforeCheckout()).then(function (response) {
                    if (response.transactionResult === "SUCCESSFUL") {
                        paymentSenseData['transactionId'] = response.transactionId;
                    } else if (response.transactionResult === "TIMED_OUT") {
                        doOrder = false;
                        isTimedOut = true;
                    } else {
                        doOrder = false;
                        if (response.transactionResult) {
                            APP.showNotice('The payment has failed due to ' + response.transactionResult, 'error');
                        }
                    }
                }, function (error) {
                    if (error.showPaymentMethods) {
                        doOrder = false;
                        var message = "";
                        if (error.responseJSON) {
                            if (error.responseJSON.messages) {
                                if(error.responseJSON.messages.error){
                                    message = error.responseJSON.messages.error[0];
                                }

                                if(empty(message)){
                                    var messages = Object.keys(error.responseJSON.messages);
                                    $.each(messages, function(i, msg){
                                        message = error.responseJSON.messages[msg];
                                    });
                                }

                            }
                        }
                        message = empty(message) ? error.statusText : message;
                        APP.showNotice(message, 'error');
                    } else {
                        doOrder = true;
                    }
                }).always(function () {

                    $.ajaxSettings.headers["X-WP-Nonce"] = wc_pos_params.rest_nonce;

                    $('#modal-order_payment, #post-body').unblock();

                    paymentSenseDeferred = $.Deferred();
                    paymentSenseNotifications = [];
                    paymentSenseTimeout = null;
                    paymentSenseTransaction = true;
                    paymentSenseSignature = false;

                    if (doOrder) {
                        if ($('#payment_email_receipt').bootstrapSwitch('state') && CUSTOMER.billing_address.email == '') {
                            args = {
                                content: $('#tmpl-prompt-email-receipt').html(),
                                cancel: function (answer) {
                                    APP.createOrder(true, paymentSenseData);
                                },
                                confirm: function (answer) {
                                    if (answer != '') {
                                        CUSTOMER.additional_fields['pos_payment_email_receipt'] = answer;
                                    }
                                    APP.createOrder(true, paymentSenseData);
                                }
                            };
                            openPromt(args);
                        } else {
                            if ($('#payment_email_receipt').bootstrapSwitch('state') && CUSTOMER.billing_address.email != '') {
                                CUSTOMER.additional_fields['pos_payment_email_receipt'] = CUSTOMER.billing_address.email;
                            }
                            APP.createOrder(true, paymentSenseData);
                        }
                    } else {

                        if (isTimedOut === true) {
                            openPromt({
                                content: '<p><strong>Timed Out</strong></p>' +
                                    '<p>Communication with the PDQ has failed and payment may or may not have been taken. Please manually check if the transaction was successful on the PDQ.</p><br/>' +
                                    '<p><strong>Note: </strong>if you wanna confirm the transaction manually please enter the unique key and confirm or cancel to choose another payment method.</p>',
                                cancel: function () {
                                    openModal('modal-order_payment')
                                },
                                confirm: function (answer) {
                                    paymentSenseData['transactionId'] = answer;
                                    if ($('#payment_email_receipt').bootstrapSwitch('state') && CUSTOMER.billing_address.email == '') {
                                        args = {
                                            content: $('#tmpl-prompt-email-receipt').html(),
                                            cancel: function (answer) {
                                                APP.createOrder(true, paymentSenseData);
                                            },
                                            confirm: function (answer) {
                                                if (answer != '') {
                                                    CUSTOMER.additional_fields['pos_payment_email_receipt'] = answer;
                                                }
                                                APP.createOrder(true, paymentSenseData);
                                            }
                                        };
                                        openPromt(args);
                                    } else {
                                        if ($('#payment_email_receipt').bootstrapSwitch('state') && CUSTOMER.billing_address.email != '') {
                                            CUSTOMER.additional_fields['pos_payment_email_receipt'] = CUSTOMER.billing_address.email;
                                        }
                                        APP.createOrder(true, paymentSenseData);
                                    }
                                }
                            });
                            return;
                        }

                        openModal('modal-order_payment');
                        return doOrder;
                    }
                });
            });
            $('#lock_register').click(function (event) {
                $('#unlock_password').val('');
                openModal('modal-lock-screen');
                APP.setCookie('pos_lockScreen', 'yes', 30);
                return false;
            });
            $('#unlock_button').click(function (event) {
                unlockScreen();
                return false;
            });
            $('#unlock_password').keypress(function (e) {
                if (e.which == 13) {
                    unlockScreen();
                }
            });
            $('#edit_wc_pos_registers').on('click', '.span_clear_order_coupon', function (e) {
                var $row = $(this).closest('tr');
                var coupon_code = $row.data('coupon');
                if (CART.remove_coupon(coupon_code, true)) {
                    $row.remove();
                }

            });

            $('#edit_wc_pos_registers').on('click', '.remove-fee', function (e) {
                var $row = $(this).closest('.fee-tr');
                var fee = $row.data('fee');
                if (CART.remove_fee(fee)) {
                    $row.remove();
                    CART.calculate_totals();
                }
            });

            $(document.body).on('change', '#product_type', function () {
                var type = $(this).val();
            });

            if (wc_pos_params.ready_to_scan == 'yes') {
                $(document).anysearch({
                    searchSlider: false,
                    //05.02.2018 - twice scanning
                    /*isBarcode: function (barcode) {
                     if (!$('.md-modal.md-show').length) {
                     searchProduct(barcode);
                     }
                     },*/
                    searchFunc: function (search) {
                        if (!$('.md-modal.md-show').length) {
                            searchProduct(search);
                        }
                    },
                });
            }

            $('#order_items_list').on('change', '.product_price', function (e) {
                changeProductPrice($(this));
            });
            $('#order_items_list').on('keyup', '.product_price', function (e) {
                changeProductPrice($(this));
            });

            $('#edit_wc_pos_registers').css('visibility', 'visible');
            runTips();
            ADDONS.init();
            $('#modal-1, .md-overlay-logo').remove();
            lockScreen();

            $(".dining-option-selector").click(function () {
                wc_pos_dining_option = $(this).attr('data-option');
                $('.dining-option-selector').removeClass('checked');
                $(this).addClass('checked');
            });

            $('#save_dining_option').click(function () {
                $('.selected-dining').html(wc_pos_dining_option);
                closeModal('modal-dining_option');
            });

            $("#wc-pos-register-buttons").on("click", ".wc_pos_register_refund", function () {
                $.when(APP.processRefundAmountBeforeRefunds()).then(function (response) {
                    if (response.transactionResult === "SUCCESSFUL") {
                        POS_CART.pos_refund.order["pos_refund_payment"] = {};
                        POS_CART.pos_refund.order.pos_refund_payment["transaction_id"] = response.transactionId;
                    } else {
                        if (response.transactionResult) {
                            APP.showNotice('The refund payment has failed due to ' + response.transactionResult, 'error');
                        }
                    }
                }).always(function () {

                    paymentSenseDeferred = $.Deferred();
                    paymentSenseNotifications = [];
                    paymentSenseTimeout = null;
                    paymentSenseTransaction = true;
                    paymentSenseSignature = false;

                    APP.create_refund();
                });
            });
        },
        setUpCustomerModal: function (_openModal, setAccountFunds, event) {
            if (event && event.originalEvent) {
                var source = $('#tmpl-form-add-customer').html();
                var template = Handlebars.compile(source);
                var html = template(CUSTOMER);
                $('#customer_details').html(html);
            }
            var tabs = $('#modal-order_customer .nav-tab-wrapper');
            if (setAccountFunds === true) {
                tabs.find('a[href=#pos_account_fund_fields]').click();
            } else {
                tabs.find('a').first().click();
            }
            var button_text = pos_i18n[54];
            if (CUSTOMER.customer) {
                button_text = pos_i18n[55];
            }
            if (CUSTOMER.create_account === true) {
                $('#modal-order_customer').find('#createaccount').prop('checked', 'checked');
            }
            if (CUSTOMER.id > 0) {
                $('#modal-order_customer').find('label[for="createaccount"]').hide();
            } else {
                $('#modal-order_customer').find('label[for="createaccount"]').show();
            }
            jQuery('button#save_customer').text(button_text);
            if (_openModal) {
                openModal('modal-order_customer');
            }
            wc_country_select_select2();
            jQuery(document).trigger('acf/setup_fields', [jQuery('#pos_custom_fields')]);
            if (sizeof(wc_country_select_params.allowed_countries) > 1) {
                var shipping_country = empty(CUSTOMER.shipping_address.country) ? CUSTOMER.default_country : CUSTOMER.shipping_address.country,
                    billing_country = empty(CUSTOMER.billing_address.country) ? CUSTOMER.default_country : CUSTOMER.billing_address.country,
                    billing_state = empty(CUSTOMER.billing_address.state) ? CUSTOMER.default_state : CUSTOMER.billing_address.state;

                $('#shipping_country').val(shipping_country).trigger('change');
                $('#billing_country').val(billing_country).trigger('change');
                $('#billing_state').val(billing_state).trigger('change');
            }

            if (CUSTOMER.account_funds && !empty(CUSTOMER.account_funds)) {
                $("#account-funds-data").children('strong').html(accountingPOS(CUSTOMER.account_funds, 'formatMoney'));
            } else {
                $("#account-funds-data").children('strong').html(accountingPOS(0, 'formatMoney'));
            }
            var modal_order_customer = $("#modal-order_customer");
            modal_order_customer.on("change", "#billing_country, #shipping_country", function () {
                var country = $(this).val(),
                    type = $(this).is("#shipping_country") ? "shipping" : "billing";

                $("#customer_details").block({
                    message: null,
                    overlayCSS: {
                        background: '#fff',
                        opacity: 0.6,
                        "z-index": 99999
                    }
                });

                $.ajax({
                    url: wc_pos_params.ajax_url,
                    type: "POST",
                    data: {
                        action: 'wc_pos_get_states_country',
                        country: country,
                        type: type
                    },
                    success: function(response){
                        var responseField = $(response.data),
                            jsonFieldName = type + '_address',
                            stateField = $("#" + type + "_state_field");

                        var existingVal = $("#" + type + "_state").val(),
                            stateVal = existingVal ? existingVal : CUSTOMER[jsonFieldName].state;

                        stateField.remove();
                        responseField.find('select').select2();
                        responseField.find('#' + type + '_state').val(stateVal).change();

                        $("#" + type + "_city_field").after(responseField);

                    },
                    complete: function(){
                        $("#customer_details").unblock();
                    }
                });
            });
            if (wc_pos_params.customer_saving === 'yes' && CUSTOMER.id === 0) {
                $('#createaccount').prop('checked', true);
            }
            $.each(CUSTOMER.acf_fields, function (key, val) {
                var field = Array.isArray(val) ? '[name = "acf-field-' + key + '[]"]' : '#acf-field-' + key;
                var a_el = $('#customer_details #pos_custom_fields ' + field );
                if (a_el.length) {
                    if (a_el.first().is(':radio') || a_el.first().is(':checkbox')) {
                        a_el.each(function (index, el) {
                            if ($(el).val() == val) {
                                $(el).attr('checked', 'checked').trigger('change');
                            }
                        });
                    } else {
                        a_el.val(val).trigger('change');
                    }
                }
            });
            runTips();
        },
        setup_refund_elements: function (destroy) {

            if (typeof destroy === "undefined") {
                destroy = false;
            }

            $(".wc_pos_register_refund").remove();
            var pay_btn = $("#wc-pos-register-buttons").find('.wc_pos_register_pay');
            var refund_btn = pay_btn.clone();
            var refund_modal_container = $("#poststuff_stock");

            if (destroy == true) {
                pay_btn.show();
                pay_btn.next('.wc_pos_register_refund').remove();

                refund_modal_container.find('tbody').html("");
                refund_modal_container.find('#order-details span').html("");
                refund_modal_container.find("#order_id").val("");
                $("#pos_register_buttons .order-remove").remove();

                return;
            }

            pay_btn.hide();
            pay_btn.parent().append(refund_btn);
            refund_btn.removeClass("wc_pos_register_pay").addClass("wc_pos_register_refund").show();
            refund_btn.text("Refund");
        },
        is_not_list: function (product) {
            if (pos_grid.grid_id == "all" || pos_grid.grid_id != "categories") {
                return $(".hndle").find("span.attr_title, span.cat_title").length < 1;
            } else if (pos_grid.grid_id == "categories") {
                var category = $('.cat_title:not(#wc-pos-register-grids-title)').last().data('parent');
                if (typeof product !== "undefined") {
                    var catIds = [];
                    $('.hndle .cat_title').not('#wc-pos-register-grids-title').each(function (i, el) {
                        catIds.push(parseInt($(el).data('parent')));
                    });
                    return $(".hndle").find("span.attr_title").length < 1 && APP.categoriesEqual(catIds.sort(), product.categories_ids.sort());
                } else {
                    return $(".hndle").find("span.attr_title").length < 1 && typeof category !== "undefined";
                }
            }
        },
        getOffSet: function (type) {
            if (pos_grid.grid_id == "all") {
                return APP.lastOffset['allProducts'];
            } else if (pos_grid.grid_id == "categories") {
                var category = $('.cat_title:not(#wc-pos-register-grids-title)').last().data('parent');
                return APP.lastOffset[category] ? APP.lastOffset[category] : 0;
            } else if (typeof type != "undefined") {
                return APP.lastOffset[type];
            } else {
                return APP.lastOffset["productGrids"];
            }
        },
        setOffSet: function (offset) {
            if (typeof offset === "undefined") {
                offset = 0;
            }

            if (pos_grid.grid_id == "all") {
                APP.lastOffset['allProducts'] += offset;
            } else if (pos_grid.grid_id == "categories") {
                var category = $('.cat_title:not(#wc-pos-register-grids-title)').last().data('parent');
                if(APP.lastOffset[category]){
                    APP.lastOffset[category] = APP.lastOffset[category] + offset;
                } else {
                    APP.lastOffset[category] = offset;
                }
            } else {
                APP.lastOffset['productGrids'] += offset;
            }
        },
        getLimit: function () {
            if (pos_grid.grid_id == "all") {
                return pos_grid.tile_limit;
            } else if (pos_grid.grid_id == "categories") {
                var category = $('.cat_title:not(#wc-pos-register-grids-title)').last().data('parent');
                if (isset(APP.limits[category])) {
                    return parseInt(APP.limits[category]);
                } else {
                    return 1;
                }
            } else {
                return Object.keys(pos_grid.tile_styles).length;
            }
        },
        getRequestLimit: function () {
            if (pos_grid.grid_id == "all" || pos_grid.grid_id != "categories") {
                tile_limit = getLimit();
                return tile_limit < 50 ? tile_limit : tile_limit - APP.getOffSet() < 50 ? tile_limit - APP.getOffSet() : 50;
            } else {
                return 50;
            }
        },
        getIncludes: function () {
            if (pos_grid.grid_id != "categories" || pos_grid.grid_id != "all") {
                return Object.keys(pos_grid.tile_styles)
            }
            return [];
        },
        checkPOSUserLogin: function (data) {
            if (!data.register_status_data || data.register_status_data === false) {
                return;
            }

            var loggedInUser = !empty(data.register_status_data.display_name) ? data.register_status_data.display_name : data.register_status_data.user_nicename;
            openModal("modal-locked-register");

            $("#modal-locked-register").find('.md-content').html("").html("<div>" + loggedInUser + " has taken over this register.</div>");
            setTimeout(function () {
                location.href = wc_pos_params.admin_url + "admin.php?page=wc_pos_registers&close=" + pos_register_data.ID + '&forced=true';
            }, 1500);
        },
        isTabVisible: function () {
            if (typeof document.hidden !== "undefined") {
                return document.hidden;
            }

            if (typeof document.webkitHidden !== "undefined") {
                return document.webkitHidden;
            }

            if (typeof document.msHidden !== "undefined") {
                return document.msHidden;
            }

            return false;
        },
        startInactivity: function () {
            APP.sessionTimeout = setTimeout(APP.doInactivity, wc_pos_params.auto_logout_session * 60000);
        },
        doInactivity: function () {
            var url = $("#close_register").attr('href');
            location.href = url;
        },
        isOnline: function () {
            if (typeof window.Offline === "undefined") return true;

            return window.Offline.state === "up";
        },
        categoriesEqual: function (arr1, arr2) {
            for (var i = arr2.length; i--;) {
                if (arr1.includes(arr2[i])) {
                    return true;
                }
            }

            return false;
        },
        process_que: function () {
            if (APP.category_que.length < 1 || APP.category_request) {
                return;
            }
            var cats = APP.category_que.slice(0, 10);
            APP.category_request = true;
            $.each(cats, function (i, cat) {
                APP.category_que.splice(0, 1);
                var $li = $("#category_" + cat);

                var index = '_' + cat;
                cat = pos_grid.categories[index];
                $li.css({
                    'background-image': 'url(' + cat.image + ')'
                }).removeClass('loading-cat').find('span').hide().html(cat.name).show(300);
                if (i == (cats.length - 1)) {
                    APP.category_request = false;
                    APP.process_que();
                }
            });
        },
        insertRelationships: function (product) {

            if (typeof product === "undefined") {
                return;
            }

            if (product.categories_ids.length > 0) {
                $.each(product.categories_ids, function (j, cat_id) {
                    if (typeof pos_grid.term_relationships.relationships[cat_id] !== "undefined") {
                        if (typeof pos_grid.term_relationships.relationships[cat_id][product.id] === "undefined") {
                            pos_grid.term_relationships.relationships[cat_id].push(product.id);
                        }
                    }
                });
            }
        }
    };

    function lockScreen() {
        var lock_screen = APP.getCookie('pos_lockScreen');
        if (wc_pos_params.lock_screen && lock_screen == 'yes') {
            openModal('modal-lock-screen');
            APP.setCookie('pos_lockScreen', 'yes', 30);
        }
    }

    function unlockScreen() {
        var pwd = $('#unlock_password').val();
        if (pwd == '') {
            toastr.error(pos_i18n[27]);
        } else {

            if (md5(pwd) != wc_pos_params.unlock_pass) {
                toastr.error(pos_i18n[28]);
            } else {
                closeModal('modal-lock-screen');
                APP.setCookie('pos_lockScreen', '', 30);
            }
        }
        $('#unlock_password').val('');
    }

    resizeCart = function () {  //TODO: need reworking this hardcode solution
        var h = $('#wc-pos-register-data').height();
        var sh = $('.wc_pos_register_subtotals').height();
        var lh = $('.woocommerce_order_items.labels').height();
        var h_cor = 0;
        if (sh !== subtotals_height) {
            subtotals_height = sh;
            h_cor = 8.5;
        }
        if (total_height === 0) {
            total_height = h;
            h_cor = 8.5;
        }
        if (total_height !== h) {
            if (total_height > h) {
                if (h_cor === 8.5) {
                    h_cor = 17;
                } else {
                    h_cor = 8.5;
                }
            }
            h = total_height;
        }
        $('div#order_items_list-wrapper').height(h - sh - lh - h_cor);
    };

    function resizeGrid() {
        var h = $('#wc-pos-register-data').height();
        var sub_h = $('.wc_pos_register_subtotals').height();
        var th = $('#order_items_th').height();
        if (pos_grid.second_column_layout == 'product_grids') {
            var h = parseFloat($('#wc-pos-register-grids').height()) - 39;
            var hh = 100;
            if (pos_grid.tile_layout == 'image_title_price') {
                hh = 123;
            }
            var _int = parseInt(h / hh);
            var _round = Math.round(h / hh);
            var count = _int * 5;

            if (h / hh >= (_int + 0.7)) {
                var count = _round * 5;
            }

            if ($('#grid_layout_cycle').length) {
                $('#grid_layout_cycle').height(h);
                $('#grid_layout_cycle').category_cycle('destroy');
                $('#grid_layout_cycle').category_cycle({
                    count: count,
                    hierarchy: pos_grid.term_relationships.hierarchy,
                    relationships: pos_grid.term_relationships.relationships,
                    parents: pos_grid.term_relationships.parents,
                    archive_display: pos_grid.category_archive_display,
                    breadcrumbs: $('#wc-pos-register-grids .hndle'),
                    breadcrumbs_h: $('#wc-pos-register-grids-title'),
                });
            }
        }
    }

    function retrieve_sales(reg_id, reg_name, search) {
        if (typeof reg_id == 'undefined') {
            reg_id = 'all';
            reg_name = pos_i18n[26];
        }
        if (typeof search == 'undefined') {
            search = '';
        }
        $('#modal-retrieve_sales h3 i').text(reg_name);
        $('#bulk-action-retrieve_sales').val(reg_id);
        $('#orders-search-input').val(search);

        $('#retrieve_sales_popup_inner').html('');
        $('#modal-retrieve_sales .wrap-button').html('');

        $('#retrieve-sales-wrapper .box_content').hide();
        $('#retrieve-sales-wrapper').block({
            message: null,
            overlayCSS: {
                background: '#fff',
                opacity: 0.6
            }
        });
        openModal('modal-retrieve_sales');
        $.when(APP.getServerOrders(reg_id, search)).then(function (orders) {
            if (orders.length > 0) {

                $('#retrieve-sales-wrapper .box_content').hide();
                $('#retrieve_sales_popup_inner').html('');
                $('#modal-retrieve_sales .wrap-button').html('');
                $('#retrieve-sales-wrapper').block({
                    message: null,
                    overlayCSS: {
                        background: '#fff',
                        opacity: 0.6
                    }
                });

                APP.db.putAll('orders', orders);

                var opt = {count: orders.length, currentpage: 1, reg_id: reg_id, search: search};
                var pager = getOrdersListPager(opt);
                $('#modal-retrieve_sales .wrap-button').html(pager);

                var source = $('#tmpl-retrieve-sales-orders-list').html();
                var template = Handlebars.compile(source);
                var html = template(orders);

                $('#retrieve-sales-wrapper .box_content').css('visibility', 'hidden').show();
                $('#retrieve_sales_popup_inner').html(html);

                var table_h = $('#retrieve_sales_popup_inner table').height();
                var wrapper_h = $('#retrieve-sales-wrapper .box_content').height();
                var nav_h = $('#retrieve-sales-wrapper .tablenav_wrap_top').height();

                if (table_h > (wrapper_h - nav_h)) {
                    $('#retrieve-sales-wrapper').addClass('big-size');
                } else {
                    $('#retrieve-sales-wrapper').removeClass('big-size');
                }
                $('#retrieve-sales-wrapper .box_content').removeAttr('style');
                runTips();
                $('#retrieve-sales-wrapper').unblock();


                // var opt = {count: result.count, currentpage: 1, reg_id: reg_id, search: search};
                //
                //APP.getOrdersListContent(opt);

            } else {
                $('#modal-retrieve_sales .wrap-button').html('');

                var source = $('#tmpl-retrieve-sales-orders-not-found').html();
                $('#retrieve_sales_popup_inner').html(source);
                $('#retrieve-sales-wrapper').removeClass('big-size');
                $('#retrieve-sales-wrapper .box_content').removeAttr('style').show();
                runTips();
                $('#retrieve-sales-wrapper').unblock();
            }
        });
    }

    function getOrdersListPager(opt) {

        opt.urls = {
            a: false,
            b: false,
            c: false,
            d: false
        };
        if (opt.count == 1) {
            opt.items = opt.count + ' ' + pos_i18n[25][0];
        } else {
            opt.items = opt.count + ' ' + pos_i18n[25][1];
        }
        opt.countpages = Math.ceil(opt.count / 20);
        if (opt.countpages > 1) {
            if (opt.currentpage > 1) {

                opt.urls.b = 'APP.getOrdersListContent({count: ' + opt.count + ', currentpage: ' + (opt.currentpage - 1) + ', reg_id: \'' + opt.reg_id + '\', search: \'' + opt.search + '\' })';
                if (opt.currentpage - 1 > 1) {
                    opt.urls.a = 'APP.getOrdersListContent({count: ' + opt.count + ', currentpage: 1, reg_id: \'' + opt.reg_id + '\', search: \'' + opt.search + '\' })';
                }
            }
            if (opt.currentpage != opt.countpages) {
                opt.urls.c = 'APP.getOrdersListContent({count: ' + opt.count + ', currentpage: ' + (opt.currentpage + 1) + ', reg_id: \'' + opt.reg_id + '\', search: \'' + opt.search + '\' })';
                if (opt.currentpage + 1 != opt.countpages) {
                    opt.urls.d = 'APP.getOrdersListContent({count: ' + opt.count + ', currentpage: ' + opt.countpages + ', reg_id: \'' + opt.reg_id + '\', search: \'' + opt.search + '\' })';
                }
            }
        } else {
            opt.count = false;
        }

        var source = $('#tmpl-retrieve-sales-orders-pager').html();
        var template = Handlebars.compile(source);
        var html = template(opt);
        return html;
    }

    changeProductPrice = function (el) {
        if (typeof change_price_timer != 'undefined') {
            clearTimeout(change_price_timer);
        }
        var price = el.val();
        jQuery('#discount-value').val(price);
        var cart_item_key = el.data('row');
        if (typeof CART.cart_contents[cart_item_key] != 'undefined') {

            if (typeof CART.cart_contents[cart_item_key].original_price == 'undefined') {
                CART.cart_contents[cart_item_key].original_price = parseFloat(CART.cart_contents[cart_item_key].price);
            }

            var order_id = POS_TRANSIENT.order_id ? POS_TRANSIENT.order_id : 0;
            APP.db.get('orders', order_id).then(function (order) {
                if(order && order.prices_include_tax === true){
                    $.each(order.line_items, function (i, line_item) {
                        if(line_item.id == CART.cart_contents[cart_item_key].data.item_id){
                            price = parseFloat(line_item.total) + parseFloat(line_item.total_tax) / line_item.quantity;
                        }
                    });
                }

                CART.cart_contents[cart_item_key].price = price;
                if (CART.cart_contents[cart_item_key].variation_id > 0) {
                    CART.cart_contents[cart_item_key].v_data.price = price;
                } else {
                    CART.cart_contents[cart_item_key].data.price = price;
                }
                CART.calculate_totals();
            });
        }
    };

    changeProductQuantity = function (el) {
        var qty = el.val();
        if (qty == '') {
            qty = 0;
        }
        var quantity = parseInt(qty);
        if (wc_pos_params.decimal_quantity == 'yes') {
            quantity = parseFloat(qty);
        }

        var cart_item_key = el.closest('tr').attr('id');
        if (typeof CART.cart_contents[cart_item_key] != 'undefined' && quantity > 0) {
            var old_quantity = CART.cart_contents[cart_item_key]['quantity'];
            var product_data = CART.cart_contents[cart_item_key]['v_data'] != 'undefined' ? CART.cart_contents[cart_item_key]['v_data'] : CART.cart_contents[cart_item_key]['data'];
            if (product_data === false)
                product_data = CART.cart_contents[cart_item_key]['data'];

            var checkStock = APP.checkStock(product_data, quantity, cart_item_key);


            if (checkStock === true) {
                CART.set_quantity(cart_item_key, quantity, true);
            } else {
                $(el).val(old_quantity);
            }
        }
    };

    if (pos_ready_to_start == true) {
        WindowStateManager = new WindowStateManager(false, windowUpdated);
    }

    jQuery('#createaccount').on('change', function () {
        if (jQuery(this).attr('checked')) {
            jQuery('#billing_account_username_field, #billing_account_password_field, #billing_password_confirm_field').show();
        } else {
            jQuery('#billing_account_username_field, #billing_account_password_field, #billing_password_confirm_field').hide();
        }
    });
    //Todo: pos_register_data.detail.opening_cash_amount === undefined
    if (pos_register_data.detail.float_cash_management == 1 &&
        pos_register_data.detail.opening_cash_amount && !pos_register_data.detail.opening_cash_amount.status) {
        openModal('modal-opening_cash_amount');
    }

    jQuery('#set_opening_cash_amount').on('click', function () {
        var amount = $('#opening_amount').val();
        var note = $('#opening_amount_note').val();
        $.ajax({
            type: 'POST',
            url: wc_pos_params.ajax_url,
            data: {
                action: 'wc_pos_set_register_opening_cash',
                amount: amount,
                note: note,
                register_id: pos_register_data.ID
            },
            success: function (responce) {
                closeModal('modal-opening_cash_amount');
            }
        });
    });

    jQuery('#full_screen').on('click', function (e) {
        e.preventDefault();
        var elem = document.getElementsByTagName("html")[0];
        element_fullscreen(elem);
    })

    $('#less-amount-notice .approve-less-amount').on('click', function () {
        $('#less-amount-notice').data('approve', '1').fadeOut();
    });
    jQuery('#order_items_list').on('click', '.item', function () {
        var id = jQuery(this).attr('id');
        if (jQuery('#item_note-' + id).hasClass('open')) {
            jQuery('tr.item_note.open').hide().removeClass('open');
        } else {
            jQuery('tr.item_note.open').hide().removeClass('open');
            jQuery('#item_note-' + id).show().addClass('open');
        }
    });

    jQuery('#order_items_list').on('change', 'input.item_note', function () {
        var cart = CART.get_cart();
        var item_key = jQuery(this).data('item');
        var value = jQuery(this).val();
        var meta = '';
        var variation = cart[item_key]['variation'];
        if (value != '' && item_key != '') {
            variation[pos_i18n[45]] = value;
            meta += '<li class="item_note_meta"><span class="meta_label">' + pos_i18n[45] + '</span><span class="meta_value">' + value + '</span></li>';
        }
        var display_meta = $('tr#' + item_key + ' td.name .display_meta');
        if (display_meta.length) {
            if (display_meta.find('.item_note_meta').length >= 1) {
                display_meta.find('.item_note_meta').html(meta)
            } else {
                display_meta.append(meta);
            }
        } else {
            meta = '<ul class="display_meta">' + meta + '</ul>';
            $('tr#' + item_key + ' td.name .view').append(meta);
        }
        cart[item_key]['variation'] = variation;
        CART.calculate_totals();
        jQuery('#item_note-' + item_key).hide();
    });

    jQuery('#select-shipping-method').on('change', function () {
        var price = (jQuery(this).find('option:selected').data('cost')) ? jQuery(this).find('option:selected').data('cost') : 0;
        jQuery('#custom_shipping_price').val(price);
        let title = jQuery(this).find('option:selected').text();
        jQuery('#custom_shipping_title').val(title);
    });

    $('.tab-tabs').on('click', '.tab', function (e) {
        $('.tab-tabs .tab').removeClass('active');
        $(this).addClass('active');
        let id = $(this).data('tab_id');
        $('.woocommerce_order_items_wrapper .tab').hide().removeClass('active');
        $('#tab-' + id).attr('style', 'display: table-cell;').addClass('active');
        APP.active_tab = id;
        if (typeof APP.tabs[id] === 'undefined') {
            APP.tabs[id] = new Tab(id);
        }
        APP.tabs[id].getCart(CART);
        let limit = jQuery('.woocommerce_order_items_wrapper .tab.active').data('spend_limit');
        if (limit > 0) {
            CART.spend_limit = limit;
        }
        CART.calculate_totals();
    });

    jQuery('body').on('click', '.is-keypad', function () {
        hideKeyboard($(this));
        if ($(this).hasClass('product_price')) {
            $('#discount-value').focus();
        } else {
            $(this).focus();
        }
    });

    $('.keypad-clear').on('click', function () {
        $("#less-amount-notice").fadeOut();
    });


});

function windowUpdated() {
    //"this" is a reference to the WindowStateManager
    if (this.isMainWindow()) {
        closeModal('modal-clone-window');
        if (APP.initialized === false) {
            APP.init();
            //process_offline_orders();
        }
    } else {
        openModal('modal-clone-window');
    }
}

function searchProduct(barcode) {
    var barcode = barcode.trim();

    jQuery.ajax({
        type: 'post',
        url: wc_pos_params.ajax_url,
        data: {
            barcode: barcode,
            action: 'wc_pos_scan_product',
        },
        success: function (response) {
            if (response.success && typeof response.data !== 'undefined' && response.data.length > 0) {
                // Always use the first result, even if multiple products found.
                response.data = response.data[0];

                // Index the product if not indexed, then add it to cart.
                APP.indexProduct({
                    id: response.data.id,
                    vid: (typeof response.data.vid != 'undefined' ? response.data.vid : 0),
                    variation: {},
                    quantity: wc_pos_params.decimal_quantity_value,
                }, true);
            } else {
                APP.showNotice(pos_i18n[36], 'error');
            }
        }
    });
}

function element_fullscreen(elem) {
    if (!document.webkitFullscreenElement && !document.fullscreenElement && !document.mozFullScreenElement && !document.msFullscreenElement) {
        if (elem.requestFullscreen) {
            elem.requestFullscreen();
        } else if (elem.mozRequestFullScreen) {
            elem.mozRequestFullScreen();
        } else if (elem.webkitRequestFullscreen) {
            elem.webkitRequestFullscreen();
        }
    } else {
        if (document.webkitExitFullscreen) {
            document.webkitExitFullscreen();
        } else if (document.exitFullscreen) {
            document.exitFullscreen();
        } else if (document.mozCancelFullScreen) {
            document.mozCancelFullScreen();
        } else if (document.msExitFullscreen) {
            document.msExitFullscreen();
        }
    }
}

function process_offline_orders() {
    APP.db.keys('offline_orders').then(function (keys) {
        if (keys.length) {
            openModal('modal-offline-orders');
            var progressbar = jQuery('.progressbar');
            progressbar.progressbar({
                max: keys.length
            });
            var sec = 1;
            jQuery.each(keys, function (index) {
                APP.db.get("offline_orders", keys[index]).done(function (record) {
                    setTimeout(function () {
                        var cart = {
                            order: record
                        };
                        APP.processPayment(cart, true, false, false);
                        APP.db.remove('offline_orders', keys[index]);
                        var progress_val = progressbar.progressbar("value");
                        progressbar.progressbar("value", progress_val + 1);
                    }, sec * 3000);//3 seconds delay to each order process
                    sec = sec + 1;
                });
            });
            setTimeout(function () {
                closeModal('modal-offline-orders');
            }, (keys.length + 1) * 3000);
        }
    });
}

function escapeHtml(string) {
    var entityMap = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;',
        '/': '&#x2F;',
        '`': '&#x60;',
        '=': '&#x3D;'
    };
    return String(string).replace(/[&<>"'`=\/]/g, function (s) {
        return entityMap[s];
    });
}

function hideKeyboard(elem) {
    document.activeElement.blur();
    jQuery(elem).blur();
}

function isValidMac(mac) {

    if (typeof mac == "undefined") return false;

    if (mac.length < 17) return false;

    if (mac.substr(2, 1) != ":" || mac.substr(5, 1) != ":" || mac.substr(8, 1) != ":" || mac.substr(11, 1) != ":" || mac.substr(14, 1) != ":") return false;

    if (mac.substring(0, 5) != '00:11') return false;

    return true;
}

function getLimit() {
    return APP ? APP.getLimit() : 1
}
