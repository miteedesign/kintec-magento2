/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define([
    'uiComponent',
    'Magento_Customer/js/customer-data'
], function(Component, customerData) {
    'use strict';
    var countryData = customerData.get('directory-data');

    return Component.extend({
        defaults: {
            template: 'Magento_Checkout/shipping-information/address-renderer/default'
        },

        getCountryName: function(countryId) {
            return (countryData()[countryId] != undefined) ? countryData()[countryId].name : "";
        },
        getAddr: function(){
            customerData.reload(['directory-data'], false);
            countryData = customerData.get('directory-data');
            return (countryData()['address'] != undefined) ? countryData()['address'] : false;
        },
		getPhone: function(){
            return (countryData()['phone'] != undefined) ? countryData()['phone'] : false;
        }
    });
});
