# æneria version 1.0.4

* [Télécharger les sources complètes d'æneria](http://statics.aeneria.com/) (Avec les dépendances Composer et les assets compilés)
* [Accéder à la documentation](https://docs.aeneria.com/fr/latest/)

## Nouveautés

* Ajout d'une doc sur le transfert de données depuis Pilea
* Correction d'un bug sur la manière dont les différents flux de données étaient récupérés

## Anciennes versions

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
