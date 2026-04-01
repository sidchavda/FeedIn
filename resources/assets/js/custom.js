
(function () {
  "use strict";
  /* page loader */

  function hideLoader() {
    const loader = document.getElementById("loader");
    loader.classList.add("d-none")
  }

  window.addEventListener("load", hideLoader);
  /* page loader */

  /* tooltip */
  const tooltipTriggerList = document.querySelectorAll(
    '[data-bs-toggle="tooltip"]'
  );
  const tooltipList = [...tooltipTriggerList].map(
    (tooltipTriggerEl) => new bootstrap.Tooltip(tooltipTriggerEl)
  );

  /* popover  */
  const popoverTriggerList = document.querySelectorAll(
    '[data-bs-toggle="popover"]'
  );
  const popoverList = [...popoverTriggerList].map(
    (popoverTriggerEl) => new bootstrap.Popover(popoverTriggerEl)
  );

  /* header theme toggle */
  function toggleTheme() {
    let html = document.querySelector("html");
    if (html.getAttribute("data-theme-mode") === "dark") {
      html.setAttribute("data-theme-mode", "light");
      html.setAttribute("data-header-styles", "light");
      html.setAttribute("data-menu-styles", "light");
      if (!localStorage.getItem("primaryRGB")) {
        html.setAttribute("style", "");
      }
      html.removeAttribute("data-bg-theme");
      if (document.querySelector("#switcher-canvas")) {
        document.querySelector("#switcher-light-theme").checked = true;
        document.querySelector("#switcher-menu-light").checked = true;
      }
      document.querySelector("html").style.removeProperty("--body-bg-rgb", localStorage.bodyBgRGB);
      html.style.removeProperty("--body-bg-rgb2");
      html.style.removeProperty("--light-rgb");
      html.style.removeProperty("--form-control-bg");
      html.style.removeProperty("--input-border");
      if (document.querySelector("#switcher-canvas")) {
        document.querySelector("#switcher-header-light").checked = true;
        document.querySelector("#switcher-menu-dark").checked = true;
        document.querySelector("#switcher-light-theme").checked = true;
        document.querySelector("#switcher-background4").checked = false;
        document.querySelector("#switcher-background3").checked = false;
        document.querySelector("#switcher-background2").checked = false;
        document.querySelector("#switcher-background1").checked = false;
        document.querySelector("#switcher-background").checked = false;
      }
      localStorage.removeItem("syntodarktheme");
      localStorage.removeItem("syntoMenu");
      localStorage.removeItem("syntoHeader");
      localStorage.removeItem("bodylightRGB");
      localStorage.removeItem("bodyBgRGB");
      if (localStorage.getItem("syntolayout") != "horizontal") {
        html.setAttribute("data-menu-styles", "dark");
      }
      html.setAttribute("data-header-styles", "light");
    } else {
      html.setAttribute("data-theme-mode", "dark");
      html.setAttribute("data-header-styles", "dark");
      if (!localStorage.getItem("primaryRGB")) {
        html.setAttribute("style", "");
      }
      html.setAttribute("data-menu-styles", "dark");
      if (document.querySelector("#switcher-canvas")) {
        document.querySelector("#switcher-dark-theme").checked = true;
        document.querySelector("#switcher-menu-dark").checked = true;
        document.querySelector("#switcher-header-dark").checked = true;
        document.querySelector("#switcher-menu-dark").checked = true;
        document.querySelector("#switcher-header-dark").checked = true;
        document.querySelector("#switcher-dark-theme").checked = true;
        document.querySelector("#switcher-background4").checked = false;
        document.querySelector("#switcher-background3").checked = false;
        document.querySelector("#switcher-background2").checked = false;
        document.querySelector("#switcher-background1").checked = false;
        document.querySelector("#switcher-background").checked = false;
      }
      localStorage.setItem("syntodarktheme", "true");
      localStorage.setItem("syntoMenu", "dark");
      localStorage.setItem("syntoHeader", "dark");
      localStorage.removeItem("bodylightRGB");
      localStorage.removeItem("bodyBgRGB");
    }
  }
  let layoutSetting = document.querySelector(".layout-setting");
  layoutSetting.addEventListener("click", toggleTheme);
  /* header theme toggle */

  /* Choices JS */
  document.addEventListener("DOMContentLoaded", function () {
    var genericExamples = document.querySelectorAll("[data-trigger]");
    for (let i = 0; i < genericExamples.length; ++i) {
      var element = genericExamples[i];
      new Choices(element, {
        allowHTML: true,
        placeholderValue: "This is a placeholder set in the config",
        searchPlaceholderValue: "Search",
      });
    }
  });
  /* Choices JS */


  /* footer year */
  document.getElementById("year").innerHTML = new Date().getFullYear();
  /* footer year */

  /* node waves */
  Waves.attach(".btn-wave", ["waves-light"]);
  Waves.init();
  /* node waves */

  /* card with close button */
  let DIV_CARD = ".card";
  let cardRemoveBtn = document.querySelectorAll(
    '[data-bs-toggle="card-remove"]'
  );
  cardRemoveBtn.forEach((ele) => {
    ele.addEventListener("click", function (e) {
      e.preventDefault();
      let $this = this;
      let card = $this.closest(DIV_CARD);
      card.remove();
      return false;
    });
  });
  /* card with close button */

  /*header-remove */
  const headerbtn = document.querySelectorAll(".header-remove-btn");

  headerbtn.forEach((button, index) => {
    button.addEventListener("click", (e) => {
      e.preventDefault();
      e.stopPropagation();
      button.parentNode.remove();
      if (document.getElementById("allCartsContainer")) {
        document.getElementById("cart-data").innerText = `${
          document.getElementById("allCartsContainer").children.length
        } Items`;
        document.getElementById("cart-data2").innerText = `${
          document.getElementById("allCartsContainer").children.length
        }`;
      }
      if (document.getElementById("allNotifyContainer")) {
        document.getElementById("notify-data").innerText = `${
          document.getElementById("allNotifyContainer").children.length
        }`;
      }

      if (document.getElementById("allCartsContainer")) {
        if (document.getElementById("allCartsContainer").children.length == 0) {
          document.getElementById("allCartsContainer").parentNode.innerHTML = `
                        <div class="p-6 pb-8 text-center">
                          <div>
                            <i class="ri ri-shopping-cart-2-line leading-none text-4xl avatar avatar-lg bg-primary/20 text-primary rounded-full p-3 align-middle flex justify-center mx-auto"></i>
                            <div class="mt-5">
                              <p class="text-base font-semibold text-gray-800 dark:text-white mb-1">
                                No Items In Cart
                              </p>
                              <p class="text-xs text-gray-500 dark:text-white/70">
                              When you have Items added here , they will appear here.
                              </p>
                              <a href="javascript:void(0);" class="m-0 ti-btn ti-btn-primary py-1 mt-5"><i class="ti ti-arrow-right text-base leading-none"></i>Continue Shopping</a>
                            </div>
                          </div>
                        </div>`;
        }
      }
      if (document.getElementById("allNotifyContainer")) {
        if (
          document.getElementById("allNotifyContainer").children.length == 0
        ) {
          document.getElementById("allNotifyContainer").parentNode.innerHTML = `
          <div class="p-6 pb-8 text-center">
          <div>
            <i class="ri ri-notification-off-line leading-none text-4xl avatar avatar-lg bg-secondary/20 text-secondary rounded-full p-3 align-middle flex justify-center mx-auto"></i>
            <div class="mt-5">
              <p class="text-base font-semibold text-gray-800 dark:text-white mb-1">
                No Notifications Found
              </p>
              <p class="text-xs text-gray-500 dark:text-white/70">
              When you have notifications added here , they will appear here.
              </p>
            </div>
          </div>
        </div>`;
        }
      }
    });
  });
  /*header-remove */

  /* card with fullscreen */
  let cardFullscreenBtn = document.querySelectorAll(
    '[data-bs-toggle="card-fullscreen"]'
  );
  cardFullscreenBtn.forEach((ele) => {
    ele.addEventListener("click", function (e) {
      let $this = this;
      let card = $this.closest(DIV_CARD);
      card.classList.toggle("card-fullscreen");
      card.classList.remove("card-collapsed");
      e.preventDefault();
      return false;
    });
  });
  /* card with fullscreen */

  /* count-up */
  var i = 1;
  setInterval(() => {
    document.querySelectorAll(".count-up").forEach((ele) => {
      if (ele.getAttribute("data-count") >= i) {
        i = i + 1;
        ele.innerText = i;
      }
    });
  }, 10);
  /* count-up */

  /* back to top */
  const scrollToTop = document.querySelector(".scrollToTop");
  const $rootElement = document.documentElement;
  const $body = document.body;
  window.onscroll = () => {
    const scrollTop = window.scrollY || window.pageYOffset;
    const clientHt = $rootElement.scrollHeight - $rootElement.clientHeight;
    if (window.scrollY > 100) {
      scrollToTop.style.display = "flex";
    } else {
      scrollToTop.style.display = "none";
    }
  };
  scrollToTop.onclick = () => {
    window.scrollTo(0, 0);
  };
  /* back to top */

  /* header dropdowns scroll */
  var myHeaderShortcut = document.getElementById("header-shortcut-scroll");
  new SimpleBar(myHeaderShortcut, { autoHide: true });

//   var myHeadernotification = document.getElementById(
//     "header-notification-scroll"
//   );
//   new SimpleBar(myHeadernotification, { autoHide: true });
  /* header dropdowns scroll */
})();

