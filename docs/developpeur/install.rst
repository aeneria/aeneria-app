Mettre en place un environnement de développement
******************************************************

Deux solutions pour mettre en place un environnement de développement :

* installer æneria à la main (cf la doc d'administration)
* utiliser l'environnement de développement docker fourni avec le dépôt d'æneria

On va ici partir du principe :
* qu'on utilise l'environnement docker
* que docker et docker-compose sont correctement installés sur votre machine

Pour créer votre environnement de développement, suivez ces étapes :

.. code-block:: sh
    # Récupérez le dépot git du projet
    git clone git@gitlab.com:aeneria/aeneria-app.git

    # Construire et lancer les containers dockers
    docker-compose up -d
    # Pour trouver le port où est exposée l'application
    docker-compose ps

    # Récupérer les dépendance composer
    docker-compose exec php-fpm composer install

    # Copier le .env.dist
    cp app/.env.dist app/.env
    # L'adapter pour être en environnement de développement

    # Installer aeneria
    docker-compose exec php-fpm bin/console aeneria:install

    # Générer des users de tests
    docker-compose exec php-fpm bin/console aeneria:dev:generate-fake-data --from="7 days ago" --user-name=admin@example.com --user-password=password
    docker-compose exec php-fpm bin/console aeneria:user:grant admin@example.com
    docker-compose exec php-fpm bin/console aeneria:dev:generate-fake-data --from="7 days ago" --user-name=user-test@example.com --user-password=password

    # Générer les assests une première fois :
    cd app
    nvm use # Sélectionner la bonne version de node
    yarn install # Installer les dépendances javascript
    yarn dev # Générer les assets en mode dev

Et voilà, `votre environnement <http://localhost:8066>`_ est près.