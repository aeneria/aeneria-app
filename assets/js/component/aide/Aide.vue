
<template>
  <div class="parameter p-d-grid p-ai-start p-mt-2">
    <div class="p-col-12">
      <h1>Aide</h1>
      <TabView>
        <TabPanel header="Comprendre les différents graphiques">
          <AideEvolution :asDialog="false" />
          <AideSemaineJours :asDialog="false" />
          <AideCalendrier :asDialog="false" />
          <AidePapillon :asDialog="false" />
          <AideAnalyseCroisee :asDialog="false" />
        </TabPanel>
        <TabPanel header="Glossaire" class="p-mb-3">
          <Fieldset legend="kWh" class="p-mb-3">
            <p>
              Kilowatt-heure est l'unité d'énergie généralement utilisée pour mesurer la consommation d'électricité.
            </p>

            <a class="p-button p-button-outlined p-button-rounded p-button-secondary" href="https://fr.wikipedia.org/wiki/Kilowatt-heure">le kWh sur Wikipédia</a>
          </Fieldset>
          <Fieldset legend="DJU" class="p-mb-3">
            Les Degrés Jour Unifié sont la différence entre la température extérieure et une température de référence (18°C pour æneria)

            Plus le nombre de DJU est grand pour une période donnée et plus il a fait froid.

            æneria utilise la méthode chauffagiste pour calculer les DJU.

            <a href="https://fr.wikipedia.org/wiki/Degr%C3%A9_jour_unifi%C3%A9">Le DJU sur Wikipédia</a>
          </Fieldset>
          <Fieldset legend="Humidité" class="p-mb-3">
            C'est la mesure de l'humidité relative dans l'air en %.

            * A 0%, l'air est complètement sec
            * A 100%, l'air est saturé en eau

            Pour l'homme, un air sec est plus confortable qu'un air humide.

            <a href="https://fr.wikipedia.org/wiki/Humidit%C3%A9_relative">L'humidité relative sur Wikipédia</a>
          </Fieldset>
          <Fieldset legend="Nebulosité" class="p-mb-3">
            C'est une mesure qui permet de caracériser la fraction de ciel couverte par des nuages en %.

            * A 0%, le ciel est bleu et compètement dégagé
            * A 100% le ciel est complètement obstrué par des nuages

            <a href="https://fr.wikipedia.org/wiki/Nuage#N%C3%A9bulosit%C3%A9_et_opacit%C3%A9">La nébulosité sur Wikipédia</a>
          </Fieldset>
          <Fieldset legend="Précipitations" class="p-mb-3">
            C'est la mesure du nombre de millimètres de pluie tombée par m².

            <a href="https://fr.wikipedia.org/wiki/Pr%C3%A9cipitations#Pluviom%C3%A9trie">Les précipitations sur Wikipédia</a>
          </Fieldset>
        </TabPanel>
      </TabView>
    </div>
  </div>
</template>

<script lang="ts" src="./Aide.ts"/>
