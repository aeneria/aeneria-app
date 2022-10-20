<template>
  <div class="selection-form p-d-flex p-ai-center p-pt-3 p-mb-2">
    <div class="p-formgroup-inline p-ai-center p-ml-auto p-mr-auto">
      <div class="p-field">
        <EnergieSelect/>
      </div>
      <div v-if="['analyse', 'comparaison'].includes(type)" class="p-field">
        <MeteoSelect/>
      </div>

      <DoublePeriodeSelect v-if="type=='comparaison'"/>
      <div v-else class="p-field">
        <PeriodeSelect/>
      </div>

      <div class="p-field">
        <GranulariteSelect/>
      </div>
    </div>
  </div>
</template>

<script lang="ts" src="./SelectionForm.ts" />

<style lang="scss">
  @import '../../../css/variables';

  .selection-form {
    margin-right: -1em;
  }
</style>
