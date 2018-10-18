/**
 * @file
 *
 * Handles selection in localStorage elements.
 */

$(document).ready(function () {
  // Deal with start and end date ///////////////////////////////////////////////////

  // If we can't retrieve start or end date from localStorage, we create them.
  var startString = localStorage.getItem('startDate');
  var endString = localStorage.getItem('endDate');

  var minDate = period.start.substring(0,10).split('-') ;
  minDate = minDate[2] + '/' + minDate[1] + '/' + minDate[0];
  var maxDate = period.end.substring(0,10).split('-') ;
  maxDate = maxDate[2] + '/' + maxDate[1] + '/' + maxDate[0];

  if (startString == null || startString == null) {
    var now = new Date();
    startDate = new Date(new Date().setMonth(now.getMonth() - 6));
    startString = startDate.getDate() < 10 ? '0' + startDate.getDate() : startDate.getDate();
    startString += '/';
    startString += startDate.getMonth() < 9 ? '0' + (startDate.getMonth() + 1) : (startDate.getMonth() + 1);
    startString += '/';
    startString += startDate.getFullYear();
    localStorage.setItem('startDate', startString);

    endDate = now;
    endString = endDate.getDate() < 10 ? '0' + endDate.getDate() : endDate.getDate();
    endString = '/';
    endString += endDate.getMonth() < 9 ? '0' + (endDate.getMonth() + 1) : (endDate.getMonth() + 1);
    endString = '/';
    endString += endDate.getFullYear();
    localStorage.setItem('endDate', endDate);
  }

  // Initiate datepicker.
  $('#start-date').val(startString);
  $('#end-date').val(endString);
  $('.input-daterange').datepicker({
    format: 'dd/mm/yyyy',
    endDate: maxDate,
    startDate: minDate,
    language: 'fr'
  });

  // Add event on refresh button.
  $('.pilea-select-date').click(function(e) {
    // Store new value in localStorage.
    localStorage.setItem('startDate', $("#start-date").val());
    localStorage.setItem('endDate', $("#end-date").val());

    // Tell the world we have new values.
    document.dispatchEvent(new Event('selection'));

    e.preventDefault(); // avoid to execute the actual submit of the form.
  });

  // Add event on week selection button.
  $('.pilea-select-week').click(function(e) {
    // Store new value in localStorage.
    var now = new Date();
    var startDate = new Date(new Date().setDate(now.getDate() - 7));
    var startString = startDate.getDate() < 10 ? '0' + startDate.getDate() : startDate.getDate();
    startString += '/';
    startString += startDate.getMonth() < 9 ? '0' + (startDate.getMonth() + 1) : (startDate.getMonth() + 1);
    startString += '/';
    startString += startDate.getFullYear();

    var endDate = new Date(new Date().setDate(now.getDate() - 1));
    var endString = endDate.getDate() < 10 ? '0' + endDate.getDate() : endDate.getDate();
    endString += '/';
    endString += endDate.getMonth() < 9 ? '0' + (endDate.getMonth() + 1) : (endDate.getMonth() + 1);
    endString += '/';
    endString += endDate.getFullYear();

    localStorage.setItem('startDate', startString);
    localStorage.setItem('endDate', endString);
    $('#start-date').val(startString);
    $('#end-date').val(endString);
    $('.input-daterange').datepicker({
      format: 'dd/mm/yyyy',
      endDate: maxDate,
      startDate: minDate,
      language: 'fr'
    });

    // Tell the world we have new values.
    document.dispatchEvent(new Event('selection'));

    e.preventDefault(); // avoid to execute the actual submit of the form.
  });

  // Add event on month selection button.
  $('.pilea-select-month').click(function(e) {
    // Store new value in localStorage.
    var now = new Date();
    var startDate = new Date(new Date().setMonth(now.getMonth() - 1));
    var startString = startDate.getDate() < 10 ? '0' + startDate.getDate() : startDate.getDate();
    startString += '/';
    startString += startDate.getMonth() < 9 ? '0' + (startDate.getMonth() + 1) : (startDate.getMonth() + 1);
    startString += '/';
    startString += startDate.getFullYear();

    var endDate = new Date(new Date().setDate(now.getDate() - 1));
    var endString = endDate.getDate() < 10 ? '0' + endDate.getDate() : endDate.getDate();
    endString += '/';
    endString += endDate.getMonth() < 9 ? '0' + (endDate.getMonth() + 1) : (endDate.getMonth() + 1);
    endString += '/';
    endString += endDate.getFullYear();

    localStorage.setItem('startDate', startString);
    localStorage.setItem('endDate', endString);
    $('#start-date').val(startString);
    $('#end-date').val(endString);
    $('.input-daterange').datepicker({
      format: 'dd/mm/yyyy',
      endDate: maxDate,
      startDate: minDate,
      language: 'fr'
    });

    // Tell the world we have new values.
    document.dispatchEvent(new Event('selection'));

    e.preventDefault(); // avoid to execute the actual submit of the form.
  });

  // Add event on year selection button.
  $('.pilea-select-year').click(function(e) {
    // Store new value in localStorage.
    var now = new Date();
    var startDate = new Date(new Date().setFullYear(now.getFullYear() - 1));
    var startString = startDate.getDate() < 10 ? '0' + startDate.getDate() : startDate.getDate();
    startString += '/';
    startString += startDate.getMonth() < 9 ? '0' + (startDate.getMonth() + 1) : (startDate.getMonth() + 1);
    startString += '/';
    startString += startDate.getFullYear();

    var endDate = new Date(new Date().setDate(now.getDate() - 1));
    var endString = endDate.getDate() < 10 ? '0' + endDate.getDate() : endDate.getDate();
    endString += '/';
    endString += endDate.getMonth() < 9 ? '0' + (endDate.getMonth() + 1) : (endDate.getMonth() + 1);
    endString += '/';
    endString += endDate.getFullYear();

    localStorage.setItem('startDate', startString);
    localStorage.setItem('endDate', endString);
    $('#start-date').val(startString);
    $('#end-date').val(endString);
    $('.input-daterange').datepicker({
      format: 'dd/mm/yyyy',
      endDate: maxDate,
      startDate: minDate,
      language: 'fr'
    });

    // Tell the world we have new values.
    document.dispatchEvent(new Event('selection'));

    e.preventDefault(); // avoid to execute the actual submit of the form.
  });

  // Add event on select all button.
  $('.pilea-select-all').click(function(e) {
    // Store new value in localStorage.
    localStorage.setItem('startDate', minDate);
    localStorage.setItem('endDate', maxDate);

    $('#start-date').val(minDate);
    $('#end-date').val(maxDate);
    $('.input-daterange').datepicker({
      format: 'dd/mm/yyyy',
      endDate: '-1d',
      language: 'fr'
    });

    // Tell the world we have new values.
    document.dispatchEvent(new Event('selection'));

    e.preventDefault(); // avoid to execute the actual submit of the form.
  });

  // Deal with frequency ///////////////////////////////////////////////////

  //If we can't retrieve frequency from localStorage, we create it.
  var frequency = localStorage.getItem('frequency');

  if (frequency == null) {
    frequency = 'month';
    localStorage.setItem('frequency', frequency);
  }

  // Initiate frequency form
  $('input:radio[name="frequency"]').val([frequency]);

  // Add event on change
  $('input:radio[name="frequency"]').change(function(e) {
    localStorage.setItem('frequency', $(this).val());

    // Tell the world we have new values.
    document.dispatchEvent(new Event('selection'));
  })

})
