
jQuery(document).ready(function($) {
    "use strict";
    var piderror;
    var pidduplerror;
    var pidrocnikerror;
    var piddemoerror;
    var img_cross_src = '<img src="images/cross.png" width="25" class="errorpid" />';
    var img_tick_src = '<img src="images/tick-ok.png" width="25" class="errorpid" />';

    $(".pid").keyup(function(e) {
        if (e.keyCode === 13) {
            piderror = false;
            pidduplerror = false;
            pidrocnikerror = false;
            checkPid($(this));
        }
    });

    $(document).keyup(function(e) {
        if (e.keyCode === 27) {
            closeModal($(this).find('.modal'));
        }
    });

    var closeModal = function(e) {
        $("#lean_overlay").fadeOut(200);
        e.css({
            "display": "none"
        });
    };

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

        var meno;
        var priezvisko;
        var checkDuplicatePid = function(pidd) {
            var pidtds = $("td.pidtd");
            $.each(pidtds, function() {
                if ($(this).text().length > 0) {
                    if (($(this).text() === pidd)) {
                        meno = $(this).closest('tr').find('.meno').text();
                        priezvisko = $(this).closest('tr').find('.priezvisko').text();
                        pidduplerror = true;
                        return;
                    }
                }
            });
        };

        var addErrors = function() {
            var pidrocnikmsg = '&bull; zadaný PID je z ineho rocnika <br/>';
            var pidmsg = '&bull; zadaný PID nie je správny <br/>';
            var piddemomsg = '&bull; zadaný PID pochádza z demo verzie <br/>';
            var pidduplmsg1 = '&bull; zadaný PID sa už nachádza v databáze s menom: ';
            var pidduplmsg2 = ', zlikvidujte duplikát! <br/>';
            var defaultmsg = 'Nepovolené odoslanie formulára, opravte chyby: <br/>';
            var finalmsg = defaultmsg;


            if (pidrocnikerror === true) {
                finalmsg = finalmsg + pidrocnikmsg;
            }
            if (piderror === true) {
                finalmsg = finalmsg + pidmsg;
            }
            if (piddemoerror === true) {
                finalmsg = finalmsg + piddemomsg;
            }
            if (pidduplerror === true) {
                finalmsg = finalmsg + pidduplmsg1 + meno + ' ' + priezvisko + pidduplmsg2;
            }

            if (pidrocnikerror === true || piderror === true || pidduplerror === true || piddemoerror === true) {
                focusedElement.closest("div").find('.errorpid').hide();
                focusedElement.closest("div").append(img_cross_src);
                var errorText = '<div class="errorpid">' + finalmsg + '</div>';
                focusedElement.closest("div").append(errorText);
            } else {
                focusedElement.val(addHyphens(pid));
                focusedElement.closest("div").find('.errorpid').hide();
                focusedElement.closest("div").append(img_tick_src);
                var okText = '<div class="errorpid"> PID OK. Uchádzač bol presunutý do druhej časti zoznamu (medzi zaregistrovaných uchádzačov).';
                focusedElement.closest("div").append(okText);
                focusedElement.closest("div").append('</div>');
            }

            if (piderror === true) {
                focusedElement.focus();
                focusedElement.select();
            } else {
                sendPid(focusedElement);
            }
        };


        var pid = element.parent().find('.pid').val();

        var isPidVerhoeff = '';

        var focusedElement = element;

        pidduplerror = false;
        pidrocnikerror = false;
        piddemoerror = false;
        piderror = false;

        if (pid.match(/^(\d{16})$/) !== null) {
            checkDuplicatePid(addHyphens(pid), focusedElement);
        }
        if (pid.match(/^((\d{4})-(\d{4})-(\d{4})-(\d{4}))$/) !== null) {
            checkDuplicatePid(pid, focusedElement);
            pid = removeHyphens(pid);
        }

        if (pid.match(/^(\d{16})$/) && pidduplerror === false) {
            $.ajax({
                type: 'POST',
                url: 'index.php',
                dataType: 'html',
                data: {
                    action: 'check-pid',
                    pidd: pid
                },
                success: function(data) {
                    focusedElement.closest("td").find('.errorpid').hide();
                    isPidVerhoeff = data;
                    if (isPidVerhoeff === 'pidok') {
                        var c = (parseInt(pid[3]) + parseInt(pid[7])) % 10; // kontrola, ci je pid z toho roku
                        if (c === 3) {
                            pidrocnikerror = false;
                            piderror = false;
                        } else {
                            pidrocnikerror = true;
                            piderror = true;
                        }
                    } else if (isPidVerhoeff === 'demopid') {
                        piddemoerror = true;
                        piderror = true;
                    } else {
                        piderror = true;
                    }
                    addErrors();
                }
            });
        } else {
            piderror = true;
            addErrors();
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
        $('<input>').attr({
            type: 'hidden',
            name: 'action',
            value: 'update'
        }).appendTo(novyForm);
        $('<input>').attr({
            type: 'hidden',
            name: 'next',
            value: 'zoznam'
        }).appendTo(novyForm);

        setTimeout(function() {
            novyForm.submit();
        }, 1000);
    };

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
        $('<input>').attr({
            type: 'hidden',
            name: 'action',
            value: 'update'
        }).appendTo(novyForm);
        $('<input>').attr({
            type: 'hidden',
            name: 'next',
            value: 'zoznam'
        }).appendTo(novyForm);
        novyForm.submit();
    });

    $(".closebtn").click(function(e) {
        closeModal($(this).closest('div'));
    });

    $(".addbtn").click(function() {
        piderror = false;
        pidduplerror = false;
        pidrocnikerror = false;
        var element = $(this).closest('div').find('.pid');
        if (checkPid(element)) {
            sendPid(element);
        } else {
            element.focus();
            element.select();
        }
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
        if (event.type === 'mouseover') {
            $(this).closest("tr").addClass("hover");
        } else {
            $(this).closest("tr").removeClass("hover");
        }
    });
});


