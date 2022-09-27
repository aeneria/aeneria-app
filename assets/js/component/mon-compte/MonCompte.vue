<template>
  <div class="parameter p-d-grid p-ai-start p-mt-2">
    <div class="p-col-12">
      <div class="p-d-flex p-ai-center">
        <h1>Mon Compte</h1>
        <Button
          icon="pi pi-user-edit"
          title="Modifier mes informations"
          @click="toggleMenuMonCompte"
          aria-haspopup="true"
          aria-controls="overlay_menu_mon_compte"
          class="p-button-rounded p-button-secondary p-button-icon-only p-ml-4"
        />
        <Menu
          id="overlay_menu_mon_compte"
          ref="menuMonCompte"
          :model="menuMonCompteItems"
          :popup="true"
        />
      </div>

      <p><span class="pi pi-id-card p-pr-2"></span>{{ utilisateur?.username }}</p>

      <EditEmailForm :visible="displayEditEmailForm" v-on:toggleVisible="toggleEditEmailForm"/>
      <EditPasswordForm :visible="displayEditPasswordForm" v-on:toggleVisible="toggleEditPasswordForm"/>
      <DeleteAccountForm :visible="displayDeleteAccountForm" v-on:toggleVisible="toggleDeleteAccountForm"/>

      <Divider align="left" class="bg-color">
        <div class="p-d-inline-flex p-ai-center">
            <i class="pi pi-directions p-mr-2"></i>
            <h2>Mes Adresses</h2>
        </div>
      </Divider>
      <div class="p-d-flex p-flex-wrap">
        <template v-for="place in utilisateur?.places" :key="place.id">
          <Place :place="place"/>
        </template>
      </div>
      <div class="p-d-flex p-jc-center">
        <Button
          icon="pi pi-plus-circle"
          label="Ajouter une adresse"
          class="p-button-rounded p-button-secondary"
          @click="goToNewPLace"
        />
      </div>
    </div>
  </div>
</template>

<script lang="ts" src="./MonCompte.ts" />
