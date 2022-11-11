<template>
  <SelectionForm type="analyse"/>
  <div class="p-grid dash-analyse">
    <div class="p-col-12 p-md-4 p-lg-3">
      <Card class="card-papillon">
        <template #title>
          <div class="p-d-flex p-jc-between">
            <div>Analyse temporelle</div>
            <AidePapillon/>
          </div>
        </template>
        <template #content>
          <Papillon
            id="papillon"
            :periode="periode"
            :granularite="granularite"
            :feedDataId1="feedDataId1"
            :feedDataType1="energie"
            :feedDataId2="feedDataId2"
            :feedDataType2="meteo"
          />
        </template>
      </Card>
    </div>
    <div class="p-col-12 p-md-8 p-lg-9">
      <Card class="card-analse-croisee">
        <template #title>
          <div class="p-d-flex p-jc-between">
            <div>Analyse croisée</div>
            <AideAnalyseCroisee/>
          </div>
        </template>
        <template #content>
          <NuagePoint
            id="nuage"
            :periode="periode"
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


<script lang="ts" src="./Analyse.ts"></script>
