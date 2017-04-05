/**
 * Magiccart 
 * @category 	Magiccart 
 * @copyright 	Copyright (c) 2014 Magiccart (http://www.magiccart.net/) 
 * @license 	http://www.magiccart.net/license-agreement.html
 * @Author: DOng NGuyen<nguyen@dvn.com>
 * @@Create Date: 2014-04-25 13:16:48
 * @@Modify Date: 2016-04-26 15:21:11
 * @@Function:
 */

(function ($) {
	"use strict";
    $.fn.magicproduct = function (options) {
        var defaults = {
            //selector : '.magicproduct', // Selector product grid
            tabs 	 : '.magictabs',
            loading  : '.ajax_loading',
            product  : '.content-products',
            padding 	 : 15, // Margin product
        };
        var settings = $.extend(defaults, options);
        return this.each(function () {
            var selector 	= settings.selector;
            var tabs 	 	= settings.tabs;
            var loading 	= settings.loading;
            var padding 	= settings.padding;
            var product 	= settings.product;
            var $content 	= $(this);
            var $product 	= $(product, $content);
            var options 	= $product.data();
			var $tabs 		= $(tabs, $content);
			var $infotabs 	= $tabs.data('ajax')
			var $itemtabs 	= $('.item',$tabs);
			// var $toggleTab  = $('.toggle-tab.mobile', $content);
			var $loading 	= $(loading, $content);
			// $toggleTab.click(function(){
			// 	$tabs.parent().toggleClass('visible');
			// });
			magicProduct();
			$itemtabs.click(function() {
				var $this 	= $(this);
				var type  	= $this.data('type');
				var info 	= $infotabs;
				//var info 	= $infotabs.push(type:type);
				var classes 	= '.mc-'+type;
				var $product = $(product, $content);
				var $cnt_type = $(classes, $product);

				if($this.hasClass('activated')) return;
				$itemtabs.removeClass('activated');
				$this.addClass('activated');
				if($this.hasClass('loaded')){
					resetAnimate($cnt_type);
					$product.children().removeClass('activated'); //.hide();  // not fadeOut()
					$(classes, $product).addClass('activated'); //.fadeIn(); // not show()
					setAnimate($cnt_type); //require for Animate
					magicProduct(); // call again
				} else {
					if(type == undefined) return;
					$loading.show();
					$.ajax({
						type: 'post',
						data: { type: type, info: info },
						url : $loading.data('url'),
						success:function(data){
							$loading.hide();
							$(product, $content).children().removeClass('activated') //.hide();
							$(product, $content).append(data);
							$itemtabs.each(function(){
								if($(this).data('type') == type) $(this).addClass('loaded');
							});
							magicProduct();
							// $.mage.catalogAddToCart;
							// $.mage.apply;
				        	$('.action.tocart' ).unbind( "click" ).click(function() { // Callback Ajax Add to Cart
					        	var form = $(this).closest('form');
		            			var widget = form.catalogAddToCart({ bindSubmit: false });
					            widget.catalogAddToCart('submitForm', form);
					            return false;
				        	});

						}
					});
				}
			});

			function magicProduct(){
				var $product = $(product, $content);
				var $head 	= $('head');
				// var $slide  = $product.data('slider');
				// if($slide){
					$itemtabs.each(function() {
						var $this = $(this);
						if($this.hasClass('activated')){
							var type = $this.data('type');
							var classes = '.mc-'+type;
							var $product = $(product, $content);
							var $content_activated = $(classes, $product).addClass('activated');
							var options = $product.data();
							if(!options.slidesToShow) return; //if($.isEmptyObject(options)) return;
							var slide = $('.products.list', $content_activated);
							if(slide.hasClass('slick-initialized')) slide.slick("refresh"); // slide.resize(); // $(window).trigger('resize');
							else{
								// var selector = $content.selector; // '.' + $content.attr('class').trim().replace(/ /g , '.');
								var padding = options.padding;
								$head.append('<style type="text/css">' + selector + ' .product-item{float: left; padding-left: '+padding+'px; padding-right:'+padding+'px} ' + selector + ' .slick-list{margin-left: -'+padding+'px; margin-right: -'+padding+'px}</style>');
								slide.slick(options);
							}
						}
					});
				// } else {

						var responsive 	= $product.data('responsive'); // data-responsive="[{"1":"1"},{"361":"1"},{"480":"2"},{"640":"3"},{"768":"3"},{"992":"4"},{"1200":"4"}]"
						var padding 	= $product.data('padding');
						var style = '';
						var length = Object.keys(responsive).length;
						$.each( responsive, function( key, value ) {
							var col = 0;
							var maxWith = 3600;
							var minWith = 0;
							$.each( value , function(size, num) { minWith = size; col = num; });
							console.log(responsive[key+1]);
							if(key+1<length) $.each( responsive[key+1], function( size, num) { maxWith = size-1; });
							style += ' @media (min-width: '+minWith+'px) and (max-width: '+maxWith+'px) {'+selector+' .product-item{padding: 0 '+padding+'px; width: '+(Math.floor((10/col) * 100000000000) / 10000000000)+'%} '+selector+' .product-item:nth-child('+col+'n+1){clear: left;}}';
						});

						// var responsive 	= $product.data('responsive'); // data-responsive="[{"col":"1","min":1,"max":360},{"col":"2","min":361,"max":479},{"col":"3","min":480,"max":639},{"col":"3","min":640,"max":767},{"col":"4","min":768,"max":991},{"col":"4","min":992,"max":1199},{"col":"4","min":1200,"max":3600}]"
						// var padding 	= $product.data('padding');
						// var style = '';
						// $.each( responsive, function( key, value ) {
						// 	style += ' @media (min-width: '+value.min+'px) and (max-width: '+value.max+'px) {'+selector+' .product-item{padding: 0 '+padding+'px; width: '+(Math.floor((10/value.col) * 100000000000) / 10000000000)+'%} '+selector+' .product-item:nth-child('+value.col+'n+1){clear: left;}}';
						// });

						$head.append('<style type="text/css">'+style+'</style>');


			}

			// Effect
			function resetAnimate(cnt){
				var parent = cnt.parent();
				$('.products-grid', parent).removeClass("play");
				$('.products-grid .item', parent).removeAttr('style');
			}

			function setAnimate(cnt, time){
				var animate = cnt;
				var  time = time || 300; // if(typeof time == 'undefined') {time =300}
				var $_items = $('.item-animate', animate);
				$_items.each(function(i){
					$(this).attr("style", "-webkit-animation-delay:" + i * time + "ms;"
						                + "-moz-animation-delay:" + i * time + "ms;"
						                + "-o-animation-delay:" + i * time + "ms;"
						                + "animation-delay:" + i * time + "ms;");
					if (i == $_items.size() -1){
						$('.products-grid', animate).addClass("play");  // require for Animate
					}
				});
			}

        });

    };

})(jQuery);
