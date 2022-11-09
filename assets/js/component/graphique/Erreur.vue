<template>
  <div class="erreur" :style="'height:' + height + 'px'">
    <div class="p-ml-auto p-mr-auto">
      <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" width="50px" height="50px"  viewBox="0 0 100 100" style="margin: auto; display: block;" xml:space="preserve">
        <switch>
          <g i:extraneous="self" fill="#eaebec"><g>
            <path d="M67.4,40.6c2.2-2.2,2.2-5.8,0-8c-2.2-2.2-5.8-2.2-8,0l-6.6,6.6c-1.6,1.6-4.1,1.6-5.7,0l-6.6-6.6c-2.2-2.2-5.8-2.2-8,0     c-2.2,2.2-2.2,5.8,0,8l6.6,6.6c1.6,1.6,1.6,4.1,0,5.7l-6.6,6.6c-2.2,2.2-2.2,5.8,0,8c1.1,1.1,2.6,1.7,4,1.7s2.9-0.6,4-1.7     l6.6-6.6c1.6-1.6,4.1-1.6,5.7,0l6.6,6.6c1.1,1.1,2.6,1.7,4,1.7s2.9-0.6,4-1.7c2.2-2.2,2.2-5.8,0-8l-6.6-6.6     c-1.6-1.6-1.6-4.1,0-5.7L67.4,40.6z"/>
            <path d="M50,2.5C23.8,2.5,2.5,23.8,2.5,50S23.8,97.5,50,97.5c26.2,0,47.5-21.3,47.5-47.5S76.2,2.5,50,2.5z M50,86.2     C30,86.2,13.8,70,13.8,50C13.8,30,30,13.8,50,13.8S86.2,30,86.2,50C86.2,70,70,86.2,50,86.2z"/>
          </g></g>
        </switch>
      </svg>
    </div>
  </div>
</template>

<script lang="ts" src="./Erreur.ts" />

<style lang="scss">
  .erreur {
    display: flex;
  }
</style>
