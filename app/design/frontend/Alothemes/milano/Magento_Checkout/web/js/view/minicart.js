/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'uiComponent',
    'Magento_Customer/js/customer-data',
    'jquery',
    'ko',
    'underscore',
    'sidebar'
], function (Component, customerData, $, ko, _) {
    'use strict';

    var sidebarInitialized = false,
        addToCartCalls = 0,
        miniCart;

    miniCart = $('[data-block=\'minicart\']');
    miniCart.on('dropdowndialogopen', function () {
        initSidebar();
    });

    /**
     * @return {Boolean}
     */
    function initSidebar() {
        if (miniCart.data('mageSidebar')) {
            miniCart.sidebar('update');
        }

        if (!$('[data-role=product-item]').length) {
            return false;
        }
        miniCart.trigger('contentUpdated');

        if (sidebarInitialized) {
            return false;
        }
        sidebarInitialized = true;
        miniCart.sidebar({
            'targetElement': 'div.block.block-minicart',
            'url': {
                'checkout': window.checkout.checkoutUrl,
                'update': window.checkout.updateItemQtyUrl,
                'remove': window.checkout.removeItemUrl,
                'loginUrl': window.checkout.customerLoginUrl,
                'isRedirectRequired': window.checkout.isRedirectRequired
            },
            'button': {
                'checkout': '#top-cart-btn-checkout',
                'remove': '#mini-cart a.action.delete',
                'close': '#btn-minicart-close'
            },
            'showcart': {
                'parent': 'span.counter',
                'qty': 'span.counter-number',
                'label': 'span.counter-label'
            },
            'minicart': {
                'list': '#mini-cart',
                'content': '#minicart-content-wrapper',
                'qty': 'div.items-total',
                'subtotal': 'div.subtotal span.price',
                'maxItemsVisible': window.checkout.minicartMaxItemsVisible
            },
            'item': {
                'qty': ':input.cart-item-qty',
                'button': ':button.update-cart-item'
            },
            'confirmMessage': $.mage.__(
                'Are you sure you would like to remove this item from the shopping cart?'
            )
        });
    }

    return Component.extend({
        shoppingCartUrl: window.checkout.shoppingCartUrl,
        cart: {},

        /**
         * @override
         */
        initialize: function () {
            var self = this,
                cartData = customerData.get('cart');

            this.update(cartData());
            cartData.subscribe(function (updatedCart) {
                addToCartCalls--;
                this.isLoading(addToCartCalls > 0);
                sidebarInitialized = false;
                this.update(updatedCart);
                initSidebar();
            }, this);
            $('[data-block="minicart"]').on('contentLoading', function (event) {
                addToCartCalls++;
                self.isLoading(true);
            });
            if (cartData().website_id !== window.checkout.websiteId) {
                customerData.reload(['cart'], false);
            }

            return this._super();
        },
        isLoading: ko.observable(false),
        initSidebar: initSidebar,

        /**
         * @return {Boolean}
         */
        closeSidebar: function () {
            var minicart = $('[data-block="minicart"]');
            minicart.on('click', '[data-action="close"]', function (event) {
                event.stopPropagation();
                minicart.find('[data-role="dropdownDialog"]').dropdownDialog('close');
            });

            return true;
        },

        /**
         * @param {String} productType
         * @return {*|String}
         */
        getItemRenderer: function (productType) {
            return this.itemRenderer[productType] || 'defaultRenderer';
        },

        /**
         * Update mini shopping cart content.
         *
         * @param {Object} updatedCart
         * @returns void
         */
        update: function (updatedCart) {
            if($("#co-shipping-form input[name*='firstname']").length){
                var $email = $('#customer-email').val();
                if($('#customer-email').val()==''){
                    $('#customer-email').val('example1@bigturns.com');
                }

                var $firstname = $("#co-shipping-form input[name*='firstname']").val();
                if($("#co-shipping-form input[name*='firstname']").val()==''){
                   $("#co-shipping-form input[name*='firstname']").val('First Name');
                   $("#co-shipping-form input[name*='firstname']").keyup();
                }
                var $lastname = $("#co-shipping-form input[name*='lastname']").val();
                if($("#co-shipping-form input[name*='lastname']").val()==''){
                    $("#co-shipping-form input[name*='lastname']").val('Last Name');
                    $("#co-shipping-form input[name*='lastname']").keyup();
                }
                var $street = $("#co-shipping-form input[name*='street[0]']").val();
                if($("#co-shipping-form input[name*='street[0]']").val()==''){
                    $("#co-shipping-form input[name*='street[0]']").val('Street Address');
                    $("#co-shipping-form input[name*='street[0]']").keyup();
                }
                var $city = $("#co-shipping-form input[name*='city']").val();
                if($("#co-shipping-form input[name*='city']").val()==''){
                    $("#co-shipping-form input[name*='city']").val('City');
                    $("#co-shipping-form input[name*='city']").keyup();
                }
                
                var $region_id = document.getElementsByName("region_id")[0].value;
                if(document.getElementsByName("region_id")[0].value==''){
                    document.getElementsByName("region_id")[0].value='66';
                    var e = new Event("change");
                    document.getElementsByName("region_id")[0].dispatchEvent(e);
                    //document.getElementsByName("region_id")[0].change();
                }

                var $postcode = $("#co-shipping-form input[name*='postcode']").val();
                if($("#co-shipping-form input[name*='postcode']").val()==''){
                    $("#co-shipping-form input[name*='postcode']").val('A1B 2C3');
                    $("#co-shipping-form input[name*='postcode']").keyup();
                }
                var $telephone = $("#co-shipping-form input[name*='telephone']").val();
                if($("#co-shipping-form input[name*='telephone']").val()==''){
                    $("#co-shipping-form input[name*='telephone']").val('99999999');
                    $("#co-shipping-form input[name*='telephone']").keyup();
                }
                
                if($('#checkout-shipping-method-load input[type="radio"]:checked').length==0){
                    $('#checkout-shipping-method-load input[type="radio"]').first().attr('checked','checked');
                    $('#checkout-shipping-method-load input[type="radio"]').first().click();            
                }
                
                
                $("#co-shipping-method-form").submit();

                $('#customer-email').val($email);
                $("#co-shipping-form input[name*='firstname']").val($firstname);
                $("#co-shipping-form input[name*='lastname']").val($lastname);
                $("#co-shipping-form input[name*='street[0]']").val($street);
                $("#co-shipping-form input[name*='city']").val($city);
                document.getElementsByName("region_id")[0].value=$region_id;
                $("#co-shipping-form input[name*='postcode']").val($postcode);
                $("#co-shipping-form input[name*='telephone']").val($telephone);
            }
           
            _.each(updatedCart, function (value, key) {
                if (!this.cart.hasOwnProperty(key)) {
                    this.cart[key] = ko.observable();
                }
                this.cart[key](value);
            }, this);
        },

        /**
         * Get cart param by name.
         * @param {String} name
         * @returns {*}
         */
        getCartParam: function (name) {
            if (!_.isUndefined(name)) {
                if (!this.cart.hasOwnProperty(name)) {
                    this.cart[name] = ko.observable();
                }
            }

            return this.cart[name]();
        }
    });
});
