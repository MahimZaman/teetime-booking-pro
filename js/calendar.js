$ = jQuery;
let isUs = false;
let ttbpAjax = ttbp_cal.ajax_url;
let slotData = null;

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

  TTBP__updateTopDate(date);
  TTBP_updateAnalytics(date);

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
      TTBP_resetBlockmode();
      TTBP_updateStarterLink();
    },
    error: function (err) {
      alert("Something went wrong, try again");
    },
  });
}

function TTBP__populateForm() {
  $(".slot").click(function () {
    document.querySelector(".ttbp_entry").reset();
    $(".ttbp_form_message").hide();

    const course = $(this).data("course");
    const date = $(this).data("date");
    const time = $(this).data("time");
    const players = $(this).data("players");
    const holes = $(this).data("holes") ? $(this).data("holes") : 18;
    const ride = $(this).data("ride") ? $(this).data("ride") : "cart";
    const name = $(this).data("name");
    const email = $(this).data("email");
    const phone = $(this).data("phone");
    const booking_id = $(this).data("booking_id");
    const bookingAmount = $(this).data("booking_amount")
      ? $(this).data("booking_amount")
      : 0;
    const totalPay9 = $(this).data("total_pay_9")
      ? $(this).data("total_pay_9")
      : 0;
    const totalPay18 = $(this).data("total_pay_18")
      ? $(this).data("total_pay_18")
      : 0;
    const payment_status = $(this).data("payment_status");
    const payment = $(this).data("payment");
    const method = $(this).data("method");
    const reason = $(this).data("reason");
    const explanation = $(this).data("explanation");
    const discount_player = $(this).data("discount_player");

    slotData = $(this).data();

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
      //$("#ttbp_golf_course").val(course).change();
      $("#ttbp_players").val(players).change();
      $("#ttbp_holes").val(holes).change();
      $("#ttbp_ride").val(ride).change();
      $("#ttbp_name").val(name).change();
      $("#ttbp_email").val(email).change();
      $("#ttbp_phone").val(phone).change();
      $("#ttbp_booking_id").val(booking_id);
    } else if ($(this).data("modal") == "ttbp_booking_info") {
      slotData.per_player =
        (parseFloat(slotData.total_amount) -
          parseFloat(slotData.others.booking_discount)) /
        players;
      $("#ttbp_pos_object").val("");
      $(".info_date").text(date);
      $(".info_time").text(time);
      $(".info_golf_course").text($(this).data("course_name"));
      let playerOptions = "";
      for (let j = 1; j <= parseInt(players); j++) {
        playerOptions += `<option value="${j}">${
          j + (payment[`payment_status_${j}`] == "paid" ? " (Paid)" : "")
        }</option>`;
      }
      $("#ttbp_slot_players").html(playerOptions);
      let couponForOpts = "";
      for (let c = 1; c <= parseInt(players); c++) {
        couponForOpts += `<option value="${c}" ${
          payment[`payment_status_${c}`] == "paid" ? "disabled" : ""
        }>${
          c + (payment[`payment_status_${c}`] == "paid" ? " (Paid)" : "")
        }</option>`;
      }
      $("#ttbp_coupon_for").html(couponForOpts);
      $("#ttbp_slot_players").val(players);
      $("#ttbp_discount_player_1").val(discount_player.ttbp_discount_player_1);
      $("#ttbp_discount_player_2").val(discount_player.ttbp_discount_player_2);
      $("#ttbp_discount_player_3").val(discount_player.ttbp_discount_player_3);
      $("#ttbp_discount_player_4").val(discount_player.ttbp_discount_player_4);

      const amountTotal = $(this).data("total_amount");
      $("#ttbp_booking_total").val(amountTotal);
      $("#ttbp_total_amount").val(amountTotal);
      $("#ttbp_slot_players").on("change", function () {
        TTBP__generatePaymentFields(
          $(this).val(),
          payment,
          amountTotal,
          bookingAmount
        );

        couponForOpts = "";
        for (let c = 1; c <= parseInt($(this).val()); c++) {
          couponForOpts += `<option value="${c}" ${
            payment[`payment_status_${c}`] == "paid" ? "disabled" : ""
          }>${
            c + (payment[`payment_status_${c}`] == "paid" ? " (Paid)" : "")
          }</option>`;
        }
        $("#ttbp_coupon_for").html(couponForOpts);
      });
      $("#ttbp_slot_players").trigger("change");
      $("input[name='ttbp_bookingID']").val(booking_id);
      $(".info_holes").text(holes);
      $(".info_ride").text(ride);
      $(".info_name").text(name);
      $(".info_email").text(email);
      $(".info_phone").text(phone);
      $(".info_method").text(method);
      $("#payment_status").val(payment_status).change();

      // Payment Notice
      $(".ttbp_payment_notice").show();
      if (isPastDateTime(date, time)) {
        $(".past-delete").show();
        $("#delete_reason").val(reason).change();
        $("#delete_reason_explain").val(explanation).change();
        $("#staff_delete_booking").hide();
      } else {
        $(".past-delete").hide();
        $("#staff_delete_booking").show();
      }

      $(".ttbp_payment_notice").html(ttbp_cal.notice);
      let updatedNotice = $(".ttbp_payment_notice").text();

      if (slotData.others.is_advanced) {
        const noticeDiscount = parseFloat(slotData.others.booking_discount);
        updatedNotice = `Customer paid <strong>${TTBP__price(
          slotData.booking_amount + (slotData.others.tax_per_person * slotData.players)
        )}</strong> for <strong>${
          slotData.players
        }</strong> players.  Discounted <strong>${TTBP__price(
          noticeDiscount
        )}</strong>`;
      } else {
        updatedNotice = updatedNotice.replace(
          "{booking_amount}",
          "<strong>" + TTBP__price(bookingAmount) + "</strong>"
        );
        updatedNotice = updatedNotice.replace(
          "{total_amount}",
          "<strong>" +
            TTBP__price(
              $(this).data("total_amount") +
                slotData.others.tax_per_person * slotData.players
            ) +
            "</strong>"
        );
        updatedNotice = updatedNotice.replace(
          "{due_amount}",
          `<strong>${TTBP__price(
            parseFloat(
              $(this).data("total_amount") +
                slotData.others.tax_per_person * slotData.players
            ) - parseFloat(bookingAmount)
          )}</strong>`
        );
      }
      $(".ttbp_payment_notice").html(updatedNotice);

      $(".ttbp_delete_booking").attr("data-id", booking_id);
    }
  });
}

