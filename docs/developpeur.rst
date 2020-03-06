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
    php7.3 bin/console pilea:dev:generate-fake-data

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
