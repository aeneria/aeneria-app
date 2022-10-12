
<template>
  <Aide titre="Graphique d'évolution temporelle" :asDialog="asDialog">
    <p>
      Le graphique d'évolution temporelle est un graphique assez classique.
      Il affiche, pour la période sélectionnée, des points de donnée en fonction de la granulosité choisie.
      Il présente chaque point de donnée sous forme de points reliés par une courbe.
    </p>

    <h4>Aperçus</h4>

    <div class="p-d-flex p-jc-center">
      <img
        src="/image/aide/graph_evo_temp.png"
        alt="Aperçu du Graphique d'évolution temporelle"
      >
    </div>

    <h4>Comment lire le graphique</h4>

    <p>
      Ce graphique permet par exemple de relever des consommations anormales :
      J'observe ma consommation d'électricité, pour un jour donné je vois un pic sur le graphique, est-ce normal ? Que s'est-il passé ce jour ?
      <ul>
        <li>Était-ce un retour de vacances ? Dans ce cas le pic est peut-être normal (reprise de chauffage par exemple)</li>
        <li>Ou bien ai-je oublié d'éteindre un appareil gourmand avant d'aller me coucher ce jour-là ?</li>
      </ul>
    </p>
  </Aide>
</template>

<script lang="ts" src="./AideEvolution.ts"/>

<style lang="scss" scoped>
  img {
    width: 600px;
    max-width: 100%;
  }
</style>
