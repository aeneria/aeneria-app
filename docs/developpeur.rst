Documentation développeur
##########################

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


Générer les assets
*************************

Les assets sont gérer avec `Webpack Encore <https://symfony.com/doc/current/frontend.html>`_.

.. code-block:: bash

    # Installer les dépendances
    yarn install

    # Build en mode prod
    yarn build

    # Build en mode dev
    yarn dev

    # Build en mode watch
    yarn dev --watch

Générer cette Documentation
*******************************

La documentation est automatiquement générer à chaque nouveau tag à l'aide de `Read the Docs <https://readthedocs.org/>`_.
Tout est donc basé sur `Sphinx <https://www.sphinx-doc.org/>`_ et écrit en RST.

Les fichiers se trouvent dans le dossier ``docs``.

Si vous la modifiez, il est nécessaire de la générer en local pour être sûr qu'il n'y a pas d'erreur
de syntax.

Pour se faire, suivez ces étapes :

.. code-block:: bash

    # Vérifier que vous avez bien descendu le repository
    # du thème sphinx pour aeneria
    git submodule init
    git submodule update --recursive

    # La première fois que vous générer cette doc, install
    # l'environement python pour sphinx
    pip install --user virtualenv
    mkdir ~/venvs
    virtualenv ~/venvs/sphinx
    . ~/venvs/sphinx/bin/activate
    pip install sphinx

    # Regénérer la documentation
    . ~/venvs/sphinx/bin/activate
    cd docs/
    make html

La page d'accueil de la documentation ainsi générée se trouve ici : ``docs/_build/html.index.html``

Tests & CS fixer
******************

PHPUNIT
---------

Pour lancer les tests PHPUNIT, il faut préalablement avoir créé un minimum de données de tests :

.. code-block:: bash

  # Il faut avoir un utilisateur 'admin/password' avec des données à jour:
  php7.3 bin/console aeneria:dev:generate-fake-data --from="7 days ago" --user-name=admin --user-password=password
  # Cette commande est à lancer une fois par jour

  # On s'assure qu'il a les drtois admin:
  php7.3 bin/console aeneria:user:grant admin

  # Il faut avoir un utilisateur 'user-test/password' avec des données pour les 7 derniers jours:
  php7.3 bin/console aeneria:dev:generate-fake-data --from="7 days ago" --user-name=user-test --user-password=password
  # La commande précédente est à lancer une fois par jour

  # Enfin, on s'assure que user-test n'est pas admin
  php7.3 bin/console aeneria:user:ungrant user-test

  # On peut maintenant lancer les tests l'esprit tranquille:
  php7.3  bin/phpunit

CS Fixer
-------------

Avant de commiter, passez-donc un petit coup de CS-Fixer pour s'assurer que le style de code reste homogène :

.. code-block:: bash

    vendor/bin/php-cs-fixer fix --allow-risky=yes

Livrer une nouvelle version
******************************

Pour livrer une nouvelle version d'æneria :

Commencez par :

* Mettre à jour le numéro de version dans `config/services.yaml`
* Renseigner les nouveautés de cette version dans le fichier `CHANGELOG.md`

Commitez ces changements et poussez un nouveau tag :

.. code-block:: bash

    git add CHANGELOG.md config/services.yaml
    git commit -m "Prepare 1.2.3"
    git push
    git tag 1.2.3
    git push --tag

La CI va alors :

* Générer les assets en mode prod
* Télécharger les dépendences Composer en mode prod
* Passer les tests
* Archiver les sources (avec les dépendences Composer et les assets)
* Envoyer cette archive sur statics.aeneria.com
* Créer une release Gitlab

Il reste ensuite à modifier la `release Gitlab <https://gitlab.com/aeneria/aeneria-app/-/releases>`_ pour y renseigner le changelog
(copier/coller du fichier `CHANGELOG.md`)

La documentation sera regénérée automatiquement par `readthedocs <https://readthedocs.org/projects/aeneria/>`_.

C'est bon, votre nouvelle version est en ligne !
