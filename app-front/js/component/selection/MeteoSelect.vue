<template>
  <Dropdown
    :modelValue="meteoData"
    :options="feedDataTypeMeteoList"
    optionLabel="label"
    @change="setSelectedMeteoData"
  />
</template>

<script lang="ts" src="./MeteoSelect.ts" />
