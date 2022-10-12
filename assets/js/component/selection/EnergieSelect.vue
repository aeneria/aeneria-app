<template>
  <Dropdown
    v-if="!onlyOneEnergie"
    :modelValue="energie"
    :options="feedDataTypeEnergieList"
    optionLabel="label"
    @change="setSelectedEnergie"
  />
</template>

<script lang="ts" src="./EnergieSelect.ts" />
