<template>
  <SelectButton
    :modelValue="granularite"
    :options="granulariteList"
    optionLabel="label"
    @change="setSelectedGranularite"
  />
</template>

<script lang="ts" src="./GranulariteSelect.ts" />
