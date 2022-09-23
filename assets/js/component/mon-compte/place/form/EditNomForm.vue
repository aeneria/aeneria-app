<template>
  <Dialog
    header="Modifier le nom de l'adresse"
    v-model:visible="visible"
    :style="{width: '650px'}"
    :breakpoints="{'650px': '100vw'}"
    :modal="true" :closable="false"
  >
    <div class="p-d-flex p-jc-center">
      <InputText
        id="nom"
        type="nom"
        v-model="newName"
        :class="{'p-invalid':v$.newName.$invalid && submitted}"
        :placeholder="place.name"
      />
    </div>

    <template #footer>
      <Button
        label="Annuler"
        icon="pi pi-times"
        @click="closeBasic"
        class="p-button-text p-button-rounded p-button-secondary"
      />
      <Button
        label="Enregistrer"
        icon="pi pi-check"
        @click="post(!v$.$invalid)"
        autofocus
        class="p-button-rounded p-button-secondary"
      />
    </template>
  </Dialog>
</template>

<script lang="ts" src="./EditNomForm.ts" />