function isPastDateTime(dateStr, timeStr) {
  // Combine into full ISO format string
  const fullDateTime = new Date(`${dateStr}T${timeStr}`);
  const fullDateTimeNow = new Date();
  fullDateTimeNow.setMinutes(
    fullDateTimeNow.getMinutes() + parseInt(ttbp_cal.duration)
  );
  // Compare with current time
  return fullDateTime < fullDateTimeNow;
}

function TTBP__generatePaymentFields(
  players,
  payment,
  total_amount,
  bookingAmount
) {
  let paymentList = "";
  let booking_amount = parseFloat(slotData.booking_amount);
  let eachPayable = 0;
  eachPayable = parseFloat(
    slotData.per_player + slotData.others.tax_per_person
  );
  // if (parseFloat(booking_amount) > 0) {
  //   const totalIncTax = (slotData.total_amount - booking_amount) + slotData.others.total_tax;
  //   eachPayable = parseFloat(totalIncTax) / players;
  // }

  for (let j = 1; j <= parseInt(players); j++) {
    paymentList += `<div class="payment-row">`;
    paymentList += `<div class="payment-column">`;
    paymentList += `<label class="payment-label" for="payment_status_${j}">Status (Player- ${j})</label>`;
    paymentList += `<select class="player_payment_status ${
      payment[`payment_status_${j}`] == "paid" ? "ttbp_paid" : ""
    }" id="payment_status_${j}" name="ttbp_payment_status_${j}" ${
      payment[`payment_status_${j}`] == "paid" ? "disabled" : ""
    }>`;
    paymentList += `<option value="due" ${
      payment[`payment_status_${j}`] == "due" ? "selected" : ""
    }>Due (${TTBP__price(
      eachPayable - parseFloat($(`#ttbp_discount_player_${j}`).val())
    )})</option>`;
    paymentList += `<option value="paid" ${
      payment[`payment_status_${j}`] == "paid" ? "selected" : ""
    }>Paid (${TTBP__price(
      eachPayable - parseFloat($(`#ttbp_discount_player_${j}`).val())
    )})</option>`;
    paymentList += `</select>`;
    paymentList += `</div>`;
    paymentList += `<div class="payment-column">`;
    paymentList += `<label class="payment-label" for="player_name_${j}">Name (Player- ${j})</label>`;
    paymentList += `<input type="text" id="player_name_${j}" name="ttbp_player_name_${j}" value="${
      payment[`player_name_${j}`]
    }">`;
    paymentList += `</div>`;
    paymentList += `<div class="payment-column">`;
    paymentList += `<label class="payment-label" for="player_email_${j}">Email (Player- ${j})</label>`;
    paymentList += `<input type="email" id="player_email_${j}" name="ttbp_player_email_${j}" value="${
      payment[`player_email_${j}`]
    }">`;
    paymentList += `<ul class="ttbp_autocomplete"></ul>`;
    paymentList += `</div>`;
    paymentList += `<div class="payment-column">`;
    paymentList += `<label class="payment-label" for="player_phone_${j}">Phone (Player- ${j})</label>`;
    paymentList += `<input type="tel" id="player_phone_${j}" name="ttbp_player_phone_${j}" value="${
      payment[`player_phone_${j}`]
    }">`;
    paymentList += `<ul class="ttbp_autocomplete"></ul>`;
    paymentList += `</div>`;
    paymentList += `</div>`;
  }

  $(".info_payment_status").html(paymentList);
}

