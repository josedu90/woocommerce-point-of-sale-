<div class="md-modal md-dynamicmodal md-close-by-overlay md-register" id="modal-refund">
    <div class="md-content">
        <h1><?php _e('Refund', 'wc_point_of_sale'); ?><span class="md-close"></span></h1>
        <div class="full-height">
            <div class="two_cols">
                <div class="">
                    <form action="" method="post" id="put_wc_pos_barcode">
                        <p class="form-row form-row-wide">
                            <label for="product_barcode"><?php _e("Search Order Number", 'wc_point_of_sale'); ?></label>
                            <input type="text" id="product_barcode" name="product_barcode" value="" minlength="1" >
                            <input type="submit" value="<?php _e("Find Order Number", 'wc_point_of_sale'); ?>" class="button" id="find_product_by_barcode">
                        </p>
                    </form>
                </div>
                <div id="poststuff_stock" style="display: none">
                    <input type="hidden" name="order_id" id="order_id" value="">
                    <div id="order-details"><span class="order-number"></span><span class="order-date"></span></div>
                    <table class="wp-list-table widefat striped posts" id="barcode_options_table">
                        <thead>
                        <tr>
                            <th scope="col" id="name" class="manage-column column-name" colspan="2"><?php _e("Product", 'wc_point_of_sale'); ?></th>
                            <th scope="col" id="cost" class="manage-column column-cost"><?php _e("Cost", 'wc_point_of_sale'); ?></th>
                            <th scope="col" colspan="2" id="qty" class="manage-column column-qty"><?php _e("Qty", 'wc_point_of_sale'); ?></th>
                            <th scope="col" id="total" class="manage-column column-total"><?php _e("Total", 'wc_point_of_sale'); ?></th>
                            <th scope="col" id="vat" class="manage-column column-var"><?php _e("Vat", 'wc_point_of_sale'); ?></th>
                        </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>

            </div>
            <div id="refund_message">

            </div>
            <div class="clearfix"></div>
        </div>
        <div class="wrap-button">
            <span>
                <input class="input-checkbox" id="reduce_stock" type="checkbox" value="1"/><label for="reduce_stock" class="pos_register_toggle" id="create_new_account"></label>
			    <label for="reduce_stock"><?php _e('Restore Stock', 'wc_point_of_sale'); ?></label>
            </span>
            <button class="button button-primary wp-button-large alignright" type="button" id="refund_all"><?php _e('Return All', 'wc_point_of_sale'); ?></button>
            <button class="button button-primary wp-button-large alignright" type="button" id="load_refund_products"><?php _e('Refund Product', 'wc_point_of_sale'); ?></button>
        </div>
    </div>
</div>

