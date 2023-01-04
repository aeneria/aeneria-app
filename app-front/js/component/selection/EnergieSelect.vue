<template>
  <Dropdown
    :modelValue="energie"
    :options="feedDataTypeEnergieList"
    optionLabel="label"
    @change="setSelectedEnergie"
  />
</template>

<script lang="ts" src="./EnergieSelect.ts" />
