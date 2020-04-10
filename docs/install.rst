
Installation
##############

Tout d'abord pour utiliser æneria,

* Vous devez avoir accès à un Linky et à un `compte Enedis <https://espace-client-connexion.enedis.fr/auth/UI/Login?realm=particuliers>`_
* Via ce compte, vous devez activer l'option *Courbe de charge* pour pouvoir avoir accès à votre consommation horaire

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
    :target: https://install-app.yunohost.org/?app=pilea
    :align: center


Installation à la main
***********************

æneria est une application basée sur le framework Symfony. Elle s'installe sur un serveur web disposant
d'un PHP récent et d'un serveur de base de données MySQL.

Prérequis
==========

* PHP 7.3 et supérieur
* MySQL (5.5 et supérieur) / PostreSQL (9.6 et supérieur)

.. note::

    SQLite devrait fonctionner mais vous aurez à adapter les fichiers ``.env`` & ``config/packages/doctrine.yaml``

    Il n'est pas prévu que æneria le supporte *officiellement*, si vous souhaitez vous y coller allez-y, faites une merge request et
    je regarderai :)

.. warning::

    Les migrations de æneria sont uniquement générées pour MySQL, si vous utilisez un autre type de serveur, gardez à l'esprit qu'il
    faudra vérifier chaque migration avant de la lancer !

Installation
=============

La dernière version d'æneria se trouve sur son dépos Gitlab sur `la page des Releases <https://gitlab.com/aeneria/aeneria-app/-/releases>`_.

1. Récupérer les sources
-------------------------

Téléchargez et décompressez `le dernière version au format *tar.gz* <https://gitlab.com/aeneria/aeneria-app/-/jobs/artifacts/master/raw/aeneria-test-11.tar.gz?job=release:on-tag>`_ :

.. code-block:: sh

    wget https://gitlab.com/aeneria/aeneria-app/-/jobs/artifacts/master/raw/aeneria-test-11.tar.gz?job=release:on-tag
    tar -xvzf https://gitlab.com/aeneria/aeneria-app/-/jobs/artifacts/master/raw/aeneria-test-11.tar.gz?job=release:on-tag [app_folder]

2. Créer et rensiegner la base de données
------------------------------------------

Créez une base de donnés puis adaptez les fichiers ``.env`` et ``config/packages/doctrine.yaml``

.. code-block:: bash

    # fichier .env

    ...

    ###> doctrine/doctrine-bundle ###
    # Format described at http://docs.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/configuration.html#connecting-using-a-url
    # For MysSQL database use: "pgsql://[database_user]:[database_password]@127.0.0.1:5432/[database_name]
    # For PostgreSQL database use: "mysql://[database_user]:[database_password]@127.0.0.1:3306/[database_name]
    # For an SQLite database, use: "sqlite:///%kernel.project_dir%/var/data.db"
    # Configure your db driver and server_version in config/packages/doctrine.yaml
    DATABASE_URL=[VOTRE CONFIG ICI]
    ###< doctrine/doctrine-bundle ###

    ...

.. code-block:: yaml

    # fichier config/packages/doctrine.yaml

    ...

    # Renseigner ici les info de votre dbal
    doctrine:
        dbal:
            # Configure these for your database server

            # Mysql
            driver: 'pdo_mysql'
            server_version: '5.2'
            charset: utf8mb4
            default_table_options:
                charset: utf8mb4
                collate: utf8mb4_unicode_ci

            # PostgreSQL
            # driver: 'pdo_pgsql'
            # server_version: '9.6'
            # charset: utf8

            #SQLLite
            # driver:   pdo_sqlite
            # charset: utf8

    ...

3. Générer la base de données
-------------------------------

Lancez le commande d'installation d'aeneria :

.. code-block:: sh

    php7.3 bin/console aeneria:install

4. Créer un utilisateur administrateur
----------------------------------------

Ajoutez une premier utilisateur et donnez-lui les droits administrateur :

.. code-block:: sh

    php7.3 bin/console aeneria:user:add [username] [password]
    php7.3 bin/console aeneria:user:grant [username]

5. Mise en place du CRON
-------------------------

Mettez en place le CRON en exécutant la commande suivante :

.. code-block:: sh

    echo "*/10  *  *  *  * [user] php7.3 /[app_folder]/bin/console aeneria:fetch-data false" > /etc/cron.d/aeneria
    # où [user] est l'utilisateur linux qui lancera le cron


6. Configuration du serveur web
--------------------------------

Enfin, configurez `NGINX <https://symfony.com/doc/current/setup/web_server_configuration.html#web-server-nginx>`_ ou
`Apache <https://symfony.com/doc/current/setup/web_server_configuration.html#apache-with-php-fpm>`_ comme pour une
application Symfony 5 classique
