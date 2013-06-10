
// Immediately Invoked Function Expression
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


Tester.now = function () {
  return (+new Date())/1000 - Tester.timeOffset;
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
  $('<label/>', { 'for': pidInputId, text: 'Zadajte vaše ID: ' }).appendTo($form);
  $pidInput = $('<input type="text" class="pid" />').attr({ id: pidInputId, name: 'pid', maxlength: '19' }).appendTo($form).focus();
  $(document.createTextNode(' ')).appendTo($form);
  $('<input type="submit" />').attr('value', 'OK').appendTo($form);
  if (demoPid) {
    $('<p/>').appendTo($form).
      text('Skúšobná verzia – použite toto ID: ').
      append($('<span class="pid" />').text(demoPid));
  }
  else {
    $('<p/>').appendTo($form).text('Vaše ID je napísané na vašom náramku. (Zadávajte aj s pomlčkami.)');
  }
  $form.on('submit', function (event) {
    Tester.doLogin($pidInput.val(), loginSuccess, loginFailure);
    return false;
  });
}


function init() {
  // bindneme vseobecne eventy
  if (Tester.config.disableRefresh) {
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

  Tester.showLoginForm(Tester.config.demoPid, showQuestions);
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
    if (data.error == 'login blocked') {
      failure(data.error, 'Skúška ešte nebola zahájená. Počkajte, kým skončí poučenie.');
    }
    else if (data.error == 'invalid pid') {
      failure(data.error, 'Nesprávne ID. Skontrolujte, či ste pri prepise nespravili preklep.');
    }
    else if (data.error == 'closed') {
      failure(data.error, 'Váš test už je ukončený.');
    }
    else if (data.error) {
      failure(data.error, 'Chyba pri komunikácii so serverom. Kontaktujte technický dozor.');
    }
    else {
      Tester.pid = pid;
      Tester.sessid = data.sessid;
      Tester.timeOffset = 0;
      if (Tester.config.attemptTimeCorrection) {
        Tester.timeOffset = (+new Date())/1000 - data.now;
      }
      Tester.beginTime = data.beginTime;
      Tester.events = {};
      Tester.eventsBegin = data.savedEvents;
      Tester.eventsEnd = data.savedEvents;

      success(data.questions, data.state);
    }
  });
}


var _overlay_initialized = false;
Tester.overlay = function () {
  if (!_overlay_initialized) {
    $(document).on('keydown', function (event) {
      if (event.which == 27) $('.overlay').trigger('close');
    });
    _overlay_initialized = true;
  }

  var $overlay = $('<div class="overlay"><div class="dialog-wrapper"><div class="dialog"></div></div></div>');
  $overlay.appendTo('body');
  $overlay.on('close', function () { $(this).remove(); });
  return $overlay;
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
  $widget.append('<span><input type="text" name="'+id+'" id="'+id+'" maxlength="100"></span>');
  $widget.find('input').val(state);
  $widget.on('change', 'input', function (event) { valueChanged($(this).val()); })
  return $widget;
}


function showQuestions(questions, state) {
  Tester.questions = questions;

  var tocLinks = [];
  var $toc = $('<div/>', { id: 'toc' }).appendTo('body');
  var $ol = $('<ol/>').appendTo($toc);
  $.each(questions, function (i, q) {
    var $body = $('<div/>').html(q.body);
    $body.find('br, hr').replaceWith(' ');   // add a space in place of every <br> and <hr>
    var $li = $('<li/>').appendTo($ol);
    $('<div>&nbsp;</div>').css({width:0,height:0}).appendTo($li); // workaround for https://bugs.webkit.org/show_bug.cgi?id=13332 - numbers not showing in the <li>
    tocLinks[i] = Tester.fakelink().
      text($body.text()).
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
    var incomplete = questions.length - $toc.find('.complete').length;

    var $overlay = Tester.overlay();
    var $dialog = $overlay.find('.dialog');

    $('input').attr('disabled', 'disabled');
    $overlay.on('close', function () { $('input').removeAttr('disabled'); });

    $('<p />').text('Ste si istí, že chcete ukončiť vypĺňanie testu?').appendTo($dialog);
    if (incomplete != 0) {
      $('<p />').text(
        incomplete == 1 ? incomplete+' otázka ešte nie je zodpovedaná!' :
        incomplete <= 4 ? incomplete+' otázky ešte nie sú zodpovedané!' :
        incomplete+' otázok ešte nie je zodpovedaných!'
      ).appendTo($dialog);
    }

    $dialog.append($('<p><input type="button" class="submit" value="Odovzdať test"> <input type="button" class="cancel" value="Pokračovať v teste"></p>'));
    $dialog.find('.cancel').on('click', function () { $overlay.trigger('close'); });
    $dialog.find('.submit').on('click', function () { $overlay.trigger('close'); doClose(); });
    $dialog.find(incomplete ? '.cancel' : '.submit').focus();
  });

  var $main = $('<div/>', { id: 'main' }).appendTo('body');
  var $questions = $('<div/>', { id: 'questions' }).appendTo($main);
  $.each(questions, function (i, q) {
    function updateToc() {
      var complete = true;
      for (var j in stateTable[i]) {
        if (stateTable[i][j] === '') complete = false;
      }
      tocLinks[i].toggleClass('complete', complete);
    }
    function addSub(j) {
      function valueChanged(value) {
        stateTable[i][j] = value;
        emitEvent({ qorder: i, qsubord: j, value: value, time: Math.floor(Tester.now()) });
        updateToc();
      }
      var $option = $('<div/>', { 'class': 'option' }).appendTo($options);
      $('<div/>', { 'class': 'text', html: '<div class="num">'+j+')&nbsp;</div>'+q[j].body }).appendTo($option);
      if (stateTable[i][j] === undefined) stateTable[i][j] = '';
      if (q[j].type == 'bool') {
        $option.append(Tester.booleanQuestionWidget(valueChanged, stateTable[i][j]));
      }
      else {
        $option.append(Tester.textQuestionWidget(valueChanged, stateTable[i][j]));
      }
    }

    var $question = $('<div/>', { 'class': 'question' }).appendTo($questions);
    $('<h3/>', { 'class': 'statement', html: '<div class="num">'+(i+1)+'.&nbsp;</div>'+q.body }).appendTo($question);
    var $options = $('<div/>', { 'class': 'options' }).appendTo($question);
    for (var jc = 97; q[String.fromCharCode(jc)]; jc++) {
      addSub(String.fromCharCode(jc));
    }
    updateToc();
  });

  var $stopwatch = $('<span/>').addClass('stopwatch').appendTo($submit.parent());
  var lastTime;
  function updateStopwatch() {
    var elapsed = Math.floor(Tester.now() - Tester.beginTime);
    if (elapsed == lastTime) return;
    lastTime = elapsed;
    if (elapsed > Tester.config.timeLimit) {
      doClose(true);
      return;
    }
    var now = Tester.config.timeLimit - elapsed;
    var minutes = ''+Math.floor(now/60);
    if (minutes.length < 2) minutes = '0' + minutes;
    var seconds = ''+(now%60);
    if (seconds.length < 2) seconds = '0' + seconds;
    var text = minutes+':'+seconds;
    $stopwatch.text(text);
  }
  Tester.stopwatchInterval = setInterval(updateStopwatch, 250);

  if (Tester.config.savingInterval) {
    Tester.savingInterval = setInterval(function () {
      saveEvents(true);
    }, Tester.config.savingInterval);
  }
}


