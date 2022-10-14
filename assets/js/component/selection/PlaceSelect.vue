<template>
  <p class="select-place p-d-flex p-ai-center">
    <span class="p-pr-2">{{ place?.name }}</span>
    <Button
      v-if="placeList.length > 1"
      icon="pi pi-directions"
      title="Sélectionner une autre adresse"
      class="p-button-rounded p-button-secondary p-button-icon"
      @click="openDialog()"
    />
  </p>
  <Dialog header="Sélectionner une autre adresse" v-model:visible="displayDialog" :style="{width: '50vw'}">
    <div class="p-d-flex p-jc-center p-flex-wrap">
      <template v-for="placeItem of placeList" :key="placeItem.id">
        <Button
          class="p-button p-button-rounded p-button-secondary p-mr-1 p-mb-1"
          :label="placeItem.name"
          @click="setSelectedPlace(placeItem)"
        />
      </template>
    </div>
  </Dialog>
</template>

<script lang="ts" src="./PlaceSelect.ts" />

<style lang="scss" >
  @import '../../../css/variables';

  .select-place {
    font-family: $font-title;
    color: white;
  }
</style>
