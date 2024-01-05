<template>
  <div class="parameter p-d-block">

    <div class="p-text-center p-my-4">
      <img class="img-fluid" src="/image/logo-enedis.png" alt="Logo d'Enedis">
    </div>

    <p>
      À l'étape suivante, vous allez être rediriger vers votre compte personnel
      Enedis où vous pourrez donner votre accord pour qu’Enedis nous transmette
      vos données.
    </p>

    <Message severity="warn" :closable="false">
      <p>Pour donner votre autorisation, vous devez avoir un compte personnel Enedis.</p>
      <div class="p-text-center">
        <a
        class="p-button p-button-rounded p-button-sm p-button-outlined p-button-warning"
        href="https://mon-compte-particulier.enedis.fr/"
          target="_blank"
        >
          Je crée mon compte personnel Enedis
        </a>
      </div>
      <div class="p-text-center">
        <small class="p-mt-2">(Munissez-vous d'une facture d’électricité pour créer votre espace)</small>
      </div>
    </Message>

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
        label="Associer votre compteur Linky"
        icon="pi pi-arrow-right"
        @click="$emit('next')"
        class="p-button-rounded p-button-secondary"
      />
    </div>
  </div>
</template>

<script lang="ts" src="./Step3Enedis.ts" />
