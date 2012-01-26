
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
    doLogin($('#login-form input.id').val());
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
  $('#login-form input.id').focus();
}


function ajaj(data, success, error) {
  $.ajax({
    type: 'POST', url: '?', dataType: 'json',
    data: data, success: success, error: error
  });
}


function doLogin(id) {
  function success(data, textStatus, jqXHR) {
    log('success', data, textStatus, jqXHR);
    // TODO
    // zapamataj si id, defocusni submit ak treba, stiahni a zobraz otazky.
    // chceme si aj zapamatat ze sme prihlaseni a druhykrat sa neprihlasovat?
  }
  function error(jqXHR, textStatus, errorThrown) {
    log('error', textStatus, errorThrown, jqXHR);
    alert('Chyba pri komunikácii so serverom. Skúste prosím znova.\nAk problém pretrváva, kontaktujte technickú podporu.\nFakt dúfam, že niekto vymyslí lepšiu hlášku.');
  }
  ajaj({ action: 'login', id: id }, success, error);
}


$(init);

})(jQuery);
