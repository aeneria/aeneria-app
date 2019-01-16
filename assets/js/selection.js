/**
 * @file
 *
 * Handles selection in localStorage elements.
 */

$(document).ready(function () {

  // Get min and max dates.
  var minDate = period.start.substring(0,10).split('-') ;
  minDate = minDate[2] + '/' + minDate[1] + '/' + minDate[0];
  periodStart = new Date(period.start);
  var maxDate = period.end.substring(0,10).split('-') ;
  maxDate = maxDate[2] + '/' + maxDate[1] + '/' + maxDate[0];
  periodEnd = new Date(period.end);

  // Deal with start and end date ///////////////////////////////////////////////////

  // If we can't retrieve start or end date from localStorage, we create them.
  var startString = localStorage.getItem('startDate');
  var endString = localStorage.getItem('endDate');

  if (startString == null || endString == null) {
    var now = new Date();
    startDate = new Date(new Date().setMonth(now.getMonth() - 6));
    startString = startDate.getDate() < 10 ? '0' + startDate.getDate() : startDate.getDate();
    startString += '/';
    startString += startDate.getMonth() < 9 ? '0' + (startDate.getMonth() + 1) : (startDate.getMonth() + 1);
    startString += '/';
    startString += startDate.getFullYear();
    localStorage.setItem('startDate', startString);

    endString = now.getDate() < 10 ? '0' + now.getDate() : now.getDate();
    endString += '/';
    endString += now.getMonth() < 9 ? '0' + (now.getMonth() + 1) : (now.getMonth() + 1);
    endString += '/';
    endString += now.getFullYear();
    localStorage.setItem('endDate', endString);
  }

  // Initiate datepicker.
  $('.pilea-start-date').val(startString);
  $('.pilea-end-date').val(endString);
  $('.input-daterange').datepicker({
    format: 'dd/mm/yyyy',
    endDate: maxDate,
    startDate: minDate,
    language: 'fr'
  });

  // Add event on refresh button.
  $('.pilea-select-date').click(function(e) {
    var startDate = $(e.target).parent().find(".pilea-start-date");
    var endDate = $(e.target).parent().find(".pilea-end-date");

    // Store new value in localStorage.
    localStorage.setItem('startDate', startDate.val());
    localStorage.setItem('endDate', endDate.val());

    // Tell the world we have new values.
    document.dispatchEvent(new Event('selection'));

    e.preventDefault();
  });

  $('.pilea-select-period a').click(function(e) {

    var now = new Date();
    var startDate = new Date();
    var endDate = new Date();
    switch (e.target.getAttribute('data')) {
      case 'current-week':
        startDate.setDate(now.getDate() - (now.getDay() - 1));
        endDate.setDate(now.getDate() - 1);
        break;
      case 'last-week':
        startDate.setDate(now.getDate() - (now.getDay() + 6));
        endDate.setDate(now.getDate() - now.getDay());
        break;
      case 'current-month':
        startDate.setDate(1);
        endDate.setDate(now.getDate() - 1);
        break;
      case 'last-month':
        startDate.setDate(1);
        startDate.setMonth(now.getMonth() - 1);
        endDate.setDate(0);
        break;
      case 'last-3-months':
        startDate.setDate(1);
        startDate.setMonth(now.getMonth() - 3);
        endDate.setDate(now.getDate() - 1);
        break;
      case 'last-6-months':
        startDate.setDate(1);
        startDate.setMonth(now.getMonth() - 6);
        endDate.setDate(now.getDate() - 1);
        break;
        case 'current-year':
        startDate.setDate(1);
        startDate.setMonth(0);
        endDate.setDate(now.getDate() - 1);
        break;
        case 'last-year':
        startDate.setDate(1);
        startDate.setMonth(0);
        startDate.setFullYear(now.getFullYear() - 1);
        endDate.setMonth(0);
        endDate.setDate(0);
        break;
      case 'sliding-year':
        startDate.setDate(1);
        startDate.setMonth(now.getMonth() - 11);
        endDate.setDate(now.getDate() - 1);
        break;
      default:
        startDate = new Date(period.start);
        endDate = new Date(period.end);
    }

    if (+startDate < +periodStart) {
      startDate = new Date(period.start);
    }

    if (+endDate > +periodEnd) {
      endDate = new Date(period.end);
    }

    var startString = startDate.getDate() < 10 ? '0' + startDate.getDate() : startDate.getDate();
    startString += '/';
    startString += startDate.getMonth() < 9 ? '0' + (startDate.getMonth() + 1) : (startDate.getMonth() + 1);
    startString += '/';
    startString += startDate.getFullYear();

    var endString = endDate.getDate() < 10 ? '0' + endDate.getDate() : endDate.getDate();
    endString += '/';
    endString += endDate.getMonth() < 9 ? '0' + (endDate.getMonth() + 1) : (endDate.getMonth() + 1);
    endString += '/';
    endString += endDate.getFullYear();

    localStorage.setItem('startDate', startString);
    localStorage.setItem('endDate', endString);

    $('.pilea-start-date').val(startString);
    $('.pilea-end-date').val(endString);

    $('.input-daterange').datepicker('destroy');
    $('.input-daterange').datepicker({
      format: 'dd/mm/yyyy',
      endDate: maxDate,
      startDate: minDate,
      language: 'fr'
    });

    e.preventDefault(); // avoid to execute the actual submit of the form.

    // Tell the world we have new values.
    document.dispatchEvent(new Event('selection', {start: startString, end: endString}));
  });

  // Deal with frequency ///////////////////////////////////////////////////

  //If we can't retrieve frequency from localStorage, we create it.
  var frequency = localStorage.getItem('frequency');

  if (frequency == null) {
    frequency = 'month';
    localStorage.setItem('frequency', frequency);
  }


  // Initiate frequency button label
  $('.pilea-select-frequency').each((index, element) => {
    var button = $(element).children('button');
    var frequencyLabel = $(element).find('[data="' + frequency + '"]')[0].innerHTML;
    button[0].innerHTML = frequencyLabel;
  });

  // Add event on change
  $('.pilea-select-frequency a').click(function(e) {
    localStorage.setItem('frequency', e.target.getAttribute('data'));
    $('.pilea-select-frequency button')[0].innerHTML =  e.target.innerHTML;

    e.preventDefault(); // avoid to execute the actual submit of the form.

    // Tell the world we have new values.
    document.dispatchEvent(new Event('selection'));
  });
})
