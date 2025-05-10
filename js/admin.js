$ = jQuery;

function TTBP__addRule() {
  const button = document.getElementById("ttbp_add_rule");

  if (!button) return;

  button.addEventListener("click", () => {});
}

function TTBP__openModal() {
  const buttons = document.querySelectorAll(".ttbp_open_modal");
  if (buttons.length < 1) {
    return;
  }

  Array.from(buttons).forEach((button, i) => {
    button.addEventListener("click", () => {
      const modalId = button.dataset.modal;
      const modal = document.getElementById(modalId);

      if (!modal) return;
      const closeModal = modal.querySelector(".ttbp_close_modal");
      modal.classList.add("active");
      TTBP__closeModal(closeModal, modal);
    });
  });
}

function TTBP__closeModal(closeBtn, modal) {
  if (!closeBtn) return;

  closeBtn.addEventListener("click", () => {
    modal.classList.remove("active");
  });
}

$(document).ready(function () {
  TTBP__openModal();

  $(".ttbp_delete").click(function () {
    $(this).css({
      opacity: 0.4,
      pointerEvents: "none",
    });
    $.ajax({
      url: ttbpLocal.ajax,
      type: "POST",
      data: {
        action: "ttbp_delete_rule",
        key: $(this).data("date"),
      },
      success: function (res) {
        location.reload();
      },
      error: function (err) {
        console.error(err.responseText);
      },
    });
  });

  $(".ttbp_edit").click(function () {
    const date = $(this).data("date");
    const start_morning = $(this).data("start_morning");
    const start_midday = $(this).data("start_midday");
    const start_evening = $(this).data("start_evening");
    const end = $(this).data("end");
    const duration = $(this).data("duration");

    $("#custom_date").val(date).change();
    $("#custom_start_morning").val(start_morning).change();
    $("#custom_start_midday").val(start_midday).change();
    $("#custom_start_evening").val(start_evening).change();
    $("#custom_end").val(end).change();
    $("#custom_duration").val(duration).change();
  });

  $(".ttbp_edit_pricing").click(function () {
    const weekday = $(this).data("weekday");
    const booking_amount = $(this).data("booking_amount");
    const window_start = $(this).data("window_start");
    const window_end = $(this).data("window_end");
    const total_amount_9 = $(this).data("total_amount_9");
    const total_amount_18 = $(this).data("total_amount_18");

    $("#custom_weekday").val(weekday).change();
    $("#custom_booking_amount").val(booking_amount).change();
    $("#custom_window_start").val(window_start).change();
    $("#custom_window_end").val(window_end).change();
    $("#custom_total_amount_9").val(total_amount_9).change();
    $("#custom_total_amount_18").val(total_amount_18).change();
  });

  $('#ttbp_clover_env').change(function(){

    if ($(this).val() == 'test'){
      $(`label[for="ttbp_clover_merchant_id"]`).text('Sandbox Merchant ID')
      $(`label[for="ttbp_clover_public_key"]`).text('Sandbox Public Key')
      $(`label[for="ttbp_clover_private_key"]`).text('Sandbox Private Key')
    } else {
      $(`label[for="ttbp_clover_merchant_id"]`).text('Merchant ID')
      $(`label[for="ttbp_clover_public_key"]`).text('Public Key')
      $(`label[for="ttbp_clover_private_key"]`).text('Private Key')
    }
  });

});
