var forma_studia = '';
var priemer1error;
var priemer2error;
var piderror;
var pidduplerror;
var pidrocnikerror;
var lastrow;

jQuery(document).ready(function ($) {
    "use strict";
    

//    $("input").keyup(function(e) {
//        if(e.keyCode == 13) {
//            var inputs = $(this).closest("tr").find(':input');
//            var index = inputs.index( this );
//            inputs.eq( index + 1 ).focus();
//            if (piderror == true && $(this).hasClass('pidcheck')) {
//                $(this).focus();
//                $(this).select();
//            }
//        }
//    });
    
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
            value: $(this).closest("div").find(".idsub").val()
        }).appendTo(novyForm);
        $('<input>').attr({
            type: 'hidden',
            name: 'info',
            value: $(this).closest("div").find(".infosub").val()
        }).appendTo(novyForm);
        $('<input>').attr({
            type: 'hidden',
            name: 'pid',
            value: $(this).closest("div").find(".pidsub").val()
        }).appendTo(novyForm);
        novyForm.submit();
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
    

});


