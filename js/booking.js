$ = jQuery;
let currentFetchSlots = null;

function updateSlots() {
  let selectedDate = $("#ttbp_date").val();
  const slots = $('input:radio[name="ttbp_players"]:checked').val();
  const holes = $('input:radio[name="ttbp_holes"]:checked').val();
  const tod = $('input:radio[name="ttbp_tod"]:checked').val();
  const ride = $('input:radio[name="ttbp_ride"]:checked').val();
  const golf_course = $("#ttbp_golf_course").val();
  $(".ttbp-form").addClass("ttbp-loading");
  $(".ttbp_slots").html("<p>Loading...</p>");

  if (currentFetchSlots) {
    currentFetchSlots.abort();
  }

  currentFetchSlots = $.ajax({
    url: ttbp_ajax.ajax_url,
    type: "POST",
    data: {
      action: "ttbp_fetch_slots",
      date: selectedDate,
      slots: slots,
      holes: holes,
      tod: tod,
      golf_course: golf_course,
      ride: ride,
      nonce: ttbp_ajax.nonce,
    },
    success: function (response) {},
    error: function (err) {
      err.responseText ? console.error(err.responseText) : "";
    },
    complete: function (jqXHR, textStatus) {
      currentFetchSlots = null;
      if (textStatus == "success") {
        $(".ttbp_slots").html(
          jqXHR.responseText ? jqXHR.responseText : "No slots available"
        );
        $(".ttbp-form").removeClass("ttbp-loading");
        $(`#ttbp_golf_course`).on("change", function () {
          $(`#ttbp_date`).change();
        });

        $(`input:radio[name="ttbp_players"]`).on("change", function () {
          $(`#ttbp_date`).change();
        });

        $(`input:radio[name="ttbp_holes"]`).on("change", function () {
          $(`#ttbp_date`).change();
        });

        $(`input:radio[name="ttbp_ride"]`).on("change", function () {
          $(`#ttbp_date`).change();
        });

        $(`input:radio[name="ttbp_tod"]`).on("change", function () {
          $(`#ttbp_date`).change();
        });

        $(`input:radio[name="ttbp_time"]`).on("change", function () {
          TTBP__moveToCheckout();
        });
      } else if (textStatus == "error") {
        $(".ttbp_slots").html(
          "<p>Error loading times, Try reloading the page</p>"
        );
      }
    },
  });
}

function TTBP__moveToCheckout() {
  $(".ttbp_booking_step").hide();
  $("#ttbp_step_2").show();
  TTBP__updateBookingReview();
}

function TTBP__updateBookingReview() {
  let bookingValue = $(`input[name="ttbp_time"]:checked`).data(
    "booking_amount"
  );
  const paymentMethod = $(".ttbp_method_select:checked").val();
  if (paymentMethod == "offline") {
    bookingValue = 0;
  }
  const time = $(`input[name="ttbp_time"]:checked`).val();
  const holes = $(`input[name="ttbp_holes"]:checked`).val();
  const ride = $(`input[name="ttbp_ride"]:checked`).val();
  const players = $(`input[name="ttbp_players"]:checked`).val();
  const course = $(`#ttbp_golf_course option:selected`).text();
  const advPay = $("#ttbp_adv_pay:checked").val();
  const taxRate = $("#ttbp_tax_rate").val();
  let taxAmount = 0;

  let totalValue = $(`input[name="ttbp_time"]:checked`).data(
    `total_pay_${holes}`
  );
  let taxableAmount =
    $("#ttbp_taxable_amount").val() > 0
      ? $("#ttbp_taxable_amount").val()
      : totalValue;
  if (ride === "cart") {
    taxAmount =
      parseFloat(taxableAmount) *
      (parseFloat(taxRate) / 100) *
      parseInt(players);
  }
  bookingAmount = parseFloat(bookingValue) * parseInt(players);
  totalAmount = parseFloat(totalValue) * parseInt(players);
  let duePay =
    parseFloat(totalAmount) - parseFloat(bookingAmount) > 0
      ? parseFloat(totalAmount) - parseFloat(bookingAmount) + taxAmount
      : 0;
  if (advPay && paymentMethod !== "offline") {
    duePay = 0;
    bookingAmount = totalAmount - totalAmount * (parseFloat(advPay) / 100);
  }
  const date = $("#ttbp_date").val();

  $("#ttbp_booking_review").html(`<div class="ttbp_loader_placeholder">
                <span class="ttbp_loader_bar" style="width: 100%"></span>
                <span class="ttbp_loader_bar" style="width: 80%"></span>
                <span class="ttbp_loader_bar" style="width: 60%"></span>
            </div>`);

  $("#ttbp_total_amount").val(totalAmount);
  $("#ttbp_booking_amount").val(bookingAmount);

  $.ajax({
    url: ttbp_ajax.ajax_url,
    type: "POST",
    data: {
      action: "ttbp_checkout_review",
      booking_amount: bookingAmount,
      holes: holes,
      total_amount: totalAmount,
      due_pay: duePay,
      players: players,
      course: course,
      date: date,
      time: time,
      ride: ride,
      advPay: advPay,
      taxRate: taxRate,
      taxAmount: taxAmount,
      method: $(".ttbp_method_select:checked").val(),
    },
    success: function (res) {
      $("#ttbp_booking_review").html(res);
    },
    error: function (err) {
      $("#ttbp_booking_review").html(err.responseText);
    },
  });
}

