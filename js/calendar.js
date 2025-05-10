let isUs = false;

if (ttbp_cal.timezone.indexOf("America") > -1) {
  isUs = true;
}

function TTBP__price(price, args = {}) {
  // Default settings - You can replace these with dynamic values from your backend or global JS object
  const defaults = {
    currency: ttbp_cal.currency,
    currency_position: ttbp_cal.currency_position, // Options: left, right, left_space, right_space
    decimal_separator: ttbp_cal.decimal_separator,
    thousand_separator: ttbp_cal.thousand_separator,
    decimals: ttbp_cal.decimals,
  };

  args = Object.assign({}, defaults, args);

  // Format the number
  const floatPrice = parseFloat(price);
  const parts = floatPrice.toFixed(args.decimals).split(".");

  const integerPart = parts[0].replace(
    /\B(?=(\d{3})+(?!\d))/g,
    args.thousand_separator
  );
  const decimalPart = parts[1];

  const formattedPrice = decimalPart
    ? `${integerPart}${args.decimal_separator}${decimalPart}`
    : integerPart;

  // Currency symbol - You can expand this mapping or fetch dynamically
  const symbols = {
    USD: "$",
    EUR: "€",
    GBP: "£",
    AUD: "A$",
    CAD: "C$",
    JPY: "¥",
    CNY: "¥",
    INR: "₹",
    RUB: "₽",
    BRL: "R$",
    ZAR: "R",
    NZD: "NZ$",
    CHF: "CHF",
    SEK: "kr",
    NOK: "kr",
    DKK: "kr",
    HKD: "HK$",
    SGD: "S$",
    MXN: "MX$",
    PLN: "zł",
    TRY: "₺",
    THB: "฿",
    MYR: "RM",
    IDR: "Rp",
    PHP: "₱",
    VND: "₫",
    KRW: "₩",
    ILS: "₪",
    SAR: "﷼",
    AED: "د.إ",
    EGP: "E£",
  };

  const currencySymbol = symbols[args.currency] || args.currency;

  // Format based on position
  let formatted;
  switch (args.currency_position) {
    case "left":
      formatted = currencySymbol + formattedPrice;
      break;
    case "right":
      formatted = formattedPrice + currencySymbol;
      break;
    case "left_space":
      formatted = currencySymbol + " " + formattedPrice;
      break;
    case "right_space":
      formatted = formattedPrice + " " + currencySymbol;
      break;
    default:
      formatted = currencySymbol + formattedPrice;
      break;
  }

  return formatted;
}

function fetchWeek() {
  $("#ttbp_calendar_date").change(function () {
    $(".calendar-loader").css({
      display: "flex",
    });

    $.ajax({
      url: ttbp_cal.ajax_url,
      type: "POST",
      data: {
        action: "render_week_dates",
        current: $(this).val(),
      },
      success: function (res) {
        $(".calendar-nav").html(res);
        calendarDate();
        $($(".calendar-nav button")[0]).click();
      },
      error: function (err) {
        alert("Something went wrong, try again");
      },
    });
  });
}

function fetchBookings(date) {
  $(".calendar-loader").css({
    display: "flex",
  });

  $.ajax({
    url: ttbp_cal.ajax_url,
    type: "POST",
    data: {
      action: "ttbp_render_bookings",
      date: date,
    },
    success: function (res) {
      $(".time-slots").html(res);
      $(".calendar-loader").hide();
      TTBP__openModal();
      TTBP__populateForm();
    },
    error: function (err) {
      alert("Something went wrong, try again");
    },
  });
}

