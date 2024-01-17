<template>
  <Dialog
    :header="title"
    v-model:visible="visible"
    :style="{width: '650px'}"
    :breakpoints="{'650px': '100vw'}"
    :modal="true" :closable="false"
  >
    <slot></slot>
    <Message severity="warn" class="p-mb-4 p-mt-1">
      Attention, si des données existent pour les dates importées, elle seront écrasées !
    </Message>
    <div class="p-d-flex p-ai-center">
      <FileUpload
        name="file[]"
        :fileLimit="1"
        mode="basic"
        customUpload
        @uploader="onUpload"
        :multiple="false"
        :auto="true"
      />
      <div v-if="file" class="p-ml-3 overflow-wrap"><p>{{ file.name }}</p></div>
    </div>

    <template #footer>
        <Button
          label="Annuler"
          icon="pi pi-times"
          @click="closeBasic"
          class="p-button-text p-button-rounded p-button-secondary"
        />
        <Button
          label="Importer"
          icon="pi pi-check"
          @click="post(!v$.$invalid)"
          autofocus
          class="p-button-rounded p-button-secondary"
        />
    </template>
  </Dialog>
</template>

<script lang="ts" src="./ImportDataForm.ts" />
