window.onload = function () {
  //remove link breadcrumb, header and save button
  document.querySelector(".wc-admin-breadcrumb").style.display = 'none';
  document.querySelector(".mp-header-logo").style.display = 'none';
  document.querySelector("#_wpnonce").parentElement.style.display = 'none';
  document.querySelectorAll("h2")[4].style.display = 'none';

  //update form_fields label
  var label = document.querySelectorAll("th.titledesc");
  for (var i = 0; i < label.length; i++) {
    label[i].id = "mp_field_text";
    if (label[i].children[0].children[0] != null) {
      label[i].children[0].children[0].style.position = 'relative';
      label[i].children[0].children[0].style.fontSize = '22px';
    }
  }

  //collpase ajustes avanzados
  var table = document.querySelectorAll(".form-table");
  for (i = 0; i < table.length; i++) {
    table[i].id = "mp_table_" + i;
  }

  // Remove title and description label necessary for custom
  document.querySelector(".hidden-field-mp-title").setAttribute("type", "hidden");
  document.querySelector(".hidden-field-mp-desc").setAttribute("type", "hidden");
  var removeLabel = document.querySelectorAll("#mp_table_0");
  removeLabel[0].children[0].children[0].style.display = 'none';
  removeLabel[0].children[0].children[1].style.display = 'none';

  //clone save button
  var cloneSaveButton = document.getElementById('woocommerce_woo-mercado-pago-basic_checkout_btn_save');
  if (document.getElementById("woocommerce_woo-mercado-pago-basic_checkout_homolog_title") != undefined || document.getElementById("woocommerce_woo-mercado-pago-basic_checkout_credential_description_prod") != undefined) {
    document.getElementById("woocommerce_woo-mercado-pago-basic_checkout_credential_description_prod").nextElementSibling.append(cloneSaveButton.cloneNode(true));
  }

  if (document.getElementById("woocommerce_woo-mercado-pago-basic_checkout_homolog_title") != undefined || document.getElementById("woocommerce_woo-mercado-pago-basic_checkout_options_title") != undefined) {

    document.getElementById("woocommerce_woo-mercado-pago-basic_checkout_advanced_settings").nextElementSibling.append(cloneSaveButton.cloneNode(true));
    document.getElementById("woocommerce_woo-mercado-pago-basic_checkout_options_subtitle").nextElementSibling.append(cloneSaveButton.cloneNode(true));
    document.getElementById("woocommerce_woo-mercado-pago-basic_checkout_payments_description").nextElementSibling.append(cloneSaveButton.cloneNode(true));
    document.getElementById("woocommerce_woo-mercado-pago-basic_checkout_payments_advanced_description").nextElementSibling.append(cloneSaveButton.cloneNode(true));

    var collapse_title = document.querySelector("#woocommerce_woo-mercado-pago-basic_checkout_advanced_settings");
    var collapse_table = collapse_title.nextElementSibling;
    collapse_table.style.display = "none";
    collapse_title.style.cursor = "pointer";

    collapse_title.innerHTML += "<span class='btn-collapsible' id='header_plus' style='display:block'>+</span>\
            <span class='btn-collapsible' id='header_less' style='display:none'>-</span>";

    var header_plus = document.querySelector("#header_plus");
    var header_less = document.querySelector("#header_less");

    collapse_title.onclick = function () {
      if (collapse_table.style.display == "none") {
        collapse_table.style.display = "block";
        header_less.style.display = "block";
        header_plus.style.display = "none";
      }
      else {
        collapse_table.style.display = "none";
        header_less.style.display = "none";
        header_plus.style.display = "block";
      }
    }

    //collpase Configuración Avanzada
    var collapse_title_2 = document.querySelector("#woocommerce_woo-mercado-pago-basic_checkout_payments_advanced_title");
    var collapse_table_2 = document.querySelector("#woocommerce_woo-mercado-pago-basic_checkout_payments_advanced_description").nextElementSibling;
    var collapse_description_2 = document.querySelector("#woocommerce_woo-mercado-pago-basic_checkout_payments_advanced_description");
    collapse_table_2.style.display = "none";
    collapse_description_2.style.display = "none";
    collapse_title_2.style.cursor = "pointer";

    collapse_title_2.innerHTML += "<span class='btn-collapsible' id='header_plus_2' style='display:block'>+</span>\
            <span class='btn-collapsible' id='header_less_2' style='display:none'>-</span>";

    var header_plus_2 = document.querySelector("#header_plus_2");
    var header_less_2 = document.querySelector("#header_less_2");

    collapse_title_2.onclick = function () {
      if (collapse_table_2.style.display == "none") {
        collapse_table_2.style.display = "block";
        collapse_description_2.style.display = "block";
        header_less_2.style.display = "block";
        header_plus_2.style.display = "none";
      }
      else {
        collapse_table_2.style.display = "none";
        collapse_description_2.style.display = "none";
        header_less_2.style.display = "none";
        header_plus_2.style.display = "block";
      }
    }

    //payment methods
    var tablePayments = document.querySelector("#woocommerce_woo-mercado-pago-basic_checkout_payments_description").nextElementSibling.getAttribute('class');
    var input_payments = document.querySelectorAll('.' + tablePayments + ' td.forminp label');
    for (i = 0; i < input_payments.length; i++) {
      input_payments[i].id = "input_payments_mt";
    }

    //online payments
    var onlineChecked = "";
    var countOnlineChecked = 0;
    var onlineInputs = document.querySelectorAll(".online_payment_method");
    for (var ion = 0; ion < onlineInputs.length; ion++) {
      var online_payment_translate = onlineInputs[ion].getAttribute("data-translate");
      if (onlineInputs[ion].checked == true) {
        countOnlineChecked += 1;
      }
    }

    if (countOnlineChecked == onlineInputs.length) {
      onlineChecked = "checked";
    }

    for (var ion = 0; ion < onlineInputs.length; ion++) {
      if (ion == 0) {
        var checkbox_online_prepend = "<div class='all_checkbox'>\
          <label for='checkmeon' id='input_payments'>\
            <input type='checkbox' name='checkmeon' id='checkmeon' "+ onlineChecked + " onclick='completeOnlineCheckbox()'>\
            "+ online_payment_translate + "\
          </label>\
        </div>";
        onlineInputs[ion].parentElement.insertAdjacentHTML('beforebegin', checkbox_online_prepend);
        break;
      }
    }

    //debit and prepaid payments
    var debitChecked = "";
    var countDebitChecked = 0;
    var debitInputs = document.querySelectorAll(".debit_payment_method");
    for (var ideb = 0; ideb < debitInputs.length; ideb++) {
      var debit_payment_translate = debitInputs[ideb].getAttribute("data-translate");
      if (debitInputs[ideb].checked == true) {
        countDebitChecked += 1;
      }
    }

    if (countDebitChecked == debitInputs.length) {
      debitChecked = "checked";
    }

    for (var ideb = 0; ideb < debitInputs.length; ideb++) {
      if (ideb == 0) {
        var checkbox_debit_prepend = "<div class='all_checkbox'>\
          <label for='checkmedeb' id='input_payments'>\
            <input type='checkbox' name='checkmedeb' id='checkmedeb' "+ debitChecked + " onclick='completeDebitCheckbox()'>\
            "+ debit_payment_translate + "\
          </label>\
        </div>";
        debitInputs[ideb].parentElement.insertAdjacentHTML('beforebegin', checkbox_debit_prepend);
        break;
      }
    }

    //offline payments configuration form
    var offlineChecked = "";
    var countOfflineChecked = 0;
    var offlineInputs = document.querySelectorAll(".offline_payment_method");
    for (var ioff = 0; ioff < offlineInputs.length; ioff++) {
      var offline_payment_translate = offlineInputs[ioff].getAttribute("data-translate");
      if (offlineInputs[ioff].checked == true) {
        countOfflineChecked += 1;
      }
    }

    if (countOfflineChecked == offlineInputs.length) {
      offlineChecked = "checked";
    }

    for (ioff = 0; ioff < offlineInputs.length; ioff++) {
      if (ioff == 0) {
        var checkbox_offline_prepend = "<div class='all_checkbox'>\
          <label for='checkmeoff' id='input_payments' style='margin-bottom: 37px !important;'>\
            <input type='checkbox' name='checkmeoff' id='checkmeoff' "+ offlineChecked + " onclick='completeOfflineCheckbox()'>\
            "+ offline_payment_translate + "\
          </label>\
        </div>";
        offlineInputs[ioff].parentElement.insertAdjacentHTML('beforebegin', checkbox_offline_prepend);
        break;
      }
    }

  }

  if (document.querySelector('.homologScroll') != null) {
    document.querySelector('.homologScroll').addEventListener('click', function () {
      document.querySelector('#woocommerce_woo-mercado-pago-basic__mp_access_token_prod').scrollIntoView({
        block: "start",
        behavior: "smooth"
      });
    });
  }
}

//Online payments
function completeOnlineCheckbox() {
  var onlineCheck = document.getElementById("checkmeon").checked;
  var onlineInputs = document.querySelectorAll(".online_payment_method");
  for (var i = 0; i < onlineInputs.length; i++) {
    if (onlineCheck == true) {
      onlineInputs[i].checked = true;
    }
    else {
      onlineInputs[i].checked = false;
    }
  }
}

//Debit and prepaid payments
function completeDebitCheckbox() {
  var debitCheck = document.getElementById("checkmedeb").checked;
  var debitInputs = document.querySelectorAll(".debit_payment_method");
  for (var i = 0; i < debitInputs.length; i++) {
    if (debitCheck == true) {
      debitInputs[i].checked = true;
    }
    else {
      debitInputs[i].checked = false;
    }
  }
}

//Offline payments
function completeOfflineCheckbox() {
  var offlineCheck = document.getElementById("checkmeoff").checked;
  var offlineInputs = document.querySelectorAll(".offline_payment_method");
  for (var i = 0; i < offlineInputs.length; i++) {
    if (offlineCheck == true) {
      offlineInputs[i].checked = true;
    }
    else {
      offlineInputs[i].checked = false;
    }
  }
}