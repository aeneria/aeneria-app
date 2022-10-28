<template>
  <div >
    <Button
      v-if="!isMobile"
      type="button"
      icon="pi pi-ellipsis-v"
      @click="toggleMenuMonCompte"
      aria-haspopup="true"
      aria-controls="overlay_menu_mon_compte"
      class="p-button-secondary p-button-rounded p-button-icon-only"
    />
    <Menu
      id="overlay_menu_mon_compte"
      ref="menuMonCompte"
      :model="menuMonCompteItems"
      :popup="!isMobile"
      :class="{'menu-mon-compte': true, 'mobile': isMobile}"
    />
  </div>
</template>

<script lang="ts" src="./MenuMonCompte.ts"/>

<style lang="scss">
.menu-mon-compte.mobile {
  border: none;
}
</style>