
Migrer depuis Pilea
##########################

Un peu d'histoire
==================

Pilea est l'ancien nom d'æneria.

Les versions 0.5.x de Pilea reposaient sur l'architecture de l'ancien espace personnel Enedis. Elles
utilisaient des techniques de web-scrapping pour récupérer les données de consommation d'électricité.

Cette solution n'était pas pérenne et au moment où Enedis a changé son site, les scripts de
Pilea ne fonctionnaient plus.

Une refonte de toute une partie de Pilea a donc été réalisée pour s'appuyer sur la nouvelle API d'Enedis :
`Data Connect <https://datahub-enedis.fr/data-connect/>`_. L'utilisation de l'API officielle permet une plus
grande stabilité.

Cette refonte a été l'occasion de revoir plusieurs parties du code de Pilea, rendant les version 0.5.x incompatibles
avec les version 1.x.
Le nom de Pilea est peu évocateur et n'est pas très *moteur-de-recherche-friendly*. Cette petite refonte était donc
l'occasion de le changer.

L'histoire de Pilea s'arrête donc avec la version 0.5.8 pour laisser sa place à **æneria** !

Migrer de Pilea vers æneria
============================

Comme évoqué précédemment, il n'est pas possible de faire une mise à jour automatique de Pilea vers æneria.
Mais, grâce aux fonctions d'import et d'export, il est possible de transférer ses données depuis Pilea
vers æneria.

.. danger::
    Lisez bien la procédure avant de commencer.

    **Ne pas** désinstallez Pilea avant la fin de la procédure.

.. note::
    Il n'y a pas de moyen d'exporter l'ensemble des données de l'ensemble des utilisateurs d'un seul coup.
    Ceux-ci vont devoir tous se connecter pour sauvegarder *leurs* données, avant de les réimporter
    dans æneria.

Voici donc la marche à suivre :

* Mettez à jour Pilea vers une version supérieure ou égale à 0.5.6
  (le numéro de version est visible dans l'encart "à propos" sur la page de configuration)
* Installez æneria
* Pour chaque utilisateur
    * Connectez-vous à Pilea
    * Allez sur la page de configuration
    * Pour chaque adresse, exportez l'ensemble des données en cliquant sur le bouton export
    * Connectez-vous à æneria
    * Allez sur la page de configuration
    * Créez chacune des adresses
    * Pour chaque adresse, importez les données précédemment exportées en cliquant sur le bouton import
* Vérifiez que chaque utilisateurs a bien importé ses données
* Désinstallez Pilea
