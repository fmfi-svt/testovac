
(function ($, undefined) {
"use strict";


window.log = function () {
  var args = Array.prototype.slice.call(arguments);
  log.history.push(args);
  if (window.console) console.log(args);
}
window.log.history = [];


function fakelink() {
  return $('<span class="fakelink" tabindex="0" role="button"></span>');
}


function init() {
  // namiesto ozajstnych <a> pouzivame falosne odkazy, aby sa neotvarali
  // v novom tabe pri kliknuti strednym tlacitkom. lenze co s klavesnicou? pri
  // <a> staci dat onclick a zavola sa to aj, ked sa stlaci enter. HTML5 spec
  // tvrdi, ze elementy, co maju tabindex, to maju robit tiez, ale nerobia.
  // nejaky W3C clanok <http://www.w3.org/TR/WCAG20-TECHS/SCR29> poradil toto:
  $(document).on('keypress', '.fakelink', function (event) {
    if (event.which == 13) {
      $(this).click();
      return false;
    }
    return true;
  });

  // bindneme vsetky eventy
  $(document).on('submit', '#login-form', function (event) {
    doLogin($('#login-form-uid').val());
    return false;
  });
  $(document).on('click', '.toclink', function (event) {
    goToQuestion($(this).data('question'));
  });
  // mozno TOC starred button
  // mozno NEXT button
  // mozno PREV button
  // mozno TOGGLE-MODE button (continuous vs per-page)
  // zakliknutie jednotlivych moznosti
  // FINISH button (a UI okolo "are you sure")

  // inicializujeme HTML
  var $form = $('<form/>', { id: 'login-form' }).appendTo('body');
  $('<label/>', { 'for': 'login-form-uid', text: 'Zadajte ID: ' }).appendTo($form);
  $('<input type="text" />').attr({ id: 'login-form-uid', name: 'uid', maxlength: '16' }).appendTo($form).focus();
  $('<input type="submit" />').attr('value', 'OK').appendTo($form);
  if (Tester.config.demo_mode) {
    $('<p/>').addClass('demo-message').text(Tester.config.demo_message).appendTo($form);
  }
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
  if (loginBusy || Tester.userInfo) return;
  loginBusy = true;

  ajaj({ action: 'login', uid: uid }, function (data) {
    loginBusy = false;
    if (data.error == 'invalid uid') {
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
      Tester.userInfo = data;

      showQuestions();
    }
  });
}


function showQuestions() {
  var $toc = $('<div/>', { id: 'toc' }).appendTo('body');
  var $ul = $('<ul/>').appendTo($toc);
  $.each(Tester.userInfo.questions, function (i, q) {
    var $li = $('<li/>').appendTo($ul);
    fakelink().
      addClass('toclink').
      text((i+1)+'. '+q[0]).
      data('question', i).
      appendTo($li);
  });

  var $status = $('<div/>', { id: 'status' }).appendTo('body');
  $('<div/>', { 'class': 'uid', text: Tester.userInfo.uid }).appendTo($status);

  var $main = $('<div/>', { id: 'main' }).appendTo('body');
  var $questions = $('<div/>', { id: 'questions' }).appendTo($main);
  $.each(Tester.userInfo.questions, function (i, q) {
    var $question = $('<div/>', { 'class': 'question' }).appendTo($questions);
    $('<h3/>', { 'class': 'statement', text: (i+1)+'. '+q[0] }).appendTo($question);
    var $options = $('<div/>', { 'class': 'options' }).appendTo($question);
    for (var j = 1; j < q.length; j++) {
      var $option = $('<div/>', { 'class': 'option' }).appendTo($options);
      $('<div/>', { 'class': 'text', text: String.fromCharCode(96+j)+') '+q[j] }).appendTo($option);
      $('<span class="control"><input type="radio" name="q'+i+'o'+j+'" id="q'+i+'o'+j+'y" value="y"><label for="q'+i+'o'+j+'y"> ÁNO </label></span>').appendTo($option);
      $('<span class="control"><input type="radio" name="q'+i+'o'+j+'" id="q'+i+'o'+j+'n" value="n"><label for="q'+i+'o'+j+'n"> NIE </label></span>').appendTo($option);
    }
  });
}


function goToQuestion(question) {
  window.scrollTo(0, $('.question').eq(question).offset().top);
}


$(init);

})(jQuery);
