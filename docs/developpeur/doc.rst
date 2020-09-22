Générer cette documentation
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
