/**
 * @file
 *
 * Handles localStorage elements.
 */

// If we can't retrieve start or end date from localStorage, we create them.
var startDate = localStorage.getItem('startDate');
var endDate = localStorage.getItem('endDate');

if (startDate == null || endDate == null) {
  var now = new Date().format('Y-m-d');
  startDate = now.format('Y-m-d');
  endDate = new Date(new Date().setMonth(new now.getMonth() - 6)).format('Y-m-d');
  localStorage.setItem('startDate', startDate);
  localStorage.setItem('endDate', endDate);
}

//If we can't retrieve frequency from localStorage, we create it.
var frequency = localStorage.getItem('frequency');

if (frequency == null) {
  frequency = 'month';
  localStorage.setItem('frequency', frequency);
}
