
<template>
<Dialog
  header="Bienvenue sur æneria"
  :visible="true"
  :breakpoints="{'960px': '75vw', '640px': '90vw'}"
  :style="{width: '50vw'}"
  :modal="true"
  :closable="false"
  class="welcome"
>
  <div class="p-d-flex p-flex-column p-jc-center p-ai-center p-mb-4">

    <h2>Oooh</h2>
    <i class="fas fa-sad-tear"></i>

    <p> Il n'y a pas encore de données disponibles...</p>
      <h2>Pour commencer à en acquérir&nbsp;:</h2>
      <div class="p-d-flex p-jc-center">
        <Button
          icon="pi pi-plus-circle"
          label="Ajouter une adresse"
          class="p-button-rounded p-button-secondary"
          @click="goToNewPLace"
        />
      </div>
      <p>Les premières données devraient ensuite apparaître dans les 5 minutes !</p>

  </div>

  <template #footer>
  </template>
</Dialog>
</template>

<script lang="ts" src="./NoAdresse.ts"/>

<style lang="scss">
  @import '../../../css/variables';

  .welcome {
    .fas {
      font-size: 6em;
      margin-bottom: 25px;
    }
  }
</style>
