
<template>
  <Aide titre="Carte de chaleur Jours/Heures" :asDialog="asDialog">
    <p>
      Chaque case de la carte de chaleur Jours/Heures affiche la moyenne, sur la période sélectionnée,
      de la grandeur observée pour une tranche horaire. Par exemple, la case en haut à gauche
      représente la consommation moyenne le lundi entre minuit et 1h sur la période sélectionnée.
      Plus une case est foncée et plus la consommation est élévée.
    </p>

    <h4>Aperçus</h4>

    <div class="p-d-flex p-jc-center">
      <img
        src="/image/aide/graph_jours_heures.png"
        alt="Aperçu de la Carte de chaleur Jours/Heures"
      >
    </div>

    <h4>Comment lire le graphique</h4>

    <p>
      Ce graphique permet de mettre en valeur des schémas de consommation. Les tâches observées doivent reflêter vos habitudes de consommation.
      <ul>
        <li>Une tâche foncée apparait les jours de semaine entre 7h30 et 9h, est-ce normal ?</li>
        <li>Je ne vois aucun schéma se dégager, est-ce normal que ma consommation soit la même quelque soit l'heure et le jour de la semaine ?</li>
        <li>Il y a une tâche continue toute la journée du jeudi, mais pas les autres jours, n'y aurait-il pas un problème dans la programmation de mon chauffage ?</li>
      </ul>
    </p>
  </Aide>
</template>

<script lang="ts" src="./AideSemaineJours.ts"/>

<style lang="scss" scoped>
  img {
    width: 600px;
    max-width: 100%;
  }
</style>
