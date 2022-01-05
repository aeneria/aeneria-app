<template>
  <SelectButton
    :modelValue="selectedGranularite"
    :options="granulariteList"
    optionLabel="label"
    @change="setSelectedGranularite"
  />
</template>

<script lang="ts" src="./GranulariteSelect.ts" />
