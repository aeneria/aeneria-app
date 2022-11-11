
<template>
  <div class="feed-detail">
    <span :class="icon + ' p-mr-2 icon'" :title="description"></span>
    <i
      v-if="feed.fetchError > 5"
      v-tooltip="'Il semble qu\'il y ait des erreurs avec ce compteur. Vérifiez son fonctionnement en cliquant sur le bouton en bout de ligne.'"
      class="pi pi-exclamation-triangle p-mr-1"
      style="color: #c63737;"
    ></i>
    <span v-html="label"></span>
    <Button
      icon="pi pi-wrench"
      title="Modifier"
      class="p-button-xs p-button-rounded p-button-secondary p-button-icon-only p-ml-1"
      @click="toggleForm"
    />
    <EditMeteoForm
      v-if="isMeteoFeed"
      :visible="displayForm"
      v-on:toggleVisible="toggleForm()"
      :place="place"
    />
    <EditLinkyForm
      v-if="isElectricityFeed"
      :visible="displayForm"
      :update="true"
      v-on:toggleVisible="toggleForm()"
      :place="place"
      :feed="feed"
    />
    <EditGazparForm
      v-if="isGazFeed"
      :visible="displayForm"
      :update="true"
      v-on:toggleVisible="toggleForm()"
      :place="place"
      :feed="feed"
    />
  </div>
</template>

<script lang="ts" src="./Feed.ts"/>

<style lang="scss">
.feed-detail {
  .icon {
    width: 1.5rem;
    text-align: center;
  }
}
</style>