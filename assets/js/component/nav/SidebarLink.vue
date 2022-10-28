
<template>
  <router-link :to="to" :title="titre" class="side-bar-link p-mb-3">
    <template v-slot="slotProps">
      <Button
        :icon="icon"
        :class="'p-button-lg p-button-rounded p-button-secondary' + (slotProps.isActive ? '' : ' p-button-text')"
        :label="isMobile ? label : undefined"
      />
    </template>
  </router-link>
</template>

<script lang="ts" src="./SidebarLink.ts"/>

<style lang="scss">
  @import '../../../css/variables';

  .side-bar-link .p-button-label {
    font-family: $font-title;
  }
</style>