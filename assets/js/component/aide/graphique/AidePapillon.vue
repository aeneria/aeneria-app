
<template>
  <Aide titre="Graphique papillon" :asDialog="asDialog">
    <p>
      La graphique papillon est un graohique d'évolution temporelle, à la vertical, sur lequel on affiche simultanément l'évolution de 2 grandeurs
      dans le temps.
    </p>

    <h4>Aperçus</h4>

    <div class="p-d-flex p-jc-center">
      <img
        src="/image/aide/graph_papillon.png"
        alt="graph_papillon"
      >
    </div>

    <h4>Comment lire le graphique</h4>

    <p>
      Comme le graphique d'analyse croisée, le graphique papillon permet de chercher des corrélations entre 2 grandeurs. Dans le cas de æneria, on
      compare toujours la consommation d'électicité et une grandeur météorologique.
    </p>
    <p>
      Si 2 grandeurs sont directement corrélées, le graphique devrait ressembler à un papillon, c'est à dire être symétrique, d'où sont nom.
    </p>
  </Aide>
</template>

<script lang="ts" src="./AidePapillon.ts"/>

<style lang="scss" scoped>
  img {
    width: 150px;
    max-width: 100%;
  }
</style>
