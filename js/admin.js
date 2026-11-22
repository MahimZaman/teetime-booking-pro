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

function TTBP__addWeekRuleRow() {
  $("#add_weekday_rule").click(function () {
    let row = ``;
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

  $(document).on("click", "#add_weekday_rule", function () {
    const row = `<tr>
                        <td>
                            <input type="checkbox" name="ttbp_select_row[]" />
                        </td>
                        <td>
                            <div class="ttbp_form_group">
                                <input type="number" step="0.01" min="1" name="custom_booking_amount[]" id="custom_booking_amount" class="ttbp_input" required />
                            </div>
                        </td>
                        <td>
                            <div class="ttbp_form_group">
                                <input type="time" name="custom_window_start[]" id="custom_window_start" class="ttbp_input" required />
                            </div>
                        </td>
                        <td>
                            <div class="ttbp_form_group">
                                <input type="time" name="custom_window_end[]" id="custom_window_end" class="ttbp_input" required />
                            </div>
                        </td>
                        <td>
                            <div class="ttbp_form_group">
                                <input type="number" step="0.01" min="1" name="custom_total_amount_9_walking[]" id="custom_total_amount_9_walking" class="ttbp_input" required />
                            </div>
                        </td>
                        <td>
                            <div class="ttbp_form_group">
                                <input type="number" step="0.01" min="1" name="custom_total_amount_9_cart[]" id="custom_total_amount_9_cart" class="ttbp_input" required />
                            </div>
                        </td>
                        <td>
                            <div class="ttbp_form_group">
                                <input type="number" step="0.01" min="1" name="custom_total_amount_18_walking[]" id="custom_total_amount_18_walking" class="ttbp_input" required />
                            </div>
                        </td>
                        <td>
                            <div class="ttbp_form_group">
                                <input type="number" step="0.01" min="1" name="custom_total_amount_18_cart[]" id="custom_total_amount_18_cart" class="ttbp_input" required />
                            </div>
                        </td>
                        <td>
                            <button type="button" class="delete-row ttbp_danger ttbp_button">Delete</button>
                        </td>
                  </tr>`;
    $(".ttbp_weekday_rules tbody").append(row);
  });

  $(document).on("click", ".delete-row", function () {
    $(this).parent().parent().remove();
  });

  $(".ttbp_delete_pricing").click(function () {
    $(this).css({
      opacity: 0.4,
      pointerEvents: "none",
    });
    $.ajax({
      url: ttbpLocal.ajax,
      type: "POST",
      data: {
        action: "ttbp_delete_pricing",
        key: $(this).data("weekday"),
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

  $(document).on("change", "#custom_weekday", function () {
    const weekday = $(this).val();
    $(".ttbp_weekday_rules tbody").html(
      '<tr><td colspan="6" style="text-align: center;">Loading...</td></tr>'
    );
    $.ajax({
      url: ttbpLocal.ajax,
      type: "POST",
      data: {
        action: "ttbp_edit_pricing",
        weekday: weekday,
      },
      success: function (res) {
        $(".ttbp_weekday_rules tbody").html(res);
      },
      error: function (err) {
        console.error(err.responseText);
      },
    });
  });

  $(".ttbp_edit_pricing").click(function () {
    const weekday = $(this).data("weekday");
    $("#custom_weekday").val(weekday).change();
  });

  $(document).on("click", ".ttbp_open_pricing", function () {
    const row = `<tr>
                        <td>
                            <input type="checkbox" name="ttbp_select_row[]" />
                        </td>
                        <td>
                            <div class="ttbp_form_group">
                                <input type="number" step="0.01" min="1" name="custom_booking_amount[]" id="custom_booking_amount" class="ttbp_input" required />
                            </div>
                        </td>
                        <td>
                            <div class="ttbp_form_group">
                                <input type="time" name="custom_window_start[]" id="custom_window_start" class="ttbp_input" required />
                            </div>
                        </td>
                        <td>
                            <div class="ttbp_form_group">
                                <input type="time" name="custom_window_end[]" id="custom_window_end" class="ttbp_input" required />
                            </div>
                        </td>
                        <td>
                            <div class="ttbp_form_group">
                                <input type="number" step="0.01" min="1" name="custom_total_amount_9_walking[]" id="custom_total_amount_9_walking" class="ttbp_input" required />
                            </div>
                        </td>
                        <td>
                            <div class="ttbp_form_group">
                                <input type="number" step="0.01" min="1" name="custom_total_amount_9_cart[]" id="custom_total_amount_9_cart" class="ttbp_input" required />
                            </div>
                        </td>
                        <td>
                            <div class="ttbp_form_group">
                                <input type="number" step="0.01" min="1" name="custom_total_amount_18_walking[]" id="custom_total_amount_18_walking" class="ttbp_input" required />
                            </div>
                        </td>
                        <td>
                            <div class="ttbp_form_group">
                                <input type="number" step="0.01" min="1" name="custom_total_amount_18_cart[]" id="custom_total_amount_18_cart" class="ttbp_input" required />
                            </div>
                        </td>
                        <td>
                            <button type="button" class="delete-row ttbp_danger ttbp_button">Delete</button>
                        </td>
                    </tr>`;
    $("#custom_weekday").val("").change();
    $(".ttbp_weekday_rules tbody").html(row);
  });

  $("#ttbp_clover_env").change(function () {
    if ($(this).val() == "test") {
      $(`label[for="ttbp_clover_merchant_id"]`).text("Sandbox Merchant ID");
      $(`label[for="ttbp_clover_public_key"]`).text("Sandbox Public Key");
      $(`label[for="ttbp_clover_private_key"]`).text("Sandbox Private Key");
    } else {
      $(`label[for="ttbp_clover_merchant_id"]`).text("Merchant ID");
      $(`label[for="ttbp_clover_public_key"]`).text("Public Key");
      $(`label[for="ttbp_clover_private_key"]`).text("Private Key");
    }
  });

  $("#ttbp_pos_mode").change(function () {
    if ($(this).val() == "sandbox") {
      $(`label[for="ttbp_pos_public_key"]`).text("Sandbox Public Key");
      $(`label[for="ttbp_pos_private_key"]`).text("Sandbox Private Key");
    } else {
      $(`label[for="ttbp_pos_public_key"]`).text("Public Key");
      $(`label[for="ttbp_pos_private_key"]`).text("Private Key");
    }
  });

  $(document)
    .on("change", ".ttbp_select_all", function () {
      const table = $(this).data("table");
      if ($(this).prop("checked") == true) {
        $(`.${table}`)
          .find('input[name="ttbp_select_row[]"]')
          .prop("checked", true);
      } else {
        $(`.${table}`)
          .find('input[name="ttbp_select_row[]"]')
          .prop("checked", false);
      }
    })
    .on("click", "#ttbp_apply_filters", function () {
      if ($("#ttbp_bulk_clone").val() == "") {
        alert("Select a day to clone to.");
        return;
      }
      if ($('input[name="ttbp_select_row[]"]:checked').length == 0) {
        alert("Select rows to be cloned");
        return;
      }
      $(this).css({
        pointerEvents: "none",
        opacity: 0.5,
      });
      const formData = $(".ttbp_add_rule").serialize();
      $.ajax({
        url: ttbpLocal.ajax,
        type: "POST",
        data: {
          action: "ttbp_clone_rules",
          formData: formData,
        },
        success: function (res) {
          if (res.success) {
            $("#custom_weekday").val(res.data.day).change();
            $(".ttbp_select_all").prop("checked", false);
            $("#ttbp_bulk_clone").val("");
            $("#ttbp_apply_filters").css({
              pointerEvents: "all",
              opacity: 1,
            });
          }
        },
        error: function (err) {
          console.error(err.responseText);
        },
      });
    })
    .on("click", "#ttbp_reset_settings", function () {
      $("#ttbp_reset_settings").find(".ttbp-btn-loader").css({
        display: "inline-block",
      });
      $.ajax({
        url: ttbpLocal.ajax,
        type: "POST",
        data: {
          action: "ttbp_reset_settings",
        },
        success: function (res) {
          location.reload();
        },
        error: function (err) {
          alert("Something went wrong.");
        },
      });
    });
});


