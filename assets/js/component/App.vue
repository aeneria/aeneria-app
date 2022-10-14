<template>
  <div id="body" class="">
    <div class="p-grid">
      <div id="top-bar" class="p-col p-d-flex p-ai-center">
        <div class="logo p-d-flex p-ai-center">
          <svg
            height="46px"
            width="auto"
            viewBox="0 0 116.40089 116.40089"
            version="1.1"
            x="0px"
            y="0px"
          >
            <g
              id="g941"
              style="fill:#ffffff;fill-rule:evenodd;stroke:none;stroke-width:1"
              transform="translate(8.2004433,8.3865552)">
              <path
                d="m 95,28.436606 v 2.714231 C 95,46.73776 83.33876,59.683736 67.863121,61.294179 v -5.481841 c 11.512862,-1.460256 20.34135,-10.577357 21.563658,-21.94727 h -9.043816 c -11.540005,0 -20.927624,9.38762 -20.927624,20.927624 v 26.684507 c 0,5.045755 -4.104822,9.150577 -9.150577,9.150577 -5.045755,0 -9.150577,-4.104822 -9.150577,-9.150577 v -7.85046 c 0,-5.646504 -4.171772,-10.324029 -9.592091,-11.145536 -2.744088,4.697429 -10.374695,10.500454 -10.374695,10.500454 -3.47693,2.24105 -8.055837,-0.255138 -8.055837,-4.39253 V 68.247134 H 7.1466832 c -1.0151223,0 -1.9551509,-0.693938 -2.1189095,-1.69549 -0.2071863,-1.276593 0.7726511,-2.379475 2.0103403,-2.379475 h 6.093448 V 55.077686 H 7.1466832 c -1.0151223,0 -1.9551509,-0.693033 -2.1189095,-1.694584 -0.2071863,-1.276594 0.7726511,-2.380381 2.0103403,-2.380381 h 6.093448 v -0.341993 c 0,-4.137392 4.578907,-6.63358 8.055837,-4.39253 0,0 7.902935,6.010212 10.51493,10.749259 8.356212,0.917409 14.880318,8.013314 14.880318,16.609282 v 7.85046 c 0,2.052864 1.669252,3.722116 3.722115,3.722116 2.051958,0 3.722115,-1.669252 3.722115,-3.722116 V 35.356085 c 0,-11.539099 -9.387619,-20.927624 -20.927624,-20.927624 h -9.042007 c 1.35621,12.447463 11.930853,22.166218 24.732975,22.166218 v 5.428462 c -16.712423,0 -30.308005,-13.596487 -30.308005,-30.30891 V 9 H 33.099253 C 47.586912,9 59.37934,20.75081 59.45172,35.222183 h 0.0036 V 38.79773 C 64.275812,32.505238 71.862087,28.436606 80.382963,28.436606 Z"
                id="path939"
                inkscape:connector-curvature="0"
                style="fill:#ffffff" />
            </g>
          </svg>
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
          v-for="item in sideBarLinks"
          :key="item.to"
          :to="item.to"
          :icon="item.icon"
          :titre="item.titre"
        />
      </div>
      <div id="main-content" class="p-col">
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
      }
  }
  #main-content {
    padding-right: 1em;
  }
  #side-menu {
    width: 60px;
  }
</style>
