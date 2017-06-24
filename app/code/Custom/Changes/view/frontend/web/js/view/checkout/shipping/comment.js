define([
	'jquery',
    'uiComponent',
    'mage/url'

], function ($,Component,url) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Custom_Changes/checkout/shipping/comment',
            setComment : function(){
            	$.post( url.build('changes/comment/save'), { comment: $('#shipping-comment').val() } );
            }
        }
    });
});
