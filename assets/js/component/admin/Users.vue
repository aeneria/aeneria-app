<template>
  <div class="users">
    <DataTable
      :value="list"
      :loading="loading"
      stripedRows
      responsiveLayout="scroll"
      :paginator="true"
      :rows="10"
      v-model:filters="filters"
      filterDisplay="menu"
      paginatorTemplate="CurrentPageReport FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink RowsPerPageDropdown"
      :rowsPerPageOptions="[10,20,50]"
      currentPageReportTemplate="{first} à {last} sur {totalRecords}"
      v-model:expandedRows="expandedRows" dataKey="id"
    >
      <Column :expander="true" headerStyle="width: 3rem" />
      <Column field="username" header="Email" filterField="username" :sortable="true" :showFilterMatchModes="false">
        <template #filter="{filterModel,filterCallback}">
          <InputText
            type="text"
            v-model="filterModel.value"
            @input="filterCallback()"
            class="p-column-filter"
          />
        </template>
      </Column>
      <Column field="nbPlaces" header="Adresses" bodyClass="text-center">
        <template #body="{data}">
          {{ data.places.length }}
        </template>
      </Column>
      <Column header="Activé" bodyClass="text-center">
        <template #body="{data}">
          <i v-if="data.active" class="pi true-icon pi-check-circle"></i>
          <i v-else class="pi false-icon pi-times-circle"></i>
        </template>
      </Column>
      <Column header="Admin" bodyClass="text-center">
        <template #body="{data}">
          <i v-if="data.roles.includes('ROLE_ADMIN')" class="pi true-icon pi-check-circle"></i>
          <i v-else class="pi false-icon pi-times-circle"></i>
        </template>
      </Column>
      <Column header="">
        <template #body="{data}">
          <Button
            :title="data.active ? 'Désactiver' : 'Activer'"
            :icon="'pi ' + (data.active ? 'pi-lock' : 'pi-lock-open')"
            class="p-button-sm p-button-outlined p-button-rounded p-button-secondary p-mr-1"
            @click="toggleActif($event, data)"
          />
          <Button
            title="Modifier"
            icon="pi pi-user-edit"
            class="p-button-sm p-button-outlined p-button-rounded p-button-secondary p-mr-1"
            @click="openEditUserForm($event, data)"
          />
          <Button
            title="Supprimer"
            icon="pi pi-trash"
            class="p-button-sm p-button-outlined p-button-rounded p-button-danger"
            @click="remove($event, data)"
          />
        </template>
      </Column>
      <template #expansion="slotProps">
        <DataTable :value="slotProps.data.places" responsiveLayout="scroll">
          <Column field="name" header="Nom"></Column>
          <Column header="Linky">
            <template #body="{data}">
              <i v-if="hasLinky(data)" class="pi true-icon pi-check-circle"></i>
              <i v-else class="pi false-icon pi-times-circle"></i>
            </template>
          </Column>
          <Column header="Gazpar">
            <template #body="{data}">
              <i v-if="hasGazpar(data)" class="pi true-icon pi-check-circle"></i>
              <i v-else class="pi false-icon pi-times-circle"></i>
            </template>
          </Column>
          <Column header="Station Météo">
            <template #body="{data}">
              {{ getMeteo(data) }}
            </template>
          </Column>
        </DataTable>
      </template>
    </DataTable>
    <div class="p-d-flex p-jc-center">
      <Button
        icon="pi pi-user-plus"
        label="Ajouter un utilisateur"
        class="p-mt-2 p-button-rounded p-button-secondary"
        @click="toggleUserAddForm()"
      />
    </div>
  </div>
  <ConfirmPopup group="popup"></ConfirmPopup>
  <ConfirmDialog group="dialog"></ConfirmDialog>
  <UserEditForm
    :visible="!!editedUtilisateur"
    :utilisateur="editedUtilisateur"
    v-on:toggleVisible="closeEditUserForm"
  />
  <UserAddForm
    :visible="showUserAddForm"
    v-on:toggleVisible="toggleUserAddForm"
    v-on:refreshUserList="loadUsers"
  />
</template>

<script lang="ts" src="./Users.ts"></script>

<style lang="scss">
.users {
  .true-icon {
    color:#256029
  }
  .false-icon {
    color:#c63737
  }
}
</style>