Installer æneria
##################

Tout d'abord pour utiliser æneria,

* Vous devez avoir accès
    * soit à un compteur Linky (et donc à un compte Enedis raccroché à ce compteur)
    * soit à un compteur Gazpar (et donc à un compte GRDF raccroché à ce compteur)

Installation via YunoHost
**************************

`YunoHost <https://yunohost.org/>`_ est un projet ayant pour but de promouvoir l'autohébergement.
Son but est de faciliter l'administration d'un serveur : `en savoir plus <https://yunohost.org/#/whatsyunohost_fr>`_

.. image:: https://yunohost.org/images/ynh_logo_black_300dpi.png
    :align: center
    :height: 200px
    :width: 200px

`Des nombreuses applications sont déjà packagées <https://yunohost.org/#/apps>`_ pour être utilisées
avec et c'est le cas de æneria.

.. image:: https://install-app.yunohost.org/install-with-yunohost.png
    :target: https://install-app.yunohost.org/?app=aeneria
    :align: center

Installation à la main
***********************

æneria est une application basée sur le framework Symfony. Elle s'installe sur un serveur web disposant
d'un PHP récent et d'un serveur de base de données PostgreSQL.

Prérequis
==========

* PHP 8.2 et supérieur
* PostgreSQL (9.6 et supérieur)

Installation
=============

Retrouvez les différentes versions d'æneria sur son dépos Gitlab sur `la page des Releases <https://gitlab.com/aeneria/aeneria-app/-/releases>`_.

Les différentes versions accompagnées de leurs dépendances Composer et des assets compilés sont disponibles sur `le dépot d'æneria <http://statics.aeneria.com>`_

1. Récupérer les sources
-------------------------

Téléchargez et décompressez `le dernière version au format tar.gz <http://statics.aeneria.com/aeneria-app-latest.tar.gz>`_ :

.. code-block:: sh

    wget http://statics.aeneria.com/aeneria-app-latest.tar.gz
    tar -xvzf aeneria-app-latest.tar.gz
    rm aeneria-app-latest.tar.gz
    cd aeneria-app

2. Créer et renseigner la base de données
------------------------------------------

Créez une base de données.

Copiez le fichier ``.env.dist`` puis adaptez-le :

.. code-block:: bash

    cp .env.dist .env

.. code-block:: bash

    # fichier .env

    ...

    ###> doctrine/doctrine-bundle ###
    # Format    described at http://docs.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/configuration.html#connecting-using-a-url
    # For PostgreSQL database use: "pgsql://[database_user]:[database_password]@127.0.0.1:5432/[database_name]
    # For MySQL database use: "mysql://[database_user]:[database_password]@127.0.0.1:3306/[database_name]
    # For an SQLite database, use: "sqlite:///%kernel.project_dir%/var/data.db"
    # Configure your db driver and server_version in config/packages/doctrine.yaml
    DATABASE_URL=[VOTRE CONFIG ICI]
    ###< doctrine/doctrine-bundle ###

    ...


3. Générer la base de données
-------------------------------

Lancez le commande d'installation d'aeneria :

.. code-block:: sh

    php8.2 bin/console aeneria:install

4. Configurer Enedis Data-connect et GRDF ADICT
------------------------------------------------

.. note::

    Avant d'aller plus loin, lisez :ref:`la page sur notre proxy communautaire <proxy>` pour savoir
    dans quel mode vous souhaitez utiliser æneria.

Mode 1 - Connexion directe à Enedis et GRDF
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
Soit vous créez un compte Enedis et vous renseignez vos informations de connexion
de cette manière dans le fichier `.env` :

