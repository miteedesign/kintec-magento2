/**
 * Copyright © 2015 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
var SimpleGoogleShopping = null;


require(["jquery", "Magento_Ui/js/modal/confirm", "jquery/ui", "Magento_Ui/js/modal/modal"], function ($, confirm) {
    $(function () {
        if (typeof updater_url === 'undefined') {
            updater_url = "";
        }

        SimpleGoogleShopping = {
            feeds : {
                generate : function(url) {
                    confirm({
                        title: "Generate data feed",
                        content: "Generate a data feed can take a while. Are you sure you want to generate it now ?",
                        actions: {
                            confirm: function () {
                                document.location.href = url;
                            }
                        }
                    });
                },
                delete : function(url) {
                    confirm({
                        title: "Delete data feed",
                        content: "Are you sure you want to delete this feed ?",
                        actions: {
                            confirm: function () {
                                document.location.href = url;
                            }
                        }
                    });
                }
            },
            updater: {
                init: function () {
                    data = new Array();
                    jQuery('.updater').each(function () {
                        var feed = [jQuery(this).attr('id').replace("feed_", ""), jQuery(this).attr('cron')];
                        data.push(feed);
                    });

                    jQuery.ajax({
                        url: updater_url,
                        data: {
                            data: JSON.stringify(data)
                        },
                        type: 'GET',
                        showLoader: false,
                        success: function (data) {
                            data.each(function (r) {
                                jQuery("#feed_" + r.id).parent().html(r.content)
                            });
                            setTimeout(SimpleGoogleShopping.updater.init, 1000)
                        }
                    });

                }
            }
        };

        SimpleGoogleShopping.updater.init();
    });
});