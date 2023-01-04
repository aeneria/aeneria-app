<template>
  <div v-show="!(loading||error)" class="graph graph-semaine-h p-ai-center p-jc-center">
    <Legende
      class="p-mr-5 p-ml-5"
      :id="id + '--legende'"
      :max="max"
      :min="min"
      :feedDataType="feedDataType"
      :range="range"
      :maxValeur="maxValeur"
      :moyValeur="moyValeur"
      :minValeur="minValeur"
    />
    <div :id="id" >
    </div>
  </div>
  <Spinner v-show="loading&&!error"/>
  <Erreur v-show="!loading&&error"/>
</template>

<script lang="ts" src="./SemaineHorizontal.ts" />

<style lang="scss">
  .graph-semaine-h {
    display: flex;

    rect {
      stroke: white;
      stroke-width: 2;
    }

    .bar-chart {
      rect {
        stroke-width: 0;
      }
    }
  }
</style>