function TTBP__reducePlayers() {
  $(document).on("submit", "#staff_update_booking", function (e) {
    e.preventDefault();

    if (
      !confirm(
        `Are you sure you want to update players to ${$(
          "#ttbp_slot_players"
        ).val()} & update the payment status`
      )
    )
      return;

    // const delete_reason = $("#delete_reason").val();
    // const infoDate = $(".info_date").text();
    // const infoTime = $(".info_time").text();

    // if (isPastDateTime(infoDate, infoTime)) {
    //   if (delete_reason == "") {
    //     alert("Reason of Updates needs to be selected.");
    //     return;
    //   }
    // } - This code is commented to make the notes fields optional.

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
        $(".ttbp_applied_coupon").html("");
        if (res.success) {
          TTBP__closeModal(document.querySelector("#ttbp_booking_info"));
          $(".calendar-nav > button.active").trigger("click");
          $(".ttbp_update_notice").addClass("success");
        } else {
          $(".ttbp_update_notice").addClass("error");
          $(".ttbp_update_notice").html(res.data);
          setTimeout(function () {
            $(".ttbp_update_notice").html("");
            $(".ttbp_update_notice").removeClass("success");
            $(".ttbp_update_notice").removeClass("error");
          }, 3000);
        }
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
    $(this).find(".ttbp_form_message").css("color", "green");
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
    // if (!btn.classList.contains("ttbp_disallow")) {
    // }
    btn.addEventListener("click", function () {
      document
        .querySelectorAll(".calendar-nav button")
        .forEach((b) => b.classList.remove("active"));
      this.classList.add("active");
      fetchBookings(btn.dataset.date);
    });
  });
}

