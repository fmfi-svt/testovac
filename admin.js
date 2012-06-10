/**
 * @copyright Copyright (c) 2011 The FMFI Anketa authors (see AUTHORS).
 * Use of this source code is governed by a license that can be
 * found in the LICENSE file in the project root directory.
 */

var forma_studia = '';
var menoerror;
var priezviskoerror;
var priemer1error;
var priemer2error;
var piderror;
var pidrocnikerror;
var pidduplerror;
var datumerror;
var datumvekerror;

jQuery(document).ready(function ($) {
    "use strict";
    
    var $fields = $("tr.name_listing");
    //if (!$fields.length) return;
    var myTimeout;
    
    function search(query) {
        
        $(".input_row").each(function(){
            $(this).css("display","none");
        })
        
        query = query.replace(",",".");
        if (query.length > 0 && query.length < 3) {
            return;
        }
        query = removeDiacritics(query);
        
        var words = [];
        $.each(query.toLowerCase().split('"'), function (index, chunk) {

            var matches = chunk.match(/\S+/g) || [];
            for (var i = 0; i < matches.length; i++) words.push(matches[i]);
        });
        words.push(forma_studia);
        var batchSize = 550;
        var i = 0;
        function doit() {
            for (var end = i + batchSize; i < $fields.length && i != end; i++) {
                var $field = $fields.eq(i);
                var text = removeDiacritics($field.text().toLowerCase());
                var hidden = false;
                for (var u = 0; u < words.length; u++) {
                    if (text.indexOf(words[u]) == -1) {
                        $field.css("display","none");
                        hidden = true;
                        break;
                    }
                }
                if (!hidden) {
                    $field.css("display","table-row");
                }
            }
            if (i < $fields.length) {
                if (myTimeout !== undefined) clearTimeout(myTimeout);
                myTimeout = setTimeout(doit, 0);
            }
        }
        doit();
    }
  
    function resetInput() {
        var doc = document.getElementById('student-filter');
        doc.value = "";
        setTimeout(function () {
            search($input.val()); 
        }, 0);
        $(".unlockable").each(function(){
            $(this).show();
        });
        $(".input_row").each(function(){
            $(this).hide();
        });
    }
  
    var $input = $('#student-filter');
    $input.focus();
    
    setTimeout(function () { 
        $('input:radio[value=vsetci]').attr('checked',true).click();
    }, 0);
    
    $input.bind('keydown keyup input', function () {
        var delay = (function(){
            var timer = 0;
            return function(callback, ms){
                clearTimeout (timer);
                timer = setTimeout(callback, ms);
            };
        })();
        delay(function(){
            search($input.val());
        }, 500 );
    });
  
    $('#reset-button').bind('click', function () {
        resetInput();
    });
   
    $("input").keyup(function(e) {
        if(e.keyCode == 13) {
            var inputs = $(this).closest("tr").find(':input');
            var index = inputs.index( this );
            inputs.eq( index + 1 ).focus();
        }
    });
    
    $(".subbtn").click(function(e) {
        var menomsg = 'Meno \n -> musí mať najmenej jeden znak \n';
        var priezviskomsg = 'Priezvisko \n -> musí mať najmenej jeden znak \n';
        var datummsg = 'Dátum narodenia \n -> musí byť v tvare dd.mm.rrrr \n';
        var datumvekmsg = 'Dátum narodenia \n -> určite má študent viac ako 70 alebo menej ako 15 rokov?';
        var p1msg = 'III. roč. priemer \n -> musí byť v rozsahu 1 až 4, dve desatinné miesta \n';
        var p2msg = 'IV. roc. priemer \n -> musí byť v rozsahu 1 až 4, dve desatinné miesta \n';
        var pidrocnikmsg = 'PID \n -> zadaný PID je z iného ročníka \n';
        var pidmsg = 'PID \n -> zadaný PID nie je správny \n';
        var pidduplmsg = 'PID \n -> zadaný PID sa uz nachádza v databáze, zlikvidujte duplikát!';
        var defaultmsg = 'Nepovolené odoslanie formulára, opravte chyby: \n';
        var finalmsg = defaultmsg;
 
        if (menoerror == true) {
            finalmsg = finalmsg + menomsg;
        }
        if (priezviskoerror == true) {
            finalmsg = finalmsg + priezviskomsg;
        }        
        if (datumerror == true) {
            finalmsg = finalmsg + datummsg;
        }        
        if (priemer1error == true) {
            finalmsg = finalmsg + p1msg;
        }
        if (priemer2error == true) {
            finalmsg = finalmsg + p2msg;
        }
        if (pidduplerror == true) {
            finalmsg = finalmsg + pidduplmsg;
        }
        if (pidrocnikerror == true) {
            finalmsg = finalmsg + pidrocnikmsg;
        }
        if (piderror == true) {
            finalmsg = finalmsg + pidmsg;
        }
        if (menoerror == true || priezviskoerror == true || priemer1error == true || datumerror == true ||
            priemer2error == true || pidrocnikerror == true || pidduplerror == true || piderror == true) {
            alert(finalmsg);
            return;
        }
        var isPrinted = $(this).closest("tr").find(".infoprinted").val();
        var isExported = $(this).closest("tr").find(".infoexported").val();

        //$(this).closest("tr").find(".sub").val('save');
        var novyForm = $('<form/>').hide().appendTo('body');
        novyForm.attr('method', 'post');
        $('<input>').attr({
            type: 'hidden',
            name: 'id',
            value: $(this).closest("tr").find(".idsub").val()
        }).appendTo(novyForm);
        $('<input>').attr({
            type: 'hidden',
            name: 'info',
            value: $(this).closest("tr").find(".infosub").val()
        }).appendTo(novyForm);
        $('<input>').attr({
            type: 'hidden',
            name: 'meno',
            value: $(this).closest("tr").find(".menosub").val()
        }).appendTo(novyForm);
        $('<input>').attr({
            type: 'hidden',
            name: 'priezvisko',
            value: $(this).closest("tr").find(".priezviskosub").val()
        }).appendTo(novyForm);
        $('<input>').attr({
            type: 'hidden',
            name: 'datum',
            value: $(this).closest("tr").find(".datumsub").val()
        }).appendTo(novyForm);
        $('<input>').attr({
            type: 'hidden',
            name: 'forma',
            value: $(this).closest("tr").find(".formasub").val()
        }).appendTo(novyForm);
        $('<input>').attr({
            type: 'hidden',
            name: 'priemer1',
            value: $(this).closest("tr").find(".priemer1sub").val()
        }).appendTo(novyForm);
        $('<input>').attr({
            type: 'hidden',
            name: 'priemer2',
            value: $(this).closest("tr").find(".priemer2sub").val()
        }).appendTo(novyForm);
        $('<input>').attr({
            type: 'hidden',
            name: 'pid',
            value: $(this).closest("tr").find(".pidsub").val()
        }).appendTo(novyForm);
        
        if (isExported == 'áno') {
            if (confirm('POZOR! Študent už bol exportovaný! \n Chcete napriek tomu urobiť zmeny?\n Ak áno, prosím, urýchlene kontaktuje administrátora, pretože bude nutné vyexportovať študenta ešte raz.')){
                novyForm.submit();
            }
        } else if (isPrinted == 'áno') {
            if (confirm('POZOR! Prvá strana pre študenta už bola vytlačená! \n Chcete napriek tomu urobiť zmeny?\n Pokiaľ ste editovali niečo iné ako priemery, bude potrebné vytlačiť prvú stranu znova.')){

                novyForm.submit();
            }
        } else if (datumvekerror == true) {
            if (confirm('Zelate si pokracovat? \n' + datumvekmsg)){
                novyForm.submit();
            }
        } else {
            novyForm.submit();
        }

    });
    
    // odomknutie row
    $(".unlock_btn").click(function() {
        menoerror = false;
        priezviskoerror = false;
        priemer1error = false;
        priemer2error = false;
        piderror = false;
        dateerror = false;
        pidrocnikerror = false;
        //        var id;
        //        var id_name = $(this).find()
        //        var novyRow = $(
        //            "<tr class=\"hide input_row\"> " + 
        //            "<td>" + 
        //            "<input type=\"hidden\" name=\"" + id_name + "\" class=\"idsub\" value=\"" + id + "\">" +
        //            "</td>" +
        //            "" +
        //            "" +
        //            "" +
        //            "" +
        //            "" +
        //            "" +
        //            "</tr>" 
        //            ).insertAfter($(this).closest("tr"));
        var lolz = $(this).closest("tr");
        search($input.val());
        setTimeout( function(){
            lolz.css("display","none");
            lolz.next("tr").css("display","table-row");
        },250);


    });
    
    // zvyraznenie row pri kliknuti na row
    $("td").click(function() {
        $(this).closest("tr").siblings().removeClass("highlight");
        $(this).closest("tr").addClass("highlight", this.clicked);
    });
    
    // zvyraznenie row pri mouseover
    $("td").live('mouseover mouseout', function(event) {
        if (event.type == 'mouseover') {
            $(this).closest("tr").addClass("hover");
        } else {
            $(this).closest("tr").removeClass("hover");
        }
    });
    
    $(".filterforext").click(function() {
        $('.ext').css("display","table-row");
        $('.int').css("display","none");
        forma_studia = 'extern';
        search($input.val());
    });
    $(".filterforden").click(function() {
        $('.ext').css("display","none");
        $('.int').css("display","table-row");
        forma_studia = 'denn';
        search($input.val());
    });
    $(".filterforall").click(function() {
        $('.ext').css("display","table-row");
        $('.int').css("display","table-row");
        forma_studia = '';
        search($input.val());
    });

});
