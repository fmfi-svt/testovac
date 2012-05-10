
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


var _idseq_next = 0;
function idseq() {
  return 'id'+(_idseq_next++);
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

  var pidInputId = idseq();
  $form = $('<form/>', { id: 'login-form' }).appendTo('body');
  $('<label/>', { 'for': pidInputId, text: 'Zadajte ID: ' }).appendTo($form);
  $pidInput = $('<input type="text" />').attr({ id: pidInputId, name: 'pid', maxlength: '16' }).appendTo($form).focus();
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
  // bindneme vseobecne eventy
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


Tester.booleanQuestionWidget = function (valueChanged, state) {
  var id = idseq();
  var $widget = $('<div/>', { 'class': 'boolean-widget' });
  $widget.append('<span><input type="radio" name="'+id+'" id="'+id+'y" value="true"><label for="'+id+'y"> ÁNO </label></span>');
  $widget.append('<span><input type="radio" name="'+id+'" id="'+id+'n" value="false"><label for="'+id+'n"> NIE </label></span>');
  $widget.append('<span><input type="radio" name="'+id+'" id="'+id+'x" value=""><label for="'+id+'x"> nezodpovedané </label></span>');
  $widget.find('#'+id+(state == 'true' ? 'y' : state == 'false' ? 'n' : 'x'))[0].checked = true;
  $widget.on('change', 'input:radio', function (event) { valueChanged($(this).val()); });
  return $widget;
}


Tester.textQuestionWidget = function (valueChanged, state) {
  var id = idseq();
  var $widget = $('<div/>', { 'class': 'text-widget' });
  $widget.append('<span><input type="text" name="'+id+'" id="'+id+'"></span>');
  $widget.find('input').val(state);
  $widget.on('change', 'input', function (event) { valueChanged($(this).val()); })
  return $widget;
}


function showQuestions(questions, state) {
  Tester.questions = questions;

  var tocLinks = [];
  var $toc = $('<div/>', { id: 'toc' }).appendTo('body');
  var $ul = $('<ul/>').appendTo($toc);
  $.each(questions, function (i, q) {
    var $li = $('<li/>').appendTo($ul);
    tocLinks[i] = Tester.fakelink().
      text((i+1)+'. '+q.body).
      on('click', function (event) { goToQuestion(i); }).
      appendTo($li);
  });

  var stateTable = [];
  for (var i = 0; i < questions.length; i++) {
    stateTable[i] = {};
  }
  for (var i = 0; i < state.length; i++) {
    var entry = state[i];
    stateTable[entry.qorder][entry.qsubord] = entry.value;
  }

  var $status = $('<div/>', { id: 'status' }).appendTo('body');
  $('<div/>', { 'class': 'pid', text: Tester.pid }).appendTo($status);

  var $submit = $('<input type="button" />').attr({ value: 'Odovzdať test' }).appendTo($('<div />', { 'class': 'submit' }).appendTo($status));
  $submit.on('click', function () {
    saveEvents();
    var incomplete = $toc.find('.incomplete').length;
    var message = 'Ste si istí, že chcete ukončiť vypĺňanie testu?';
    if (incomplete == 1) message += '\n\n1 otázka ešte nie je zodpovedaná!';
    if (incomplete > 1 && incomplete < 5) message += '\n\n'+incomplete+' otázky ešte nie sú zodpovedané!';
    if (incomplete >= 5) message += '\n\n'+incomplete+' otázok ešte nie je zodpovedaných!';
    if (confirm(message)) doClose();
  });

  var $main = $('<div/>', { id: 'main' }).appendTo('body');
  var $questions = $('<div/>', { id: 'questions' }).appendTo($main);
  $.each(questions, function (i, q) {
    function updateToc() {
      var complete = true;
      for (var j in stateTable[i]) {
        if (stateTable[i][j] === '') complete = false;
      }
      tocLinks[i].toggleClass('incomplete', !complete);
    }
    function addSub(j) {
      function valueChanged(value) {
        stateTable[i][j] = value;
        emitEvent({ qorder: i, qsubord: j, value: value, time: +new Date() });
        updateToc();
      }
      var $option = $('<div/>', { 'class': 'option' }).appendTo($options);
      $('<div/>', { 'class': 'text', text: j+') '+q[j].body }).appendTo($option);
      if (stateTable[i][j] === undefined) stateTable[i][j] = '';
      if (q[j].type == 'bool') {
        $option.append(Tester.booleanQuestionWidget(valueChanged, stateTable[i][j]));
      }
      else {
        $option.append(Tester.textQuestionWidget(valueChanged, stateTable[i][j]));
      }
    }

    var $question = $('<div/>', { 'class': 'question' }).appendTo($questions);
    $('<h3/>', { 'class': 'statement', text: (i+1)+'. '+q.body }).appendTo($question);
    var $options = $('<div/>', { 'class': 'options' }).appendTo($question);
    for (var jc = 97; q[String.fromCharCode(jc)]; jc++) {
      addSub(String.fromCharCode(jc));
    }
    updateToc();
  });
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


function doClose() {
  $('#main').hide();
  $('#toc').hide();
  $('#status').hide();
  var $closing = $('<div />').attr('class', 'global-message').appendTo('body');
  $closing.text('Prosím čakajte...');

  function attemptClose() {
    if (Tester.eventsBegin != Tester.eventsEnd) {
      sendEvents();
      setTimeout(attemptClose, 500);
      return;
    }

    var request = { action: 'close', pid: Tester.pid, sessid: Tester.sessid };
    ajaj(request, function (data) {
      if (data.error == 'invalid pid') {
        alert('Nesprávne ID, uáá.');
      }
      else if (data.error == 'invalid sessid') {
        alert('Tento užívateľ sa medzitým prihlásil z iného počítača. Z tohto počítača bude odhlásený.');
        location.reload();
      }
      else if (data.error) {
        alert('Chyba pri komunikácii so serverom, uáá.');
      }
      else {
        $closing.text('Test bol úspešne ukončený.');
      }
    });
  }
  attemptClose();
}


$(init);

})(jQuery);
