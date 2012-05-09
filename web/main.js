
(function ($, undefined) {
"use strict";


window.log = function (message) {
  log.history.push(message);
  if (window.console) console.log.apply(console, message);
}
window.log.history = [];



var _fakelink_initialized = false;
Tester.fakelink = function () {
  // namiesto ozajstnych <a> pouzivame falosne odkazy, aby sa neotvarali
  // v novom tabe pri kliknuti strednym tlacitkom. lenze co s klavesnicou? pri
  // <a> staci dat onclick a zavola sa to aj, ked sa stlaci enter. HTML5 spec
  // tvrdi, ze elementy, co maju tabindex, to maju robit tiez, ale nerobia.
  // nejaky W3C clanok <http://www.w3.org/TR/WCAG20-TECHS/SCR29> poradil toto:
  if (!_fakelink_initialized) {
    $(document).on('keypress', '.fakelink', function (event) {
      if (event.which == 13) {
        $(this).click();
        return false;
      }
      return true;
    });
    _fakelink_initialized = true;
  }

  return $('<span class="fakelink" tabindex="0" role="button"></span>');
}


Tester.showLoginForm = function (demoPid, showQuestions) {
  var $form, $pidInput;

  function loginSuccess(questions, state) {
    $form.find('*').blur();
    $form.hide();
    showQuestions(questions, state);
  }
  function loginFailure(errorCode, errorMessage) {
    alert(errorMessage);
    $pidInput[0].select();
  }

  $form = $('<form/>', { id: 'login-form' }).appendTo('body');
  $('<label/>', { 'for': 'login-form-pid', text: 'Zadajte ID: ' }).appendTo($form);
  $pidInput = $('<input type="text" />').attr({ id: 'login-form-pid', name: 'pid', maxlength: '16' }).appendTo($form).focus();
  $('<input type="submit" />').attr('value', 'OK').appendTo($form);
  if (demoPid) {
    $('<p/>').addClass('demo-message').text('Demo – použite ID '+demoPid).appendTo($form);
  }
  $form.on('submit', function (event) {
    Tester.doLogin($pidInput.val(), loginSuccess, loginFailure);
    return false;
  });
}


function init() {
  // bindneme vsetky eventy
  $(document).on('click', '.toclink', function (event) {
    goToQuestion($(this).data('question'));
  });
  // TOC starred button
  $(document).on('change', '#questions input:radio', function (event) {
    emitEvent({
      qorder: $(this).closest('.question').data('qorder'),
      qsubord: $(this).closest('.option').data('qsubord'),
      value: $(this).val(),
      time: +new Date()
    });
  });
  // FINISH button (a UI okolo "are you sure")
  if (Tester.config.disable_refresh) {
    $(document).on('keydown', function (event) {
      if (event.which == 116 && !event.altKey && !event.shiftKey) return false;
      if (event.which == 82 && event.ctrlKey && !event.altKey && !event.shiftKey) return false;
    });
  }
  $(document).on('keydown', function (event) {
    // Firefox pri stlaceni Esc okamzite zastavi vsetky requesty, aj AJAX
    // <https://bugzilla.mozilla.org/show_bug.cgi?id=614304>
    if (event.which == 27) event.preventDefault();
  });

  Tester.showLoginForm(Tester.config.demo_pid, showQuestions);
}


function ajaj(requestData, callback) {
  function success(data, textStatus, jqXHR) {
    log($.extend(['ajax success', data, textStatus], { jqXHR: jqXHR }));
    callback(data || { error: 'empty response' });
  }
  function error(jqXHR, textStatus, errorThrown) {
    log($.extend(['ajax error', textStatus, errorThrown], { jqXHR: jqXHR }));
    callback({ error: 'ajax error', textStatus: textStatus, errorThrown: errorThrown, jqXHR: jqXHR });
  }
  $.ajax({
    type: 'POST', url: '?', dataType: 'json',
    data: requestData, success: success, error: error
  });
}


var _doLogin_busy = false;
Tester.doLogin = function (pid, success, failure) {
  if (_doLogin_busy || Tester.pid) return;
  _doLogin_busy = true;

  ajaj({ action: 'login', pid: pid }, function (data) {
    _doLogin_busy = false;
    if (data.error == 'invalid pid') {
      failure(data.error, 'Nesprávne ID, uáá.');
    }
    else if (data.error == 'closed') {
      failure(data.error, 'Váš test už je ukončený, uáá.');
    }
    else if (data.error) {
      failure(data.error, 'Chyba pri komunikácii so serverom, uáá.');
    }
    else {
      Tester.pid = pid;
      Tester.sessid = data.sessid;
      Tester.beginTime = data.beginTime;
      Tester.events = {};
      Tester.eventsBegin = data.savedEvents;
      Tester.eventsEnd = data.savedEvents;

      success(data.questions, data.state);
    }
  });
}


function showQuestions(questions, state) {
  Tester.questions = questions;

  var $toc = $('<div/>', { id: 'toc' }).appendTo('body');
  var $ul = $('<ul/>').appendTo($toc);
  $.each(questions, function (i, q) {
    var $li = $('<li/>').appendTo($ul);
    Tester.fakelink().
      addClass('toclink').
      text((i+1)+'. '+q.body).
      data('question', i).
      appendTo($li);
  });

  var $status = $('<div/>', { id: 'status' }).appendTo('body');
  $('<div/>', { 'class': 'pid', text: Tester.pid }).appendTo($status);

  var $main = $('<div/>', { id: 'main' }).appendTo('body');
  var $questions = $('<div/>', { id: 'questions' }).appendTo($main);
  $.each(questions, function (i, q) {
    var $question = $('<div/>', { 'class': 'question', 'data-qorder': i }).appendTo($questions);
    $('<h3/>', { 'class': 'statement', text: (i+1)+'. '+q.body }).appendTo($question);
    var $options = $('<div/>', { 'class': 'options' }).appendTo($question);
    for (var jc = 97; q[String.fromCharCode(jc)]; jc++) {
      var j = String.fromCharCode(jc);
      var $option = $('<div/>', { 'class': 'option', 'data-qsubord': j }).appendTo($options);
      $('<div/>', { 'class': 'text', text: j+') '+q[j] }).appendTo($option);
      $('<span class="control"><input type="radio" name="q'+i+'o'+j+'" id="q'+i+'o'+j+'y" value="true"><label for="q'+i+'o'+j+'y"> ÁNO </label></span>').appendTo($option);
      $('<span class="control"><input type="radio" name="q'+i+'o'+j+'" id="q'+i+'o'+j+'n" value="false"><label for="q'+i+'o'+j+'n"> NIE </label></span>').appendTo($option);
    }
  });

  for (var i = 0; i < state.length; i++) {
    var entry = state[i];
    document.getElementById('q' + entry.qorder +
      'o' + entry.qsubord +
      (entry.value == 'true' ? 'y' : 'n')).checked = true;
  }
}


function goToQuestion(question) {
  window.scrollTo(0, $('.question').eq(question).offset().top);
}


function saveEvents() {
  if (Tester.eventsBegin == Tester.eventsEnd) return;
  if (Tester.sendingEvents) return;
  Tester.sendingEvents = true;
  log(['sending event range', Tester.eventsBegin, Tester.eventsEnd]);
  var sentEvents = [];
  for (var i = Tester.eventsBegin; i < Tester.eventsEnd; i++) {
    sentEvents[i - Tester.eventsBegin] = Tester.events[i];
  }
  var request = {
    action: 'save', pid: Tester.pid, sessid: Tester.sessid,
    savedEvents: Tester.eventsBegin, events: sentEvents
  };
  ajaj(request, function (data) {
    Tester.sendingEvents = false;
    if (data.error == 'invalid pid') {
      alert('Nesprávne ID, uáá.');
    }
    else if (data.error == 'invalid sessid') {
      alert('Tento užívateľ sa medzitým prihlásil z iného počítača. Z tohto počítača bude odhlásený.');
      location.reload();
    }
    else if (data.error == 'invalid savedEvents') {
      alert('Chyba v časopriestorovej kontinuite, uáá.');
      location.reload();
    }
    else if (data.error == 'closed') {
      alert('Váš test už je ukončený, uáá.');
    }
    else if (data.error) {
      alert('Chyba pri komunikácii so serverom, uáá.');
    }
    else {
      while (Tester.eventsBegin < data.savedEvents) {
        delete Tester.events[Tester.eventsBegin++];
      }
      Tester.eventsEnd = Math.max(Tester.eventsBegin, Tester.eventsEnd);
      if (Tester.eventsBegin != Tester.eventsEnd) saveEvents();
    }
  });
}


function emitEvent(event) {
  log(['event', Tester.eventsEnd, event]);
  Tester.events[Tester.eventsEnd++] = event;
  // TODO: save events immediately or every X seconds?
  saveEvents();
}


$(init);

})(jQuery);
