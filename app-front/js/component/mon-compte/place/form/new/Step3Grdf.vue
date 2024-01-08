<template>
  <div class="parameter p-d-block">

    <div class="p-text-center p-my-4">
      <img class="img-fluid" src="/image/logo-grdf.png" alt="Logo de GRDF">
    </div>

    <p>
      À l'étape suivante, vous allez être rediriger vers service <i>Client Connect</i>
      de GRDF qui vous permettera de donner votre accord pour que GRDF nous transmette
      vos données.
    </p>

    <ProxyWarningMessage
      v-if="$store.state.configuration?.proxyForGrdf"
      provider="GRDF"
    />

    <div class="p-d-flex p-jc-end">
      <Button
        label="Annuler"
        icon="pi pi-times"
        @click="$emit('cancel')"
        class="p-button-text p-button-rounded p-button-secondary"
      />
      <Button
        label="Étape précédente"
        icon="pi pi-arrow-left"
        @click="$emit('previous')"
        class="p-button-text p-button-rounded p-button-secondary"
      />
      <Button
        label="Associer votre compteur Gazpar"
        icon="pi pi-arrow-right"
        @click="$emit('next')"
        class="p-button-rounded p-button-secondary"
      />
    </div>
  </div>
</template>

<script lang="ts" src="./Step3Grdf.ts" />
