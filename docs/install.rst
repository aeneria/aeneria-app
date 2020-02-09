
Installation
##############

Tout d'abord pour utiliser Pilea,

* Vous devez avoir accès à un Linky et à un `compte Enedis <https://espace-client-connexion.enedis.fr/auth/UI/Login?realm=particuliers>`_
* Via ce compte, vous devez activer l'option *Courbe de charge* pour pouvoir avoir accès à votre consommation horaire

Installation via YunoHost
=================================

`YunoHost <https://yunohost.org/>`_ est un projet ayant pour but de promouvoir l'autohébergement.
Son but est de faciliter l'administration d'un serveur : `en savoir plus <https://yunohost.org/#/whatsyunohost_fr>`_

.. image:: https://yunohost.org/images/ynh_logo_black_300dpi.png
    :align: center
    :height: 200px
    :width: 200px

`Des nombreuses applications sont déjà packagées <https://yunohost.org/#/apps>`_ pour être utilisées
avec et c'est le cas de Pilea.

.. image:: https://install-app.yunohost.org/install-with-yunohost.png
    :target: https://install-app.yunohost.org/?app=pilea
    :align: center


Installation à la main
========================

Pilea est une application basée sur le framework Symfony. Elle s'installe sur un serveur web disposant
d'un PHP récent et d'un serveur de base de données MySQL.

**Prérequis :**

* PHP 7.3 et supérieur
* MySQL (5.5 et supérieur) / PostreSQL (9.6 et supérieur)

.. note::

    SQLite devrait fonctionner mais vous aurez à adapter les fichiers ``.env`` & ``config/packages/doctrine.yaml``

    Il n'est pas prévu que Pilea le supporte *officiellement*, si vous souhaitez vous y coller allez-y, faites une merge request et
    je regarderai :)

.. warning::

    Les migrations de Pilea sont uniquement générées pour MySQL, si vous utilisez un autre type de serveur, gardez à l'esprit qu'il
    faudra vérifier chaque migration avant de la lancer !

**Installation :**

Télécharger `le dépot <https://gitlab.com/pilea/Pilea>`_ :

.. code-block:: sh

    git clone https://gitlab.com/pilea/Pilea.git [app_folder]

Créer un base de donnés puis adapter les fichiers ``.env`` et ``config/packages/doctrine.yaml``

Installer les dépendance `Composer <https://getcomposer.org/>`_ :

.. code-block:: sh

    cd [app_folder]
    composer install

Lancer le script d'installation :

.. code-block:: sh

    bin/console pilea:install

Ajouter une premier utilisateur et lui donner les droits administrateur :

.. code-block:: sh

    bin/console pilea:user:add [username] [password]
    bin/console pilea:user:grant [username]

Mettre en place le cron :

.. code-block:: sh

    echo "*/10  *  *  *  * [user] /[app_folder]/bin/console pilea:fetch-data false" > /etc/cron.d/pilea
    # où [user] est l'utilisateur linux qui lancera le cron


Enfin, configurer `NGINX <https://symfony.com/doc/current/setup/web_server_configuration.html#web-server-nginx>`_ ou
`Apache <https://symfony.com/doc/current/setup/web_server_configuration.html>`_ comme pour une application Symfony 4 classique