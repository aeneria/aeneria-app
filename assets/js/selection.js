/**
 * @file
 *
 * Handles selection in localStorage elements.
 */

(function() {
  $(document).ready(function () {
    // Deal with places ///////////////////////////////////////////////////

    function initPlace() {
      //If we can't retrieve place from localStorage, we create it.
      var place = getCurrentPlace();

      if (place == null || !Object.keys(places).includes(place)) {
        place = Object.keys(places)[0];
        setCurrentPlace(place);
      }

      // Initiate place button label.
      $('.aeneria-select-place').each((index, element) => {
        var button = $(element).children('button');
        var placeLabel = places[place].name;
        button[0].innerHTML = placeLabel;

        var span = $(element).prev();
        var placeClass = 'fas fa-' + places[place].icon + ' start-input';
        span[0].classList = placeClass;

      });

      initPeriod(place);
      initFrequency(place);
      initComparaison(place);

      // Add event on change.
      $('.aeneria-select-place a').click(function(e) {
        place = e.target.getAttribute('data');
        setCurrentPlace(e.target.getAttribute('data'));

        $('.aeneria-select-place button').each((i, element) => {
          element.innerHTML = places[place].name;
        });

        $('.aeneria-select-place').prev().each((i, element) => {
          let placeClass = 'fas fa-' + places[place].icon + ' start-input';
          element.classList = placeClass;
        });

        initPeriod(place);
        initFrequency(place);
        initComparaison(place);

        e.preventDefault(); // avoid to execute the actual submit of the form.

        // Tell the world we have new values.
        document.dispatchEvent(new Event('selection'));
      });
    }

    // Deal with start and end date ///////////////////////////////////////////////////

    function initPeriod(place) {
      // Get min and max dates.
      if (places[place].start) {
        var minDate = places[place].start.substring(0,10).split('-') ;
        minDate = minDate[2] + '/' + minDate[1] + '/' + minDate[0];
        var periodStart = new Date(places[place].start);
      }
      if (places[place].end) {
        var maxDate = places[place].end.substring(0,10).split('-') ;
        maxDate = maxDate[2] + '/' + maxDate[1] + '/' + maxDate[0];
        var periodEnd = new Date(places[place].end);
      }

      // If we can't retrieve start or end date from localStorage, we create them.
      var startString = getCurrentStartDate();
      var endString = getCurrentEndDate();

      if (startString == null || endString == null) {
        var now = new Date();
        startDate = new Date(new Date().setMonth(now.getMonth() - 6));

        var startString = dateToString(startDate);
        var endString = dateToString(now);
        setCurrentStartDate(startString);
        setCurrentEndDate(endString);
      }

      // Initiate datepicker.
      $('.aeneria-start-date').val(startString);
      $('.aeneria-end-date').val(endString);

      $('#period-daterange').datepicker({
        format: 'dd/mm/yyyy',
        endDate: maxDate,
        startDate: minDate,
        language: 'fr'
      });

      // Add event on refresh button.
      $('.aeneria-select-date').click(function(e) {
        var startDate = $(e.target).parent().find(".aeneria-start-date");
        var endDate = $(e.target).parent().find(".aeneria-end-date");

        // Store new value in localStorage.
        setCurrentStartDate(startDate.val());
        setCurrentEndDate(endDate.val());

        // Tell the world we have new values.
        document.dispatchEvent(new Event('selection'));

        e.preventDefault();
      });

      $('.aeneria-select-period a').click(function(e) {

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
            startDate = new Date(places[place].start);
            endDate = new Date(places[place].end);
        }

        if (+startDate < +periodStart) {
          startDate = new Date(places[place].start);
        }

        if (+endDate > +periodEnd) {
          endDate = new Date(places[place].end);
        }

        var startString = dateToString(startDate);
        var endString = dateToString(endDate);

        setCurrentStartDate(startString);
        setCurrentEndDate(endString);

        $('.aeneria-start-date').val(startString);
        $('.aeneria-end-date').val(endString);

        $('#period-daterange').datepicker('destroy');
        $('#period-daterange').datepicker({
          format: 'dd/mm/yyyy',
          endDate: maxDate,
          startDate: minDate,
          language: 'fr'
        });

        e.preventDefault(); // avoid to execute the actual submit of the form.

        // Tell the world we have new values.
        document.dispatchEvent(new Event('selection', {start: startString, end: endString}));
      });
    }

    // Deal with frequency ///////////////////////////////////////////////////

    function initFrequency(place) {
      //If we can't retrieve frequency from localStorage, we create it.
      var frequency = getCurrentFrequency();

      if (frequency == null) {
        frequency = 'month';
        setCurrentFrequency(frequency);
      }

      // Initiate frequency button label
      $('.aeneria-select-frequency').each((index, element) => {
        var button = $(element).children('button');
        var frequencyLabel = $(element).find('[data="' + frequency + '"]')[0].innerHTML;
        button[0].innerHTML = frequencyLabel;
      });

      // Add event on change
      $('.aeneria-select-frequency a').click(function(e) {
        setCurrentFrequency(e.target.getAttribute('data'));
        $('.aeneria-select-frequency button')[0].innerHTML =  e.target.innerHTML;

        e.preventDefault(); // avoid to execute the actual submit of the form.

        // Tell the world we have new values.
        document.dispatchEvent(new Event('selection'));
      });
    }

    // Deal with meteo ///////////////////////////////////////////////////

    function initMeteo() {
      //If we can't retrieve meteo from localStorage, we create it.
      var meteo = getCurrentMeteo();

      if (meteo == null) {
        meteo = 'dju';
        setCurrentMeteo(meteo);
      }

      // Initiate frequency button label
      $('.aeneria-select-meteo').each((index, element) => {
        var button = $(element).children('button');
        var meteoLabel = $(element).find('[data="' + meteo + '"]')[0].innerHTML;
        button[0].innerHTML = meteoLabel;
      });

      // Add event on change
      $('.aeneria-select-meteo a').click(function(e) {
        setCurrentMeteo(e.target.getAttribute('data'));
        $('.aeneria-select-meteo button')[0].innerHTML =  e.target.innerHTML;

        e.preventDefault(); // avoid to execute the actual submit of the form.

        // Tell the world we have new values.
        document.dispatchEvent(new Event('selection'));
      });
    }

    // Deal with compare tab ///////////////////////////////////////////////////

    function initComparaison(place) {
      // Get min and max dates.
      if (places[place].start) {
        var minDate = places[place].start.substring(0,10).split('-') ;
        minDate = minDate[2] + '/' + minDate[1] + '/' + minDate[0];
        var periodStart = new Date(places[place].start);
      }
      if (places[place].end) {
        var maxDate = places[place].end.substring(0,10).split('-') ;
        maxDate = maxDate[2] + '/' + maxDate[1] + '/' + maxDate[0];
        var periodEnd = new Date(places[place].end);
      }

      //If we can't retrieve data from localStorage, we create it.
      var period1StartString = getCurrentPeriod1StartDate();
      var period1EndString = getCurrentPeriod1EndDate();
      var period2StartString = getCurrentPeriod2StartDate();
      var period2EndString = getCurrentPeriod2EndDate();

      if (period1StartString == null || period1EndString == null || period2StartString == null || period2EndString == null) {
        var now = new Date();
        var startDate = new Date(new Date().setDate(now.getDate() - 29));

        var period1StartString = dateToString(startDate);
        var period1EndString = dateToString(now);
        setCurrentPeriod1StartDate(period1StartString);
        setCurrentPeriod1EndDate(period1EndString);

        var startDate = new Date(new Date().setDate(now.getDate() - 60));
        var endDate = new Date(new Date().setDate(now.getDate() - 30));
        var period2StartString = dateToString(startDate);
        var period2EndString = dateToString(endDate);
        setCurrentPeriod2StartDate(period2StartString);
        setCurrentPeriod2EndDate(period2EndString);
      }

      $('.aeneria-p1-start-date').val(period1StartString);
      $('.aeneria-p1-end-date').val(period1EndString);

      $('#period-daterange-p1').datepicker('destroy');
      $('#period-daterange-p1').datepicker({
        format: 'dd/mm/yyyy',
        endDate: maxDate,
        startDate: minDate,
        language: 'fr'
      });

      $('.aeneria-p2-start-date').val(period2StartString);
      $('.aeneria-p2-end-date').val(period2EndString);

      $('#period-daterange-p2').datepicker('destroy');
      $('#period-daterange-p2').datepicker({
        format: 'dd/mm/yyyy',
        endDate: maxDate,
        startDate: minDate,
        language: 'fr'
      });

      // Add event on refresh button.
      $('#refresh-select-period').click(function(e) {
        var period1StartDate = $(".aeneria-p1-start-date");
        var period1EndDate = $(".aeneria-p1-end-date");
        var period2StartDate = $(".aeneria-p2-start-date");
        var period2EndDate = $(".aeneria-p2-end-date");

        // Store new value in localStorage.
        setCurrentPeriod1StartDate(period1StartDate.val());
        setCurrentPeriod1EndDate(period1EndDate.val());
        setCurrentPeriod2StartDate(period2StartDate.val());
        setCurrentPeriod2EndDate(period2EndDate.val());

        // Tell the world we have new values.
        document.dispatchEvent(new Event('selection'));

        e.preventDefault();
      });
    }


    if (typeof user !== 'undefined') {
      initPlace();
      initMeteo();
    }
  })

  // HELPERS - Getters
  var getCurrentPlace = function() {
    return localStorage.getItem(user + '.place');
  }

  var getCurrentStartDate = function() {
    place = getCurrentPlace();
    return localStorage.getItem(user + '.' + place + '.startDate');
  }

  function getCurrentEndDate() {
    place = getCurrentPlace();
    return localStorage.getItem(user + '.' + place + '.endDate');
  }

  function getCurrentFrequency() {
    place = getCurrentPlace();
    return localStorage.getItem(user + '.' + place + '.frequency');
  }

  function getCurrentMeteo() {
    return localStorage.getItem(user + '.meteo');
  }

  function getCurrentPeriod1StartDate() {
    return localStorage.getItem(user + '.' + place + '.compare.period1.startDate');
  }

  function getCurrentPeriod1EndDate() {
    return localStorage.getItem(user + '.' + place + '.compare.period1.endDate');
  }

  function getCurrentPeriod2StartDate() {
    return localStorage.getItem(user + '.' + place + '.compare.period2.startDate');
  }

  function getCurrentPeriod2EndDate() {
    return localStorage.getItem(user + '.' + place + '.compare.period2.endDate');
  }

  function getCurrentCompared() {
    return localStorage.getItem(user + '.' + place + '.compare.compared');
  }

  // HELPERS - Setters
  function setCurrentPlace(place) {
    return localStorage.setItem(user + '.place', place);
  }

  function setCurrentStartDate(startDate) {
    place = getCurrentPlace();
    return localStorage.setItem(user + '.' + place + '.startDate', startDate);
  }

  function setCurrentEndDate(endDate) {
    place = getCurrentPlace();
    return localStorage.setItem(user + '.' + place + '.endDate', endDate);
  }

  function setCurrentFrequency(frequency) {
    place = getCurrentPlace();
    return localStorage.setItem(user + '.' + place + '.frequency', frequency);
  }

  function setCurrentMeteo(meteo) {
    return localStorage.setItem(user + '.meteo', meteo);
  }

  function setCurrentPeriod1StartDate(period) {
    return localStorage.setItem(user + '.' + place + '.compare.period1.startDate', period);
  }

  function setCurrentPeriod1EndDate(period) {
    return localStorage.setItem(user + '.' + place + '.compare.period1.endDate', period);
  }

  function setCurrentPeriod2StartDate(period) {
    return localStorage.setItem(user + '.' + place + '.compare.period2.startDate', period);
  }

  function setCurrentPeriod2EndDate(period) {
    return localStorage.setItem(user + '.' + place + '.compare.period2.endDate', period);
  }

  function setCurrentCompared(compared) {
    return localStorage.setItem(user + '.' + place + '.compare.compared', compared);
  }

  function dateToString(date) {
    dateString = date.getDate() < 10 ? '0' + date.getDate() : date.getDate();
    dateString += '/';
    dateString += date.getMonth() < 9 ? '0' + (date.getMonth() + 1) : (date.getMonth() + 1);
    dateString += '/';
    dateString += date.getFullYear();

    return dateString;
  }

  exports.getPlace = getCurrentPlace;
  exports.getStartDate = getCurrentStartDate;
  exports.getEndDate = getCurrentEndDate;
  exports.getFrequency = getCurrentFrequency;
  exports.getMeteo = getCurrentMeteo;
  exports.getPeriod1StartDate = getCurrentPeriod1StartDate;
  exports.getPeriod1EndDate = getCurrentPeriod1EndDate;
  exports.getPeriod2StartDate = getCurrentPeriod2StartDate;
  exports.getPeriod2EndDate = getCurrentPeriod2EndDate;
  exports.getCompared = getCurrentCompared;
})();