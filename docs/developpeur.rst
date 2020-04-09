Documentation développeur
##########################

Mettre en place un environnement de développement
-----------------------------------------------------

Il n'y pas encore de docker ou de truc comme ça pour installer facilement le projet.
Pour monter un environement de dev :

* Suivre la doc d'installation classique
* Refaire un ``composer install`` en incluant les dépendances de dev :

.. code-block:: sh

    php7.3 composer.phar install

* Changer la variable ``APP_ENV`` dans le ``.env`` :

.. code-block:: sh

    ...
    ###> symfony/framework-bundle ###
    APP_ENV=dev
    ...

* Générer des données de tests :

.. code-block:: sh

    # Génére des données pour les 3 derniers mois pour un utilisateur user-test/password
    # attention, la génération peut-être un peu longue, vous pouvez réduire le nombre de
    # de données créées avec l'option --from
    php7.3 bin/console aeneria:dev:generate-fake-data

Et voilà !

Générer les assets
------------------------------

Les assets sont gérer avec `Webpack Encore <https://symfony.com/doc/current/frontend.html>`_

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
--------------------------

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

Tests
-----------------------------

Pour lancer les tests PHP, il faut préalablement avoir créé un minimum de données de tests :

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
