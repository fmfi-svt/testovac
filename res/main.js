
(function ($) {
"use strict";

function init() {
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
  $('#content').html(velky_html_obsah);
  $('#login-form').show();
}

function ajax(data, success, error) {
  $.ajax({
    type: 'POST', url: 'action.php', dataType: 'json',
    data: data, success: success, error: error
  });
}

function doLogin(id) {
  function success(data, textStatus, jqXHR) {
    // TODO
    // zapamataj si id, defocusni submit ak treba, stiahni a zobraz otazky.
    // chceme si aj zapamatat ze sme prihlaseni a druhykrat sa neprihlasovat?
  }
  function error(jqXHR, textStatus, errorThrown) {
    // TODO
    // ak je zly kod, povedz ze zly kod.
    // ak je problem s komunikaciou so serverom, povedz to.
  }
  ajax({ action: 'login', id: id }, success, error);
}

$(init);

})(jQuery);
