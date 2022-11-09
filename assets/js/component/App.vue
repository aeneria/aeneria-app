<template>
  <div id="body" class="">
    <TopbarDesktop v-if="['xl', 'lg'].includes(grid.breakpoint)"/>
    <TopbarMobile v-else/>
    <div class="p-grid">
      <SidebarMenu v-if="['xl', 'lg'].includes(grid.breakpoint)"/>
      <div id="main-content" class="p-col p-pt-4">
        <Spinner v-if="!initialized" />
        <Welcome v-else-if="displayWelcome"/>
        <router-view v-else></router-view>
      </div>
    </div>
    <Toast>
      <template #message="slotProps">
        <span :class="iconClass"></span>
        <div class="p-toast-message-text">
          <span class="p-toast-summary">{{slotProps.message.summary}}</span>
          <div class="p-toast-detail" v-html="slotProps.message.detail" />
        </div>
      </template>
    </Toast>
  </div>
</template>

<script lang="ts" src="./App.ts"/>

<style lang="scss">
  #main-content {
    padding-right: 1em;
    max-width: 100%;
  }
</style>
