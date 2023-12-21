Générer les assets
*************************

Les sources de l'application front se situent dans le dossier `app-front`.

Les assets sont gérer avec `Webpack Encore <https://symfony.com/doc/current/frontend.html>`_.

.. code-block:: bash

    cd app-front

    nvm use

    # Installer les dépendances
    yarn install

    # Build en mode prod
    yarn build

    # Build en mode dev
    yarn dev

    # Build en mode watch
    yarn dev --watch
