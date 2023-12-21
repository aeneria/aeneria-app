<template>
  <div class="spinner" :style="'height:' + height + 'px'">
    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" style="margin: auto; display: block;" width="100px" height="100px" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid">
      <circle cx="84" cy="50" r="10" fill="#eaebec">
        <animate attributeName="r" repeatCount="indefinite" dur="0.5102040816326531s" calcMode="spline" keyTimes="0;1" values="10;0" keySplines="0 0.5 0.5 1" begin="0s"></animate>
        <animate attributeName="fill" repeatCount="indefinite" dur="2.0408163265306123s" calcMode="discrete" keyTimes="0;0.25;0.5;0.75;1" values="#eaebec;#eaebec;#eaebec;#eaebec;#eaebec" begin="0s"></animate>
      </circle>
      <circle cx="16" cy="50" r="10" fill="#eaebec">
        <animate attributeName="r" repeatCount="indefinite" dur="2.0408163265306123s" calcMode="spline" keyTimes="0;0.25;0.5;0.75;1" values="0;0;10;10;10" keySplines="0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1" begin="0s"></animate>
        <animate attributeName="cx" repeatCount="indefinite" dur="2.0408163265306123s" calcMode="spline" keyTimes="0;0.25;0.5;0.75;1" values="16;16;16;50;84" keySplines="0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1" begin="0s"></animate>
      </circle>
      <circle cx="50" cy="50" r="10" fill="#eaebec">
        <animate attributeName="r" repeatCount="indefinite" dur="2.0408163265306123s" calcMode="spline" keyTimes="0;0.25;0.5;0.75;1" values="0;0;10;10;10" keySplines="0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1" begin="-0.5102040816326531s"></animate>
        <animate attributeName="cx" repeatCount="indefinite" dur="2.0408163265306123s" calcMode="spline" keyTimes="0;0.25;0.5;0.75;1" values="16;16;16;50;84" keySplines="0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1" begin="-0.5102040816326531s"></animate>
      </circle>
      <circle cx="84" cy="50" r="10" fill="#eaebec">
        <animate attributeName="r" repeatCount="indefinite" dur="2.0408163265306123s" calcMode="spline" keyTimes="0;0.25;0.5;0.75;1" values="0;0;10;10;10" keySplines="0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1" begin="-1.0204081632653061s"></animate>
        <animate attributeName="cx" repeatCount="indefinite" dur="2.0408163265306123s" calcMode="spline" keyTimes="0;0.25;0.5;0.75;1" values="16;16;16;50;84" keySplines="0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1" begin="-1.0204081632653061s"></animate>
      </circle>
      <circle cx="16" cy="50" r="10" fill="#eaebec">
        <animate attributeName="r" repeatCount="indefinite" dur="2.0408163265306123s" calcMode="spline" keyTimes="0;0.25;0.5;0.75;1" values="0;0;10;10;10" keySplines="0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1" begin="-1.530612244897959s"></animate>
        <animate attributeName="cx" repeatCount="indefinite" dur="2.0408163265306123s" calcMode="spline" keyTimes="0;0.25;0.5;0.75;1" values="16;16;16;50;84" keySplines="0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1" begin="-1.530612244897959s"></animate>
      </circle>
    </svg>
    <!-- <ProgressSpinner strokeWidth="3" class="p-ml-auto p-mr-auto"/> -->
  </div>
</template>

<script lang="ts" src="./Spinner.ts" />

<style lang="scss">
  .spinner {
    display: flex;
  }
</style>
