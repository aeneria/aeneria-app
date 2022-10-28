<template>
  <SelectButton
    v-if="!isMobile"
    :modelValue="granularite"
    :options="granulariteList"
    optionLabel="label"
    @change="setSelectedGranularite"
  />
  <Dropdown
  v-else
    :modelValue="granularite"
    :options="granulariteList"
    optionLabel="label"
    @change="setSelectedGranularite"
  />
</template>

<script lang="ts" src="./GranulariteSelect.ts" />
