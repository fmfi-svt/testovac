/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

var lastEdit = '';

jQuery(document).ready(function ($) {
    "use strict";
    
    $('.menocheck').blur(function() {
        // meno musi mat aspon jeden znak
        $(this).closest("td").find('.errorcontrol').hide();
        if ($(this).val().length < 1) {
            $(this).closest("td").append('<img src="cross.png" width="15" class="errorcontrol" />');
            menoerror = true;
        } else {
            menoerror = false;
            $(this).closest("td").append('<img src="tick-ok.png" width="15" class="errorcontrol" />');
        }
    });
    
    $('.priezviskocheck').blur(function() {
        // meno musi mat aspon jeden znak
        $(this).closest("td").find('.errorcontrol').hide();
        if ($(this).val().length < 1) {
            $(this).closest("td").append('<img src="cross.png" width="15" class="errorcontrol" />');
            priezviskoerror = true;
        } else {
            priezviskoerror = false;
            $(this).closest("td").append('<img src="tick-ok.png" width="15" class="errorcontrol" />');
        }
    });
    
    $('.datecheck').blur(function() {
        // datum moze byt v tvare dd-mm-yyyy, pricom rok v rozsahu 1910-1999
//        var validateDate2 = function(date) {
//            return date.match(/^(0[1-9]|[12][0-9]|3[01])[.](0[1-9]|1[012])[.](19[1-9])\d$/);
//        };
        var validateDate = function (s) {
            var bits = s.split('.');
            var d = new Date(bits[2] + '/' + bits[1] + '/' + bits[0]);
            if (bits[2] > 1998 || bits[2] < 1942) {
                datumvekerror = true;
            } else {
                datumvekerror = false;
            }
            return !!(d && (d.getMonth() + 1) == bits[1] && d.getDate() == Number(bits[0]));
        } 
        $(this).closest("td").find('.errorcontrol').hide();
        if (!validateDate($(this).val())) {
            $(this).closest("td").append('<img src="cross.png" width="15" class="errorcontrol" />');
            datumerror = true;
        } else {
            datumerror = false;
            $(this).closest("td").append('<img src="tick-ok.png" width="15" class="errorcontrol" />');
        };
    });
    
    $('.priemer1check').blur(function() {
        // priemer moze byt v rangi 1-4
        var validateAverage = function(average) {
            if (average < 1 || average > 4 || !average.match(/^\d([,.]\d{0,2})?$/)) {
                return false;
            }
            return true;
        };
        $(this).closest("td").find('.errorcontrol').hide();
        if ($(this).val().length < 1) { 
		priemer1error = false;
		return;
	}
	if (!validateAverage($(this).val())) {
            $(this).closest("td").append('<img src="cross.png" width="15" class="errorcontrol" />');
            priemer1error = true;
        } else {
            $(this).closest("td").append('<img src="tick-ok.png" width="15" class="errorcontrol" />');
            priemer1error = false;
        };        
    });
    
    $('.priemer2check').blur(function() {
        // priemer moze byt v rangi 1-4
        var validateAverage = function(average) {
            if (average < 1 || average > 4 || !average.match(/^\d([,.]\d{0,2})?$/)) {
                return false;
            }
            return true;
        };

        $(this).closest("td").find('.errorcontrol').hide();
        if ($(this).val().length < 1) { 
		priemer2error = false;
		return;
	}
        if (!validateAverage($(this).val())) {
            $(this).closest("td").append('<img src="cross.png" width="15" class="errorcontrol" />');
            priemer2error = true;
        } else {
            $(this).closest("td").append('<img src="tick-ok.png" width="15" class="errorcontrol" />');
            priemer2error = false;
        };        
    });
    
    $('.pidcheck').blur(function() {
        var checkDuplicatePid = function(pidd,el) {
            var pidtds = $("td.pidtd");
            var focusedId = el.closest('tr').find('.idsub').val();
 	    $.each(pidtds, function () {
	//	if ($(this).closest('tr').find('.idsub').val() == el.closest('tr').find('.idsub').val()) {
	//		alert('FUCK');
	//	}
                if ($(this).text().length > 0) {
               // if ($(this).text().length > 0) {
                    if (($(this).text() == pidd) && ($(this).closest('tr').find('.idsub').val() != focusedId)) {
		    pidduplerror = true;
                    }
                }
            });
        };        
        var addHyphens = function(pid) {
            var str1 = pid.substr(0,4);
            var str2 = pid.substr(4,4);
            var str3 = pid.substr(8,4);
            var str4 = pid.substr(12,4);
            var ret;
            ret = str1 + '-' + str2 + '-' + str3 + '-' + str4;   
            return ret;
        }
        var removeHyphens = function(pid) {
            var splitted = pid.split("-");
            var ret = splitted[0] + splitted[1] + splitted[2] + splitted[3];
            return ret;
        }       
        var pid = $(this).val();
        var isPidVerhoeff;
        
        var focusedElement = $(this);
        pidduplerror = false;
        
        if (pid.match(/^(\d{16})$/)) {
            checkDuplicatePid(addHyphens(pid),focusedElement);
        }
        if (pid.match(/^((\d{4})-(\d{4})-(\d{4})-(\d{4}))$/)) {
            checkDuplicatePid(pid,focusedElement);
            pid = removeHyphens(pid,focusedElement);
        }
        
        if (pid.match(/^(\d{16})$/) && pidduplerror == false) {
            var c = (parseInt(pid[3]) + parseInt(pid[7])) % 10;
            if (c == 2) {
                pidrocnikerror = false;
            } else {
                pidrocnikerror = true;
            }
            $.ajax({
                type : 'POST',
                url : 'verhoeffChecker.php',
                dataType : 'html',
                data: {
                    pidd : pid
                },
                success : function(data){
                    focusedElement.closest("td").find('.errorcontrol').hide();
                    isPidVerhoeff = data;
                    if (isPidVerhoeff == 'true' && pidrocnikerror == false) {
                        focusedElement.val(addHyphens(pid));
                        focusedElement.closest("td").append('<img src="tick-ok.png" width="15" class="errorcontrol" />');
                        piderror = false;
                    } else {
                        focusedElement.closest("td").append('<img src="cross.png" width="15" class="errorcontrol" />');
                        piderror = true;
                    }
                }
            });
        } else {
            focusedElement.closest("td").find('.errorcontrol').hide();
            focusedElement.closest("td").append('<img src="cross.png" width="15" class="errorcontrol" />');

            piderror = true;
        }
        
    });

});