function TTBP__insertBooking(formData) {
  $.ajax({
    url: ttbp_ajax.ajax_url,
    type: "POST",
    data: {
      action: "ttbp_submit_booking",
      nonce: ttbp_ajax.nonce,
      form: formData,
    },
    success: function (response) {
      if (response.success) {
        // Get current URL without query parameters
        const baseUrl = window.location.origin + window.location.pathname;
        // Build new URL with query parameters
        const newUrl = `${baseUrl}?booking_id=${response.data.booking_id}&booking_confirmation=1`;
        // Redirect
        window.location.href = newUrl;
      } else {
        $("#ttbp_payment_processing").removeClass("visible");
        $("#ttbp_error").addClass("visible");
        $("#ttbp_error .status-message").html(response.data);
      }
    },
    error: function (error) {
      $("#ttbp_payment_processing").removeClass("visible");
      $("#ttbp_error").addClass("visible");
      $("#ttbp_error .status-message").html(
        error.responseText || "Something went wrong."
      );
    },
  });
}

function updateDateText(date) {
  const dateText = document.getElementById("date-text");
  if (!dateText) return;
  const options = {
    weekday: "long",
    month: "short",
    day: "numeric",
  };
  const formatted = date.toLocaleDateString("en-US", options);
  dateText.textContent = formatted;
  const year = date.getFullYear();
  const unformatted = `${year}-${String(date.getMonth() + 1).padStart(
    2,
    "0"
  )}-${String(date.getDate()).padStart(2, "0")}`;
  $("#ttbp_date").val(unformatted).change();
}

