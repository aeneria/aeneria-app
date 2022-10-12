<template>
  <SelectionForm type="classique"/>
  <div class="p-grid dash-energie">
    <div class="p-col-12 p-md-4 p-lg-3">
      <Card class="card-calendrier">
        <template #title>
          <div class="p-d-flex p-jc-between">
            <div>{{ energie?.label }}</div>
            <AideCalendrier/>
          </div>
        </template>
        <template #content>
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
    <div class="p-col-12 p-md-8 p-lg-9">
      <div class="p-grid p-mb-2">
        <div class="p-col">
          <Card class="card-repartition-jour-heure">
            <template #title>
              <div class="p-d-flex p-jc-between">
                <div>En moyenne sur la semaine</div>
                <AideSemaineJours/>
              </div>
            </template>
            <template #content>
              <template v-if="energie?.hasHourlyData">
                <SemaineHorizontal
                  v-if="['xl', 'lg', 'md'].includes(grid.breakpoint)"
                  id="semaine-h"
                  :periode="periode"
                  :feedDataId="feedDataId"
                  :feedDataType="energie"
                  :min="0"
                />
                <template v-else>
                  <SemaineVertical
                    id="semaine-v"
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
                </template>
              </template>
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
      </div>
      <div class="p-grid">
        <div class="p-col">
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
