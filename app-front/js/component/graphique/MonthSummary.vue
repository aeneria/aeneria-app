<template>
  <div class="p-grid">
    <div :class="'p-col-0 ' + (onlyOneEnergie ? 'p-lg-2' : 'p-lg-0')"></div>
    <div
      class="p-col-12 p-lg-4 p-d-flex p-jc-around p-flex-column p-flex-lg-row"
      v-for="(column, index) in columns"
      :key="index"
    >
      <div>
        <Index
          :feedDataType="column.index.type"
          :valeur="column.index.valeur"
          :texte="column.index.texte"
        />
        <Calendrier
          :id="'calendrier-' + monthId + '-' + column.type.id"
          :rawPeriode="[monthStart, monthEnd]"
          :feedDataId="column.feedDataId"
          :feedDataType="column.type"
          :min="0"
        />
      </div>
      <div v-if="index != columns.length - 1" class="vert-sep p-m-2"></div>
    </div>
  </div>
</template>

<script lang="ts" src="./MonthSummary.ts"></script>

<style lang="scss">
  .vert-sep {
    border-right: solid 1px #e5e7eb;
    border-top: solid 1px #e5e7eb;
  }
</style>