jQuery(document).ready(function ($) {
  //Toggle Payment Methods
  $(document)
    .on("change", ".ttbp_method_select", function () {
      $(".ttbp_payment_content").slideUp();
      $(`[data-tab="${$(this).attr("id")}"`).slideDown();
      TTBP__updateBookingReview();
    })
    .on("change", "#ttbp_adv_pay", function () {
      TTBP__updateBookingReview();
    });

  $($(".ttbp_method_select")[0]).trigger("click");

  // Checkout Steps
  $("#ttbp_previous").click(function () {
    $(".ttbp_booking_step").hide();
    $("#ttbp_step_1").show();
    $("#ttbp_error").removeClass("visible");
    $("#ttbp_error .status-message").html("");
    updateSlots();
  });

  // Custom Datepicker for booking form

  const dateText = document.getElementById("date-text");
  const dateDisplay = document.getElementById("date-display");
  const prevBtn = document.getElementById("prev-date");
  const nextBtn = document.getElementById("next-date");

  let selectedDate = new Date();

  if (dateDisplay) {
    let dateOption = {
      defaultDate: selectedDate,
      clickOpens: true,
      minDate: "today",
      onChange: function (selectedDates) {
        if (selectedDates.length) {
          selectedDate = selectedDates[0];
          updateDateText(selectedDate);
        }
      },
    };

    if (ttbp_ajax.booking_window) {
      dateOption.maxDate = new Date().fp_incr(ttbp_ajax.booking_window);
    }
    const fp = flatpickr(dateDisplay, dateOption);

    const minSelectable = fp.parseDate(fp.config.minDate, "Y-m-d");
    const maxSelectable = fp.parseDate(fp.config.maxDate, "Y-m-d");

    prevBtn.addEventListener("click", () => {
      selectedDate.setDate(selectedDate.getDate() - 1);

      if (selectedDate >= minSelectable) {
        fp.setDate(selectedDate, true);
        updateDateText(selectedDate);
      } else {
        selectedDate.setDate(selectedDate.getDate() + 1);
      }
    });

    nextBtn.addEventListener("click", () => {
      selectedDate.setDate(selectedDate.getDate() + 1);
      if (selectedDate <= maxSelectable) {
        fp.setDate(selectedDate, true);
        updateDateText(selectedDate);
      } else {
        selectedDate.setDate(selectedDate.getDate() - 1);
      }
    });
  }

  // Custom Datepicker for booking form Ends

  // Submit booking form
  $(document)
    .on("submit", "#ttbp-booking-form", function (e) {
      e.preventDefault();

      const slot = $('input:radio[name="ttbp_time"]:checked').val();

      if (!slot) {
        alert("Select a slot first");
        return;
      }

      $("#ttbp_payment_processing").addClass("visible");
      $("#ttbp_error").removeClass("visible");

      const gateway = $(".ttbp_method_select:checked").val();

      let formData = $(this).serialize();

      if (gateway === "offline") {
        TTBP__insertBooking(formData);
      } else {
        if (!clover) {
          $("#ttbp-clover-response").html("Something went wrong.");
          $("#ttbp-clover-response").css({
            color: "var(--danger)",
          });
          return;
        }

        clover.createToken().then(function (result) {
          if (result.errors) {
            Object.values(result.errors).forEach(function (value) {
              $("#ttbp-clover-response").text(value);
              $("#ttbp-clover-response").css({
                color: "var(--danger)",
              });
            });
          } else {
            formData +=
              "&ttbp_clover_token=" + encodeURIComponent(result.token);
            TTBP__insertBooking(formData);
          }
        });
      }
    })
    .on("change", '[name="ttbp_time"]', function () {
      const date = $("#ttbp_date").val();
      const time = $('[name="ttbp_time"]:checked').val();
      $("#ttbp_payment_methods").css({
        opacity: 0.5,
        pointerEvents: "none",
      });
      $.ajax({
        url: ttbp_ajax.ajax_url,
        type: "POST",
        data: {
          action: "ttbp_update_payment_gateway",
          date: date,
          time: time,
        },
        success: function (res) {
          if (res.success) {
            const forceAdv = res.data.forceAdv;
            if (forceAdv) {
              $("#ttbp_adv_pay").prop("checked", true);
              $("#payment_method_offline").parent().parent().hide();
              $("#payment_method_clover").trigger("click");
              $("#ttbp_adv_pay").css({
                pointerEvents: "none",
              });
            } else {
              $("#ttbp_adv_pay").prop("checked", false);
              $("#payment_method_offline").parent().parent().show();
              $("#payment_method_offline").trigger("click");
              $("#ttbp_adv_pay").css({
                pointerEvents: "all",
              });
            }
          }
          $("#ttbp_payment_methods").css({
            opacity: 1,
            pointerEvents: "all",
          });
        },
        error: function (err) {
          $("#ttbp_payment_methods").html(err.responseText);
          $("#ttbp_payment_methods").css({
            opacity: 0.5,
            pointerEvents: "all",
          });
        },
      });
    });

  $($('input:radio[name="ttbp_holes"]')[0]).trigger("click");
  $($('input:radio[name="ttbp_ride"]')[0]).trigger("click");
  $($('input:radio[name="ttbp_players"]')[3]).trigger("click");
  $($('input:radio[name="ttbp_tod"]')[0]).trigger("click");

  // Fetch available tee times on date change
  $(`#ttbp_date`).on("change", function () {
    updateSlots();
  });

  // Initial text
  updateDateText(selectedDate);
});
