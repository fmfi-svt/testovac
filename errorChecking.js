/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */



jQuery(document).ready(function($) {
    "use strict";

    var img_cross_src_p = '<img src="cross.png" width="15" class="errorpriemery" />';
    var img_tick_src_p = '<img src="tick-ok.png" width="15" class="errorpriemery" />';
    var p1 = {};
    var p2 = {};
    var priemer1error;
    var priemer2error;

    var parseAverages = function() {
        var trs = $("tr");

        $.each(trs, function() {
            var id = $(this).attr("id");
            var p1val = $(this).find('.priemer1check').attr("value");
            var p2val = $(this).find('.priemer2check').attr("value");
            p1[id] = p1val;
            p2[id] = p2val;
        });
        if (window.console)
            console.log(p1);
        if (window.console)
            console.log(p2);
    };

    var normalizeAverage = function(e) {
        var priemer = e;
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
        if ($(this).closest("tr").find('.priemer1check').val().length < 1) {
            $(this).closest("tr").find('.priemer1check').focus();
        } else if ($(this).closest("tr").find('.priemer2check').val().length < 1) {
            $(this).closest("tr").find('.priemer2check').focus();
        }
    });

    $(':input.priemer1check').blur(function() {
        var element = $(this);

        hideErrorsForTd(element);
        priemer1error = false;

        var id = $(this).closest("tr").find('.idsub').val();
        var p1zadany = $(this).val();

        if (p1zadany === p1[id]) {
            return; // nic sa nezmenilo
        }

        if (!validateAverage($(this).val())) {
            priemer1error = true;
        } else {
            priemer1error = false;
        }



        if (priemer1error === true) {
            $(this).closest("td").append(img_cross_src_p);
            setTimeout(function() {
                element.val(p1[id]);
                hideErrorsForTd(element);
            }, 2500);
        } else {
            $(this).closest("td").append(img_tick_src_p);
            $.ajax({
                type: 'POST',
                url: 'db.php',
                dataType: 'html',
                data: {
                    id: id,
                    priemer1: p1zadany
                },
                success: function() {
                    p1[id] = normalizeAverage(p1zadany);
                    setTimeout(function() {
                        hideErrorsForTd(element);
                        element.val(p1[id]);
                    }, 2500);
                }
            });
        }
    });

    $(':input.priemer2check').blur(function() {
        var element = $(this);

        hideErrorsForTd(element);
        priemer2error = false;

        var id = $(this).closest("tr").find('.idsub').val();
        var p2zadany = $(this).val();

        if (p2zadany === p2[id]) {
            return; // nic sa nezmenilo
        }

        if (!validateAverage($(this).val())) {
            priemer2error = true;
        } else {
            priemer2error = false;
        }

        if (priemer2error === true) {
            $(this).closest("td").append(img_cross_src_p);
            setTimeout(function() {
                element.val(p2[id]);
                hideErrorsForTd(element);
            }, 2500);
        } else {
            $(this).closest("td").append(img_tick_src_p);
            $.ajax({
                type: 'POST',
                url: 'db.php',
                dataType: 'html',
                data: {
                    id: id,
                    priemer2: p2zadany
                },
                success: function() {
                    
                    p2[id] = normalizeAverage(p2zadany);
                    setTimeout(function() {
                        hideErrorsForTd(element);
                        element.val(p2[id]);
                    }, 2500);
                }
            });
        }
    });


    $('.priemer1td').click(function() {
        $(this).find('.priemer1check').focus();
    });

    $('.priemer2td').click(function() {
        $(this).find('.priemer2check').focus();
    });

});