.. code-block:: bash

    # fichier .env

    ...

    # æneria proxy URL
    AENERIA_PROXY_URL=https://proxy.aeneria.com
    # should the app use æneria proxy (1 for yes, 0 for no)
    AENERIA_PROXY_FOR_ENEDIS=0
    AENERIA_PROXY_FOR_GRDF=0

    ## If you use your own API keys, fill the fields below

    # Enedis Data Hub
    ENEDIS_CLIENT_ID=[votreClientIdEnedis]
    ENEDIS_CLIENT_SECRET=[votreClientSecretEnedis]
    ENEDIS_REDIRECT_URI=[votreRedirectUriEnedis]
    ENEDIS_ENDPOINT_AUTH=https://mon-compte-particulier.enedis.fr
    ENEDIS_ENDPOINT_TOKEN=https://gw.prd.api.enedis.fr
    ENEDIS_ENDPOINT_DATA=https://gw.prd.api.enedis.fr

    # Grdf adict
    GRDF_CLIENT_ID=[votreClientIdGrdf]
    GRDF_CLIENT_SECRET=[votreClientSecretGrdf]
    GRDF_REDIRECT_URI=[votreRedirectUriGrdf]
    GRDF_ENDPOINT_AUTH=https://sofit-sso-oidc.grdf.fr
    GRDF_ENDPOINT_DATA=https://api.grdf.fr

    ...

.. note::

    Pour obtenir vos propres identifiants de connexion Enedis Data Connect, rendez-vous sur
    `le Data Hub d'Enedis <https://datahub-enedis.fr/data-connect/>`_

.. note::

    Pour obtenir vos propres identifiants de connexion Grdf Adict, rendez-vous sur
    `le portail Grdf Adict <https://sites.grdf.fr/web/portail-api-grdf-adict/>`_


Mode 2 - Connexion à Enedis et GRDF via le proxy æneria
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
Soit vous utilisez proxy.aeneria.com en utilisant cette configuration

.. code-block:: bash

    # fichier .env

    ...

    # æneria proxy URL
    AENERIA_PROXY_URL=https://proxy.aeneria.com
    # should the app use æneria proxy (1 for yes, 0 for no)
    AENERIA_PROXY_FOR_ENEDIS=1
    AENERIA_PROXY_FOR_GRDF=1

    # les variables en dessous _doivent_ rester

    # Enedis Data Hub
    ENEDIS_CLIENT_ID=%%ENEDIS_CLIENT_ID%%
    ENEDIS_CLIENT_SECRET=%%ENEDIS_CLIENT_SECRET%%
    ENEDIS_REDIRECT_URI=%%ENEDIS_REDIRECT_URI%%
    ENEDIS_ENDPOINT_AUTH=https://mon-compte-particulier.enedis.fr
    ENEDIS_ENDPOINT_TOKEN=https://gw.prd.api.enedis.fr
    ENEDIS_ENDPOINT_DATA=https://gw.prd.api.enedis.fr

    # Grdf adict
    GRDF_CLIENT_ID=%%GRDF_CLIENT_ID%%
    GRDF_CLIENT_SECRET=%%GRDF_CLIENT_SECRET%%
    GRDF_REDIRECT_URI=%%GRDF_REDIRECT_URI%%
    GRDF_ENDPOINT_AUTH=https://sofit-sso-oidc.grdf.fr
    GRDF_ENDPOINT_DATA=https://api.grdf.fr

    ...


5. Créer un administrateur
----------------------------------------

Ajoutez une premier utilisateur et donnez-lui les droits administrateur :

.. code-block:: sh

    php8.2 bin/console aeneria:user:add [admin_email] [password]
    php8.2 bin/console aeneria:user:grant [admin_email]

7. Mettre en place le CRON
----------------------------

Mettez en place le CRON en exécutant la commande suivante :

.. code-block:: sh

    echo "*/10  *  *  *  * [user] php8.2 /[app_folder]/bin/console aeneria:fetch-data" > /etc/cron.d/aeneria-fetch
    echo "*/10  *  *  *  * [user] php8.2 /[app_folder]/bin/console aeneria:pending-action:process-expired" > /etc/cron.d/aeneria-pending-action
    # où [user] est l'utilisateur linux qui lancera le cron


8. Configurer le serveur web
--------------------------------

Enfin, configurez `NGINX <https://symfony.com/doc/current/setup/web_server_configuration.html#web-server-nginx>`_ ou
`Apache <https://symfony.com/doc/current/setup/web_server_configuration.html#apache-with-php-fpm>`_ comme pour une
application Symfony 5 classique
