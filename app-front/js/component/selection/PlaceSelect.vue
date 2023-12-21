<template>
  <div class="select-place p-d-flex p-ai-center">
    <p class="p-pr-2">{{ place?.name }}</p>
    <template v-if="placeList">
      <Button
        v-if="!onlyOnePlace"
        icon="pi pi-directions"
        title="Sélectionner une autre adresse"
        class="p-button-rounded p-button-secondary p-button-icon"
        @click="openDialog()"
      />
      <Dialog
        header="Sélectionner une autre adresse"
        v-model:visible="displayDialog"
        :style="{'max-width': '90vw'}"
      >
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
  </div>
</template>

<script lang="ts" src="./PlaceSelect.ts" />

<style lang="scss" >
  @import '../../../css/variables';

  .select-place {
    font-family: $font-title;
    color: white;
  }
</style>
