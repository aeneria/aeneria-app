Livrer une nouvelle version
******************************

Pour livrer une nouvelle version d'æneria :

Commencez par :

* Mettre à jour le numéro de version dans ``config/services.yaml``
* Renseigner les nouveautés de cette version dans le fichier ``CHANGELOG.md``

Commitez ces changements et poussez un nouveau tag :

.. code-block:: bash

    git add CHANGELOG.md config/services.yaml
    git commit -m "Prepare 1.2.3"
    git push
    git tag 1.2.3
    git push --tag

La `CI Gitlab <https://gitlab.com/aeneria/aeneria-app/pipelines>`_ va alors :

* Générer les assets en mode prod
* Télécharger les dépendences Composer en mode prod
* Passer les tests
* Archiver les sources (avec les dépendences Composer et les assets)
* Envoyer cette archive sur statics.aeneria.com
* Créer une release Gitlab

Il reste ensuite à modifier la `release Gitlab <https://gitlab.com/aeneria/aeneria-app/-/releases>`_ pour y renseigner le changelog
(copier/coller du fichier ``CHANGELOG.md``)

La documentation sera regénérée automatiquement par `readthedocs <https://readthedocs.org/projects/aeneria/>`_.

C'est bon, votre nouvelle version est en ligne !
