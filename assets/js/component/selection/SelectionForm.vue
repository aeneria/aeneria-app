<template>
  <div
    v-if="['xl', 'lg'].includes(grid.breakpoint)"
    class="selection-form p-d-flex p-jc-center p-ai-center p-formgroup-inline"
  >
    <div v-if="!onlyOneEnergie" class="p-field">
      <EnergieSelect/>
    </div>
    <div v-if="['analyse', 'comparaison'].includes(type)" class="p-field">
      <MeteoSelect/>
    </div>

    <DoublePeriodeSelect v-if="type=='comparaison'"/>
    <div v-else class="p-field">
      <PeriodeSelect/>
    </div>

    <div class="p-field">
      <GranulariteSelect/>
    </div>
  </div>
  <div v-else>
    <Button
      id="button-selection"
      icon="pi pi-filter-fill"
      title="Modifier la sélection courante"
      class="p-button-rounded p-button-secondary p-button-icon"
      @click="toggleDialog()"
    />
    <Dialog
      class="p-m-2 select-dialog"
      header="Sélection courante"
      v-model:visible="displayDialog"
      :modal="true"
      :style="{width: '90vw'}"
    >
      <div class="p-d-flex p-flex-column p-jc-center p-ai-center p-ml-auto p-mr-auto">
        <div v-if="!onlyOneEnergie" class="p-field">
          <EnergieSelect/>
        </div>
        <div v-if="['analyse', 'comparaison'].includes(type)" class="p-field">
          <MeteoSelect/>
        </div>

        <DoublePeriodeSelect v-if="type=='comparaison'"/>
        <div v-else class="p-field">
          <PeriodeSelect/>
        </div>

        <div class="p-field">
          <GranulariteSelect :isMobile="true"/>
        </div>
      </div>
    </Dialog>
  </div>
</template>

<script lang="ts" src="./SelectionForm.ts" />

<style lang="scss">
  @import '../../../css/variables';

  .selection-form {
    margin-right: -1em;
    .p-calendar {
      max-width: 230px;
    }
  }

  #button-selection {
    position: fixed;
    bottom: 1rem;
    right: 1rem;
    z-index: 4;
  }
  .select-dialog {
    .p-field, .p-component {
      max-width: 100%;
    }
  }

</style>
