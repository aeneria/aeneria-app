Divers commands utiles
******************************

.. code-block:: sh

  # Allumer les containers
  docker compose up -d

  # Éteindre les containers
  docker compose down

  # Utiliser la console de symfony
  docker compose exec php-fpm bin/console

  # CLI vers la base de données
  docker compose exec postgres psql -U db db