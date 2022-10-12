
<template>
  <Aide titre="Carte de chaleur Jours/Semaines" :asDialog="asDialog">
    <p>
      Sur ce graphique, chaque case représente un jour, chaque ligne une semaine et chaque colonne un jour de la semaine. Plus la case est foncée est plus
      la grandeur observée est grande.
    </p>

    <h4>Aperçus</h4>

    <div class="p-d-flex p-jc-center">
      <img
        src="/image/aide/graph_jours_semaines.png"
        alt="Aperçu de la Carte de chaleur Jours/Semaines"
      >
    </div>

    <h4>Comment lire le graphique</h4>

    <p>
      Comme la carte de chaleur Jours/Heures, ce graphique permet de mettre en évidence des schémas de consomation. Par exemple : En hiver,
      j'observe que les 2 dernières colonnes sont plus foncées, c'est peut-être normal si l'on chauffe toute la journée le weekend
      contrairement aux jours de semaine.
    </p>

    <p>
      Ensuite, comme le graphique d'évolution, il permet de repérer des pics de consommation : pourquoi cette case est-elle foncée au milieu de l'été ?
      Que s'est-il passé ce jour là ?
    </p>

    <p>
      Cette représentation permet aussi d'observer des évenements tels que les vacances : je suis parti une semaine en février, pourtant, je ne le vois pas sur le graphique ?
      Ai-je éteint le chauffage avant de partir ?
    </p>

    <p>
      Afficher sur une ou plusieurs années, il permet aussi de voir apparaite des saisonalités (notamment sur le tableau de bords météo)
    </p>
  </Aide>
</template>

<script lang="ts" src="./AideCalendrier.ts"/>

<style lang="scss" scoped>
  img {
    width: 275px;
    max-width: 100%;
  }
</style>