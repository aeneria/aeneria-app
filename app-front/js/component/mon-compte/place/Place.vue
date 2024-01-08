
<template>
  <div>
    <Card class="card-place p-mr-3 p-mb-3">
      <template #title>
       {{ place.name }}
      </template>
      <template #content>
        <div class="p-d-flex p-flex-column p-jc-between">
          <div class="p-p-0 p-d-flex p-flex-wrap">
            <ul>
              <li class="p-mb-2" v-if="feedMeteo">
                <Feed :place="place" :feed="feedMeteo"/>
              </li>
              <li class="p-mb-2" v-if="feedGaz">
                <Feed :place="place" :feed="feedGaz"/>
              </li>
              <li class="p-mb-2" v-if="feedElectricite">
                <Feed :place="place" :feed="feedElectricite"/>
              </li>
              <li class="p-mb-2" v-if="configuration.placeCanBePublic">
                <span class="p-mr-2 icon fas fa-eye"></span>
                <span>Adresse publique&nbsp;: {{ place.public ? 'oui' : 'non' }}</span>
              </li>
              <li v-if="configuration.userCanSharePlace">
                <span class="p-mr-2 icon fas fa-share-nodes"></span>
                <span>Partagée avec&nbsp;: </span>
                <ul v-if="place.allowedUsers.length" class="p-mt-1" >
                  <li v-for="allowedUser of place.allowedUsers">
                    {{ allowedUser.username }}
                  </li>
                </ul>
                <template v-else>Personne</template>
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
      v-if="displayDeleteForm"
      :visible="displayDeleteForm"
      v-on:toggleVisible="toggleDeleteForm()"
      :place="place"
    />
    <EditForm
      v-if="displayEditNomForm"
      :visible="displayEditNomForm"
      v-on:toggleVisible="toggleEditNomForm()"
      :place="place"
    />
    <ExportDataForm
      v-if="displayExportDataForm"
      :visible="displayExportDataForm"
      v-on:toggleVisible="toggleExportDataForm()"
      :place="place"
    />
    <ImportDataForm
      v-if="displayImportDataForm"
      :visible="displayImportDataForm"
      v-on:toggleVisible="toggleImportDataForm()"
      :place="place"
    />
    <RefreshDataForm
      v-if="displayRefreshDataForm"
      :visible="displayRefreshDataForm"
      v-on:toggleVisible="toggleRefreshDataForm()"
      :place="place"
    />
    <AddLinkyForm
      v-if="displayAddLinkyForm"
      :visible="displayAddLinkyForm"
      v-on:toggleVisible="toggleLinkyForm()"
      :place="place"
    />
    <AddGazparForm
      v-if="displayAddGazparForm"
      :visible="displayAddGazparForm"
      v-on:toggleVisible="toggleGazparForm()"
      :place="place"
    />
  </div>
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
      .icon {
        width: 1.5rem;
        text-align: center;
      }
      ul {
        list-style: disc;
        padding-left: 2rem;
      }
    }
  }
}
</style>