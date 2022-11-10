<template>
  <div v-show="!(loading||error)" class="graph graph-calendrier p-ai-center p-jc-around">
    <div :id="id" class=""></div>
    <Legende
      class="p-ml-3"
      :id="id + '--legende'"
      :max="max"
      :min="min"
      :feedDataType="feedDataType"
      :range="range"
      :maxValeur="maxValeur"
      :moyValeur="moyValeur"
      :minValeur="minValeur"
    />
  </div>
  <Spinner v-show="loading&&!error"/>
  <Erreur v-show="!loading&&error"/>
</template>

<script lang="ts" src="./Calendrier.ts" />

<style lang="scss">
  .graph-calendrier {
    display: flex;

    rect {
      stroke: white;
      stroke-width: 2;
    }
  }
</style>
