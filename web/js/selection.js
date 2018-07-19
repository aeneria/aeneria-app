/**
 * @file
 *
 * Handles selection in localStorage elements.
 */

$(document).ready(function () {
  // Deal with start and end date ///////////////////////////////////////////////////

  // If we can't retrieve start or end date from localStorage, we create them.
  var startDate = localStorage.getItem('startDate');
  var endDate = localStorage.getItem('endDate');

  if (startDate == null || endDate == null) {
    var now = new Date();
    startDate = new Date(new Date().setMonth(now.getMonth() - 6)).toISOString().slice(0,10);
    endDate = now.toISOString().slice(0,10);
    localStorage.setItem('startDate', startDate);
    localStorage.setItem('endDate', endDate);
  }

  // Initiate datepicker.
  $('#start-date').val(startDate);
  $('#end-date').val(endDate);
  $('.input-daterange').datepicker({
    format: 'dd/mm/yyyy',
    endDate: '-1d',
    language: 'fr'
  });

  // Add event on refresh button
  $('#selection-form').submit(function(e) {
    // Store new vlaue in localStorage.
    localStorage.setItem('startDate', $("#start-date").val());
    localStorage.setItem('endDate', $("#end-date").val());

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
