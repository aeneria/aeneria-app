# æneria version 1.1.5

* [Télécharger les sources complètes d'æneria](http://statics.aeneria.com/) (Avec les dépendances Composer et les assets compilés)
* [Accéder à la documentation](https://docs.aeneria.com/fr/latest/)

## Nouveautés

* Fix bug in Notificatin feature

## Anciennes versions
### 1.1.4

* Ajout d'un système de notification pour avertir lors des problèmes de récupérations de données
* Ajout d'un système pour prévenir le flooding vers l'API d'Enedis en cas de rupture de consentement
* Ajout d'un système d'action différée permettant
  * de programmer la récupération des données passées sur une plus grande période de temps
  * l'import de fichier de données de taille plus important
* Meteo : mise à jour de l'échelle de couleur pour l'humidité
*
### 1.1.3

* Ajout de la possibilité de faire du suivi avec Matomo
* CLI - Mise à jour de la commande de rafraichissement des données pour pouvoir rafraichir entre 2 dates
* Configuration - Ajout du PDL dans la description des compteurs linky
* CLI - Ajout d'une commande pour regénérer la clef RSA

### 1.1.2

* Fix - Bug sur la modification d'une adresse
* Documentation - Mise à jour de la documentation d'installation
* Mise à jour vers Symfony 5.2

### 1.1.1

* Fix - bug sur le chargement des assets
* Fix - formulaire d'ajout d'une adresse

### 1.1.0

* Ajout d'un onglet pour comparer sa consommation entre 2 périodes
* Mise à jour du style de la navigation et du formulauire de sélection

### Autres mises à jour techniques

* Refacto de la gestion des couleurs dans les graphiques
* Modification de l'utilisation de Webpack Encore
* Formulaire d'ajout d'une adresse : on envoie maintenant explicitement une url de callback
  (au cas où le navigateur nettoierait le referer voir #4 & #5)

### 1.0.4

* Ajout d'une doc sur le transfert de données depuis Pilea
* Correction d'un bug sur la manière dont les différents flux de données étaient récupérés

### 1.0.3

* Modification du processus d'ajout d'une nouvelle adresse

### 1.0.2

* Bugfix - infobulles non-visibles
* Bugfix - formaulaire d'édition d'adresse avec le partage désactivé
* Bugfix - compatibilité PostgreSQL du DataValueRepository
* Doc - Mise à jour de la documentation d'intallation
* Doc - Ajout d'une documentation pour la mise à jour applicative
* Doc - Ajout d'une documentation pour la livraison d'une nouvelle version
* CI - Les tests s'effectuent désormais avec PostgreSQL et non MySQL

### 1.0.1

* Ajout de la possibilité de personnaliser le message sur la page de connexion

### 1.0.0

* Implémentation de l'[API Enedis Data-connect](https://datahub-enedis.fr/data-connect/) et du [proxy aeneria](https://gitlab.com/aeneria/aeneria-proxy) pour la récupération des données de consommation d'énergie
* Partage des flux météo entre utilisateurs : on ne stoque plus 2 fois les données d'une même station
* Ajout d'une commande pour ajouter toutes les stations météo à un utilisateur : permet
  de récupérer les données de toute la France
* Mise à jour du formulaire de création/modification d'adresse
* æneria utilise désormais PostgreSQL par défaut