function TTBP__populateForm() {
  $(".slot").click(function () {
    document.querySelector(".ttbp_entry").reset();

    const course = $(this).data("course");
    const date = $(this).data("date");
    const time = $(this).data("time");
    const players = $(this).data("players");
    const holes = $(this).data("holes") ? $(this).data("holes") : 18;
    const name = $(this).data("name");
    const email = $(this).data("email");
    const phone = $(this).data("phone");
    const booking_id = $(this).data("booking_id");
    const bookingAmount = $(this).data("booking_amount");
    const totalPay9 = $(this).data("total_pay_9");
    const totalPay18 = $(this).data("total_pay_18");
    const payment_status = $(this).data("payment_status");
    const payment = $(this).data("payment");

    if ($(this).data("modal") == "ttbp_entry") {
      $("#ttbp_date").val(date).change();
      $("#ttbp_time").val(time).change();
      TTBP__checkAvailability();

      if (players) {
        let playerOpts = "";
        for (let i = 1; i <= parseInt(players); i++) {
          playerOpts += `<option value="${i}">${i}</option>`;
        }
        $("#ttbp_players").html(playerOpts);
      }

      if (booking_id == "") {
        return;
      }

      $("input[name='ttbp_booking_amount']").val(bookingAmount);
      $(`#ttbp_holes option[value="9"]`).attr("data-amount", totalPay9);
      $(`#ttbp_holes option[value="18"]`).attr("data-amount", totalPay18);
      $("#ttbp_golf_course").val(course).change();
      $("#ttbp_players").val(players).change();
      $("#ttbp_holes").val(holes).change();
      $("#ttbp_name").val(name).change();
      $("#ttbp_email").val(email).change();
      $("#ttbp_phone").val(phone).change();
      $("#ttbp_booking_id").val(booking_id);
    } else if ($(this).data("modal") == "ttbp_booking_info") {
      $(".info_date").text(date);
      $(".info_time").text(time);
      $(".info_golf_course").text($(this).data("course_name"));
      let playerOptions = "";
      for (let j = 1; j <= parseInt(players); j++) {
        playerOptions += `<option value="${j}">${j}</option>`;
      }

      $("#ttbp_slot_players").html(playerOptions);
      $("#ttbp_slot_players").val(players);
      $("#ttbp_slot_players").on("change", function () {
        TTBP__generatePaymentFields($(this).val(), payment);
      });
      $("#ttbp_slot_players").trigger("change");

      $(`input[name="ttbp_updated_player"]`).val(players);
      $("input[name='ttbp_bookingID']").val(booking_id);
      $(".info_holes").text(holes);
      $(".info_name").text(name);
      $(".info_email").text(email);
      $(".info_phone").text(phone);
      $("#payment_status").val(payment_status).change();
      if (payment_status == "due") {
        $(".ttbp_payment_notice").show();
        $(".ttbp_payment_notice").html(ttbp_cal.notice);
        let updatedNotice = $(".ttbp_payment_notice").text();
        updatedNotice = updatedNotice.replace(
          "{booking_amount}",
          "<strong>" + TTBP__price(bookingAmount) + "</strong>"
        );
        updatedNotice = updatedNotice.replace(
          "{total_amount}",
          "<strong>" + TTBP__price($(this).data("total_amount")) + "</strong>"
        );
        updatedNotice = updatedNotice.replace(
          "{due_amount}",
          `<strong>${TTBP__price(
            parseFloat($(this).data("total_amount")) - parseFloat(bookingAmount)
          )}</strong>`
        );
        $(".ttbp_payment_notice").html(updatedNotice);
      } else {
        $(".ttbp_payment_notice").hide();
      }

      $(".ttbp_delete_booking").attr("data-id", booking_id);
    }
  });
}

function TTBP__generatePaymentFields(players, payment) {
  let paymentList = "";
  let fields = "";
  for (let j = 1; j <= parseInt(players); j++) {
    paymentList += `<div class="payment-row">`;
    paymentList += `<div class="payment-column">`;
    paymentList += `<label class="payment-label" for="payment_status_${j}">Status (Player- ${j})</label>`;
    paymentList += `<select id="payment_status_${j}">`;
    paymentList += `<option value="due">Due</option>`;
    paymentList += `<option value="paid">Paid</option>`;
    paymentList += `</select>`;
    paymentList += `</div>`;
    paymentList += `<div class="payment-column">`;
    paymentList += `<label class="payment-label" for="player_name_${j}">Name (Player- ${j})</label>`;
    paymentList += `<input type="text" id="player_name_${j}">`;
    paymentList += `</div>`;
    paymentList += `</div>`;

    fields += `<input type="hidden" name="ttbp_payment_status_${j}">`;
    fields += `<input type="hidden" name="ttbp_player_name_${j}">`;
  }

  $(".ttbp_hidden_payment_fields").html(fields);
  $(".info_payment_status").html(paymentList);

  $(".payment-column select").on("change", function () {
    const id = $(this).attr("id");
    const value = $(this).val();
    $(`input[name="ttbp_${id}"]`).val(value).change();
  });

  $(".payment-column input").on("input", function () {
    const id = $(this).attr("id");
    const value = $(this).val();
    $(`input[name="ttbp_${id}"]`).val(value).change();
  });

  for (let i = 1; i <= parseInt(players); i++) {
    if (payment[`payment_status_${i}`]) {
      $(`#payment_status_${i}`).val(payment[`payment_status_${i}`]).change();
    }
    if (payment[`player_name_${i}`]) {
      $(`#player_name_${i}`).val(payment[`player_name_${i}`]).trigger("input");
    }
  }
}

