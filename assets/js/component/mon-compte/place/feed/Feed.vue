
<template>
  <div class="feed-detail">
    <span :class="icon + ' p-mr-2 icon'" :title="description"></span>
    <span v-html="label"></span>
    <Button
      icon="pi pi-pencil"
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
    <AddLinkyForm
      v-if="isElectricityFeed"
      :visible="displayForm"
      :update="true"
      v-on:toggleVisible="toggleForm()"
      :place="place"
    />
    <AddGazparForm
      v-if="isGazFeed"
      :visible="displayForm"
      :update="true"
      v-on:toggleVisible="toggleForm()"
      :place="place"
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