function TTBP__openModal() {
  const buttons = document.querySelectorAll(".ttbp_open_modal");
  if (buttons.length < 1) {
    return;
  }

  Array.from(buttons).forEach((button, i) => {
    button.addEventListener("click", () => {
      if (button.classList.contains("blockmode")) return;
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
  slotData = null;
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

function TTBP__updateTopDate(inputDate) {
  const calDateText = document.getElementById("calendar-date-text");

  const options = {
    weekday: "long",
    month: "short",
    day: "numeric",
  };
  const passedDate = new Date(inputDate);
  const strDate = isUs
    ? passedDate.setDate(passedDate.getDate() + 1)
    : inputDate;
  const dateObj = new Date(strDate);
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

function TTBP_bulkPriceCalculate() {
  const $blockForm = $("#ttbp_blockbook_form");
  const ride = $blockForm.find("#ttbp_block_ride").val();
  const holes = $blockForm.find("#ttbp_block_holes").val();
  const discount = $blockForm.find("#ttbp_block_discount").val()
    ? parseFloat($blockForm.find("#ttbp_block_discount").val())
    : 0;

  let totalAmount = 0;
  let bookingAmount = 0;
  let pricePerBook = {};

  $(".slot.blocked").each(function (index) {
    totalAmount += parseFloat($(this).data(`total_pay_${holes}_${ride}`));
    bookingAmount += parseFloat($(this).data(`booking_amount`));
    const time = $(this).data("time");
    if (pricePerBook[time]) {
      pricePerBook[time] += parseFloat(
        $(this).data(`total_pay_${holes}_${ride}`)
      );
    } else {
      pricePerBook[time] = parseFloat(
        $(this).data(`total_pay_${holes}_${ride}`)
      );
    }
  });

  $("#ttbp_block_booking_amount").val(bookingAmount);
  $("#ttbp_block_total_amount").val(totalAmount);
  $("#ttbp_per_book_price").val(JSON.stringify(pricePerBook));

  $(".ttbp_subtotal").html(TTBP__price(totalAmount));
  $(".ttbp_discount").html(TTBP__price(discount));
  $(".ttbp_total").html(TTBP__price(parseFloat(totalAmount) - discount));
}

function TTBP_resetBlockmode() {
  $(".slot.empty:not(.ttbp_not_allowed)").addClass("ttbp_open_modal");
  $(".slot.empty:not(.ttbp_not_allowed)").removeClass("blockmode");
  $("#enable_blockmode").show();
  $("#book_block").hide();
}

function TTBP_debounce(func, delay) {
  let timer;
  return function () {
    const context = this,
      args = arguments;
    clearTimeout(timer);
    timer = setTimeout(function () {
      func.apply(context, args);
    }, delay);
  };
}

function TTBP_updateStarterLink() {
  const link = document.querySelector("#starter_btn a");
  if (!link) return;

  const url = new URL(link.href);
  url.searchParams.set("date", $("#ttbp_calendar_date").val()); // replace or add the date param

  link.href = url.toString(); // update the anchor tag
}

function TTBP_updateAnalytics(date) {
  $.ajax({
    url: ttbpAjax,
    type: "POST",
    data: {
      action: "ttbp_update_analytics",
      date: date,
    },
    success: function (res) {
      if (res.success) {
        $(".da_bookings .da_value").html(res.data.bookings);
        $(".da_occupancy .da_value").html(res.data.occupancy);
        $(".da_noshows .da_value").html(res.data.noshows);
        $(".da_checked-in .da_value").html(res.data.checked_in);
        $(".da_revenue .da_value").html(res.data.revenue);
      } else {
        $(".da_bookings .da_value").html("--");
        $(".da_occupancy .da_value").html("--");
        $(".da_noshows .da_value").html("--");
        $(".da_checked-in .da_value").html("--");
        $(".da_revenue .da_value").html("--");
      }
    },
    error: function (err) {
      console.error(err.responseText);
    },
  });
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

    const calFp = flatpickr(calDateDisplay, calDateOption);
    calUpdateDateText(calSelectedDate);
  }

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

// Bulk Mode JS
$(document)
  .on("click", "#enable_blockmode", function (e) {
    e.preventDefault();
    $(".slot.empty:not(.ttbp_not_allowed)").removeClass("ttbp_open_modal");
    $(".slot.empty:not(.ttbp_not_allowed)").addClass("blockmode");
    $("#enable_blockmode").hide();
    $("#book_block").show();
  })
  .on("click", ".slot.blockmode", function () {
    $(this).toggleClass("blocked");
    if ($(this).find("i").hasClass("fa-circle-plus")) {
      $(this).find("i").removeClass("fa-circle-plus");
      $(this).find("i").addClass("fa-circle-check");
    } else {
      $(this).find("i").addClass("fa-circle-plus");
      $(this).find("i").removeClass("fa-circle-check");
    }
  })
  .on("click", "#cancel_blockbook", function () {
    $("#enable_blockmode").trigger("click");
    $(".slot.blocked").find("i").removeClass("fa-circle-check");
    $(".slot.blocked").find("i").addClass("fa-circle-plus");
    $(".slot").removeClass("blocked");
    $(".slot").removeClass("blockmode");
    TTBP_resetBlockmode();
    TTBP__closeModal(document.querySelector("#ttbp_blockbook"));
  })
  .on("click", "#book_block", function () {
    const slots = {};
    $(".slot.blocked").each(function (index) {
      const date = $(this).data("date");
      const time = $(this).data("time");

      if (!slots[date]) {
        slots[date] = {};
      }

      if (slots[date][time]) {
        slots[date][time] += 1;
      } else {
        slots[date][time] = 1;
      }
    });

    if (Object.keys(slots).length < 1) {
      alert("No slots have been selected.");
      return;
    }

    $("#ttbp_block_slots").val(JSON.stringify(slots));
    TTBP_bulkPriceCalculate();
  })
  .on("submit", "#ttbp_blockbook_form", function (e) {
    e.preventDefault();
    if ($("#ttbp_block_slots").val() == "") {
      alert("No slots have been selected");
      return;
    }
    if (
      $("#ttbp_block_name").val() == "" ||
      $("#ttbp_block_email").val() == "" ||
      $("#ttbp_block_phone").val() == ""
    ) {
      alert("Required fields cannot be empty.");
      return;
    }
    const formData = $(this).serialize();
    $("#submit_blockbook").find(".ttbp_button_loader").css({
      display: "inline-block",
    });
    $(this).find(".form-status").html("Processing...");
    $.ajax({
      url: ttbpAjax,
      type: "POST",
      data: {
        action: "ttbp_bulk_booking",
        data: formData,
      },
      success: function (res) {
        if (res.success) {
          $("#submit_blockbook").find(".ttbp_button_loader").hide();
          if (res.data.errors) {
            $("#ttbp_blockbook_form")
              .find(".form-status")
              .html(
                "Booking successful with few failures in - " + res.data.errors
              )
              .css({
                color: "var(--success)",
              });
          } else {
            $("#ttbp_blockbook_form")
              .find(".form-status")
              .html("Booking successful")
              .css({
                color: "var(--success)",
              });
          }
          $("#ttbp_blockbook_form").trigger("reset");
          $(".calendar-nav > button.active").trigger("click");
          TTBP__closeModal(document.querySelector("#ttbp_blockbook"));
          $(document).trigger("ttbp_reset_block");
          $(".remove-coupon").trigger("click");
        } else {
          $("#ttbp_blockbook_form").find(".form-status").html(res.data).css({
            color: "var(--danger)",
          });
          $("#submit_blockbook").find(".ttbp_button_loader").hide();
        }

        setTimeout(function () {
          $("#ttbp_blockbook_form").find(".form-status").html("").css({
            color: "var(--info)",
          });
        }, 3000);
      },
      error: function (err) {
        console.error(err.responseText);
        $("#ttbp_blockbook_form")
          .find(".form-status")
          .html("Something went wrong!")
          .css({
            color: "var(--danger)",
          });

        setTimeout(function () {
          $("#ttbp_blockbook_form").find(".form-status").html("").css({
            color: "var(--info)",
          });
        }, 3000);
      },
    });
  })
  .on("click", "#submit_blockbook", function () {
    $("#ttbp_blockbook_form").trigger("submit");
  })
  .on("ttbp_reset_block", function () {
    $("#enable_blockmode").trigger("click");
    $(".slot.blocked").find("i").removeClass("fa-circle-check");
    $(".slot.blocked").find("i").addClass("fa-circle-plus");
    $(".slot").removeClass("blocked");
  })
  .on(
    "change",
    '#ttbp_blockbook_form select[name="ttbp_holes"], #ttbp_blockbook_form select[name="ttbp_ride"]',
    function () {
      TTBP_bulkPriceCalculate();
    }
  )
  .on("click", "#redeem_coupon", function () {
    const discountFor = $("#ttbp_coupon_for").val();
    const appliedCoupon = $("#ttbp_discount_player_" + discountFor).val();
    const total = $("#ttbp_booking_total").val();
    const players = $("#ttbp_slot_players").val();
    const perPlayer = parseFloat(total) / parseInt(players);
    if (appliedCoupon > 0) {
      alert(
        "A coupon is already applied to this order. You have to cancel it to apply a new coupon."
      );
      return;
    }
    const coupon = $("#ttbp_discount_coupon").val();
    if (coupon == "") {
      alert("Enter a valid coupon code to apply discounts.");
      return;
    }

    if (discountFor == "" || discountFor == null) {
      alert("Select a player to apply coupon for.");
      return;
    }

    $(this).find(".ttbp_button_loader").css({
      display: "inline-block",
    });

    $.ajax({
      url: ttbpAjax,
      type: "POST",
      data: {
        action: "ttbp_redeem_coupon",
        coupon: coupon,
        discount_for: discountFor,
        per_player_cost: perPlayer,
      },
      success: function (res) {
        $("#redeem_coupon").find(".ttbp_button_loader").hide();
        if (res.success) {
          if (slotData == null) return;
          const discount = res.data.discount;
          const coupon_code = res.data.coupon_code;
          const coupon_id = res.data.coupon_id;
          const player = res.data.player;
          const el = document.createElement("span");
          el.classList.add("remove-coupon");
          el.innerHTML =
            coupon_code +
            ` (${TTBP__price(discount)} off - Player ${player})` +
            "&times";
          el.dataset.coupon_id = coupon_id;
          el.dataset.discount = discount;
          el.dataset.player = player;
          document.querySelector(".ttbp_applied_coupon").appendChild(el);
          $(`#ttbp_discount_player_${player}`).val(discount).change();
          TTBP__generatePaymentFields(
            $("#ttbp_slot_players").val(),
            slotData.payment,
            slotData.total_amount,
            slotData.booking_amount
          );

          $("#ttbp_discount_coupon").val("");
        } else {
          alert(res.data);
        }
      },
      error: function (err) {
        console.error(err.responseText);
      },
    });
  })
  .on("click", ".remove-coupon", function () {
    const player = $(this).data("player");
    $(`#ttbp_discount_player_${player}`).val(0).change();
    TTBP__generatePaymentFields(
      $("#ttbp_slot_players").val(),
      slotData.payment,
      slotData.total_amount,
      slotData.booking_amount
    );
    $(this).remove();
  })
  .on("click", ".turn_btn", function () {
    const key = $(this).data("meta_key");
    const el = $(this).prop("id");
    const booking_id = $(this).val();
    $(this).find(".ttbp_button_loader").css({
      display: "inline-block",
    });
    $.ajax({
      url: ttbpAjax,
      type: "POST",
      data: {
        action: "ttbp_update_starter_times",
        key: key,
        el: el,
        booking_id: booking_id,
      },
      success: function (res) {
        $(`#${res.data.el}`).find(".ttbp_button_loader").hide();
        if (res.success) {
          $(`#${res.data.el}`).find(".ttbp_clicked_time").html(res.data.time);
          $(`#${res.data.el}`).addClass("ttbp_disabled");
          if (res.data.front_nine) {
            $(`#front_nine_${res.data.id} strong`).html(res.data.front_nine);
          }
          if (res.data.back_nine) {
            $(`#back_nine_${res.data.id} strong`).html(res.data.back_nine);
          }
          if (res.data.time_total) {
            $(`#time_total_${res.data.id} strong`).html(res.data.time_total);
          }
        } else {
          alert(res.data);
        }
      },
      error: function (err) {
        console.error(err.responseText);
      },
    });
  })
  .on(
    "input",
    "#ttbp_cart_code_1, #ttbp_cart_code_2",
    TTBP_debounce(function () {
      const serial = $(this).val();
      if (serial !== "") {
        $("#ttbp_cart_code_1").prop("readonly", true);
        $("#ttbp_cart_code_2").prop("readonly", true);
        $.ajax({
          url: ttbpAjax,
          type: "POST",
          data: {
            action: "ttbp_update_cart_serial",
            serial: serial,
            booking_id: $(this).data("booking_id"),
            key: $(this).prop("id"),
          },
          success: function (res) {
            if (res.success) {
              $("#ttbp_cart_code_1").prop("readonly", false);
              $("#ttbp_cart_code_2").prop("readonly", false);
            }
          },
          error: function (err) {
            console.error(err.responseText);
          },
        });
      }
    }, 1000)
  )
  .on("click", '.slot[data-modal="ttbp_entry"]', function () {
    const $entryForm = $(".ttbp_entry");
    const holes = $entryForm.find("#ttbp_holes").val();
    const ride = $entryForm.find("#ttbp_ride").val();
    $entryForm
      .find("#ttbp_total_amount_9_walking")
      .val($(this).data("total_pay_9_walking"));
    $entryForm
      .find("#ttbp_total_amount_9_cart")
      .val($(this).data("total_pay_9_cart"));
    $entryForm
      .find("#ttbp_total_amount_18_walking")
      .val($(this).data("total_pay_18_walking"));
    $entryForm
      .find("#ttbp_total_amount_18_cart")
      .val($(this).data("total_pay_18_cart"));

    $entryForm
      .find("#ttbp_entry_total_amount")
      .val($(`#ttbp_total_amount_${holes}_${ride}`).val())
      .change();
  })
  .on("change", ".ttbp_entry #ttbp_holes, .ttbp_entry #ttbp_ride", function () {
    const $entryForm = $("#ttbp_entry");
    const holes = $entryForm.find("#ttbp_holes").val();
    const ride = $entryForm.find("#ttbp_ride").val();
    $entryForm
      .find("#ttbp_entry_total_amount")
      .val($(`#ttbp_total_amount_${holes}_${ride}`).val())
      .change();
  })
  .on(
    "input",
    ".ttbp_entry #ttbp_email, .ttbp_entry #ttbp_phone",
    TTBP_debounce(function () {
      const str = $(this).val();
      if (str !== "") {
        $(this)
          .parent()
          .find(".ttbp_autocomplete")
          .html(`<li class="ttbp_acResult">Loading...</li>`);
        $.ajax({
          url: ttbpAjax,
          type: "POST",
          data: {
            action: "ttbp_customer_details",
            keyword: str,
            id: $(this).prop("id"),
          },
          success: function (res) {
            if (res.success) {
              $(`#${res.data.id}`)
                .parent()
                .find(".ttbp_autocomplete")
                .html(res.data.output);
            } else {
              $(`#${res.data.id}`).parent().find(".ttbp_autocomplete").html("");
            }
          },
          error: function (err) {
            console.error(err.responseText);
          },
        });
      } else {
        $(this).parent().find(".ttbp_autocomplete").html(``);
      }
    }, 500)
  )
  .on("click", ".ttbp_entry .ttbp_acResult", function () {
    $(".ttbp_entry #ttbp_name").val($(this).data("name")).change();
    $(".ttbp_entry #ttbp_email").val($(this).data("email")).change();
    $(".ttbp_entry #ttbp_phone").val($(this).data("phone")).change();
    $(this).parent().html("");
  })
  .on(
    "input",
    ".ttbp_booking_info #player_email_1, .ttbp_booking_info #player_email_2, .ttbp_booking_info #player_email_3, .ttbp_booking_info #player_email_4, .ttbp_booking_info #player_phone_1, .ttbp_booking_info #player_phone_2, .ttbp_booking_info #player_phone_3, .ttbp_booking_info #player_phone_4",
    TTBP_debounce(function () {
      const str = $(this).val();
      if (str !== "") {
        $(this)
          .parent()
          .find(".ttbp_autocomplete")
          .html(`<li class="ttbp_acResult">Loading...</li>`);
        $.ajax({
          url: ttbpAjax,
          type: "POST",
          data: {
            action: "ttbp_customer_details",
            keyword: str,
            id: $(this).prop("id"),
          },
          success: function (res) {
            if (res.success) {
              $(`#${res.data.id}`)
                .parent()
                .find(".ttbp_autocomplete")
                .html(res.data.output);
            } else {
              $(`#${res.data.id}`).parent().find(".ttbp_autocomplete").html("");
            }
          },
          error: function (err) {
            console.error(err.responseText);
          },
        });
      } else {
        $(this).parent().find(".ttbp_autocomplete").html(``);
      }
    }, 500)
  )
  .on("click", ".ttbp_booking_info .ttbp_acResult", function () {
    $(this)
      .parent()
      .parent()
      .parent()
      .find('input[type="text"]')
      .val($(this).data("name"))
      .trigger("value_change");
    $(this)
      .parent()
      .parent()
      .parent()
      .find('input[type="email"]')
      .val($(this).data("email"))
      .trigger("value_change");
    $(this)
      .parent()
      .parent()
      .parent()
      .find('input[type="tel"]')
      .val($(this).data("phone"))
      .trigger("value_change");
    $(this).parent().html("");
  })
  .on(
    "input",
    "#ttbp_blockbook_form #ttbp_block_email, #ttbp_blockbook_form #ttbp_block_phone",
    TTBP_debounce(function () {
      const str = $(this).val();
      if (str !== "") {
        $(this)
          .parent()
          .find(".ttbp_autocomplete")
          .html(`<li class="ttbp_acResult">Loading...</li>`);
        $.ajax({
          url: ttbpAjax,
          type: "POST",
          data: {
            action: "ttbp_customer_details",
            keyword: str,
            id: $(this).prop("id"),
          },
          success: function (res) {
            if (res.success) {
              $(`#${res.data.id}`)
                .parent()
                .find(".ttbp_autocomplete")
                .html(res.data.output);
            } else {
              $(`#${res.data.id}`).parent().find(".ttbp_autocomplete").html("");
            }
          },
          error: function (err) {
            console.error(err.responseText);
          },
        });
      } else {
        $(this).parent().find(".ttbp_autocomplete").html(``);
      }
    }, 500)
  )
  .on("click", "#ttbp_blockbook_form .ttbp_acResult", function () {
    $("#ttbp_blockbook_form #ttbp_block_name")
      .val($(this).data("name"))
      .change();
    $("#ttbp_blockbook_form #ttbp_block_email")
      .val($(this).data("email"))
      .change();
    $("#ttbp_blockbook_form #ttbp_block_phone")
      .val($(this).data("phone"))
      .change();
    $(this).parent().html("");
  })
  .on("click", ".ttbp_reduce_player", function () {
    $("#staff_update_booking").trigger("submit");
  })
  .on("change", ".player_payment_status", function () {
    const paidPlayers = [];
    $(".player_payment_status:not(.ttbp_paid)").each(function (index) {
      if ($(this).val() == "paid") {
        const id = $(this).prop("id");
        const playerIndex = id.replace("payment_status_", "");
        paidPlayers.push(playerIndex);
      }
    });

    $("#ttbp_pos_object").val(
      paidPlayers.length > 0 ? paidPlayers.join(",") : ""
    );
  });
