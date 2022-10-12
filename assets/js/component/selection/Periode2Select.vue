<template>
  <div class="p-inputgroup">
      <Calendar
        v-model="periode2[0]"
        :yearNavigator="true"
        :showButtonBar="true"
        @update:modelValue="setSelectedPeriode2"
      />
      <Calendar
        v-model="periode2[1]"
        :yearNavigator="true"
        :showButtonBar="true"
        :disabled="true"
        @update:modelValue="setSelectedPeriode2"
      />
  </div>
</template>

<script lang="ts" src="./Periode2Select.ts" />

<style lang="scss" >
  .p-inputgroup .p-inputwrapper.preselect-period {
    max-width: 2.5rem;
    .p-inputtext {
      display: none;
    }
  }
</style>
