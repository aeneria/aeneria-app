<template>
  <div class="p-inputgroup">
      <Calendar
        v-model="periode"
        selectionMode="range"
        :yearNavigator="true"
        :showButtonBar="true"
        @update:modelValue="setSelectedPeriode"
      />
      <Dropdown
        :options="preSelectList"
        @change="setPreselectedPeriode"
        class="preselect-period"
      >
        <template #value>
          <div></div>
        </template>
      </Dropdown>
  </div>
</template>

<script lang="ts" src="./PeriodeSelect.ts" />

<style lang="scss" >
  .p-inputgroup .p-inputwrapper.preselect-period {
    max-width: 2.5rem;
    .p-inputtext {
      display: none;
    }
  }
</style>
