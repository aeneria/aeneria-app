<template>
  <Dialog
    :header="(update ? 'Modifier votre' : 'Associer un') + ' compteur Linky dEnedis'"
    v-model:visible="visible"
    :style="{width: '650px'}"
    :breakpoints="{'650px': '100vw'}"
    :modal="true" :closable="false"
  >
    <div class="p-text-center p-mb-4">
      <img class="img-fluid" src="/image/logo-enedis.png" alt="Logo d'Enedis">
    </div>

    <p>
      En cliquant sur ce bouton, vous allez accéder à votre compte personnel Enedis où vous pourrez donner votre accord pour qu’Enedis nous transmette vos données.

    </p>
    <Message v-if="!update" severity="warn" :closable="false">
      <p>Pour donner votre autorisation, vous devez avoir un compte personnel Enedis.</p>
      <div class="p-text-center">
        <a href="https://mon-compte-particulier.enedis.fr/" class="p-button p-button-rounded p-button-secondary p-button-outlined" target="_blank">Je crée mon compte personnel Enedis</a>
      </div>
      <div class="p-text-center">
        <small class=" p-mt-2">(Munissez-vous de votre facture d’électricité pour créer votre espace)</small>
      </div>
    </Message>

    <template #footer>
      <div class="p-text-center">
        <Button
          label="Annuler"
          icon="pi pi-times"
          @click="closeBasic()"
          class="p-button-text p-button-rounded p-button-secondary"
        />
        <Button
          :label="(update ? 'Modifier votre' : 'Associer un') + ' votre compteur Linky'"
          icon="pi pi-arrow-right"
          @click="post()"
          class="p-button-rounded p-button-secondary"
        />
      </div>
    </template>
  </Dialog>
</template>

<script lang="ts" src="./AddLinkyForm.ts" />
