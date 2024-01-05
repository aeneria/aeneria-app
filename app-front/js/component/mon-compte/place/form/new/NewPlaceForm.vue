<template>
  <Dialog
  header="toto"
    v-model:visible="visible"
    :style="{width: '650px'}"
    :breakpoints="{'650px': '100vw'}"
    :modal="true" :closable="false"
  >
    <template #header>
      <span class="p-dialog-title" v-if="currentStep == 1">
        Ajouter une nouvelle adresse
      </span>
      <span class="p-dialog-title" v-else-if="currentStep == 2">
        Associer un compteur
      </span>
      <span class="p-dialog-title" v-else-if="currentStep == 'enedis'">
        Associer un compteur Linky
      </span>
      <span class="p-dialog-title" v-else-if="currentStep == 'grdf'">
        Associer un compteur Gazpar
      </span>
    </template>
    <Step1
      v-if="currentStep == 1"
      :name="name"
      :meteo="meteo"
      @next="onStep1"
      @cancel="closeBasic"
    />
    <Step2
      v-if="currentStep == 2"
      @next="onStep2"
      @previous="onPrevious"
      @cancel="closeBasic"
    />
    <Step3Enedis
      v-if="currentStep == 'enedis'"
      @next="onStep3"
      @previous="onPrevious"
      @cancel="closeBasic"
    />
    <Step3Grdf
      v-if="currentStep == 'grdf'"
      @next="onStep3"
      @previous="onPrevious"
      @cancel="closeBasic"
    />
  </Dialog>

</template>

<script lang="ts" src="./NewPlaceForm.ts" />