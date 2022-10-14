
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
          <div class=" p-grid">

            <div class="p-col-12 p-md-6">
              <Fieldset legend="kWh">
                <p>
                  Kilowatt-heure est l'unité d'énergie généralement utilisée pour mesurer la consommation d'électricité.
                </p>

                <a
                  class="p-button p-button-outlined p-button-rounded p-button-secondary"
                  href="https://fr.wikipedia.org/wiki/Kilowatt-heure"
                >
                  le kWh sur Wikipédia
                  <i class="pi pi-external-link p-ml-2"></i>
                </a>
              </Fieldset>
            </div>
            <div class="p-col-12 p-md-6">
              <Fieldset legend="DJU">
                <p>
                  Les Degrés Jour Unifié sont la différence entre la température extérieure et une température de référence (18°C pour æneria)
                  Plus le nombre de DJU est grand pour une période donnée et plus il a fait froid.
                  æneria utilise la méthode chauffagiste pour calculer les DJU.
                </p>

                <a
                  class="p-button p-button-outlined p-button-rounded p-button-secondary"
                  href="https://fr.wikipedia.org/wiki/Degr%C3%A9_jour_unifi%C3%A9"
                >
                  Le DJU sur Wikipédia
                  <i class="pi pi-external-link p-ml-2"></i>
                </a>
              </Fieldset>
            </div>
            <div class="p-col-12 p-md-6">
              <Fieldset legend="Humidité">
                <p>
                  C'est la mesure de l'humidité relative dans l'air en %.
                  <ul>
                    <li>À 0%, l'air est complètement sec</li>
                    <li>À 100%, l'air est saturé en eau</li>
                  </ul>
                  Pour l'homme, un air sec est plus confortable qu'un air humide.
                </p>

                <a
                  class="p-button p-button-outlined p-button-rounded p-button-secondary"
                  href="https://fr.wikipedia.org/wiki/Humidit%C3%A9_relative"
                >
                  L'humidité relative sur Wikipédia
                  <i class="pi pi-external-link p-ml-2"></i>
                </a>
              </Fieldset>
            </div>
            <div class="p-col-12 p-md-6">
              <Fieldset legend="Nebulosité">
                <p>
                  C'est une mesure qui permet de caracériser la fraction de ciel couverte par des nuages en %.
                  <ul>
                    <li>À 0%, le ciel est bleu et compètement dégagé</li>
                    <li>À 100% le ciel est complètement obstrué par des nuages</li>
                  </ul>
                </p>

                <a
                  class="p-button p-button-outlined p-button-rounded p-button-secondary"
                  href="https://fr.wikipedia.org/wiki/Nuage#N%C3%A9bulosit%C3%A9_et_opacit%C3%A9"
                >
                  La nébulosité sur Wikipédia
                  <i class="pi pi-external-link p-ml-2"></i>
                </a>
              </Fieldset>
            </div>
            <div class="p-col-12 p-md-6">
              <Fieldset legend="Précipitations">
                <p>
                  C'est la mesure du nombre de millimètres de pluie tombée par m².
                </p>

                <a
                  class="p-button p-button-outlined p-button-rounded p-button-secondary"
                  href="https://fr.wikipedia.org/wiki/Pr%C3%A9cipitations#Pluviom%C3%A9trie"
                >
                Les précipitations sur Wikipédia
                  <i class="pi pi-external-link p-ml-2"></i>
                </a>
              </Fieldset>
            </div>
          </div>
        </TabPanel>
      </TabView>
    </div>
  </div>
</template>

<script lang="ts" src="./Aide.ts"/>
