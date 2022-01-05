<template>
  <Dropdown
    v-if="!onlyOneEnergie"
    :modelValue="selectedEnergie"
    :options="feedDataTypeEnergieList"
    optionLabel="label"
    @change="setSelectedEnergie"
  />
</template>

<script lang="ts" src="./EnergieSelect.ts" />
