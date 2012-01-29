
(function ($, undefined) {
"use strict";


window.log = function () {
  var args = Array.prototype.slice.call(arguments);
  log.history.push(args);
  if (window.console) console.log(args);
}
window.log.history = [];


function init() {
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
  if (loginBusy || window.UserInfo) return;
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

      data.uid = uid;
      data.events = [];
      data.localSerial = data.serverSerial;
      window.UserInfo = data;

      showQuestions();
    }
  });
}


function showQuestions() {
  var $toc = $('<div/>', { id: 'toc' }).appendTo('body');
  var $status = $('<div/>', { id: 'status' }).appendTo('body');
  var $main = $('<div/>', { id: 'main' }).appendTo('body');
  var $questions = $('<div/>', { id: 'questions' }).appendTo($main);
  $.each(UserInfo.questions, function (i, q) {
    var $question = $('<div/>', { 'class': 'question' }).appendTo($questions);
    $('<h3/>', { 'class': 'statement', text: (i+1)+'. '+q[0] }).appendTo($question);
    var $options = $('<div/>', { 'class': 'options' }).appendTo($question);
    for (var j = 1; j < q.length; j++) {
      var $option = $('<div/>', { 'class': 'option' }).appendTo($options);
      $('<div/>', { 'class': 'text', text: String.fromCharCode(96+j)+') '+q[j] }).appendTo($option);
      $('<span class="control"><input type="radio" name="q'+i+'o'+j+'" value="y"> ÁNO </span>').appendTo($option);
      $('<span class="control"><input type="radio" name="q'+i+'o'+j+'" value="n"> NIE </span>').appendTo($option);
    }
  });
}


$(init);

})(jQuery);
