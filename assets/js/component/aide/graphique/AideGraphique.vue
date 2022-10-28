<template>
  <template v-if="asDialog">
    <Button
      title="Comment lire ce graphique ?"
      class="p-button-lg p-button-rounded p-button-text p-button-plain"
      icon="pi pi-question-circle"
      @click="open"
    />
    <Dialog
      :header="titre"
      v-model:visible="display"
      :breakpoints="{'960px': '75vw', '640px': '90vw'}"
      :style="{width: '50vw'}"
      :modal="true"
    >
      <slot></slot>
    </Dialog>
  </template>
  <Fieldset v-else :legend="titre" class="p-mb-3">
    <slot></slot>
  </Fieldset>
</template>

<script lang="ts" src="./AideGraphique.ts"/>
