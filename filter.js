/**
 * @copyright Copyright (c) 2011 The FMFI Anketa authors (see AUTHORS).
 * Use of this source code is governed by a license that can be
 * found in the LICENSE file in the project root directory.
 */
var forma_studia = '';
var priemer1error;
var priemer2error;
var piderror;
var pidduplerror;
var pidrocnikerror;
var lastrow;

jQuery(document).ready(function ($) {
    "use strict";
    
    var $fields = $("tr.name_listing");

    //if (!$fields.length) return;
    var myTimeout;
    

    
    function search(query) {
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
                    //                    $field.removeClass("hide");
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
      
    }
  
    function setDefaultFilter() {
        var now= new Date(); 
        var day = now.getDay();
        if (day == 5) {
            setTimeout(function () { 
                $('input:radio[value=externi]').attr('checked',true).click();
            }, 0);
        } else {
            setTimeout(function () { 
                $('input:radio[value=denni]').attr('checked',true).click();
            }, 0);
        }
    }

  
    setDefaultFilter();
    var $input = $('#student-filter');
    $input.focus();
    $input.bind('keydown keyup input', function () {
        setTimeout(function () { 
            search($input.val()); 
        }, 0);
    });
  
    var $reset = $('#reset-button');
    $reset.bind('click', function () {
        resetInput();
    });
  

    $("input").keyup(function(e) {
        if(e.keyCode == 13) {
            var inputs = $(this).closest("tr").find(':input');
            var index = inputs.index( this );
            inputs.eq( index + 1 ).focus();
            if (piderror == true && $(this).hasClass('pidcheck')) {
                $(this).focus();
                $(this).select();
            }
        }
    });
    
    $(".subbtn").click(function(e) {
        var p1msg = 'III. roč. priemer \n -> musí byť v rozsahu 1 az 4, dve desatinné miesta \n';
        var p2msg = 'IV. roč. priemer \n -> musí byť v rozsahu 1 az 4, dve desatinné miesta \n';
        var pidrocnikmsg = 'PID \n -> zadaný PID je z ineho rocnika \n';
        var pidmsg = 'PID \n -> zadaný PID nie je správny \n';
        var pidduplmsg = 'PID \n -> zadaný PID sa už nachádza v databáze, zlikvidujte duplikát';
        var defaultmsg = 'Nepovolené odoslanie formulára, opravte chyby: \n';
        var finalmsg = defaultmsg;
     
        if (priemer1error == true) {
            finalmsg = finalmsg + p1msg;
        }
        if (priemer2error == true) {
            finalmsg = finalmsg + p2msg;
        }
        if (pidrocnikerror == true) {
            finalmsg = finalmsg + pidrocnikmsg;
        }
        if (piderror == true) {
            finalmsg = finalmsg + pidmsg;
        }
        if (pidduplerror == true) {
            finalmsg = finalmsg + pidduplmsg;
        }
        if (priemer1error == true || priemer2error == true || pidrocnikerror == true || 
            piderror == true || pidduplerror == true) {
            alert(finalmsg);
            return;
        }
            
        
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
       
        
        novyForm.submit();
    });

    // nastavenie focusu pri kliknuti na row
    $('tr').click(function (e) {
        if (e.target.nodeName != 'INPUT') {
            if ($(this).find(".pid")[0]){
                $(this).find(".pid").focus();
            } else
            if ($(this).find(".priemer1check")[0]){
                $(this).find(".priemer1check").focus();
            } else
            if ($(this).find(".priemer2check")[0]){
                $(this).find(".priemer2check").focus();
            }
        }
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
    
    $("input").focus(function() {
        var deleteLastEditedRow = function(r) {
            //            alert(r.find('.idsub').val());
            if (r.find('.idsub').val() != lastrow) {
                // ideme zmazat veci z lastrow
                var lrow = $("input.idsub[value='"+lastrow+"']").closest('tr').eq(0);
                lrow.find('.pidcheck').val('');
                lrow.find('.priemer1check').val('');
                lrow.find('.priemer2check').val('');
                lrow.find("img.errorcontrol").hide();
           	 piderror = false;
           	 pidduplerror = false;
            	pidrocnikerror = false;
            	priemer1error = false;
            	priemer2error = false;
            }
            lastrow = r.find('.idsub').val();
        };
        if (!$(this).hasClass('mainfilter')) {
            deleteLastEditedRow($(this).closest('tr').eq(0));
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


