$(function () {
    $('.tooltip-me').tooltip();
    $('.popover-me').popover();

    $('.site-update-categories').click(function () {
        $(this).button('loading');
    });

    var $toggleButtons = $('.toggle-button');
    $toggleButtons.toggleButtons({
        onChange:function ($el, status, e) {
            $.ajax({
                url:$(e.currentTarget).data('path'),
                data:{
                    status:status ? 1 : 0
                }
            });
        }
    });

    var lastRange = {};
    $('input[type="range"]')
        .change(function () {
            var group = $(this).data('group');
            var $siblings = $('input[type="range"][data-group="' + group + '"]:not(#' + $(this).attr('id') + ')');
            var totalValue = window.parseInt($(this).val());
            var totalSiblings = $siblings.length;
            if (!totalSiblings) {
                $(this).val(100).trigger('update');
                return;
            }
            $siblings.each(function () {
                totalValue += window.parseInt($(this).val());
            });
            if (typeof lastRange[group] === 'undefined') {
                lastRange[group] = 0;
            }
            var i = lastRange[group];
            while (totalValue != 100) {
                var step = (totalValue > 100) ? -1 : 1;
                var currentValue = window.parseInt($siblings.eq(i % totalSiblings).val());
                if ((currentValue + step ) >= 0 && (currentValue + step) <= 100) {
                    $siblings.eq(i % totalSiblings).val(currentValue + step).trigger('update');
                    totalValue += step;
                }
                i++;
            }
            lastRange[group] = i;
            $(this).trigger('update');
        })
        .on('update', function () {
            $($(this).data('value')).val($(this).val());
        })
        .each(function () {
            $(this).trigger('update');
        });

    window.clippy = function (e) {
        $e = $('#' + e.id);
        if (e.action === 'mouseOver') {
            $e.tooltip('destroy')
                .tooltip({
                    title:$e.data('mouseover'),
                    trigger:'manual'
                }).tooltip('show');
        } else if (e.action === 'mouseOut') {
            $e.tooltip('hide');
        } else if (e.action === 'click') {
            $e.tooltip('destroy')
                .tooltip({
                    title:$e.data('click'),
                    trigger:'manual'
                }).tooltip('show');
        }
    };

    $('#campaign_countryList').chosen();
    $('#campaign_regionList').chosen();

    $('.timepicker').timepicker({
        showSeconds:true,
        defaultTime:'value',
        showMeridian:false
    });

    $('body').live('click', function () {
        $('.timepicker').timepicker({
            showSeconds:true,
            defaultTime:'value',
            showMeridian:false
        });

        $('.chosen').chosen({
            allow_single_deselect:true
        });
    });
    $('.chosen').chosen({
        allow_single_deselect:true
    });
});