function TTBP__reducePlayers() {
  $("#staff_update_booking").on("submit", function (e) {
    e.preventDefault();

    if (
      !confirm(
        `Are you sure you want to update players to ${$(
          "#ttbp_slot_players"
        ).val()} & update the payment status to ${$("#payment_status").val()}`
      )
    )
      return;

    $(".ttbp_modal_footer").css({
      opacity: 0.5,
      pointerEvents: "none",
    });

    const formData = $(this).serialize();

    $.ajax({
      url: ttbp_cal.ajax_url,
      type: "POST",
      data: {
        action: "ttbp_update_booking",
        formData: formData,
      },
      success: function (res) {
        $(".ttbp_modal_footer").css({
          opacity: 1,
          pointerEvents: "all",
        });
        TTBP__closeModal(document.querySelector("#ttbp_booking_info"));
        $(".calendar-nav > button.active").trigger("click");
      },
      error: function (err) {
        alert(err.responseText);
      },
    });
  });
}

function TTBP__checkAvailability() {
  let selector = ["#ttbp_date", "#ttbp_time"];
  selector.forEach((item) => {
    document.querySelector(item).addEventListener("change", () => {
      const date = document.querySelector(selector[0]).value;
      const time = document.querySelector(selector[1]).value;
      const players = document.querySelector(
        `.slot[data-date="${date}"][data-time="${time}"]`
      ).dataset.players;
      let playerOpts = "";
      for (let i = 1; i <= parseInt(players); i++) {
        playerOpts += `<option value="${i}">${i}</option>`;
      }
      $("#ttbp_players").html(playerOpts);
    });
  });
}

function TTBP__submitBookingEntry() {
  $(".ttbp_entry").on("submit", function (e) {
    e.preventDefault();

    $(this).find(".ttbp_submit_booking").css({
      pointerEvents: "none",
      opacity: 0.5,
    });

    $(this).find(".ttbp_form_message").css("display", "block");
    $(this).find(".ttbp_form_message").text("Processing...");

    const formData = $(this).serialize();

    $.ajax({
      url: ttbp_cal.ajax_url,
      type: "POST",
      data: {
        action: "ttbp_update_entry",
        formData: formData,
      },
      success: function (res) {
        if (res.success) {
          $(".calendar-nav button.active").trigger("click");
          TTBP__closeModal(document.querySelector("#ttbp_entry"));
          $(".ttbp_form_message").hide();
          $(".ttbp_submit_booking").css({
            pointerEvents: "all",
            opacity: 1,
          });
        } else {
          $(".ttbp_form_message").html(res.data);
          $(".ttbp_form_message").css("color", "red");
          $(".ttbp_submit_booking").css({
            pointerEvents: "all",
            opacity: 1,
          });
        }
      },
      error: function (err) {
        console.error(err.responseText);
        $(".ttbp_form_message").html(err.responseText);
        $(".ttbp_form_message").css("color", "red");
        $(".ttbp_submit_booking").css({
          pointerEvents: "all",
          opacity: 1,
        });
      },
    });
  });
}

function calendarDate() {
  const calendarNavButtons = document.querySelectorAll(".calendar-nav button");
  if (calendarNavButtons.length < 1) {
    return;
  }

  calendarNavButtons.forEach((btn) => {
    if (!btn.classList.contains("ttbp_disallow")) {
      btn.addEventListener("click", function () {
        document
          .querySelectorAll(".calendar-nav button")
          .forEach((b) => b.classList.remove("active"));
        this.classList.add("active");
        fetchBookings(btn.dataset.date);
      });
    }
  });
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
      closeModal.addEventListener("click", () => {
        TTBP__closeModal(modal);
      });
    });
  });
}

