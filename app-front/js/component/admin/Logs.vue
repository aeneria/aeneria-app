<template>
  <div class="logs">
    <div v-if="!logs" class="p-d-flex p-jc-center">
      <Button
        label="Charger les logs"
        class="p-button-rounded p-button-secondary"
        @click="loadLogs"
      ></Button>
    </div>
    <div v-else>
      <DataTable
        class="p-datatable-sm"
        :value="logs"
        stripedRows
        responsiveLayout="scroll"
        :paginator="true"
        :rows="50"
        v-model:filters="filters"
        filterDisplay="menu"
        paginatorTemplate="CurrentPageReport FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink RowsPerPageDropdown"
        :rowsPerPageOptions="[50, 100, 200]"
        currentPageReportTemplate="{first} à {last} sur {totalRecords}"
        dataKey="0"
        :rowClass="getLogRowClass"
      >
        <Column header="Date" field="date" bodyClass="text-center" :sortable="true"></Column>
        <Column header=" " bodyClass="text-center" filterField="severity" :showFilterMatchModes="false">
          <template #body="{data}">
            <i :class="data.severity"></i>
          </template>
          <template #filter="{filterModel, filterCallback}">
              <MultiSelect
                v-model="filterModel.value"
                @change="filterCallback()"
                :options="severityOptions"
                placeholder="Any"
                class="p-column-filter"
                :showClear="true"
              >
                  <template #value="slotProps">
                      <i :class="slotProps.value" v-if="slotProps.value"></i>
                      <span v-else>{{slotProps.placeholder}}</span>
                  </template>
                  <template #option="slotProps">
                      <i :class="slotProps.option"></i> {{ getLogSeverityLabel(slotProps.option) }}
                  </template>
              </MultiSelect>
          </template>
        </Column>
        <Column header="Message" field="message" bodyClass="text-center"></Column>
      </DataTable>
    </div>
  </div>
</template>

<script lang="ts" src="./Logs.ts"></script>

<style lang="scss">
.logs {
  .p-datatable.p-datatable-striped .p-datatable-tbody > tr {
    &.critique,
    &.critique:nth-child(2n) {
      background-color: #f7a0a0
    }
    &.erreur,
    &.erreur:nth-child(2n) {
      background-color: #fff187
    }
    &.info,
    &.info:nth-child(2n) {
      background-color: #afe5ff
    }
  }
}
</style>