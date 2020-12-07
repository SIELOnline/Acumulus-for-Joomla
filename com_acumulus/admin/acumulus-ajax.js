"use strict";
(function($) {
  function addAcumulusAjaxHandling() {
    const buttonSelector = "button, input[type=button], input[type=submit]";
    $(buttonSelector, ".com_virtuemart .acumulus-area").addClass("button button-primary"); // jQuery
    $(buttonSelector, ".com_hikashop .acumulus-area").addClass("btn btn-primary"); // jQuery
    $(".acumulus-ajax").click(function() { // jQuery
      // Area is the element that is going to be replaced and serves as the
      // parent in which we will search for form elements.
      const clickedElt = this;
      const area = $(clickedElt).parents(".acumulus-area").get(0); // jQuery
      $(buttonSelector, area).prop("disabled", true); // jQuery
      clickedElt.value = area.getAttribute("data-acumulus-wait");

      // The data we are going to send consists of:
      // - ajax: 1, to indicate that this is an ajax call.
      // - clicked: the name of the element that was clicked, the name should
      //   make clear what action is requested on the server and, optionally, on
      //   what object.
      // - area: the id of the area from which this request originates, the
      //   "acumulus form part" (though not necessarily a form node). This can
      //   be used for further routing the request to the correct Acumulus form
      //   as the url might not be specific enough in all webshops.
      // - {sessionToken}: 1, this is an anti-CSRF check.
      // - {values}: values of all form elements in area: input, select and
      //   textarea, except buttons (inputs with type="button").
      //noinspection JSUnresolvedVariable
      const data = {
        [area.getAttribute("data-acumulus-token")]: 1,
        ajax: 1,
        format: "json",
        clicked: clickedElt.name,
        area: area.id,
      };

      // area is not necessarily a form node, in which case FormData will not
      // work. So we clone area into a temporary form node.
      const form = document.createElement("form");
      form.appendChild(area.cloneNode(true));
      const formData = new FormData(form);
      for(let entry of formData.entries()) {
        data[entry[0]] = entry[1];
      }

      const url = area.getAttribute("data-acumulus-url");
      $.post(url, data, function(response) { // jQuery
        area.insertAdjacentHTML("beforebegin", response.data);
        area.parentNode.removeChild(area);
        addAcumulusAjaxHandling();
        $(document.body).trigger("post-load"); // jQuery
      });
    });
  }

  $(document).ready(function() { // jQuery
    addAcumulusAjaxHandling();
    $(".acumulus-auto-click").click(); // jQuery
  });
}(jQuery));
