# Pilea - A little dashboard to analyse your electricity consumption data from Linky

### *Work in progress*

The idea of Pilea is to display electricity consumption and weather data on a little dashboard that allow the user to:

* Better understand his electricity consumption,
* Analyse his electricity consumption throw weather data (in a first time, essentialy temperature).

### Interface

A dashboard with several graphics, maybe 2 tabs :

*  General consumption graphics
*  Meteo (T°) and energy consumption
 
 ### Data
 
 Data are daily collected, we get:

* Electricity consumption data from your Linky via Enedis API (with [php-LnkyAPI](https://github.com/KiboOst/php-LinkyAPI))
*  Weather observation data from [Meteo France INSPIRE API](https://donneespubliques.meteofrance.fr/client/gfx/utilisateur/File/documentation-webservices-inspire.pdf)
