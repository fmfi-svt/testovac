jQuery(document).ready(function($) {
    "use strict";

    var img_cross_src_p = '<img src="images/cross.png" width="15" class="errorpriemery" />';
    var img_tick_src_p = '<img src="images/tick-ok.png" width="15" class="errorpriemery" />';
    var p1 = {};
    var p2 = {};

    var parseAverages = function() {
        var trs = $("tr");

        $.each(trs, function() {
            var id = $(this).attr("id");
            var p1val = $(this).find('.priemer1check').attr("value");
            var p2val = $(this).find('.priemer2check').attr("value");
            p1[id] = p1val;
            p2[id] = p2val;
        });
    };

    var normalizeAverage = function(e) {
        var priemer = e;
        if (priemer === 0) {
            return null;
        }
        if (priemer.match(/^\d$/) !== null) {
            priemer = priemer + '.00';
        } else if (priemer.match(/^\d[,.]\d$/) !== null) {
            priemer = priemer + '0';
        }
        return priemer;
    };

    parseAverages();

    var hideErrorsForTd = function(element) {
        element.closest("td").find('.errorpriemery').hide();
    };

    var validateAverage = function(average) {
        // priemer moze byt v rangi 1-4
        if (average.length === 0) {
            return true;
        }
        if (average < 1 || average > 4 || !average.match(/^\d([,.]\d{0,2})?$/)) {
            return false;
        }
        return true;
    };

    $(":input.priemer1check").keydown(function(e) {
        if (e.keyCode === 13) {
            $(this).closest("tr").find('.priemer2check').focus();
        }
    });

    $(":input.priemer2check").keydown(function(e) {
        if (e.keyCode === 13) {
            $(this).blur();
        }
    });

    $('td:not(.priemery)').click(function() {
        var tr = $(this).closest("tr");
        var priemer1 = tr.find('.priemer1check');
        var priemer2 = tr.find('.priemer2check');
        var priemer1val = priemer1.val();
        var priemer2val = priemer2.val();
        
        if (priemer1val !== undefined && priemer1val.length < 1) {
            priemer1.focus();
        } else if (priemer2val !== undefined && priemer2val.length < 1) {
            priemer2.focus();
        }
    });
    
    function savePriemer(input, oldValues, fieldName) {
        hideErrorsForTd(input);
        
        var id = input.closest("tr").find('.idsub').val();
        var zadany = input.val();

        if (zadany === oldValues[id]) {
            return; // nic sa nezmenilo
        }

        if (!validateAverage(zadany)) {
            input.closest("td").append(img_cross_src_p);
            setTimeout(function() {
                input.val(oldValues[id]);
                hideErrorsForTd(input);
            }, 1500);
        } else {
            input.closest("td").append(img_tick_src_p);
            if (zadany.length === 0) {
                zadany = 0;
            }
            var data = {
                id: id
            };
            data[fieldName] = zadany;
            $.ajax({
                type: 'POST',
                url: 'db.php',
                dataType: 'html',
                data: data,
                success: function() {
                    oldValues[id] = normalizeAverage(zadany);
                    setTimeout(function() {
                        hideErrorsForTd(input);
                        input.val(oldValues[id]);
                    }, 1500);
                }
            });
        }
    }

    $(':input.priemer1check').blur(function() {
        savePriemer($(this), p1, "priemer1");
    });

    $(':input.priemer2check').blur(function() {
        savePriemer($(this), p2, "priemer2");
    });


    $('.priemer1td').click(function() {
        $(this).find('.priemer1check').focus();
    });

    $('.priemer2td').click(function() {
        $(this).find('.priemer2check').focus();
    });

});
