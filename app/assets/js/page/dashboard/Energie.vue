<template>
  <SelectionForm type="classique"/>
  <div class="p-grid dash-energie">
    <div class="p-col-12 p-sm-offset-1 p-sm-10 p-md-offset-0 p-md-6 p-xl-3">
      <Card class="card-calendrier">
        <template #title>
          <div class="p-d-flex p-jc-between">
            <div>{{ energie?.label }}</div>
            <AideCalendrier/>
          </div>
        </template>
        <template #content>
          <Index
            :feedDataType="energie"
            :valeur="indexEnergie"
            texte="consommés sur la période"
          />
          <Calendrier
            id="calendrier"
            :rawPeriode="periode"
            :feedDataId="feedDataId"
            :feedDataType="energie"
            :min="0"
          />
        </template>
      </Card>
    </div>
    <div v-if="['xl'].includes(grid.breakpoint)" class="p-col-9">
      <div class="p-grid p-mb-2">
        <div class="p-col-12">
          <Card class="card-repartition-jour-heure">
            <template #title>
              <div class="p-d-flex p-jc-between">
                <div>En moyenne sur la semaine</div>
                <AideSemaineJours/>
              </div>
            </template>
            <template #content>
              <SemaineHorizontal
                v-if="energie?.hasHourlyData"
                id="semaine-h"
                :periode="periode"
                :feedDataId="feedDataId"
                :feedDataType="energie"
                :min="0"
              />
              <JourSemaine
                v-else
                id="semaine-j"
                :periode="periode"
                :feedDataId="feedDataId"
                :feedDataType="energie"
              />
            </template>
          </Card>
        </div>
        <div class="p-col-12">
          <Card class="evol-conso">
            <template #title>
              <div class="p-d-flex p-jc-between">
                <div>Évolution de la consommation</div>
                <AideEvolution/>
              </div>
            </template>
            <template #content>
              <Evolution
                id="evolution"
                :periode="periode"
                :granularite="granularite"
                :feedDataId="feedDataId"
                :feedDataType="energie"
              />
            </template>
          </Card>
        </div>
      </div>
    </div>
    <template v-else>
      <div class="p-col-12 p-sm-offset-1 p-sm-10 p-md-offset-0 p-md-6 p-xl-9">
        <Card class="card-repartition-jour-heure">
          <template #title>
            <div class="p-d-flex p-jc-between">
              <div>En moyenne sur la semaine</div>
              <AideSemaineJours/>
            </div>
          </template>
          <template #content>
            <div class="p-d-flex p-flex-column p-ai-center p-jc-center">
              <SemaineVertical
                v-if="energie?.hasHourlyData"
                id="semaine-h"
                :periode="periode"
                :feedDataId="feedDataId"
                :feedDataType="energie"
                :min="0"
              />
              <JourSemaine
                id="semaine-j"
                :periode="periode"
                :feedDataId="feedDataId"
                :feedDataType="energie"
              />
            </div>
          </template>
        </Card>
      </div>
      <div class="p-col-12">
        <Card class="evol-conso">
          <template #title>
            <div class="p-d-flex p-jc-between">
              <div>Évolution de la consommation</div>
              <AideEvolution/>
            </div>
          </template>
          <template #content>
            <Evolution
              id="evolution"
              :periode="periode"
              :granularite="granularite"
              :feedDataId="feedDataId"
              :feedDataType="energie"
            />
          </template>
        </Card>
      </div>
    </template>
  </div>
</template>

<script lang="ts" src="./Energie.ts"></script>

<style lang="scss">
.dash-energie {
  .card-calendrier {
    min-height: 806px;
  }
  .card-repartition-jour-heure,
  .card-repartition-jour-semaine {
    min-height: 250px;
  }
  .evol-conso {
    min-height: 540px;
  }
}
</style>
