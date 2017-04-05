// Javascript on this tool
(function ($) {
    $(document).ready(function ($) {

        //flag to run all steps or not
        var running = false;
        var runAllSteps = false;
        var maxRunIndex = ($('.migrate-steps a.run').length) ? $('.migrate-steps a.run').last().data('step-index') : 0;
        var minResetIndex = ($('.migrate-steps a.reset').length) ? $('.migrate-steps a.reset').first().data('step-index') : 0;

        /**** JS for migrate process ***/
        //ajax process to run migrate
        $('.migrate-steps a.run').each(function () {
            var $step = $(this);
            $step.on('click', function (e) {
                e.preventDefault();
                if (!$step.hasClass('disabled')) {
                    $.ajaxMigrate($step);
                } else {
                    //run next step
                    var currentStepIndex = $step.data('step-index');
                    if (currentStepIndex < maxRunIndex) {
                        $('#run-step-' + (currentStepIndex + 1)).trigger('click');
                    }
                    else {
                        runAllSteps = false;
                        $.hideMask();
                    }
                }
            });
        });

        $.runAllSteps = function () {
            runAllSteps = true;
            $('.migrate-steps a.run').first().trigger('click');
        }

        $.ajaxMigrate = function ($step) {
            $.ajax({
                url: $step.attr('href'),
                dataType: 'json',
                beforeSend: function () {
                    //update step status
                    $('#step-status-' + $step.data('step-index')).html('<span class="processing">Processing...</span>');
                    //show process bar
                    $('#all-steps-process').show();
                    $.showMask('migrate');
                },
                success: function (rs) {
                    //console.log(rs);
                    if (rs.status == 'ok') {
                        if (typeof rs.percent_up != 'undefined') {
                            //update migrate process bar info
                            $.updateProcessBar2(rs.percent_up);
                        }
                        //process to continue on this step
                        $.ajaxMigrate($step);

                    } else if (rs.status == 'done') {
                        //update step status info
                        $.updateStepStatus(rs);
                        //update process bar info
                        $.updateProcessBar(rs);
                        if (runAllSteps) {
                            //process to migrate with next step if is migrate all step context
                            if (rs.step_index < maxRunIndex) {
                                $('#run-step-' + (parseInt(rs.step_index) + 1)).trigger('click');
                            } else {
                                $('#run-all-steps').html('<span class="glyphicon glyphicon-transfer"></span> Re-run migrate data all steps');
                                runAllSteps = false;
                                $.hideMask();
                            }
                        } else {
                            //update log
                            $.updateLog();
                            //hide loading mask
                            $.hideMask();
                        }
                    } else {
                        //alert errors/notice if has
                        if (typeof rs.errors != 'undefined' && rs.errors.length) {
                            $('#main-content').find('#errors').remove();
                            $('#main-content').prepend('<div id="errors" class="flash-error">' + rs.errors + '</div>');
                        } else if (typeof rs.notice != 'undefined' && rs.notice.length) {
                            $('#main-content').find('#notice').remove();
                            $('#main-content').prepend('<div id="notice" class="flash-notice">' + rs.notice + '</div>');
                            $('#step-status-' + $step.data('step-index')).html('<span class="setting">Ready to migrate</span>');
                        }
                        $.hideMask();
                    }
                }
            });
        }

        $.updateStepStatus = function (rs) {
            if (typeof rs.step_status_text != 'undefined') {
                $('#step-status-' + rs.step_index).html(rs.step_status_text);
                $('#step-status-' + rs.step_index).parent().addClass('success');
            }
            //update labels of current step
            $('#run-step-' + rs.step_index).html('<span class="glyphicon glyphicon-transfer"></span> Re-run');
            if ($('#setting-step-' + rs.step_index).length) {
                if (!$('#setting-step-' + rs.step_index).hasClass('finished')) {
                    $('#setting-step-' + rs.step_index).find('a').prepend('<span class="glyphicon glyphicon-ok-sign text-success"></span> ');
                    $('#setting-step-' + rs.step_index).addClass('finished');
                }
            }
        }

        $.updateProcessBar = function (rs) {
            if (typeof rs.percent_done != 'undefined') {
                if ($('#migrate-status-all-steps').length) {
                    $('#migrate-status-all-steps').html('<span class="value">' + rs.percent_done + '</span>%');
                }
                //update process bar info
                $('#all-steps-process').find('.progress-bar').css({"width": rs.percent_done + '%'}).attr('aria-valuenow', rs.percent_done).html('<span class="value">' + rs.percent_done + '</span>% Completed');
            }
        }

        $.updateProcessBar2 = function (percentUp) {
            //update process bar info
            var $processBar = $('#all-steps-process').find('.progress-bar');
            var cPercent = $processBar.attr('aria-valuenow');
            var cPercent = parseFloat(cPercent) + parseFloat(percentUp);
            var percent = cPercent.toFixed(3).replace(/(\d)(?=(\d{3})+\.)/g, '$1,');
            $processBar.css({"width": cPercent + '%'}).attr('aria-valuenow', cPercent).html('<span class="value">' + percent + '</span>% Completed');

            //update label in menu steps
            if ($('#migrate-status-all-steps').length) {
                $('#migrate-status-all-steps').find('.value').html(percent);
            }
        }

        //bind ajax event to reset data
        $('.migrate-steps a.reset').each(function () {
            var $step = $(this);
            $step.on('click', function (e) {
                e.preventDefault();
                $.ajaxReset($step);
            });
        });

        $.resetAllSteps = function () {
            runAllSteps = true;
            $('.migrate-steps a.reset').last().trigger('click');
        }

        $.ajaxReset = function ($step) {
            $.ajax({
                url: $step.attr('href'),
                dataType: 'json',
                beforeSend: function () {
                    //update step status
                    $('#step-status-' + $step.data('step-index')).html('<span class="processing">Processing...</span>');
                    $.showMask('reset');
                },
                success: function (rs) {
                    //console.log(rs);
                    if (rs.status == 'ok') {
                        //process to continue on this step
                        $.ajaxReset($step);

                    } else if (rs.status == 'done') {
                        //update current step status
                        if (rs.step_status_text != 'undefined') {
                            $('#step-status-' + rs.step_index).html(rs.step_status_text);
                            $('#step-status-' + rs.step_index).parent().addClass('success');
                        }
                        if (runAllSteps) {
                            //process to reset with next step if is reset all steps context
                            if (rs.step_index > minResetIndex) {
                                $('#reset-step-' + (parseInt(rs.step_index) - 1)).trigger('click');
                            } else {
                                runAllSteps = false;
                                $.hideMask();
                                //redirect to setting form of step #1
                                window.location = $('#setting-step-1').find('a').attr('href');
                            }
                        } else {
                            $.hideMask();
                            //redirect to setting form of current step.
                            window.location = $('#setting-step-' + $step.data('step-index')).find('a').attr('href');
                        }
                    } else {
                        if (typeof rs.errors != 'undefined' && rs.errors.length) {
                            $('#main-content').find('#errors').remove();
                            $('#main-content').prepend('<div id="errors" class="flash-error">' + rs.errors + '</div>');
                        } else if (typeof rs.notice != 'undefined' && rs.notice.length) {
                            $('#main-content').find('#notice').remove();
                            $('#main-content').prepend('<div id="notice" class="flash-notice">' + rs.notice + '</div>');
                        }
                        $('#step-status-' + $step.data('step-index')).html('<span class="finished">Finished</span>');
                        $.hideMask();
                    }
                }
            });
        }

        //hide the message function
        $.hideMessage = function () {
            //We only auto hide the success message.
            if ($('#message.flash-success').length) {
                $('#message.flash-success').slideUp('slow');
            }
        }
        //set timeout to hide the message
        setTimeout(function () {
            $.hideMessage();
        }, 5000);

        //show/hide loading mask function
        $.showMask = function (maskType) {
            if (!running) {
                $('#ub-tool-content').block({
                    message: (maskType == 'reset') ? $('#reset-loading') : $('#migrate-loading'),
                    centerY: 0,
                    css: {top: '10px', left: '', right: '10px', width: '350px'}
                });
                running = true;
            }
        }

        $.hideMask = function () {
            $('#ub-tool-content').unblock()
            running = false;
        }

        $.updateLog = function () {
            var url = $('#log-url').val();
            if (url.length) {
                $.ajax({
                    url: url,
                    beforeSend: function () {
                        $('#migrate-log-content').html('loading...');
                    },
                    success: function (rs) {
                        if ($('#migrate-log-content').length) {
                            $('#migrate-log-content').html(rs);
                        }
                    }
                });
            }
        }
        if ($('#migrate-log-action').length) {
            $('#migrate-log-action').on('click', function () {
                if (!$(this).hasClass('loaded')) {
                    $.updateLog();
                    $(this).addClass('loaded');
                }
            });
        }

        /******************* JS for forms settings ***********************/
        //Common functions
        $('.list-group-item-heading label span').on('click', function(){
            $(this).parent().parent().siblings('ul.list-group').toggle(200);
        });
        $('.step-controls .btn').on('click', function(){
            $(this).addClass('disabled');
        });

        /*** Step 2 ***/
        //check/un-check website & stores on it
        $('INPUT[name="website_ids[]"]').on('change', function() {
            $('.store-group-' + this.value).prop("checked", this.checked);

            $('.store-group-' + this.value).on('change', function() {
                $('.store-' + this.value).prop("checked", this.checked);
            });
            $('.store-group-' + this.value).trigger('change');
        });

        /* Step 4 */
        //check/un-check product types
        $('INPUT[name="select_all"]').on('change', function() {
            if ($('INPUT[name="website_ids[]"]').length) {
                $('INPUT[name="website_ids[]"]').prop('checked', this.checked).trigger('change');
            }
            if ($('INPUT[name="product_types[]"]').length) {
                $('INPUT[name="product_types[]"]').prop('checked', this.checked);
            }
            if ($('INPUT[name="customer_group_ids[]"]').length) {
                $('INPUT[name="customer_group_ids[]"]').prop('checked', this.checked);
            }
            if ($('INPUT[name="objects[]"]').length) {
                $('INPUT[name="objects[]"]').prop('checked', this.checked).trigger('change');
            }
            //we always select the simple products
            if ($('#product_type_simple').length) {
                $('#product_type_simple').prop('checked', true);
            }
            //show/hide child block
            /*if (this.checked) {
                $(this).siblings("ul.list-group").hide(100);
            } else {
                $(this).siblings("ul.list-group").show(200);
            }*/
        });

        /* Step 7 */
        $('#sales_object_sales_aggregated_data').on('change', function(){
            $('INPUT[name="sales_aggregated_tables[]"]').prop('checked', this.checked);
        });
        $('INPUT[name="sales_aggregated_tables[]"]').on('change', function(){
            if (this.checked) {
                $('#sales_object_sales_aggregated_data').prop('checked', this.checked);
            }
        });
        /* Step 8 */
        $('INPUT[name="objects[]"]').on('change', function() {
            $(this).parent().parent().siblings('ul.list-group').find('INPUT[name="child_objects[]"]').prop('checked', this.checked);
        });
        $('INPUT[name="child_objects[]"]').on('change', function(){
            if (this.checked) {
                $(this).parent().parent().parent().siblings('h4.list-group-item-heading').find('INPUT[name="objects[]"]').prop('checked', this.checked);
            }
        });
    });

})(jQuery);
