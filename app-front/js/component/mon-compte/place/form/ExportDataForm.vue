<template>
  <Dialog
    header="Exporter les données"
    v-model:visible="visible"
    :style="{width: '650px'}"
    :breakpoints="{'650px': '100vw'}"
    :modal="true" :closable="false"
  >
    <p class="p-mb-4">
      æneria permet à chaque utilisateur d'exporter facilement
      les données d'une adresse au format ODS.
    </p>
    <div class="p-d-flex p-flex-column p-ai-center">
      <div class="p-formgroup-inline">
        <div class="p-field">
          <label :for="range" class="">Exporter entre 2 dates</label>
          <Calendar
            :id="range"
            v-model="range"
            selectionMode="range"
            :manualInput="true"
            dateFormat="dd/mm/yy"
            :class="{'p-invalid':v$.$invalid && submitted}"
            placeholder="Sélectionnez une période"
          />
        </div>
        <Button
          label="Exporter"
          icon="pi pi-upload"
          @click="exportData(!v$.$invalid)"
          class="p-button-rounded p-button-success"
        />
      </div>

      <p>ou</p>

      <Button
        label="Exporter toutes les données"
        icon="pi pi-upload"
        @click="exportAllData()"
        autofocus
        class="p-button-rounded p-button-secondary"
      />
    </div>

    <template #footer>
      <Button
        label="Annuler"
        icon="pi pi-times"
        @click="closeBasic"
        class="p-button-text p-button-rounded p-button-secondary"
      />
    </template>
  </Dialog>
</template>

<script lang="ts" src="./ExportDataForm.ts" />
