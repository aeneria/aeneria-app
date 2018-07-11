/**
 * @file
 *
 * Handles localStorage elements.
 */

$(document).ready(function () {
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

  //If we can't retrieve frequency from localStorage, we create it.
  var frequency = localStorage.getItem('frequency');

  if (frequency == null) {
    frequency = 'month';
    localStorage.setItem('frequency', frequency);
  }
})