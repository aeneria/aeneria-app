<template>
  <div class="p-grid p-mb-5 dash-index">
    <div class="p-col-4 p-sm-4 p-d-flex p-jc-center p-ai-center p-pr-3">
      <i :class="feedDataType.icon"></i>
    </div>
    <div class="p-col-8 p-sm-8">
      <p>
        <span class="dash-index-value">{{ valeur }}</span>
        <span v-if="total" class="dash-index-total">/{{ total }}</span>
        &nbsp;{{ unite ?? feedDataType.unite }}
      </p>
      <p v-if="texte">{{ texte }}</p>
    </div>
  </div>
</template>

<script lang="ts" src="./Index.ts" />

<style lang="scss">
@import '../../../css/variables';

.dash-index {
  font-family: $font-title;
  i {
    font-size: 3rem;
  }
  p {
    margin: 0;
  }
  .dash-index-value {
    font-size: 2rem;
  }
  .dash-index-total{
    font-size: 1rem;
  }
}
</style>
