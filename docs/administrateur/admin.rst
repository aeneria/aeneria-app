Administrer æneria
###################


æneria peut-être administré via l'interface web ou bien en ligne de commande via la ``console`` Symfony.

Via l'interface web
========================

Les utilisateurs avec le rôle *Admin* ont accès aux informations d'administration d'æneria.

.. |icon_configuration| image:: ../img/config.png
             :alt: icone engrenage

Pour accéder à ces informations, aller sur la page de configuration en cliquant sur le bouton |icon_configuration|
dans la barre du haut. Si vous êtes administrateur, une carte supplémentaire apparait sous votre liste d'adresses :

.. image:: ../img/admin_card.png
    :alt: Aperçu de la partie administrateur de la page de configuration
    :align: center

Plusieurs éléments sont visibles sur cette page:

* Un bouton pour accéder à la gestion des utilisateurs
* Un bouton pour accéder aux derniers logs de l'application
* La liste des configurations courantes de l'application

Configurations
------------------------------

Plusieurs fonctionnalités sont paramètrables dans æneria :

* Le nombre maximum d'adresses qu'un utilisateur puisse créer
* La possiblité de partager des adresses entre utilisateurs
* La possiblité de rendre une adresse publique pour l'ensemble des utilisateurs
* La possiblité pour des utilisateur de pouvoir recharger leurs données via l'interface
* La possiblité pour des utilisateur de pouvoir exporter leurs données via l'interface
* L'activation du mode démo (Désactiver les fonctions de configuration via l'UI)
* Le message affiché sur l'écran de login

Les paramètres courants sont visibles via l'interface d'administration mais ne sont pas modifiable via l'interface web.

Pour les changer, il faut modifier le fichier ``.env`` à la racine d'æneria (le fichier est auto-documenté).

Ci-dessous, un exemple de paramètrage de ce fichier :

.. code-block:: sh

    ###> symfony/framework-bundle ###
    APP_ENV=prod
    APP_SECRET=app_secret_you_should_change_this_value
    #TRUSTED_PROXIES=127.0.0.1,127.0.0.2
    #TRUSTED_HOSTS='^localhost|example\.com$'
    ###< symfony/framework-bundle ###

    ###> doctrine/doctrine-bundle ###
    # Format described at http://docs.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/configuration.html#connecting-using-a-url
    # For an SQLite database, use: "sqlite:///%kernel.project_dir%/var/data.db"
    # Configure your db driver and server_version in config/packages/doctrine.yaml
    DATABASE_URL=mysql://admin:admin@127.0.0.1:3306/aeneria
    # DATABASE_URL=pgsql://admin:password@127.0.0.1:5432/aeneria
    ###< doctrine/doctrine-bundle ###

    # Number of places a user can create (-1 for no limit)
    AENERIA_USER_MAX_PLACES=-1
    # Can users share place between them
    AENERIA_USER_CAN_SHARE_PLACE=1
    # Can user fetch data from ui
    AENERIA_USER_CAN_FETCH=1
    # Can user export data from ui
    AENERIA_USER_CAN_EXPORT=1
    # Can a place be public
    AENERIA_PLACE_CAN_BE_PUBLIC=0
    # Activate demo mode
    AENERIA_DEMO_MODE=0
    # Welcome message
    AENERIA_WELCOME_MESSAGE='Bienvenu sur æneria'

Gestion des utilisateurs
------------------------------

Accédez à la page de gestion fes utilisateurs en cliquant sur le bouton ``Gérer les utilisateurs`` ou en visitant la page ``/admin/users``.

Sur cette page se trouve un tableau listant l'ensemble des utilisateurs d'æneria.

Au bout de chaque ligne, des boutons vous permettent de :

* Modifier l'utilisateur
* Désactiver/activer l'utilisateur
* Supprimer l'utilisateur (et l'ensemble de ses données)

Pour créer un nouvel utilisateur, cliquez sur le bouton ``Ajouter un utilisateur``.

Un utilisateur désactivé ne peut plus se connecter, mais ses données ne sont pas supprimées.

Les logs
----------

L'interface web permet de visualiser les derniers logs d'æneria. Cliquez sur le bouton ``Voir les derniers logs`` ou visitez la page ``/admin/log``.

Via la console Symfony
=============================

Plusieurs commandes Symfony existent pour administrer æneria.

Les commandes sont toutes auto-documentées, et s'utilisent comme des commandes Symfony classiques, par exemple :

.. code-block:: bash

    # Se rendre dans le dossier racine d'æneria
    cd /emplacement/de/aeneria

    # Pour connaitre l'utilisation d'une commande :
    php7.3 bin/console aeneria:user:activate --help

    # Pour l'utiliser :
    php7.3 bin/console aeneria:user:activate username



Commandes génériques
-----------------------

* ``aeneria:install`` : Installateur d'æneria
* ``aeneria:fetch-data`` : Récupérer les différentes données (c'est cette commande qui est appelé quotidiennement par le cron d'æneria)
* ``aeneria:version`` : Connaître la version courante d'æneria

Commandes de gestion des utilisateurs
--------------------------------------

* ``aeneria:user:add`` : Ajouter un utilisateur
* ``aeneria:user:edit`` : Modifer un utilisateur
* ``aeneria:user:activate`` : Activer un utilisateur
* ``aeneria:user:deactivate`` : Désactiver un utilisateur
* ``aeneria:user:exist`` : Savoir si un utilisateur existe déjà
* ``aeneria:user:grant`` : Donner à un utilisateur le rôle d'admin
* ``aeneria:user:ungrant`` : Retirer à un utilisateur le rôle d'admin

Commandes de développement
----------------------------
Cette commande ne peut être utilisées que sur un environnement de développement.

* ``aeneria:dev:generate-fake-data`` : Générer de fausses données
