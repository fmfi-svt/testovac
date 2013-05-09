/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

var lastEdit = '';
var img_cross_src = '<img src="cross.png" width="15" class="errorcontrol" />';
var img_tick_src = '<img src="tick-ok.png" width="15" class="errorcontrol" />';
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

    $(":input.priemerinput").keydown(function(e) {
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
            return;
        }
        if (!validateAverage($(this).val())) {
            priemer1error = true;
        } else {
            priemer1error = false;
        }

        var id = $(this).closest("tr").find('.idsub').val();
        var p1zadany = $(this).val();

        if (priemer1error === true) {
            $(this).closest("td").append(img_cross_src);
            setTimeout(function() {
                element.focus();
                element.select();
            }, 200);
        } else {
            $(this).closest("td").append(img_tick_src);
            $(this).closest("td").append(img_loading_small);
            $.ajax({
                type: 'POST',
                url: 'db.php',
                dataType: 'html',
                data: {
                    id: id,
                    priemer1: p1zadany
                },
                success: function(data) {
                    setTimeout(function() {
                        hideErrorsForTd(element);
                        element.hide();
                        element.closest("td").find('.priemertext').text(p1zadany);
                        element.closest("td").find('.priemertext').show();
                        element.closest("tr").find('.priemer2check').focus();
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
            $(this).closest("td").append(img_cross_src);
            setTimeout(function() {
                element.focus();
                element.select();
            }, 200);
        } else {
            $(this).closest("td").append(img_tick_src);
            $(this).closest("td").append(img_loading_small);
            $.ajax({
                type: 'POST',
                url: 'db.php',
                dataType: 'html',
                data: {
                    id: id,
                    priemer2: p2zadany
                },
                success: function(data) {
                    setTimeout(function() {
                        hideErrorsForTd(element);
                        element.hide();
                        element.closest("td").find('.priemertext').text(p2zadany);
                        element.closest("td").find('.priemertext').show();
                    }, 2000);
                }
            });
        }
    });


    $('.priemer1td').click(function() {
        $(this).find('.priemertext').hide();
        $(this).find('.priemer1check').show();
        $(this).find('.priemer1check').focus();
    });

    $('.priemer2td').click(function() {
        $(this).find('.priemertext').hide();
        $(this).find('.priemer2check').show();
        $(this).find('.priemer2check').focus();
    });

});