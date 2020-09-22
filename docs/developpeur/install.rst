Mettre en place un environnement de développement
******************************************************

Il n'y pas encore de docker ou de truc comme ça pour installer facilement le projet.
Pour monter un environement de dev :

* Récupérez le dépot git du projet :

.. code-block:: sh

    git clone git@gitlab.com:aeneria/aeneria-app.git

* Créez une base de données sur votre serveur de base de données (MySQL ou PostgreSQL)

* Installez ``composer`` (`Voir comment sur le site de composer <https://getcomposer.org/download/>`_)

* Récupérez les dépendances du projet :

.. code-block:: sh

    php7.3 composer.phar install


* Copiez le fichier ``.env.dist`` et adapatez-le :

    * Modifiez la varibale ``APP_ENV``
    * Adapter la chaine de connexion de la base de données

.. code-block:: sh

    cp .env.dist .env


.. code-block:: sh

    # Fichier .env

    ...

    ###> symfony/framework-bundle ###
    # Changer la variable ``APP_ENV``
    APP_ENV=dev

    ...

    ###> doctrine/doctrine-bundle ###
    # Format described at http://docs.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/configuration.html#connecting-using-a-url
    # For MysSQL database use: "pgsql://[database_user]:[database_password]@127.0.0.1:5432/[database_name]
    # For PostgreSQL database use: "mysql://[database_user]:[database_password]@127.0.0.1:3306/[database_name]
    # For an SQLite database, use: "sqlite:///%kernel.project_dir%/var/data.db"
    # Configure your db driver and server_version in config/packages/doctrine.yaml
    DATABASE_URL=[VOTRE CONFIG ICI]
    ###< doctrine/doctrine-bundle ###


* Installer aeneria :

.. code-block:: sh

    php7.3 bin/console aeneria:install


* Ajoutez une premier utilisateur et donnez-lui les droits administrateur :

.. code-block:: sh

    php7.3 bin/console aeneria:user:add [username] [password]
    php7.3 bin/console aeneria:user:grant [username]


* Générer des données de tests :

.. code-block:: sh

    # Génére des données pour les 3 derniers mois pour un utilisateur user-test/password
    # attention, la génération peut-être un peu longue, vous pouvez réduire le nombre de
    # de données créées avec l'option --from
    php7.3 bin/console aeneria:dev:generate-fake-data

* Générer les assests une première fois :

.. code-block:: sh

    # Installer les dépendances javascript
    yarn install

    # Générer les assets en mode dev
    yarn dev

* Enfin, configurez `NGINX <https://symfony.com/doc/current/setup/web_server_configuration.html#web-server-nginx>`_ ou `Apache <https://symfony.com/doc/current/setup/web_server_configuration.html#apache-with-php-fpm>`_ comme pour une application Symfony 5 classique.
