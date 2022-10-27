<template>
  <div class="p-grid p-mt-3 dash-home">
    <div
      class="p-col-12 p-sm-offset-1 p-sm-10 p-md-offset-2 p-md-8 p-lg-offset-0 p-lg-12 p-mb-2"
      v-for="month in monthsToDisplay"
      :key="month"
    >
      <Card>
        <template #title>{{ formatMonth(month) }}</template>
        <template #content>
          <MonthSummary
            :month="month"
         />
        </template>
      </Card>
    </div>

    <div class="p-col-12 p-d-flex p-jc-center">
      <Button
        icon="pi pi-plus-circle"
        label="Voir le mois précédent"
        class="p-button-rounded p-button-secondary"
        @click="addMonth"
      />
    </div>
  </div>
</template>

<script lang="ts" src="./Home.ts"></script>
