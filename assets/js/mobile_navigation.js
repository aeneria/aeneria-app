// Check touch mode
var clickedEvent = "click";
window.addEventListener('touchstart', function detectTouch() {
  clickedEvent = "touchstart";
  window.removeEventListener('touchstart', detectTouch, false);
}, false);

var menuMobile = document.querySelector("#menu-mobile");
var menuMobileBtn = document.querySelector("#menu-mobile-btn");

if (menuMobile && menuMobileBtn) {
  menuMobileBtn.addEventListener(clickedEvent, function(evt) {
    this.classList.toggle("clicked");
    this.toggleAttribute("aria-expanded")

    // Créé l'effet pour le menu slide (compatible partout)
    if(menuMobile.getAttribute("class") != "show") {
      menuMobile.setAttribute("class", "show");
    } else {
      menuMobile.setAttribute("class", "hide");
    }
  }, false);

  if(screen.width <= 1024) {
    var startX = 0; // Position de départ
    var distance = 100; // 100 px de swipe pour afficher le menu

    // Au premier point de contact
    window.addEventListener("touchstart", function(evt) {
      // Récupère les "touches" effectuées
      var touches = evt.changedTouches[0];
      startX = touches.pageX;
      between = 0;
    }, false);

    // Quand les points de contact sont en mouvement
    window.addEventListener("touchmove", function(evt) {
      // Limite les effets de bord avec le tactile...
      evt.preventDefault();
      evt.stopPropagation();
    }, false);

    // Quand le contact s'arrête
    window.addEventListener("touchend", function(evt) {
      var touches = evt.changedTouches[0];
      var between = touches.pageX - startX;

      // Détection de la direction
      if(between > 0) {
        var orientation = "ltr";
      } else {
        var orientation = "rtl";
      }

      if(Math.abs(between) >= distance && orientation == "ltr" && menuMobile.getAttribute("class") != "show") {
        menuMobileBtn.setAttribute("class", "clicked");
        menuMobile.setAttribute("class", "show");
      }
      if(Math.abs(between) >= distance && orientation == "rtl" && menuMobile.getAttribute("class") != "hide") {
        menuMobileBtn.removeAttribute("class");
        menuMobile.setAttribute("class", "hide");
      }
    }, false);
  }
}

var selectionMobile = document.querySelector("#selection-mobile");
if (selectionMobile) {

  var selectionMobileOpen = document.querySelector("#selection-mobile-open");
  if (selectionMobileOpen) {
    selectionMobileOpen.addEventListener(clickedEvent, function(evt) {
      this.classList.toggle("clicked");
      this.toggleAttribute("aria-expanded")

      // Créé l'effet pour le menu slide (compatible partout)
      if(selectionMobile.getAttribute("class") != "show") {
        selectionMobile.setAttribute("class", "show");
      } else {
        selectionMobile.setAttribute("class", "hide");
      }
    }, false);
  }

  var selectionMobileClose = document.querySelector("#selection-mobile-close");
    if (selectionMobileClose) {
      selectionMobileClose.addEventListener(clickedEvent, function(evt) {
      selectionMobileOpen.classList.toggle("clicked");
      selectionMobileOpen.toggleAttribute("aria-expanded")

      if(selectionMobile.getAttribute("class") != "show") {
        selectionMobile.setAttribute("class", "show");
      } else {
        selectionMobile.setAttribute("class", "hide");
      }
    }, false);
  }

  document.addEventListener('selection', function() {
    if(selectionMobile.getAttribute("class") == "show") {
      selectionMobileOpen.classList.toggle("clicked");
      selectionMobileOpen.toggleAttribute("aria-expanded")
      selectionMobile.setAttribute("class", "hide");
    }
  });
}
