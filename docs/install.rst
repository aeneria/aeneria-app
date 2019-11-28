
Installation
##############

Tout d'abord pour utiliser Pilea,

* Vous devez avoir accès à un Linky et à un `compte Enedis <https://espace-client-connexion.enedis.fr/auth/UI/Login?realm=particuliers>`_
* Via ce compte, vous devez activer l'option *Courbe de charge* pour pouvoir avoir accès à votre consommation horaire

La manière facile - via YunoHost
=================================

* Obtenir une instance `YunoHost<https://yunohost.org/>`_
* Installer Pilea via son `paquet<https://github.com/SimonMellerin/pilea_ynh>`_

.. image:: https://install-app.yunohost.org/install-with-yunohost.png
   :target: https://install-app.yunohost.org/?app=pilea


La manière un peu moins facile - installation à la main
=======================================================

**Prérequis:**
* PHP 7.3 ou plus
* MySQL 5.5 ou plus
  (PostgreSQL & SQLite should work but you'll have to adapt `.env` & `config/packages/doctrine.yaml`)

**Installation:**
* Télécharger `le dépot <https://github.com/SimonMellerin/Pilea>`_
* Créer un base de donnés puis renseigner son nom, l'utilisateur et le mot de passe dans le ficheir `.env`
* Installer les dépendance `Composer <https://getcomposer.org/>` : `composer install`
* Lancer le script d'installation : `bin/console pilea:install`
* Ajouter une premier utilisateur : `bin/console pilea:user:add username password`
* Lui donner les droits administrateur: `bin/console pilea:user:grant username`
* Mettre en place le cron : `echo "*/10  *  *  *  * [user] /[app_folder]/bin/console pilea:fetch-data false" > /etc/cron.d/pilea`
  (remplacer *[user]* et *[app_floder]* en fonction de votre configuration)
* Configurer `NGINX<https://symfony.com/doc/current/setup/web_server_configuration.html#web-server-nginx>`_ ou
`Apache <https://symfony.com/doc/current/setup/web_server_configuration.html>`_ comme pour une application Symfony 4 classique