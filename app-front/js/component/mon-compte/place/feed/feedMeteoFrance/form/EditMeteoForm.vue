<template>
  <Dialog
    header="Modifier la station météo de l'adresse"
    v-model:visible="visible"
    :style="{width: '650px'}"
    :breakpoints="{'650px': '100vw'}"
    :modal="true" :closable="false"
  >
    <div class="p-d-flex p-jc-center">
      <Dropdown
        id="meteo"
        v-model="meteoValue"
        :options="meteoOptionList"
        optionLabel="label"
        :filter="true"
        :class="{'p-invalid':v$.meteoValue.$invalid && submitted}"
      ></Dropdown>
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
        :disabled="v$.$invalid"
        @click="post()"
        autofocus
        class="p-button-rounded p-button-secondary"
      />
    </template>
  </Dialog>
</template>

<script lang="ts" src="./EditMeteoForm.ts" />
