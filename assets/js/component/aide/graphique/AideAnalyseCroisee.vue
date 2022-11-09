
<template>
  <Aide titre="Graphique d'analyse croisée" :asDialog="asDialog">
    <p>
      L'analyse croisée permet de chercher des corrélations entre 2 grandeurs. Pour la période sélectionnée, il affiche un point selon la granulosité choisit.
    </p>

    <h4>Aperçus</h4>

    <div class="p-d-flex p-jc-center">
      <img
        src="/image/aide/graph_analyse_croise.png"
        alt="Aperçu du Graphique d'analyse croisée"
      >
    </div>

    <h4>Comment lire le graphique</h4>

    <p>
      Si on prend le cas où l'on observe la consommation d'électricité et les :term:`DJU` et si l'on considère que le chauffage est électrique.
      Si on sélectionne une péridoe d'hiver, les points affichés devraient se regrouper le long d'une ligne.
    </p>
    <p>
      L'analyse de la pente de cette droite permet de caractériser l'isolation et le système de chauffage de votre habitation.
      Par exemple, si la droite semble horizontale, cela veut peut-être dire que votre chauffage est toujours au maximum et donc qu'il est sous
      dimensionné.
    </p>
    <p>
      Si les points semblent complètement désorganisés, c'est qu'il n'y a aucune corrélation entre la température extérieure et votre consommation
      d'électricité.
    </p>
  </Aide>
</template>

<script lang="ts" src="./AideAnalyseCroisee.ts"/>

<style lang="scss" scoped>
  img {
    width: 600px;
    max-width: 100%;
  }
</style>
