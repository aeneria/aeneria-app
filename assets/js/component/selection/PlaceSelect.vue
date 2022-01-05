<template>
  <Dropdown
    v-if="!onlyOnePlace"
    :modelValue="selectedPlace"
    :options="placeList"
    optionLabel="name"
    @change="setSelectedPlace"
  />
</template>

<script lang="ts" src="./PlaceSelect.ts" />

<style lang="scss">
</style>
