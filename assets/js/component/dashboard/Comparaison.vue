<template>
  <SelectionForm type="comparaison"/>
  <div class="p-grid dash-comparaison">
    <div class="p-col-12 p-lg-6">
      <Card class="card-calendrier">
        <template #title>
          <div class="p-d-flex p-jc-between">
            <div>Sur la période</div>
            <AideCalendrier/>
          </div>
        </template>
        <template #content>
          <div class="p-grid">
            <div class="p-col">
              <Calendrier
                id="calendrier1"
                :rawPeriode="periode1"
                :feedDataId="feedDataId1"
                :feedDataType="energie"
                :min="0"
                :max="maxEnergie"
              />
            </div>
            <div class="p-col">
              <Calendrier
                id="calendrier2"
                :rawPeriode="periode2"
                :feedDataId="feedDataId1"
                :feedDataType="energie"
                :min="0"
                :max="maxEnergie"
              />
            </div>
          </div>
        </template>
      </Card>
    </div>
    <div class="p-col-12 p-lg-6">
      <Card class="card-semaine">
        <template #title>
          <div class="p-d-flex p-jc-between">
            <div>En moyenne sur la semaine</div>
            <AideSemaineJours/>
          </div>
        </template>
        <template #content>
          <div class="p-grid">
            <div class="p-col p-md-6">
              <SemaineVertical
                id="semaine1-v"
                v-if="energie?.hasHourlyData"
                :periode="periode1"
                :feedDataId="feedDataId1"
                :feedDataType="energie"
                :min="0"
              />
              <JourSemaine
                id="semaine1-j"
                :periode="periode1"
                :feedDataId="feedDataId1"
                :feedDataType="energie"
              />
            </div>
            <div class="p-col p-md-6">
              <SemaineVertical
                id="semaine2-v"
                v-if="energie?.hasHourlyData"
                :periode="periode2"
                :feedDataId="feedDataId1"
                :feedDataType="energie"
                :min="0"
              />
              <JourSemaine
                id="semaine2-j"
                :periode="periode2"
                :feedDataId="feedDataId1"
                :feedDataType="energie"
              />
            </div>
          </div>
        </template>
      </Card>
    </div>
    <div class="p-col-12 p-md-4 p-lg-3">
      <Card class="card-semaine">
        <template #title>
          <div class="p-d-flex p-jc-between">
            <div>Évolution de la consommation</div>
            <AidePapillon/>
          </div>
        </template>
        <template #content>
          <DoubleEvolution
            id="double-evol"
            :periode1="periode1"
            :periode2="periode2"
            :granularite="granularite"
            :feedDataId="feedDataId1"
            :feedDataType="energie"
          />
        </template>
      </Card>
    </div>
    <div class="p-col-12 p-md-8 p-lg-9">
      <Card class="card-semaine">
        <template #title>
          <div class="p-d-flex p-jc-between">
            <div>Analyse croisée</div>
            <AideAnalyseCroisee/>
          </div>
        </template>
        <template #content>
          <NuagePoint
            id="nuage"
            :periode="periode1"
            :periode2="periode2"
            :granularite="granularite"
            :feedDataIdX="feedDataId1"
            :feedDataTypeX="energie"
            :feedDataIdY="feedDataId2"
            :feedDataTypeY="meteo"
          />
        </template>
      </Card>
    </div>
  </div>
</template>

<script lang="ts" src="./Comparaison.ts"></script>
