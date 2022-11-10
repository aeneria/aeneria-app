<template>
  <div v-show="!(loading||error)" class="graph graph-double-evol p-ai-center p-jc-around">
    <div :id="id"></div>
  </div>
  <Spinner v-show="loading&&!error"/>
  <Erreur v-show="!loading&&error"/>
</template>

<script lang="ts" src="./DoubleEvolution.ts" />
