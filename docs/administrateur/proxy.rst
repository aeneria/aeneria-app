.. _proxy:

Utiliser le serveur communautaire d'æneria
#########################################

Avant de vous expliquer le fonctionnement du serveur communautaire d'æneria, lisez
attentivement les 2 paragraphes suivants:

.. warning::

    proxy.aeneria.com est un serveur communautaire fourni à titre gracieux.

    **Merci de l'utiliser raisonnablement et dans un cadre privé non-commercial.**

    Nous nous réservons le droit de bannir de ce serveur les instances qui en feront
    un usage trop intensif, et ce **sans explications et sans avertissement**.

.. danger::

    Ayez conscience qu'en passant par le proxy.aeneria.com, lorsque vous donnez
    votre consentement pour accéder aux données, vous donnez votre consentement
    pour proxy.aeneria.com et non pour *votre* instance æneria.

    Cela signifie que, techniquement, le serveur communautaire peut avoir accès à toutes
    vos données. Nous sommes de bonne fois (ou du moins nous essayons de l'être
    le plus possible) et nous vous promettons que nous n'irons pas voir vos données.

    Mais nous nous pouvons pas vous le prouver. C'est à vous de voir si vous souhaitez
    nous faire confiance ou non.

    **Ayez ceci en tête en utilisant ce serveur proxy**


æneria va récupérer les données de consommation d'énergie à la fois chez Enedis et chez Grdf.

Pour se faire, le système utilise les APIs mises à disposition par ces 2 structures :

* `le Data Hub d'Enedis <https://datahub-enedis.fr/data-connect/>`_
* `le portail Grdf Adict <https://sites.grdf.fr/web/portail-api-grdf-adict/>`_

Mais pour utiliser ces APIs il est nécessaire d'avoir un compte chez ces 2 plateformes et de signer
un contrat. Seulement, pour ouvrir un compte sur chacune de ces plateformes, il faut être une
entreprise, une association ou une collectivité locale.

Pour permettre à tout le monde d'utiliser æneria, un serveur communautaire a été mis à disposition pour qu'une
instance d'æneria puisse bénéficier des clés d'API d'aeneria.com.

Au lieu d'utiliser le comportement classique pour se connecter à Enedis et Grdf:

.. code-block::

      Mode 1 - Connexion directe à Enedis et GRDF                   ┌──────────────┐
                                                                    │              │
                    ┌─────────────────────────────────────────┐     │ API Enedis   │
                 ┌──┤via vos identifiants Enedis Data Connect ├────►│ Data Connect │
                 │  └─────────────────────────────────────────┘     │              │
     ┌─────────┐ │                                                  └──────────────┘
     │Instance ├─┤
     │aeneria  │ │                                                   ┌────────────┐
     └─────────┘ │  ┌─────────────────────────────────────────┐      │            │
                 └──┤via vos identifiants Grdf Adict          ├─────►│  API Grdf  │
                    └─────────────────────────────────────────┘      │  Adict     │
                                                                     │            │
                                                                     └────────────┘



Vous pouvez configurez votre instance pour quelle passe par proxy.aeneria.com, le serveur
communautaire :

.. code-block::

   Mode 2 - Connexion à Enedis et GRDF                      ┌──────────────┐
   via proxy.aeneria.com                                    │              │
                                                            │ API Enedis   │
                                                      ┌────►│ Data Connect │
                         ┌──────────────────┐         │     │              │
      ┌─────────┐        │                  ├─────────┘     └──────────────┘
      │Instance ├────────┤      proxy       │
      │aeneria  │        │  communautaire   ├─────────┐     ┌────────────┐
      └─────────┘        │                  │         │     │            │
                         └──────────────────┘         └────►│  API Grdf  │
                                                            │  Adict     │
                                                            │            │
                                                            └────────────┘
