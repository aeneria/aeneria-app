<template>
   <div v-if="place" class="parameter p-grid p-ai-start p-mt-2">
    <div class="p-col-12">
      <h1>C'est tout bon !</h1>
      <p>Votre compteur Gazpar a été correctement configuré. Des données devraient commencer à apparaitre d'ici ~15 minutes.</p>

      <div v-if="!feedElectricite">
        <p class="p-mb-0">Si vous le souhaitez, vous pouvez en plus ajouter un compteur Linky d'Enedis</p>
        <small>(Il sera toujours possible de le faire plus tard)</small>
      </div>

      <div class="p-mt-4">
        <Button
          v-if="!feedElectricite"
          @click="toggleLinkyForm()"
          icon="pi pi-plus-circle"
          label="Associer un compteur Linky d'Enedis"
          title=""
          aria-haspopup="true"
          aria-controls="overlay_menu_edition"
          class="p-button p-button-rounded p-button-secondary p-ml-1"
        />
        <router-link
          :to="{name: 'home'}"
          class="p-button p-button-rounded p-button-secondary p-ml-2"
        >
        Aller à l'accueil
        </router-link>
      </div>
    </div>
    <AddLinkyForm
      :visible="displayAddLinkyForm"
      v-on:toggleVisible="toggleLinkyForm()"
      :place="place"
    />
  </div>
  <Spinner v-else />
</template>

<script lang="ts" src="./CallbackGrdf.ts"/>

<style lang="scss"></style>