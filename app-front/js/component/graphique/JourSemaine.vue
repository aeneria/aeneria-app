<template>
  <div v-show="!(loading||error)" class="graph graph-jour-semaine">
    <div :id="id"></div>
  </div>
  <Spinner v-show="loading&&!error"/>
  <Erreur v-show="!loading&&error"/>
</template>

<script lang="ts" src="./JourSemaine.ts" />
