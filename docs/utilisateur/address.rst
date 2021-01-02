
Gestion des adresses
##########################

Dans æneria, chaque utilisateur peux gérer des *Adresses*. Pour chaque *adresse*,
on configure un compteur Linky et une station d'observation météo.

Une *adresse* peut ensuite être rendue publique pour quelle soit visible par tous les
utilisateurs de æneria, ou bien, elle peut être partagée à une liste d'utilisateur.

Pour gérer vos adresses, allez sur la page de configuration en cliquant sur le bouton |icon_configuration|
dans la barre du haut.

.. |icon_configuration| image:: ../img/config.png
             :alt: icone engrenage

Ci-dessous, le formulaire d'ajout d'une adresse :

.. image:: ../img/adresse_list.png
    :align: center
    :scale: 50%
    :alt: Aperçu de la liste des adresses

Ajouter une adresse
====================

Cliquez sur le bouton ``Ajouter une adresse`` sous la liste des adresses existantes :

.. image:: ../img/adresse_form.png
    :align: center
    :scale: 50%
    :alt: Aperçu du formulaire de création d'une adresse

.. note::
    Selon la configuration de l'application, ce bouton peut ne pas être présent.

Editer une adresse
===================
Pour éditer une adresse, sur la liste , cliquez sur le bouton |btn_edit| de l'adresse souhaitée.

.. |btn_edit| image:: ../img/btn_edit.png
             :alt: Bouton d'édition

Il est alors possible de modifier :

* le nom de l'adresse
* son icone
* la station météo d'observation de référence
* Le compteur Linky associé à l'adresse

Rafraichir les données d'une adresse
====================================
Il peut parfois être nécessaire de rafraichir les données d'une adresse manuellement : c'est
à dire forcer la mise à jour des données depuis les serveurs d'Enedis et de Météo France.

Cliquez sur le bouton |btn_refresh| de l'adresse souhaitée.

.. |btn_refresh| image:: ../img/btn_refresh.png
             :alt: Bouton de rafraichissement

Le formulaire qui apparait permet de rafraichir les données pour le flux désiré. Il n'est actuellement
pas possible de rafraichir les données météos au delà de 2 semaines.

.. note::
    Selon la configuration de l'application, cette fonctionnalité peut ne pas être présente.

Exporter les données d'une adresse
==================================
æneria permet à chaque utilisateur d'exporter facilement les données d'une adresse au format ODS.

Cliquez sur le bouton |btn_export| de l'adresse souhaitée.

.. |btn_export| image:: ../img/btn_export.png
             :alt: Bouton d'export

Il est alors possible d'exporter :

* toutes les données
* seulement les données entre 2 dates

.. note::
    Selon la configuration de l'application, cette fonctionnalité peut ne pas être présente.

Importer des données d'une adresse
==================================
Il est possible d'importer un fichier de données provenant d'une exportation æneria.

Cliquez sur le bouton |btn_import| de l'adresse souhaitée.

.. |btn_import| image:: ../img/btn_import.png
             :alt: Bouton d'import

.. danger::
    Attention, si des données existent pour les dates importées, elle seront écrasées !

.. warning::
    Cette fonctionnalité a été réalisée pour importer des fichiers provenant d'un export
    de données créé via æneria : si vous essayez d'importer des fichiers provenant d'une autre source,
    faites-le à vos risques et périls !

.. note::
    Selon la configuration de l'application, cette fonctionnalité peut ne pas être présente.