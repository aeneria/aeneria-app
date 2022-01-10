<template>
  <div class="p-inputgroup">
      <Calendar
        v-model="periode"
        selectionMode="range"
        :yearNavigator="true"
        :showButtonBar="true"
        @update:modelValue="setSelectedPeriode"
      />
      <Button
        icon="pi pi-ellipsis-v"
        @click="toggleSelectMenu"
        aria-haspopup="true"
        aria-controls="overlay_menu_select"
        class="p-button-secondary"
      />
  </div>
  <Menu
    id="overlay_menu_select"
    ref="selectMenu"
    :model="selectMenuItem"
    :popup="true"
  />
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
