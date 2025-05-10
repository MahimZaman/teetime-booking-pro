$ = jQuery;
// replace with get_option value output
const clover = new Clover(ttbp_clover.public_key, {
  merchantId: ttbp_clover.merchantId,
});
const elements = clover.elements();

const styles = {
  input: {
    fontSize: "16px",
  },
};

const cardNumber = elements.create("CARD_NUMBER", styles);
const cardDate = elements.create("CARD_DATE", styles);
const cardCvv = elements.create("CARD_CVV", styles);
const cardPostalCode = elements.create("CARD_POSTAL_CODE", styles);

const displayCardNumberErr = document.getElementById("ttbp-card-number-errors");
const displayCardDateErr = document.getElementById("ttbp-card-date-errors");
const displayCardCvvErr = document.getElementById("ttbp-card-cvv-errors");
const displayCardPostcodeErr = document.getElementById(
  "ttbp-card-postcode-errors"
);

if (displayCardNumberErr) {
  cardNumber.mount("#ttbp-card-number");
  cardDate.mount("#ttbp-card-date");
  cardCvv.mount("#ttbp-card-cvv");
  cardPostalCode.mount("#ttbp-card-postcode");

  // Handle real-time validation errors from the card element
  cardNumber.addEventListener("change", function (event) {
    displayCardNumberErr.title = displayCardNumberErr.innerHTML =
      event.CARD_NUMBER.error || "";
  });

  cardNumber.addEventListener("blur", function (event) {
    displayCardNumberErr.title = displayCardNumberErr.innerHTML =
      event.CARD_NUMBER.error || "";
  });

  cardDate.addEventListener("change", function (event) {
    displayCardDateErr.title = displayCardDateErr.innerHTML =
      event.CARD_DATE.error || "";
  });

  cardDate.addEventListener("blur", function (event) {
    displayCardDateErr.title = displayCardDateErr.innerHTML =
      event.CARD_DATE.error || "";
  });

  cardCvv.addEventListener("change", function (event) {
    displayCardCvvErr.title = displayCardCvvErr.innerHTML =
      event.CARD_CVV.error || "";
  });

  cardCvv.addEventListener("blur", function (event) {
    displayCardCvvErr.title = displayCardCvvErr.innerHTML =
      event.CARD_CVV.error || "";
  });

  cardPostalCode.addEventListener("change", function (event) {
    displayCardPostcodeErr.title = displayCardPostcodeErr.innerHTML =
      event.CARD_POSTAL_CODE.error || "";
  });

  cardPostalCode.addEventListener("blur", function (event) {
    displayCardPostcodeErr.title = displayCardPostcodeErr.innerHTML =
      event.CARD_POSTAL_CODE.error || "";
  });
}
