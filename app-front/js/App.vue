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

    <Dialog
      v-if="isDisconnected"
      class="p-m-2 welcome"
      header="Déconnecté·e"
      :modal="true"
      :style="{'max-width': '90vw'}"
      :visible="true"
    >
      <div class="p-d-flex p-flex-column p-jc-center p-ai-center p-mb-4">
        <h2>Oooh</h2>
        <i class="fas fa-sad-tear"></i>

        <p>Il semblerait que vous soyez déconnecté·e</p>
        <div class="p-d-flex p-jc-center">
          <Button
            label="Aller à la page de connexion"
            class="p-button-rounded p-button-secondary"
            @click="goToLogin"
          />
        </div>
      </div>
    </Dialog>
    <Toast>
      <template #message="slotProps">
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
