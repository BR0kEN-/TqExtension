(function ($) {
  'use strict';

  window.errors = [];

  function catchError(error) {
    if (typeof(error) !== 'object') {
      return;
    }

    window.errors.push(error);
  }

  window.onerror = function (message, url, line, column) {
    catchError({
      type: 'js',
      message: message,
      location: url + ':' + line + ':' + column
    });
  };

  $(document).ajaxError(function (event, xhr, settings, error) {
    catchError({
      type: 'xhr',
      url: settings.url,
      method: settings.type,
      message: error,
      statusCode: xhr.status,
      response: xhr.responseJSON ? xhr.responseText : null
    });
  });
})(jQuery);
