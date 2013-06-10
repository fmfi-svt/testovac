jQuery(document).ready(function($) {
    "use strict";

    var img_cross_src_p = '<img src="images/cross.png" width="15" class="errorpriemery" />';
    var img_tick_src_p = '<img src="images/tick-ok.png" width="15" class="errorpriemery" />';
    var p1 = {};
    var p2 = {};
    var timeouts = {};

    var parseAverages = function() {
        var trs = $("tr");
        $.each(trs, function() {
            var id = $(this).attr("id");
            var p1val = $(this).find('.priemer1check').attr("value");
            var p2val = $(this).find('.priemer2check').attr("value");
            if (p1val == 0) {
                p1val = null;
            }
            if (p2val == 0) {
                p2val = null;
            }
            p1[id] = p1val;
            p2[id] = p2val;
        });
    };

    var normalizeAverage = function(priemer) {
        if (priemer === null) {
            return null;
        }
        if (priemer.match(/^\d$/) !== null) {
            priemer = priemer + '.00';
        } else if (priemer.match(/^\d[,.]\d$/) !== null) {
            priemer = priemer + '0';
        }
        priemer = priemer.replace(',', '.');
        return priemer;
    };

    parseAverages();

    var hideErrorsForTd = function(element) {
        element.closest("td").find('.errorpriemery').remove();
    };

    var validateAverage = function(priemer) {
        // priemer moze byt v rangi 1-4
        if (priemer === null) {
            return true;
        }
        if (priemer < 1 || priemer > 4 || !priemer.match(/^\d([,.]\d{0,2})?$/)) {
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
        } else {
            priemer1.focus();
        }
    });

    $("input.priemerinput").focus(function() {
        var inputid = $(this).closest('tr').attr('id');
        clearTimeout(timeouts[inputid]);
    });

    function savePriemer(input, oldValues, fieldName) {
        hideErrorsForTd(input);

        var id = input.closest("tr").find('.idsub').val();
        var info = input.closest("tr").find(".infosub").val();
        var zadany = input.val();

        if (zadany.length === 0) {
            zadany = null;
        }
        if (zadany === oldValues[id]) {
            return; // nic sa nezmenilo
        }

        if (!validateAverage(zadany)) {
            input.closest("td").append(img_cross_src_p);
            var timeoutId = setTimeout(function() {
                input.val(oldValues[id]);
                hideErrorsForTd(input);
            }, 1500);
            timeouts[id] = timeoutId;
        } else {
            var data = {
                action: 'update',
                id: id,
                info: info
            };
            data[fieldName] = zadany;
            var request = $.ajax({
                type: 'POST',
                url: 'index.php',
                dataType: 'html',
                data: data
            });
            request.done(function() {
                input.closest("td").append(img_tick_src_p);
                oldValues[id] = normalizeAverage(zadany);
                setTimeout(function() {
                    hideErrorsForTd(input);
                    input.val(oldValues[id]);
                }, 1500);
            });
            request.fail(function(jqXHR, textStatus) {
                alert("Request failed: " + textStatus + ' ' + jqXHR.status + ' \nKontaktuje technicku podporu!');
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
