<template>
  <div class="selection-form p-d-flex p-pt-3 p-mb-4">
    <div class="p-formgroup-inline p-ml-auto p-mr-auto">
      <div class="p-field">
        <EnergieSelect/>
      </div>
      <div class="p-field">
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
    background-color: $selection-bg-color;
    margin-right: -1em;
  }
</style>
