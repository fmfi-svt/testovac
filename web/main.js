
(function ($, undefined) {
"use strict";


function log() {
  Log.push(Array.prototype.slice.call(arguments));
  if (window.console && console.log) console.log.apply(console, arguments);
}


function init() {
  window.Log = [];

  // bindneme vsetky eventy
  $(document).on('submit', '#login-form', function (event) {
    doLogin($('#login-form input.uid').val());
    return false;
  });
  // TOC open
  // mozno TOC starred button
  // mozno NEXT button
  // mozno PREV button
  // mozno TOGGLE-MODE button (continuous vs per-page)
  // zakliknutie jednotlivych moznosti
  // FINISH button (a UI okolo "are you sure")

  // inicializujeme HTML
  $('body').append(Templates.main);
  $('#login-form').show();
  $('#login-form input.uid').focus();
}


function ajaj(requestData, callback) {
  function success(data, textStatus, jqXHR) {
    log('ajax success', data, textStatus, jqXHR);
    callback(data || { error: 'empty response' });
  }
  function error(jqXHR, textStatus, errorThrown) {
    log('ajax error', textStatus, errorThrown, jqXHR);
    callback({ error: 'ajax error', textStatus: textStatus, errorThrown: errorThrown, jqXHR: jqXHR });
  }
  $.ajax({
    type: 'POST', url: '?', dataType: 'json',
    data: requestData, success: success, error: error
  });
}


var loginBusy = false;
function doLogin(uid) {
  if (loginBusy) return;
  loginBusy = true;

  ajaj({ action: 'login', uid: uid }, function (data) {
    loginBusy = false;
    if (data.error == 'invalid id') {
      alert('Nesprávne ID, uáá.');
    }
    else if (data.error == 'closed') {
      alert('Váš test už je ukončený, uáá.');
    }
    else if (data.error) {
      alert('Chyba pri komunikácii so serverom, uáá.');
    }
    else {
      $('#login-form *').blur();
      $('#login-form').hide();
      loginBusy = true;

      data.uid = uid;
      window.UserInfo = data;

      // TODO zobrazit otazky a TOC
      // (zatial proste dumpneme json)
      $('body').append(document.createTextNode(JSON.stringify(data)));
    }
  });
}


$(init);

})(jQuery);