function TTBP__closeModal(modal) {
  if (!modal) return;
  modal.classList.remove("active");
}

function TTBP__deleteBooking() {
  $("#staff_delete_booking").on("submit", function (e) {
    e.preventDefault();
    const id = $(this).find(`input[name="ttbp_bookingID"]`).val();

    if (!id) return;

    if (!confirm("Are you sure you want to delete the booking?")) return;

    $(".ttbp_delete_booking").text("Deleting in process...");
    $(".ttbp_delete_booking").css({
      pointerEvents: "none",
      opacity: 0.5,
    });

    const formData = $(this).serialize();
    $.ajax({
      url: ttbp_cal.ajax_url,
      type: "POST",
      data: {
        action: "ttbp_delete_booking",
        formData: formData,
        nonce: ttbp_cal.nonce,
      },
      success: function (res) {
        const path = window.location.pathname.replace(/\/+$/, "");
        if (path.endsWith("/my-bookings")) {
          location.reload();
        } else {
          TTBP__closeModal(document.querySelector("#ttbp_booking_info"));
          $(".calendar-nav button.active").trigger("click");
          $(".ttbp_delete_booking").text("Delete");
          $(".ttbp_delete_booking").css({
            pointerEvents: "all",
            opacity: 1,
          });
        }
      },
      error: function (err) {
        alert(err.responseText);
      },
    });
  });
}

function calUpdateDateText(selectedDate) {
  const calDateText = document.getElementById("calendar-date-text");

  const options = {
    weekday: "long",
    month: "short",
    day: "numeric",
  };
  const dateObj = selectedDate;
  const formatted = dateObj.toLocaleDateString("en-US", options);
  let day = dateObj.toLocaleDateString("en-US", {
    weekday: "long",
  });
  let date = dateObj.toLocaleDateString("en-US", {
    day: "numeric",
  });
  let month = dateObj.toLocaleDateString("en-US", {
    month: "short",
  });
  let year = dateObj.getFullYear();
  const unformatted = `${year}-${String(dateObj.getMonth() + 1).padStart(
    2,
    "0"
  )}-${String(dateObj.getDate()).padStart(2, "0")}`;

  let dateHtml = `<span class="calendar-date">${date}</span>`;
  dateHtml += `<span class="calendar-content"><span>${day}</span><span>${
    month + " " + year
  }</span></span>`;
  calDateText.innerHTML = dateHtml;

  $("#ttbp_calendar_date").val(unformatted).change();
}

document.addEventListener("DOMContentLoaded", function () {
  // Custom Datepicker for booking form
  const calDateText = document.getElementById("calendar-date-text");
  const calDateDisplay = document.getElementById("calendar-date-display");

  let calSelectedDate = new Date();

  if (calDateDisplay) {
    let calDateOption = {
      defaultDate: calSelectedDate,
      showAlways: false,
      theme: "light",
      //clickOpens: true,
      minDate: "today",
      onChange: function (calSelectedDates) {
        if (calSelectedDates.length) {
          const newDate = new Date(
            calSelectedDates[0].toISOString().split("T")[0]
          );
          newDate.setDate(newDate.getDate() + 1);
          calSelectedDate = isUs ? newDate : calSelectedDates[0];
          calUpdateDateText(calSelectedDate);
        }
      },
    };

    if (ttbp_cal.booking_window) {
      calDateOption.maxDate = new Date().fp_incr(ttbp_cal.booking_window);
    }

    const calFp = flatpickr(calDateDisplay, calDateOption);
    calUpdateDateText(calSelectedDate);
  }

  $("#ttbp_slot_players").on("change", function () {
    const players = $(this).val();
    $(`input[name="ttbp_updated_player"]`).val(players);
  });

  $("#ttbp_holes").on("change", function () {
    const amount = $(this).find("option:selected").data("amount");
    $(`input[name="ttbp_total_amount"]`).val(amount);
  });

  $("#payment_status").on("change", function () {
    const status = $(this).val();
    $(`input[name="ttbp_payment_status"]`).val(status);
  });

  fetchWeek();
  calendarDate();
  TTBP__openModal();
  TTBP__populateForm();
  TTBP__submitBookingEntry();
  TTBP__deleteBooking();
  TTBP__reducePlayers();
});
