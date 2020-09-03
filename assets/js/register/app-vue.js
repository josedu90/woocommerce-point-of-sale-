(function ($) {
    const store = new Vuex.Store({
        modules: {
            product: {
                state: {
                    products: []
                },
                mutations: {
                    getProducts(state, products){
                        state.products = products;
                    },
                },
                actions: {
                    getProducts (context) {
                        $.ajax({
                            url: wc_pos_params.wc_api_url + 'products',
                            data: {
                                limit: 100,
                            },
                            headers: {
                                "X-WP-Nonce": wc_pos_params.rest_nonce
                            },
                            success: function(response, status, xhr){
                                context.commit('getProducts', response);
                            }
                        });

                    },
                },
            }
        }
    });
    new Vue({
        el: '#wc-pos-registers-edit',
        store: store,
        data: {
            message: 'Hello Vue!',
        },
        methods: {
            getProducts(){
                this.$store.dispatch("getProducts", {products: []});
            }
        },
        computed: {
            products(){
                return this.$store.product.products
            }
        },
        mounted: function(){
            this.getProducts();
        }
    });
})(jQuery);