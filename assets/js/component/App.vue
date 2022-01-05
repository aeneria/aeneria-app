<template>
  <div id="body" class="">
    <div class="p-grid">
      <div id="top-bar" class="p-col p-d-flex">
          <div class="logo">
            <img src="/image/aeneria.svg" alt="">
            <span>æneria</span>
          </div>
          <div class="p-ml-auto p-mr-auto">
            <PlaceSelect />
          </div>
      </div>
    </div>
    <div class="p-grid">
      <div id="side-menu" class="p-col-fixed p-d-flex p-flex-column">
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
        <router-link to="/app/admin">Admin</router-link>
        <router-link to="/app/parametre">Parametre</router-link>
        <a href="/logout">Déconnexion</a>
      </div>
      <div id="main-content" class="p-col">
        <router-view v-if="selectedPlace"></router-view>
        <Spinner v-else />
      </div>
    </div>
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
          margin-left: 5px;
          vertical-align: middle;
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
