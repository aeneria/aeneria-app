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
          class="button-place-change p-button-rounded p-button-secondary p-button-icon-only p-ml-4"
        />
        <Menu
          id="overlay_menu_mon_compte"
          ref="menuMonCompte"
          :model="menuMonCompteItems"
          :popup="true"
        />
      </div>

      <p><span class="pi pi-id-card"></span> Email&nbsp;: toto@pourt.com</p>
      <p><span class="pi pi-calendar"></span> Créé le 12/06/2019</p>

      <Divider align="left">
        <div class="p-d-inline-flex p-ai-center">
            <h2>Mes Adresses</h2>
        </div>
      </Divider>
      <div class="p-d-flex p-flex-wrap">
        <template v-for="place in placeList" :key="place.id">
          <Place :place="place"/>
        </template>
      </div>
      <div class="p-d-flex p-jc-center">
        <Button
          icon="pi pi-plus"
          label="Ajouter une adresse"
          class="button-place-change p-button-rounded p-button-secondary"
        />
      </div>
    </div>
  </div>
</template>

<script lang="ts" src="./MonCompte.ts" />