<script type="text/javascript">

    var pos_ready = function () {
        jQuery(document).ready(function($){
            var container = $("#poststuff_stock");
            var loader = $("#modal-refund .full-height").first();
            $(document).anysearch({
                searchSlider: false,
                isBarcode: function(barcode) {
                    filter_product(barcode);
                },
                searchFunc: function(search) {
                    filter_product(search);
                },
            });
            $("#message").fadeOut();
            container.fadeOut();
            $("#put_wc_pos_barcode").on('submit', function(e) {
                if( $('#product_barcode').val() != '' ) {
                    $("#message").fadeOut();
                    var order_id = parseInt($('#product_barcode').val());
                    var barcode = typeof  order_id === "number" ? order_id : 0;
                    APP.destroy_refund();
                    filter_order(barcode);
                }
                return false;
            });

            container.on('click', '.increase_stock, .decrease_stock', function(e) {
                e.preventDefault();
                var qty_input = $(this).siblings('.qty');
                var value = !isNaN(parseInt(qty_input.val())) ? parseInt(qty_input.val()) : 0;
                if($(this).is('.increase_stock')){
                    value += 1;
                }else{
                    value -= 1;
                }
                qty_input.val(value).change();
            }).on('change', '.column-qty .qty', function(e){
                var max_qty = !isNaN(parseInt($(this).attr('max'))) ? parseInt($(this).attr('max')) : 0;
                if($(this).val() > max_qty){
                    APP.showNotice("Max refundable qty is " + max_qty, "error");
                    $(this).val(max_qty);
                }
                if($(this).val() < 0){
                    APP.showNotice("Min refundable qty is 0", "error");
                    $(this).val(0);
                }
            });

            $("#load_refund_products").on('click', function (e) {

                loader.block({
                    message: null,
                    overlayCSS: {
                        background: '#fff',
                        opacity: 0.6
                    }
                });

                var order_items = [];
                var qty_count = 0;
                $("#barcode_options_table tbody tr").each(function(i, el){
                    var row = $(el);
                    var item_data = row.data('item_data');
                    var qty = row.find('input.qty').val();
                    if(qty < 1){
                        return true;
                    }
                    var r_qty = item_data.quantity;
                    qty_count += parseFloat(qty);
                    item_data.price /= item_data.quantity;
                    item_data.quantity = r_qty;
                    item_data.qty = qty;
                    item_data.total = parseFloat(item_data.total).toFixed(2);
                    item_data.total_tax = parseFloat(item_data.total_tax).toFixed(2);
                    array_push(order_items, item_data);
                });

                if(qty_count < 1){
                    APP.showNotice("No items to refund", "error");
                    loader.unblock();
                    return;
                }

                var order_id = parseInt($("#order_id").val());
                APP.db.get('orders', order_id).done(function(order){
                    var items_array = {};
                    $.each(order_items, function (i, item) {
                        $.each(order.line_items, function (index, line_item) {
                            if (item.id == line_item.id) {
                                if (item.quantity > 0) {
                                    line_item["qty"] = item.quantity;
                                    line_item["refund_total"] = item.total;
                                    line_item["refund_tax"] = item.total_tax;
                                    items_array[line_item.id] = line_item;
                                    return true;
                                }
                                line_item.quantity = item.quantity;
                            }
                        });
                    });
                    order.line_items = items_array;
                    CART.setup_refund({
                        refund: true,
                        items: order_items,
                        order: order
                    });
                    closeModal("modal-refund");
                    loader.unblock();
                });
            });
            $("#refund_all").on("click", function(e){
                $("#barcode_options_table tbody tr").each(function(i, el){
                    var row = $(el);
                    var qty_el = row.find('input.qty');
                    var item_data = row.data('item_data');
                    var qty = qty_el.attr("max");

                    qty_el.val(qty);
                    item_data.quantity = qty;
                });

                $(this).next("button").click();
            });

            function decrease_stock () {
                var id = $('#product_id').val();
                var operation = 'decrease';
                var value = 1;

                if (value > 0) {
                    $("#message").fadeOut();
                    $('#wc_pos_stock_controller').block({
                        message: null, overlayCSS: {
                            background: '#fff', opacity: 0.6
                        }
                    });

                    data = {
                        action: 'wc_pos_change_stock', id: id, value: value, operation: operation
                    };
                    $.ajax({
                        type: 'post',
                        dataType: 'json',
                        url: ajaxurl,
                        data: data,
                        success: function (data, textStatus, XMLHttpRequest) {
                            if (data.status == 'success' && data.response) {
                                update_sku_controller_table(data.response);
                            }
                        },
                        error: function (MLHttpRequest, textStatus, errorThrown) {
                        },
                        complete: function (argument) {
                            $('#wc_pos_stock_controller').unblock();
                        }
                    });
                }
            }

            function filter_product(barcode)
            {
                $('#wc_pos_stock_controller').block({
                    message: null,
                    overlayCSS: {
                        background: '#fff',
                        opacity: 0.6
                    }
                });
                data = {
                    action: 'wc_pos_filter_product_barcode',
                    barcode: barcode
                };
                $.ajax({
                    type: 'post',
                    dataType: 'json',
                    url: ajaxurl,
                    data: data,
                    success: function (data, textStatus, XMLHttpRequest) {
                        if( data.status == 'success' && data.response ) {
                            update_sku_controller_table (data.response);
                        } else {
                            $("#message").html("Product wasn't found.");
                            $("#message").fadeIn();
                            $("#poststuff_stock").fadeOut();
                        }
                        decrease_stock();
                    },
                    error: function (MLHttpRequest, textStatus, errorThrown) {
                    },
                    complete : function (argument) {
                        $('#wc_pos_stock_controller').unblock();
                    }
                });
                $('#product_barcode').val('');
            }

            function update_sku_controller_table (order) {
                if(order.line_items){
                    $.each(order.line_items, function (i, product) {
                        var $html = "";
                        var product_id = product.product_id;
                        $html += '<tr data-product="' + product_id + '">';
                        $html += '<td class="thumb column-thumb" data-colname="Image">' +
                            '<div class="product_image product_image_' + product_id + '">' + product.image + '</div>' +
                            '</td>';
                        $html += '<td class="column-name name" data-colname="Name">' +
                            '<span class="product_name product_name_' + product_id + '">' + product.name + '</span>';
                        if(!empty(product.sku)){
                            $html += '<br/><small class="product_sku product_sku_' + product_id + '">' + product.sku + '</small>';
                        }
                        $html += '</td>';
                        $html += '<td class="cost column-cost" data-colname="Cost">' +
                            '<div class="product_cost product_cost_' + product_id + '">' + accountingPOS(product.price, 'formatMoney') + '</div>' +
                            '</td>';
                        $html += '<td class="qty column-qty" data-colname="Qty">' +
                            '<input type="submit" value="+" class="increase_stock">' +
                            '<input class="qty product_qty_' + product_id + '" value="0" max="'+product.quantity+'">' +
                            '<input type="submit" value="-" class="decrease_stock">' +
                            '</td>';
                        $html += '<td class="qty-text column-qty-text" data-colname="Qty">' +
                            '<span class="product_qty_text product_qty_text_' + product_id + '">' + "&times;" + product.quantity + '</span>' +
                            '</td>';
                        $html += '<td class="total column-total" data-colname="Total">' +
                            '<div class="product_total product_total_' + product_id + '">' +accountingPOS(product.total + product.total_tax, 'formatMoney') + '</div>' +
                            '</td>';
                        $html += '<td class="vat column-vat" data-colname="Vat">' + accountingPOS(product.total_tax, 'formatMoney') +'</td>';
                        container.find('tbody').append($html);
                        container.find('#order-details').children('.order-number').html('<strong>Order No:</strong> ' + "#" + order.id);
                        container.find('#order-details').children('.order-date').html('<strong>Order Date:</strong> ' + order.order_date);
                        container.find('tr[data-product="' + product_id + '"]').data('item_data', product);
                    });
                }else{
                    $(".product_name_" + order.id).html(order.name);
                    $(".product_sku_" + order.id).html(order.sku);
                    $(".product_image_" + order.id).html(order.image);
                    $(".product_price_" + order.id).html(order.price);
                    $(".product_stock_" + order.id).html(order.stock_status);
                    $('.stock_value_' + order.id).val('');
                }
                container.fadeIn();
            }

            function filter_order(order_id){
                container.find('tbody').html("");
                container.find('#order-details span').html("");
                APP.db.get('orders', order_id).done(function(order){
                    if(order){
                        if(order.status === "refunded"){
                            APP.showNotice("Order already refunded!", "error");
                            return;
                        }
                        update_sku_controller_table (order);
                        $("#order_id").val(order_id);
                    }else{
                        loader.block({
                            message: null,
                            overlayCSS: {
                                background: '#fff',
                                opacity: 0.6
                            }
                        });
                        $.when(APP.getServerOrders('', order_id, 'any')).then(function (orders) {
                            if( orders.length > 0 ) {
                                APP.db.putAll('orders', orders);
                                if(orders[0].status === "refunded"){
                                    APP.showNotice("Order already refunded!", "error");
                                    return;
                                }
                                update_sku_controller_table (orders[0]);
                                $("#order_id").val(order_id);

                            } else {
                                APP.showNotice("Order not found!", "error");
                            }
                        }, function (jqXHR, textStatus, errorThrown) {
                            $("#order_id").val("");
                            APP.showNotice(jqXHR.responseJSON.data, 'error');
                        }).always(function(){
                            loader.unblock();
                        });
                    }
                });
                $('#product_barcode').val('');
            }

            function get_orders(order_id){
                var v = APP.makeid();
                var filter = {};
                filter['meta_key'] = 'wc_pos_id_register';
                filter['meta_value'] = '';
                filter['meta_compare'] = '!=';

                if(typeof order_id !== "undefined"){
                    filter['q'] = order_id;
                }

                var e = $.getJSON(wc_pos_params.wc_api_url + 'orders/', {
                    action: "wc_pos_json_api",
                    reg_id: 'all',
                    filter: filter,
                    status: "pending,processing,completed,on-hold"
                });

                return e;
            }

        });

    };
    document.addEventListener("DOMContentLoaded", pos_ready);

</script>
