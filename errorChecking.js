/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

var lastEdit = '';
var img_cross_src_p = '<img src="cross.png" width="15" class="errorcontrol" />';
var img_tick_src_p = '<img src="tick-ok.png" width="15" class="errorcontrol" />';
var img_loading_small = '<img src="image.gif" width="15" class="errorcontrol" />';

jQuery(document).ready(function($) {
    "use strict";


    var hideErrorsForTd = function(element) {
        element.closest("td").find('.errorcontrol').hide();
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
        if ($(this).closest("tr").find('.priemer1check').is(":visible")) {
            $(this).closest("tr").find('.priemer1check').focus();
        } else if ($(this).closest("tr").find('.priemer2check').is(":visible")) {
            $(this).closest("tr").find('.priemer2check').focus();
        }
    });

    $(':input.priemer1check').blur(function() {
        var element = $(this);

        hideErrorsForTd(element);
        priemer1error = false;
        if ($(this).val().length < 1) {
            priemer1error = true;
        } else if (!validateAverage($(this).val())) {
            priemer1error = true;
        } else {
            priemer1error = false;
        }

        var id = $(this).closest("tr").find('.idsub').val();
        var p1zadany = $(this).val();

        if (priemer1error === true) {
            $(this).closest("td").append(img_cross_src_p);
            setTimeout(function() {
                element.focus();
                element.select();
            }, 200);
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
                    setTimeout(function() {
                        hideErrorsForTd(element);
//                        element.closest("tr").find('.priemer2check').focus();
                    }, 2000);
                }
            });
        }
    });

    $(':input.priemer2check').blur(function() {
        var element = $(this);

        hideErrorsForTd(element);
        priemer2error = false;
        if ($(this).val().length < 1) {
            return;
        }
        if (!validateAverage($(this).val())) {
            priemer2error = true;
        } else {
            priemer2error = false;
        }

        var id = $(this).closest("tr").find('.idsub').val();
        var p2zadany = $(this).val();

        if (priemer2error === true) {
            $(this).closest("td").append(img_cross_src_p);
            setTimeout(function() {
                element.focus();
                element.select();
            }, 200);
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
                    setTimeout(function() {
                        hideErrorsForTd(element);
                    }, 2000);
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