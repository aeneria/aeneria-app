<template>
  <SelectionForm type="classique"/>
  <div class="p-grid dash-energie">
    <div
      v-for="item in meteoList"
      :key="item.type.id"
      class="p-col-12 p-sm-6 p-xl-3"
    >
      <Card :class="'card-' + item.type.id + '-calendrier'">
        <template #title>
          {{ item.type.label }}
        </template>
        <template #content>
          <Index
            :feedDataType="item.index.type"
            :valeur="item.index.valeur"
            :total="item.index.total"
            :texte="item.index.texte"
            :unite="item.index.unite"
          />
          <Calendrier
            :id="'calendrier-' + item.type.id"
            :rawPeriode="periode"
            :feedDataId="item.feedDataId"
            :feedDataType="item.type"
            :min="0"
          />
        </template>
      </Card>
    </div>
    <div
      v-for="item in meteoList"
      :key="item.type.id"
      class="p-col-12 p-lg-6"
    >
      <Card :class="'card-' + item.type.id + '-evolution'">
        <template #title>
          {{ item.type.label }}
        </template>
        <template #content>
          <Evolution
            :id="'evolution-' + item.type.id"
            :periode="periode"
            :granularite="granularite"
            :feedDataId="item.feedDataId"
            :feedDataType="item.type"
          />
        </template>
      </Card>
    </div>
  </div>
</template>

<script lang="ts" src="./Meteo.ts"></script>
