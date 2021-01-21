Mettre à jour æneria
#####################

.. warning::

    Avant toute chose :
        * faites un backup de la base de données d'æneria
        * Prenez en note la version courante d'æneria

Rendez-vous dans le répertoire parent du répertoire d'installation d'æneria, puis procédez comme suit :

.. code-block:: sh

    # Renommer la version courante :
    mv aeneria-app aeneria-app-backup

    # Téléchargez et décompressez le dernière version au format tar.gz :
    wget http://statics.aeneria.com/aeneria-app-latest.tar.gz
    tar -xvzf aeneria-app-latest.tar.gz
    rm aeneria-app-latest.tar.gz

    # Entrez dans le répertoire d'æneria :
    cd aeneria-app

    # Affichez le changelog pour vérifier s'il n'y a pas d'avertissement
    # pour la mise à jour :
    less CHANGELOG.md

    # Copiez les différents fichiers de configuration :
    mv ../aeneria-app-backup/.env .
    mv ../aeneria-app-backup/config/packages/doctrine.yaml config/packages/doctrine.yaml
    rsync -av ../aeneria-app-backup/var/ var/
    rsync -av ../aeneria-app-backup/private/ private/

    # Lancer les éventuelles migrations :
    php7.3 bin/console doctrine:migrations:migrate

    # Videz les caches :
    php7.3 bin/console c:c

Et voilà, votre instance d'æneria est à jour !
