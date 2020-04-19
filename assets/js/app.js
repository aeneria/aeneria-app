
// Loading CSS.
require('../css/app.scss');

// Loading external required libraries.
const $ = require('jquery');
global.$ = global.jQuery = $;
require('popper.js');
require('bootstrap');
require('util');
require('bootstrap-datepicker');
require('bootstrap-datepicker/js/locales/bootstrap-datepicker.fr');
require('bootstrap-multiselect');
const d3 = require('d3');
global.d3 = d3;

// Some global variables.
require('./variables.js');

// Handle Date & Granularity selections
const pileaCurrent = require('./selection.js');
global.pileaCurrent = pileaCurrent;

// Load Pilea chart library.
const pilea = require('./pilea_chart.js');
global.pilea = pilea;

require('./homepage.js');
require('./electricity_tab.js');
require('./energy_x_meteo_tab.js');
require('./meteo_tab.js');
require('./configuration.js');