
<template>
  <div class="parameter p-grid p-ai-start">
    <div class="p-col-12">
      <div class="p-d-flex p-jc-center">
        <Card class="card-404 p-mt-2">
          <template #content>
            <div class="p-d-flex p-flex-column p-ai-center">
              <h1>Oups'</h1>
              <i :class="'fas ' + icon"></i>
              <p>Désolé, la page que vous essayez de visiter n'existe pas !</p>
              <router-link
                :to="{name: 'home'}"
                class="p-button p-button-rounded p-button-secondary p-ml-2"
              >
              Aller à l'accueil
              </router-link>
            </div>
          </template>
        </Card>
      </div>
    </div>
  </div>

</template>

<script lang="ts" src="./NotFound.ts"/>

<style lang="scss">
  .card-404 {
    .fas {
      font-size: 6em;
      margin-bottom: 25px;
    }
  }
</style>