function goToQuestion(question) {
  $('#main .statement').eq(question).css('background', '#FF8');
  setTimeout(function () {
    $('#main .statement').eq(question).css('background', 'transparent');
  }, 400);
  $('#main').animate({ scrollTop: $('.question')[question].offsetTop }, 'fast');
}


function showGlobalMessage(html) {
  $('.overlay').trigger('close');
  $('#main').hide();
  $('#toc').hide();
  $('#status').hide();
  if (Tester.stopwatchInterval !== undefined) {
    clearInterval(Tester.stopwatchInterval);
    Tester.stopwatchInterval = undefined;
  }
  if (Tester.savingInterval !== undefined) {
    clearInterval(Tester.savingInterval);
    Tester.savingInterval = undefined;
  }
  $('.global-message').remove();
  $('<div />').attr('class', 'global-message').appendTo('body').html(html);
}


function saveEvents(force) {
  if (Tester.eventsBegin == Tester.eventsEnd && !force) return;
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
    if (data.error == 'invalid sessid') {
      alert('Tento užívateľ sa medzitým prihlásil z iného počítača. Z tohto počítača bude odhlásený.');
      location.reload();
    }
    else if (data.error == 'closed') {
      alert('Váš test už je ukončený.');
      location.reload();
    }
    else if (data.error) {
      alert('Chyba pri komunikácii so serverom. Kontaktujte technický dozor.');
    }
    else {
      while (Tester.eventsBegin < data.savedEvents) {
        delete Tester.events[Tester.eventsBegin++];
      }
      Tester.eventsEnd = Math.max(Tester.eventsBegin, Tester.eventsEnd);
      if (Tester.eventsBegin != Tester.eventsEnd) saveEvents();
      Tester.beginTime = data.beginTime;
    }
  });
}


function emitEvent(event) {
  log(['event', Tester.eventsEnd, event]);
  Tester.events[Tester.eventsEnd++] = event;
  if (Tester.config.saveAfterEmit) saveEvents();
}


function doClose(timedOut) {
  showGlobalMessage('Prosím čakajte...');

  function attemptClose() {
    if (Tester.eventsBegin != Tester.eventsEnd) {
      sendEvents();
      setTimeout(attemptClose, 500);
      return;
    }

    if (timedOut) {
      showGlobalMessage('Váš čas vypršal. Doteraz zadané odpovede boli uložené.');
      return;
    }

    var request = { action: 'close', pid: Tester.pid, sessid: Tester.sessid };
    ajaj(request, function (data) {
      if (data.error == 'invalid sessid') {
        alert('Tento užívateľ sa medzitým prihlásil z iného počítača. Z tohto počítača bude odhlásený.');
        location.reload();
      }
      else if (data.error) {
        alert('Chyba pri komunikácii so serverom. Kontaktujte technický dozor.');
        showGlobalMessage('Chyba pri komunikácii so serverom. Kontaktujte technický dozor.');
      }
      else {
        showGlobalMessage('Vaše odpovede boli uložené.');
      }
    });
  }
  attemptClose();
}


$(init);

})(jQuery);
