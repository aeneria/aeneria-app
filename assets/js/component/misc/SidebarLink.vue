
<template>
  <router-link :to="to" :title="titre" class="side-bar-link">
    <template v-slot="slotProps">
      <img
        :src="imageBase + '-' + (slotProps.isActive ? 'w' : 'g') + '.png'"
      >
    </template>
  </router-link>
</template>

<script lang="ts" src="./SidebarLink.ts"/>

<style lang="scss">
  .side-bar-link{
    img {
      max-width: 40px;
      height: auto;
    }
    &.router-link-active {
      img {
        background-color: #6a8389;
        border-radius: 25px;
        box-shadow: 0 2px 4px rgba(0,0,0,.1);
      }
    }
  }
</style>