var forma_studia = '';
var priemer1error;
var priemer2error;
var piderror;
var pidduplerror;
var pidrocnikerror;
var lastrow;

jQuery(document).ready(function ($) {
    "use strict";
    

    $("input").keyup(function(e) {
        if(e.keyCode == 13) {
            var inputs = $(this).closest("div").find(':input');
            var index = inputs.index( this );
            inputs.eq( index + 1 ).focus();
            if (piderror == true && $(this).hasClass('pidcheck')) {
                $(this).focus();
                $(this).select();
            }
        }
    });
    
    $(".subbtn").focus(function(e) {
        var pidrocnikmsg = 'PID \n -> zadaný PID je z ineho rocnika \n';
        var pidmsg = 'PID \n -> zadaný PID nie je správny \n';
        var pidduplmsg = 'PID \n -> zadaný PID sa už nachádza v databáze, zlikvidujte duplikát';
        var defaultmsg = 'Nepovolené odoslanie formulára, opravte chyby: \n';
        var finalmsg = defaultmsg;
        setTimeout(function(){
        
            if (pidrocnikerror == true) {
                finalmsg = finalmsg + pidrocnikmsg;
            }
            if (piderror == true) {
                finalmsg = finalmsg + pidmsg;
            }
            if (pidduplerror == true) {
                finalmsg = finalmsg + pidduplmsg;
            }
            if (pidrocnikerror == true || piderror == true || pidduplerror == true) {
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
            setTimeout(function(){
                novyForm.submit();
            }, 1000); 
        }, 10); 

    });
    
    $(".subavgbtn").click(function(e) {
        var p1msg = 'III. roč. priemer \n -> musí byť v rozsahu 1 az 4, dve desatinné miesta \n';
        var p2msg = 'IV. roč. priemer \n -> musí byť v rozsahu 1 az 4, dve desatinné miesta \n';
        var defaultmsg = 'Nepovolené odoslanie formulára, opravte chyby: \n';
        var finalmsg = defaultmsg;
     
        if (priemer1error == true) {
            finalmsg = finalmsg + p1msg;
        }
        if (priemer2error == true) {
            finalmsg = finalmsg + p2msg;
        }
        if (priemer1error == true || priemer2error == true) {
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
        setTimeout(function(){
            novyForm.submit();
        }, 2000); 
        
    });
    
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
    
    $("tr").click(function() {
        var currentId = $(this).attr('id');
        
        currentId = "#go" + currentId;

        
      
        $(currentId).click();
        
    });

    $(".addClick").click(function() {
        var currentId = $(this).attr('id');
        currentId = currentId.replace('go','');
        var modalId = "#input" + currentId;
        setTimeout(function(){
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


