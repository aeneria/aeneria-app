
<template>
  <Card class="card-place p-mr-3 p-mb-3">
    <template #title>
     {{ place.name }}
    </template>
    <template #content>
      <div class="p-d-flex p-flex-column p-jc-between">
        <div class="p-p-0 p-d-flex p-flex-wrap p-mb-3">
          <ul>
            <li v-if="feedMeteo">
              <Feed :place="place" :feed="feedMeteo"/>
            </li>
            <li v-if="feedGaz">
              <Feed :place="place" :feed="feedGaz"/>
            </li>
            <li v-if="feedElectricite">
              <Feed :place="place" :feed="feedElectricite"/>
            </li>
          </ul>
        </div>
      </div>
    </template>
    <template #footer>
      <div class="p-p-0 p-d-flex p-flex-wrap p-jc-end">
        <Button
          v-if="!feedGaz"
          @click="toggleGazparForm()"
          icon="pi pi-plus-circle"
          label="Gazpar"
          title="Associer un compteur Gazpar de GRDF"
          aria-haspopup="true"
          aria-controls="overlay_menu_edition"
          class="p-button-sm p-button-rounded p-button-secondary p-ml-1"
        />
        <Button
          v-if="!feedElectricite"
          @click="toggleLinkyForm()"
          icon="pi pi-plus-circle"
          label="Linky"
          title="Associer un compteur Linky de ERDF"
          aria-haspopup="true"
          aria-controls="overlay_menu_edition"
          class="p-button-sm p-button-rounded p-button-secondary p-ml-1"
        />
        <Button
          icon="pi pi-ellipsis-v"
          title="Gérer l'adresse"
          @click="toggleMenuEdition"
          aria-haspopup="true"
          aria-controls="overlay_menu_edition"
          class="p-button-sm p-button-rounded p-button-secondary p-button-icon-only p-ml-1"
        />
        <Menu
          id="overlay_menu_edition"
          ref="menuEdition"
          :model="menuEditionItems"
          :popup="true"
        />
      </div>
    </template>
  </Card>

  <DeleteForm
    :visible="displayDeleteForm"
    v-on:toggleVisible="toggleDeleteForm()"
    :place="place"
  />
  <EditNomForm
    :visible="displayEditNomForm"
    v-on:toggleVisible="toggleEditNomForm()"
    :place="place"
  />
  <ExportDataForm
    :visible="displayExportDataForm"
    v-on:toggleVisible="toggleExportDataForm()"
    :place="place"
  />
  <ImportDataForm
    :visible="displayImportDataForm"
    v-on:toggleVisible="toggleImportDataForm()"
    :place="place"
  />
  <RefreshDataForm
    :visible="displayRefreshDataForm"
    v-on:toggleVisible="toggleRefreshDataForm()"
    :place="place"
  />
  <AddLinkyForm
    :visible="displayAddLinkyForm"
    v-on:toggleVisible="toggleLinkyForm()"
    :place="place"
  />
  <AddGazparForm
    :visible="displayAddGazparForm"
    v-on:toggleVisible="toggleGazparForm()"
    :place="place"
  />
</template>

<script lang="ts" src="./Place.ts"/>

<style lang="scss">
.card-place {
  max-width: 440px;
  .p-card-content {
    min-height: 140px;
    ul {
      list-style: none;
      padding-left: 0.5rem;
    }
  }
}
</style>