/* full screen */
var elem = document.documentElement;
window.openFullscreen = function() {
  let open = document.querySelector(".full-screen-open");
  let close = document.querySelector(".full-screen-close");

  if (
    !document.fullscreenElement &&
    !document.webkitFullscreenElement &&
    !document.msFullscreenElement
  ) {
    if (elem.requestFullscreen) {
      elem.requestFullscreen();
    } else if (elem.webkitRequestFullscreen) {
      /* Safari */
      elem.webkitRequestFullscreen();
    } else if (elem.msRequestFullscreen) {
      /* IE11 */
      elem.msRequestFullscreen();
    }
    close.classList.add("d-block");
    close.classList.remove("d-none");
    open.classList.add("d-none");
  } else {
    if (document.exitFullscreen) {
      document.exitFullscreen();
    } else if (document.webkitExitFullscreen) {
      /* Safari */
      document.webkitExitFullscreen();
      console.log("working");
    } else if (document.msExitFullscreen) {
      /* IE11 */
      document.msExitFullscreen();
    }
    close.classList.remove("d-block");
    open.classList.remove("d-none");
    close.classList.add("d-none");
    open.classList.add("d-block");
  }
}
/* full screen */

/* toggle switches */
let customSwitch = document.querySelectorAll(".toggle");
customSwitch.forEach((e) =>
  e.addEventListener("click", () => {
    e.classList.toggle("on");
  })
);
/* toggle switches */

/* header dropdown close button */


/* for notifications dropdown */
// const headerbtn1 = document.querySelectorAll(".dropdown-item-close1");
// headerbtn1.forEach((button) => {
//   button.addEventListener("click", (e) => {
//     e.preventDefault();
//     e.stopPropagation();
//     button.parentNode.parentNode.parentNode.parentNode.remove();
//     document.getElementById("notifiation-data").innerText = `${
//       document.querySelectorAll(".dropdown-item-close1").length
//     } Unread`;
//     document.getElementById("notification-icon-badge").innerText = `${
//       document.querySelectorAll(".dropdown-item-close1").length
//     }`;
//     if (document.querySelectorAll(".dropdown-item-close1").length == 0) {
//       let elementHide1 = document.querySelector(".empty-header-item1");
//       let elementShow1 = document.querySelector(".empty-item1");
//       elementHide1.classList.add("d-none");
//       elementShow1.classList.remove("d-none");
//     }
//   });
// });
/* for notifications dropdown */

(function () {
	'use strict';
    const toastWrapper = document.getElementById('toast-wrapper');
    const toast = new bootstrap.Toast(toastWrapper);
	toast.show();
});
