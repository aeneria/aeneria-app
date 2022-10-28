<template>
  <div class="parameter p-grid p-ai-start p-mt-2">
    <div class="p-col-12">
        <h1>Ajouter une nouvelle adresse</h1>
      <Step1
        class="p-col-12"
        v-if="currentStep == 1"
        :name="name"
        :meteo="meteo"
        @next="onStep1"
      ></Step1>
      <Step2
        class="p-col-12"
        v-if="currentStep == 2"
        @next="onStep2"
      >
      </Step2>
    </div>
  </div>
</template>

<script lang="ts" src="./NewPlace.ts" />