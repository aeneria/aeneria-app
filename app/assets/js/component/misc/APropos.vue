
<template>
  <div class="parameter p-grid p-ai-start">
    <div class="p-col-12">
      <h1>À Propos</h1>
      <div class="p-grid">
        <div class="p-col-12 p-md-6 p-lg-4">
          <Card class="card-about">
            <template #title>
              <div class="p-d-flex">
                <i class="fas fa-info-circle p-mr-4"></i>
                <p class="p-my-0">Infos générales</p>
              </div>
            </template>
            <template #content>
              <ul class="p-d-flex p-flex-wrap">
                <li class="p-m-1">
                  <a
                    target="_blank"
                    href="https://aeneria.com/"
                    class="p-button p-button-rounded p-button-secondary ml-1"
                  >
                    <i class="fa fa-globe-europe p-mr-2"></i>Site web
                  </a>
                </li>
                <li class="p-m-1">
                  <a
                    target="_blank"
                    href="https://gitlab.com/aeneria/aeneria-app"
                    class="p-button p-button-rounded p-button-secondary ml-1"
                  >
                    <i class="fab fa-gitlab p-mr-2"></i>Dépôt Gitlab
                  </a>
                </li>
                <li class="p-m-1">
                  <a
                    target="_blank"
                    href="https://twitter.com/aeneria_app"
                    class="p-button p-button-rounded p-button-secondary ml-1"
                  >
                    <i class="fab fa-twitter p-mr-2"></i>@aeneria-app
                  </a>
                </li>
                <li class="p-m-1">
                  <a target="_blank" href="https://docs.aeneria.com/"
                  class="p-button p-button-rounded p-button-secondary ml-1"
                >
                    <i class="fa fa-book p-mr-2"></i>Documentation
                  </a>
                </li>
                <li class="p-m-1">
                  <a
                    target="_blank"
                    href="https://gitlab.com/aeneria/aeneria-app/-/tags/{{ aeneria_version() }}"
                    class="p-button p-button-rounded p-button-secondary ml-1"
                  >
                    <i class="fa fa-code-branch p-mr-2"></i>Version {{ configuration.version }}
                  </a>
                </li>
                <li class="p-m-1">
                  <a
                    target="_blank"
                    href="http://www.gnu.org/licenses/agpl-3.0.en.html"
                    class="p-button p-button-rounded p-button-secondary ml-1"
                  >
                    <i class="fa fa-balance-scale p-mr-2"></i>License AGPLv3
                  </a>
                </li>
              </ul>
            </template>
          </Card>
        </div>
        <div class="p-col-12 p-md-6 p-lg-4">
          <Card class="card-about">
            <template #title>
              <div class="p-d-flex">
                <i class="fas fa-hands-clapping p-mr-4"></i>
                <p class="p-my-0">Envie d'encourager æneria ?</p>
              </div>
            </template>
            <template #content>
              <p>Faîtes un don&nbsp;:</p>
              <ul class="p-d-flex p-flex-wrap">
              <li class="p-m-1">
                <a
                  target="_blank"
                  href="https://paypal.me/aeneria"
                  class="p-button p-button-rounded p-button-info"
                >
                  <i class="p-mr-2 fab fa-paypal"></i>via PayPal
                </a>
              </li>
              <li class="p-m-1">
                <a
                  target="_blank"
                  href="https://www.buymeacoffee.com/aeneria"
                  class="p-button p-button-rounded p-button-warning"
                >
                  <i class="p-mr-2 fas fa-beer"></i>via BuyMeACoffee
                </a>
                </li>
              </ul>
            </template>
          </Card>
        </div>
        <div class="p-col-12 p-md-6 p-lg-4">
          <Card class="card-about">
            <template #title>
              <div class="p-d-flex">
                <i class="fas fa-bug p-mr-4"></i>
                <p class="p-my-0">Un bug ou une idée d'amélioration ?</p>
              </div>
            </template>
            <template #content>
              <p>Faîtes-le savoir :</p>
              <ul class="p-d-flex p-flex-wrap">
                <li class="p-m-1">
                  <a
                    target="_blank"
                    href="https://gitlab.com/aeneria/aeneria-app/-/issues"
                    class="p-button p-button-rounded p-button-secondary"
                  >
                    <i class="fab fa-gitlab p-mr-2"></i>Avec une issue sur Gitlab
                  </a>
                </li>
                <li class="p-m-1">
                  <a
                    target="_blank"
                    href="https://twitter.com/aeneria_app"
                    class="p-button p-button-rounded p-button-secondary"
                  >
                    <i class="fab fa-twitter p-mr-2"></i>Via Twitter
                  </a>
                </li>
              </ul>
            </template>
          </Card>
        </div>
        <div class="p-col-12 p-md-6 p-lg-4">
          <Card class="card-about">
            <template #title>
              <div class="p-d-flex">
                <i class="fas fa-code p-mr-4"></i>
                <p class="p-my-0">Informations techniques & crédits</p>
              </div>
            </template>
            <template #content>
              <p>Développé avec <i class="fas fa-heart"></i> par Simon Mellerin, avec l'aide de&nbsp;:</p>
              <ul class="">
                <li class="p-mb-3"><i class="fas fa-server p-mr-2"></i>Côté back : PHP, PostgreSQL, Symfony et son outillage</li>
                <li class="p-mb-3"><i class="fas fa-desktop p-mr-2"></i>Côté front : Vue.js, PrimeVUE, Webpack, D3.js, Popper.js, Fontawesome</li>
                <li class="p-mb-3"><i class="fas fa-image p-mr-2"></i>
                  Logo issu de <a target="_blank" href="https://thenounproject.com/" >the Noun Project</a>,
                  dessinée par <a target="_blank" href="https://thenounproject.com/iconisland/">Icon Island</a>
                </li>
            </ul>
            </template>
          </Card>
        </div>
      </div>
    </div>
  </div>

</template>

<script lang="ts" src="./APropos.ts"/>

<style lang="scss">
.card-about {
  height: 100%;
  .p-card-body {
    ul {
      list-style: none;
      padding-left: 0.5rem;
    }
  }
}
</style>
