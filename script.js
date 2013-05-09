var forma_studia = '';
var priemer1error;
var priemer2error;
var piderror;
var pidduplerror;
var pidrocnikerror;
var lastrow;
var mistake;
var img_cross_src = '<img src="cross.png" width="15" class="errorcontrol" />';
var img_tick_src = '<img src="tick-ok.png" width="15" class="errorcontrol" />';
var img_loading_src = '<img src="image.gif" width="50" class="errorcontrol loadingGif" />';

jQuery(document).ready(function($) {
    "use strict";


    $(".pid").keyup(function(e) {
        if (e.keyCode === 13) {
            piderror = false;
            pidduplerror = false;
            pidrocnikerror = false;
            if (checkPid($(this))) {
                sendPid($(this));
            } else {
                $(this).focus();
                $(this).select();
            }
        }
    });

    var addHyphens = function(pid) {
        var str1 = pid.substr(0, 4);
        var str2 = pid.substr(4, 4);
        var str3 = pid.substr(8, 4);
        var str4 = pid.substr(12, 4);
        var ret;
        ret = str1 + '-' + str2 + '-' + str3 + '-' + str4;
        return ret;
    };

    var removeHyphens = function(pid) {
        var splitted = pid.split("-");
        var ret = splitted[0] + splitted[1] + splitted[2] + splitted[3];
        return ret;
    };

    var checkPid = function(element) {
        var pidrocnikmsg = 'zadaný PID je z ineho rocnika <br/>';
        var pidmsg = 'zadaný PID nie je správny <br/>';
        var pidduplmsg = 'zadaný PID sa už nachádza v databáze, zlikvidujte duplikát <br/>';
        var defaultmsg = 'Nepovolené odoslanie formulára, opravte chyby: <br/>';
        var finalmsg = defaultmsg;
        
        var checkDuplicatePid = function(pidd) {
            var pidtds = $("td.pidtd");
            $.each(pidtds, function() {
                if ($(this).text().length > 0) {
                    if (($(this).text() === pidd)) {
                        pidduplerror = true;
                        return;
                    }
                }
            });
        };

        var pid = element.parent().find('.pid').val();

        var isPidVerhoeff;

        var focusedElement = element;

        pidduplerror = false;

        if (pid.match(/^(\d{16})$/) !== null) {
            checkDuplicatePid(addHyphens(pid), focusedElement);
        }
        if (pid.match(/^((\d{4})-(\d{4})-(\d{4})-(\d{4}))$/) !== null) {
            checkDuplicatePid(pid, focusedElement);
            pid = removeHyphens(pid);
        }

        if (pid.match(/^(\d{16})$/) && pidduplerror === false) {
            var c = (parseInt(pid[3]) + parseInt(pid[7])) % 10;
            if (c === 2) {
                pidrocnikerror = false;
            } else {
                pidrocnikerror = true;
            }
            $.ajax({
                type: 'POST',
                url: 'verhoeffChecker.php',
                dataType: 'html',
                data: {
                    pidd: pid
                },
                success: function(data) {
                    focusedElement.closest("td").find('.errorcontrol').hide();
                    isPidVerhoeff = data;
                    if (isPidVerhoeff === 'true' && pidrocnikerror === false) {
                        piderror = false;
                    } else {
                        piderror = true;
                    }
                }
            });
        } else {
            piderror = true;
        }
        if (pidrocnikerror === true) {
            finalmsg = finalmsg + pidrocnikmsg;
        }
        if (piderror === true) {
            finalmsg = finalmsg + pidmsg;
        }
        if (pidduplerror === true) {
            finalmsg = finalmsg + pidduplmsg;
        }
        if (pidrocnikerror === true || piderror === true || pidduplerror === true) {
            focusedElement.closest("div").find('.errorcontrol').hide();
            focusedElement.closest("div").append(img_cross_src);
            var errorText = '<div class="errorcontrol">' + finalmsg + '</div>';
            focusedElement.closest("div").append(errorText);
            return false;
        } else {
            focusedElement.val(addHyphens(pid));
            focusedElement.closest("div").find('.errorcontrol').hide();
            focusedElement.closest("div").append(img_tick_src);
            var okText = '<div class="errorcontrol"> PID ok. Cakajte...';
            focusedElement.closest("div").append(okText);
            focusedElement.closest("div").append(img_loading_src);
            focusedElement.closest("div").append('</div>');
            return true;
        }
    };


    var sendPid = function(element) {

        var novyForm = $('<form/>').hide().appendTo('body');
        novyForm.attr('method', 'post');
        $('<input>').attr({
            type: 'hidden',
            name: 'id',
            value: element.closest("div").find(".idsub").val()
        }).appendTo(novyForm);
        $('<input>').attr({
            type: 'hidden',
            name: 'info',
            value: element.closest("div").find(".infosub").val()
        }).appendTo(novyForm);
        $('<input>').attr({
            type: 'hidden',
            name: 'pid',
            value: element.closest("div").find(".pidsub").val()
        }).appendTo(novyForm);

        setTimeout(function() {
            novyForm.submit();
        }, 3000);
    };

//    $(".subavgbtn").click(function(e) {
//        var p1msg = 'III. roč. priemer \n -> musí byť v rozsahu 1 az 4, dve desatinné miesta \n';
//        var p2msg = 'IV. roč. priemer \n -> musí byť v rozsahu 1 az 4, dve desatinné miesta \n';
//        var defaultmsg = 'Nepovolené odoslanie formulára, opravte chyby: \n';
//        var finalmsg = defaultmsg;
//
//        if (priemer1error === true) {
//            finalmsg = finalmsg + p1msg;
//        }
//        if (priemer2error === true) {
//            finalmsg = finalmsg + p2msg;
//        }
//        if (priemer1error === true || priemer2error === true) {
//            alert(finalmsg);
//            return;
//        }
//
//        var novyForm = $('<form/>').hide().appendTo('body');
//        novyForm.attr('method', 'post');
//        $('<input>').attr({
//            type: 'hidden',
//            name: 'id',
//            value: $(this).closest("tr").find(".idsub").val()
//        }).appendTo(novyForm);
//        $('<input>').attr({
//            type: 'hidden',
//            name: 'info',
//            value: $(this).closest("tr").find(".infosub").val()
//        }).appendTo(novyForm);
//        $('<input>').attr({
//            type: 'hidden',
//            name: 'priemer1',
//            value: $(this).closest("tr").find(".priemer1sub").val()
//        }).appendTo(novyForm);
//        $('<input>').attr({
//            type: 'hidden',
//            name: 'priemer2',
//            value: $(this).closest("tr").find(".priemer2sub").val()
//        }).appendTo(novyForm);
//        setTimeout(function() {
//            novyForm.submit();
//        }, 2000);
//    });

    $(".subdelbtn").click(function(e) {
        var novyForm = $('<form/>').hide().appendTo('body');
        novyForm.attr('method', 'post');
        $('<input>').attr({
            type: 'hidden',
            name: 'id',
            value: $(this).closest("div").find(".idsub").val()
        }).appendTo(novyForm);
        $('<input>').attr({
            type: 'hidden',
            name: 'info',
            value: $(this).closest("div").find(".infosub").val()
        }).appendTo(novyForm);
        $('<input>').attr({
            type: 'hidden',
            name: 'delete',
            value: 'yes'
        }).appendTo(novyForm);
        novyForm.submit();
    });

    $(".closebtn").click(function(e) {
        $("#lean_overlay").fadeOut(200);
        $(this).closest("div").css({
            "display": "none"
        })
    });

    $('td').css('cursor', 'pointer');

    $("tr").click(function() {
        var currentId = $(this).attr('id');
        currentId = "#go" + currentId;
        $(currentId).click();

    });

    $(".addClick").click(function() {
        var currentId = $(this).attr('id');
        currentId = currentId.replace('go', '');
        var modalId = "#input" + currentId;
        setTimeout(function() {
            $(modalId).focus();
        }, 1);

    });

    // zvyraznenie row pri kliknuti na row
    $("td").click(function() {
        $(this).closest("tr").siblings().removeClass("highlight");
        $(this).parents("tr").addClass("highlight");
    });

    $("td").live('mouseover mouseout', function(event) {
        if (event.type == 'mouseover') {
            $(this).closest("tr").addClass("hover");
        } else {
            $(this).closest("tr").removeClass("hover");
        }
    });


});


