$(document).ready(function () {
  $("#purchase-modal").hide();

  $("#close-purchase__modal").click(function () {
    $("#purchase-modal").hide();
  });
  $("#open-purchase__modal").click(function () {
    $("#purchase-modal").show();
  });
});
