<template>
  <div id="side-menu" class="p-col-fixed p-d-flex p-flex-column p-pt-3">
    <SidebarLink
      v-for="item in sideBarLinks"
      :key="item.to"
      :to="item.to"
      :icon="item.icon"
      :titre="item.titre"
      :label="item.label"
      :isMobile="isMobile"
    />
  </div>
</template>

<script lang="ts" src="./SidebarMenu.ts"/>

<style lang="scss">
  #side-menu {
    width: 60px;
  }
</style>