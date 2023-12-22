Tester & améliorer la qualité du code
****************************************

PHPUNIT
---------

Pour lancer les tests PHPUNIT, il faut préalablement avoir créé un minimum de données de tests :

.. code-block:: bash

  # Il faut avoir un utilisateur 'admin/password' avec des données à jour:
  docker compose exec php-fpm bin/console aeneria:dev:generate-fake-data --from="7 days ago" --user-name=admin@example.com --user-password=password
  # Cette commande est à lancer une fois par jour

  # On s'assure qu'il a les droits admin:
  docker compose exec php-fpm bin/console aeneria:user:grant admin@example.com

  # Il faut avoir un utilisateur 'user-test/password' avec des données pour les 7 derniers jours:
  docker compose exec php-fpm bin/console aeneria:dev:generate-fake-data --from="7 days ago" --user-name=user-test@example.com --user-password=password
  # La commande précédente est à lancer une fois par jour

  # Enfin, on s'assure que user-test n'est pas admin
  docker compose exec php-fpm bin/console aeneria:user:ungrant user-test@example.com

  # On peut maintenant lancer les tests l'esprit tranquille:
  docker compose exec php-fpm bin/phpunit

CS Fixer
-------------

Avant de commiter, passez-donc un petit coup de CS-Fixer pour s'assurer que le style de code reste homogène :

.. code-block:: bash

  docker compose exec php-fpm vendor/bin/php-cs-fixer fix --allow-risky=yes
