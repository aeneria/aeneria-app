<template>
  <div id="body" class="">
    <div class="p-grid">
      <div id="top-bar" class="p-col p-d-flex p-ai-center">
        <div class="logo p-d-flex p-ai-center">
          <img src="/image/aeneria.svg" alt="">
          <span class="p-ml-2">æneria</span>
        </div>
        <div class="p-ml-auto p-mr-auto">
          <PlaceSelect />
        </div>
        <div>
          <Button
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
            :popup="true"
          />
        </div>
      </div>
    </div>
    <div class="p-grid">
      <div id="side-menu" class="p-col-fixed p-d-flex p-flex-column p-pt-3">
        <SidebarLink
          to="/app/"
          imageBase="/image/home"
          titre="Accueil"
        />
        <SidebarLink
          to="/app/energie"
          imageBase="/image/electricity"
          titre="La consommation d'énergie en détail"
        />
        <SidebarLink
          to="/app/meteo"
          imageBase="/image/meteo"
          titre="La météo sous tous les angles"
        />
        <SidebarLink
          to="/app/analyse"
          imageBase="/image/analyze"
          titre="Analyse croisée énergie/météo"
        />
        <SidebarLink
          to="/app/comparaison"
          imageBase="/image/compare"
          titre="Comparaison de 2 période"
        />
      </div>
      <div id="main-content" class="p-col">
        <Spinner v-if="!configuration" />
        <Welcome v-else-if="displayWelcome"/>
        <router-view v-else></router-view>
      </div>
    </div>
    <Toast />
  </div>
</template>

<script lang="ts" src="./App.ts"/>

<style lang="scss">
  @import '../../css/variables';
  #top-bar {
      height: 60px;
      background-color: $primary-color;
      .logo {
        span {
          font-family: $font-logo;
          color: #fff;
          font-size: 1.7em;
        }
        img {
          height: 32px;
        }
      }
  }
  #main-content {
    padding-right: 1em;
  }
  #side-menu {
    width: 60px;
  }
</style>
