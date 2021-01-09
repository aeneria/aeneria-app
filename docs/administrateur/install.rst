Installer æneria
##################

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
    :target: https://install-app.yunohost.org/?app=aeneria
    :align: center

Installation à la main
***********************

æneria est une application basée sur le framework Symfony. Elle s'installe sur un serveur web disposant
d'un PHP récent et d'un serveur de base de données PostgreSQL.

Prérequis
==========

* PHP 7.3 et supérieur
* PostgreSQL (9.6 et supérieur)

.. note::

    MySQL et SQLite devraient fonctionner mais vous aurez à adapter les fichiers ``.env`` & ``config/packages/doctrine.yaml``

    Il n'est pas prévu que æneria Les supporte *officiellement*.

.. warning::

    Les migrations de æneria sont uniquement générées pour PostgreSQL, si vous utilisez un autre type de serveur, gardez à l'esprit qu'il
    faudra vérifier chaque migration avant de la lancer !

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


Adaptez également le fichier ``config/packages/doctrine.yaml`` si votre serveur de base de données n'est pas PostgreSQL :

.. code-block:: yaml

    # fichier config/packages/doctrine.yaml

    ...

    # Renseigner ici les info de votre dbal
    doctrine:
        dbal:
            # Configure these for your database server

            # Mysql
            # driver: 'pdo_mysql'
            # server_version: '5.2'
            # charset: utf8mb4
            # default_table_options:
            #     charset: utf8mb4
            #     collate: utf8mb4_unicode_ci

            # PostgreSQL
            driver: 'pdo_pgsql'
            server_version: '9.6'
            charset: utf8

            #SQLLite
            # driver:   pdo_sqlite
            # charset: utf8

    ...

3. Générer la base de données
-------------------------------

Lancez le commande d'installation d'aeneria :

.. code-block:: sh

    php7.3 bin/console aeneria:install

4. Configurer Enedis Data-connect
------------------------------------

æneria utilise Enedis Data-connect API pour obtenir les données de consommation
d'électricité. Mais pour utiliser cette API il est nécessaire d'avoir un compte.
Seulement, pour ouvrir un compte chez Enedis data-connect, il faut être une entreprise
ou une association.

Pour permettre à tout le monde d'utiliser æneria, un proxy a été développé pour qu'une
instance d'æneria puisse bénéficier du compte d'aeneria.com.

Au lieu d'utiliser le comportement classique pour se connecter à Enedis :

`votre instance æneria <=> Enedis`

Vous pouvez configurez votre instance comme ça :

`votre instance æneria <=> proxy.aeneria.com <=> Enedis`

Il y a donc 2 sortes de mode :

Soit vous créez un compte Enedis et vous renseignez vos informations de connection
de cette manière dans le fichier `.env` :

.. code-block:: bash

    # fichier .env

    ...

    ENEDIS_CLIENT_ID=yourEnedisClientID
    ENEDIS_CLIENT_SECRET=yourEnedisClientSecret
    ENEDIS_REDIRECT_URI=yourEnedisRedirectUri
    ENEDIS_ENDPOINT_AUTH=https://mon-compte-particulier.enedis.fr
    ENEDIS_ENDPOINT_TOKEN=https://gw.prd.api.enedis.fr
    ENEDIS_ENDPOINT_DATA=https://gw.prd.api.enedis.fr

    ...

Soit vous utilisez proxy.aeneria.com en utilisant cette configuration


.. code-block:: bash

    # fichier .env

    ...

    ENEDIS_CLIENT_ID=whatYouWantItWouldNotBeUsed
    ENEDIS_CLIENT_SECRET=whatYouWantItWouldNotBeUsed
    ENEDIS_REDIRECT_URI=whatYouWantItWouldNotBeUsed
    ENEDIS_ENDPOINT_AUTH=https://proxy.aeneria.com/enedis-data-connect
    ENEDIS_ENDPOINT_TOKEN=https://proxy.aeneria.com/enedis-data-connect
    ENEDIS_ENDPOINT_DATA=https://gw.prd.api.enedis.fr

    ...

.. warning::

    proxy.aeneria.com est un serveur communautaire fourni à titre gracieux.

    Merci de l'utiliser raisonnablement et dans un cadre privé non-commercial.

    Nous nous réservons le droit de bannir de ce serveur les instances qui en feront
    un usage trop intensif, et ce **sans explications et sans avertissement**.

5. Créer un administrateur
----------------------------------------

Ajoutez une premier utilisateur et donnez-lui les droits administrateur :

.. code-block:: sh

    php7.3 bin/console aeneria:user:add [admin_email] [password]
    php7.3 bin/console aeneria:user:grant [admin_email]

6. Générer l'ensemble des flux Météo (facultatif - usage avancée)
-------------------------------------------------------------------

.. danger::

    Cette fonctionnalité correspond à un usage avancée.
    Testez d'abord æneria sans l'utiliser.

Si vous le souhaitez, vous pouvez créer l'ensemble des flux météo pour l'utilisateur admin.
L'intérêt est de commencer à stocker toutes les données météo dès l'installation de l'instance.
Un utilisateur qui créée son compte dans le futur aura directement accès à l'ensemble de données météos
depuis l'installation d'æneria.
Par contre, en faisant ça, l'ensemble des données des 62 stations Météo sera historisé, ce qui augmente
la taille de la base de données.

Pour ça, lancer la commande suivante :

.. code-block:: sh

    php7.3 bin/console aeneria:feed:meteo:generate-all [username]

.. note::

    Les données Météo étant dans données pubiques, il n'y a pour elles pas de problème
    de confidentialité. Pour simplifier les traitements, les données des flux météo ne
    sont jamais supprimés. Si vous souhaitez quand même les supprimer, vous pouver le faire
    en utilisant la command `aeneria:feed:clean-orphans`

.. warning::

    L'adresse générée par cette commande n'est pas destinée à ensuite être utilisée via
    l'interface d'æneria. Elle a pour unique but de définir une première fois l'ensemble
    des stations météo.

7. Mettre en place le CRON
----------------------------

Mettez en place le CRON en exécutant la commande suivante :

.. code-block:: sh

    echo "*/10  *  *  *  * [user] php7.3 /[app_folder]/bin/console aeneria:fetch-data" > /etc/cron.d/aeneria
    # où [user] est l'utilisateur linux qui lancera le cron


8. Configurer le serveur web
--------------------------------

Enfin, configurez `NGINX <https://symfony.com/doc/current/setup/web_server_configuration.html#web-server-nginx>`_ ou
`Apache <https://symfony.com/doc/current/setup/web_server_configuration.html#apache-with-php-fpm>`_ comme pour une
application Symfony 5 classique
