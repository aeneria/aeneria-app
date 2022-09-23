<template>
  <Dialog
    :header="(update ? 'Modifier votre' : 'Associer un') + ' compteur Gazpar de GRDF'"
    v-model:visible="visible"
    :style="{width: '650px'}"
    :breakpoints="{'650px': '100vw'}"
    :modal="true" :closable="false"
  >
    <div class="p-text-center p-mb-''">
      <img class="img-fluid" src="/image/logo-grdf.png" alt="Logo de GRDF">
    </div>
    <p>
      En cliquant sur ce bouton, vous allez accéder au service <i>Client Connect</i> de GRDF qui vous permettera de donner votre accord pour que GRDF nous transmette vos données.
    </p>

    <template #footer>
      <div class="p-text-center">
        <Button
          label="Annuler"
          icon="pi pi-times"
          @click="closeBasic()"
          class="p-button-text p-button-rounded p-button-secondary"
        />
        <Button
          :label="(update ? 'Modifier votre' : 'Associer un') + ' compteur Gazpar'"
          icon="pi pi-arrow-right"
          @click="post()"
          class="p-button-rounded p-button-secondary"
        />
      </div>
    </template>
  </Dialog>
</template>

<script lang="ts" src="./AddGazparForm.ts" />
