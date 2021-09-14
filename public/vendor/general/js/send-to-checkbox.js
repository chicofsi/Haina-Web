$(document).ready(function () {
  var sendtodelete = document.getElementById("send-to-delete");
  var sendtoedit = document.getElementById("send-to-edit");
  var sendtodetail = document.getElementById("send-to-detail");
  var sendtodocument = document.getElementById("send-to-document");
  var sendtomove = document.getElementById("send-to-move");

  const queryString = window.location.search;
  const urlParams = new URLSearchParams(queryString);
  const get_max = urlParams.get('max');
  const get_page = urlParams.get('page');
  const get_search = urlParams.get('search');

  var link_url = document.URL;
  var parsing = link_url.split('/');
  const parsing_protocol = parsing[0];
  const parsing_mainurl = parsing[2];
  //const parsing_language = parsing[3];
  const new_url_complete = parsing_protocol+'//'+parsing_mainurl+'/backend/modal';

  var link_uri = window.location.pathname;
  var parsing2 = link_uri.split('/');
  const parsing_cpanel = parsing2[1];
  const parsing_trx = parsing2[2];
  const parsing_menu = parsing2[3];
  // jalankan fungsi jika semua dipilih
  $(".checkall").click(function() {
    if ($(this).is(":checked")) {
      var array = [];
      $(".checkone").prop("checked", true);
      $("input:checkbox[name=id_checkbox]:checked").each(function() {
        array.push($(this).val());
      });
      // kitas
      if (parsing_cpanel == 'cpanel' && parsing_trx == 'monitoring' && parsing_menu == 'kitas') {
        sendtodelete.value = "kitas,delete,"+array;
        sendtodelete.disabled = false;
        sendtoedit.value = "";
        sendtoedit.disabled = true;
        sendtodetail.value = "";
        sendtodetail.disabled = true;
        sendtodocument.value = "";
        sendtodocument.disabled = true;
      }
  	  // locations
  	  if (parsing_cpanel == 'cpanel' && parsing_trx == 'datamaster' && parsing_menu == 'location') {
        sendtodelete.value = "locations,delete,"+array;
        sendtodelete.disabled = false;
        sendtoedit.value = "";
        sendtoedit.disabled = true;
      }
  	  // positions
  	  if (parsing_cpanel == 'cpanel' && parsing_trx == 'datamaster' && parsing_menu == 'position') {
        sendtodelete.value = "positions,delete,"+array;
        sendtodelete.disabled = false;
        sendtoedit.value = "";
        sendtoedit.disabled = true;
      }
      // faq category
  	  if (parsing_cpanel == 'cpanel' && parsing_trx == 'datamaster' && parsing_menu == 'faq-category') {
        sendtodelete.value = "faq-category,delete,"+array;
        sendtodelete.disabled = false;
        sendtoedit.value = "";
        sendtoedit.disabled = true;
      }
      // faqs
      if (parsing_cpanel == 'cpanel' && parsing_trx == 'faq') {
        sendtodelete.value = "faqs,delete,"+array;
        sendtodelete.disabled = false;
        sendtoedit.value = "";
        sendtoedit.disabled = true;
        sendtodetail.value = "";
        sendtodetail.disabled = true;
      }
  	  // companies
  	  if (parsing_cpanel == 'cpanel' && parsing_trx == 'monitoring' && parsing_menu == 'legal-document') {
        if (parsing2[4] == 'undefined' || parsing2[4] == null) {
          sendtodelete.value = "";
          sendtodelete.disabled = true;
        }
      }
    } else {
      $(".checkone").prop("checked", false);
      // kitas
      if (parsing_cpanel == 'cpanel' && parsing_trx == 'monitoring' && parsing_menu == 'kitas') {
        sendtodelete.value = "";
        sendtodelete.disabled = true;
        sendtoedit.value = "";
        sendtoedit.disabled = true;
        sendtodetail.value = "";
        sendtodetail.disabled = true;
        sendtodocument.value = "";
        sendtodocument.disabled = true;
      }
  	  // locations
  	  if (parsing_cpanel == 'cpanel' && parsing_trx == 'datamaster' && parsing_menu == 'location') {
        sendtodelete.value = "";
        sendtodelete.disabled = true;
        sendtoedit.value = "";
        sendtoedit.disabled = true;
      }
  	  // positions
  	  if (parsing_cpanel == 'cpanel' && parsing_trx == 'datamaster' && parsing_menu == 'position') {
        sendtodelete.value = "";
        sendtodelete.disabled = true;
        sendtoedit.value = "";
        sendtoedit.disabled = true;
      }
      // faq category
  	  if (parsing_cpanel == 'cpanel' && parsing_trx == 'datamaster' && parsing_menu == 'faq-category') {
        sendtodelete.value = "";
        sendtodelete.disabled = true;
        sendtoedit.value = "";
        sendtoedit.disabled = true;
      }
      // faqs
      if (parsing_cpanel == 'cpanel' && parsing_trx == 'faq') {
        sendtodelete.value = "";
        sendtodelete.disabled = true;
        sendtoedit.value = "";
        sendtoedit.disabled = true;
        sendtodetail.value = "";
        sendtodetail.disabled = true;
      }
  	  // companies
  	  if (parsing_cpanel == 'cpanel' && parsing_trx == 'monitoring' && parsing_menu == 'legal-document') {
        if (parsing2[4] == 'undefined' || parsing2[4] == null) {
          sendtodelete.value = "";
          sendtodelete.disabled = true;
        }
      }
    }
  });
  // jalankan fungsi jika yang dipilih hanya satu dan bukan pilih semua
  $(".checkone").click(function() {
    if ($(this).is(":checked")) {
      var array = [];
      $("input:checkbox[name=id_checkbox]:checked").each(function() {
        array.push($(this).val());
      });
      // kitas
      if (parsing_cpanel == 'cpanel' && parsing_trx == 'monitoring' && parsing_menu == 'kitas') {
        sendtodelete.value = "kitas,delete,"+array;
        sendtodelete.disabled = false;
      }
      // folder & file legal document
      if (parsing_cpanel == 'cpanel' && parsing_trx == 'monitoring' && parsing_menu == 'legal-document') {
        if (parsing2[4] == 'ids') {
          sendtomove.value = "move,"+array;
          sendtomove.disabled = false;
        }
      }
  	  // locations
  	  if (parsing_cpanel == 'cpanel' && parsing_trx == 'datamaster' && parsing_menu == 'location') {
        sendtodelete.value = "locations,delete,"+array;
        sendtodelete.disabled = false;
      }
  	  // positions
  	  if (parsing_cpanel == 'cpanel' && parsing_trx == 'datamaster' && parsing_menu == 'position') {
        sendtodelete.value = "positions,delete,"+array;
        sendtodelete.disabled = false;
      }
      // faq category
  	  if (parsing_cpanel == 'cpanel' && parsing_trx == 'datamaster' && parsing_menu == 'faq-category') {
        sendtodelete.value = "faq-category,delete,"+array;
        sendtodelete.disabled = false;
      }
      // faqs
      if (parsing_cpanel == 'cpanel' && parsing_trx == 'faq') {
        sendtodelete.value = "faqs,delete,"+array;
        sendtodelete.disabled = false;
      }
      var countcheckone = $('[name=id_checkbox]').length;
      var input = document.getElementsByName("id_checkbox");
      var total = 0;
      for (var i = 0; i < input.length; i++) {
        if (input[i].checked) {
          total += parseFloat(input[i].id);
        }
      }
      if (total === countcheckone) {
        $(".checkall").prop("checked", true);
      }
      if (total === 1) {
        // kitas
        if (parsing_cpanel == 'cpanel' && parsing_trx == 'monitoring' && parsing_menu == 'kitas') {
          sendtoedit.value = "kitas,edit,"+array;
          sendtoedit.disabled = false;
          sendtodetail.value = "kitas,detail,"+array;
          sendtodetail.disabled = false;
          sendtodocument.value = "kitas,document,"+array;
          sendtodocument.disabled = false;
        }
    		// locations
    		if (parsing_cpanel == 'cpanel' && parsing_trx == 'datamaster' && parsing_menu == 'location') {
          sendtoedit.value = "locations,edit,"+array;
          sendtoedit.disabled = false;
        }
    		// positions
    		if (parsing_cpanel == 'cpanel' && parsing_trx == 'datamaster' && parsing_menu == 'position') {
          sendtoedit.value = "positions,edit,"+array;
          sendtoedit.disabled = false;
        }
        // faq category
    		if (parsing_cpanel == 'cpanel' && parsing_trx == 'datamaster' && parsing_menu == 'faq-category') {
          sendtoedit.value = "faq-category,edit,"+array;
          sendtoedit.disabled = false;
        }
        // faqs
        if (parsing_cpanel == 'cpanel' && parsing_trx == 'faq') {
          sendtoedit.value = "faqs,edit,"+array;
          sendtoedit.disabled = false;
          sendtodetail.value = "faqs,detail,"+array;
          sendtodetail.disabled = false;
        }
        // companies
    		if (parsing_cpanel == 'cpanel' && parsing_trx == 'monitoring' && parsing_menu == 'legal-document') {
          if (parsing2[4] == 'undefined' || parsing2[4] == null) {
            sendtodelete.value = "companies,delete,"+array;
            sendtodelete.disabled = false;
          }
        }
      }
      if (total > 1) {
        // kitas
        if (parsing_cpanel == 'cpanel' && parsing_trx == 'monitoring' && parsing_menu == 'kitas') {
          sendtoedit.value = "";
          sendtoedit.disabled = true;
          sendtodetail.value = "";
          sendtodetail.disabled = true;
          sendtodocument.value = "";
          sendtodocument.disabled = true;
        }
    		// locations
    		if (parsing_cpanel == 'cpanel' && parsing_trx == 'datamaster' && parsing_menu == 'location') {
          sendtoedit.value = "";
          sendtoedit.disabled = true;
        }
    		// positions
    		if (parsing_cpanel == 'cpanel' && parsing_trx == 'datamaster' && parsing_menu == 'position') {
          sendtoedit.value = "";
          sendtoedit.disabled = true;
        }
        // faq category
    		if (parsing_cpanel == 'cpanel' && parsing_trx == 'datamaster' && parsing_menu == 'faq-category') {
          sendtoedit.value = "";
          sendtoedit.disabled = true;
        }
        // faqs
        if (parsing_cpanel == 'cpanel' && parsing_trx == 'faq') {
          sendtoedit.value = "";
          sendtoedit.disabled = true;
          sendtodetail.value = "";
          sendtodetail.disabled = true;
        }
    		// companies
    		if (parsing_cpanel == 'cpanel' && parsing_trx == 'monitoring' && parsing_menu == 'legal-document') {
          if (parsing2[4] == 'undefined' || parsing2[4] == null) {
            sendtodelete.value = "";
            sendtodelete.disabled = true;
          }
        }
      }
    } else {
      $(".checkall").prop("checked", false);
      var array = [];
      $("input:checkbox[name=id_checkbox]:checked").each(function() {
        array.push($(this).val());
      });
      // kitas
      if (parsing_cpanel == 'cpanel' && parsing_trx == 'monitoring' && parsing_menu == 'kitas') {
        sendtodelete.value = "kitas,delete,"+array;
      }
      // folder & file legal document
      if (parsing_cpanel == 'cpanel' && parsing_trx == 'monitoring' && parsing_menu == 'legal-document') {
        if (parsing2[4] == 'ids') {
          sendtomove.value = "move,"+array;
        }
      }
  	  // locations
  	  if (parsing_cpanel == 'cpanel' && parsing_trx == 'datamaster' && parsing_menu == 'location') {
        sendtodelete.value = "locations,delete,"+array;
      }
  	  // positions
  	  if (parsing_cpanel == 'cpanel' && parsing_trx == 'datamaster' && parsing_menu == 'position') {
        sendtodelete.value = "positions,delete,"+array;
      }
      // faq category
  	  if (parsing_cpanel == 'cpanel' && parsing_trx == 'datamaster' && parsing_menu == 'faq-category') {
        sendtodelete.value = "faq-category,delete,"+array;
      }
      // faqs
      if (parsing_cpanel == 'cpanel' && parsing_trx == 'faq') {
        sendtodelete.value = "faqs,delete,"+array;
      }
      var input = document.getElementsByName("id_checkbox");
      var total = 0;
      for (var i = 0; i < input.length; i++) {
        if (input[i].checked) {
          total += parseFloat(input[i].id);
        }
      }
      if (total === 1) {
        // kitas
        if (parsing_cpanel == 'cpanel' && parsing_trx == 'monitoring' && parsing_menu == 'kitas') {
          sendtoedit.value = "kitas,edit,"+array;
          sendtoedit.disabled = false;
          sendtodetail.value = "kitas,detail,"+array;
          sendtodetail.disabled = false;
          sendtodocument.value = "kitas,document,"+array;
          sendtodocument.disabled = false;
        }
    		// locations
    		if (parsing_cpanel == 'cpanel' && parsing_trx == 'datamaster' && parsing_menu == 'location') {
          sendtoedit.value = "locations,edit,"+array;
          sendtoedit.disabled = false;
        }
    		// positions
    		if (parsing_cpanel == 'cpanel' && parsing_trx == 'datamaster' && parsing_menu == 'position') {
          sendtoedit.value = "positions,edit,"+array;
          sendtoedit.disabled = false;
        }
        // faq category
    		if (parsing_cpanel == 'cpanel' && parsing_trx == 'datamaster' && parsing_menu == 'faq-category') {
          sendtoedit.value = "faq-category,edit,"+array;
          sendtoedit.disabled = false;
        }
        // faqs
        if (parsing_cpanel == 'cpanel' && parsing_trx == 'faq') {
          sendtoedit.value = "faqs,edit,"+array;
          sendtoedit.disabled = false;
          sendtodetail.value = "faqs,detail,"+array;
          sendtodetail.disabled = false;
        }
        // companies
        if (parsing_cpanel == 'cpanel' && parsing_trx == 'monitoring' && parsing_menu == 'legal-document') {
          if (parsing2[4] == 'undefined' || parsing2[4] == null) {
            sendtodelete.value = "companies,delete,"+array;
            sendtodelete.disabled = false;
          }
        }
        // folder & file legal document
        if (parsing_cpanel == 'cpanel' && parsing_trx == 'monitoring' && parsing_menu == 'legal-document') {
          if (parsing2[4] == 'ids') {
            sendtomove.value = "move,"+array;
            sendtomove.disabled = false;
          }
        }
      }
      if (total > 1) {
        // kitas
        if (parsing_cpanel == 'cpanel' && parsing_trx == 'monitoring' && parsing_menu == 'kitas') {
          sendtoedit.value = "";
          sendtoedit.disabled = true;
          sendtodetail.value = "";
          sendtodetail.disabled = true;
          sendtodocument.value = "";
          sendtodocument.disabled = true;
        }
    		// locations
    		if (parsing_cpanel == 'cpanel' && parsing_trx == 'datamaster' && parsing_menu == 'location') {
          sendtoedit.value = "";
          sendtoedit.disabled = true;
        }
    		// positions
    		if (parsing_cpanel == 'cpanel' && parsing_trx == 'datamaster' && parsing_menu == 'position') {
          sendtoedit.value = "";
          sendtoedit.disabled = true;
        }
        // faq category
    		if (parsing_cpanel == 'cpanel' && parsing_trx == 'datamaster' && parsing_menu == 'faq-category') {
          sendtoedit.value = "";
          sendtoedit.disabled = true;
        }
        // faqs
        if (parsing_cpanel == 'cpanel' && parsing_trx == 'faq') {
          sendtoedit.value = "";
          sendtoedit.disabled = true;
          sendtodetail.value = "";
          sendtodetail.disabled = true;
        }
    		// companies
    		if (parsing_cpanel == 'cpanel' && parsing_trx == 'monitoring' && parsing_menu == 'legal-document') {
          if (parsing2[4] == 'undefined' || parsing2[4] == null) {
            sendtodelete.value = "";
            sendtodelete.disabled = true;
          }
        }
      }
      if (total === 0) {
        // kitas
        if (parsing_cpanel == 'cpanel' && parsing_trx == 'monitoring' && parsing_menu == 'kitas') {
          sendtodelete.value = "";
          sendtodelete.disabled = true;
          sendtoedit.value = "";
          sendtoedit.disabled = true;
          sendtodetail.value = "";
          sendtodetail.disabled = true;
          sendtodocument.value = "";
          sendtodocument.disabled = true;
        }
        // folder & file legal document
        if (parsing_cpanel == 'cpanel' && parsing_trx == 'monitoring' && parsing_menu == 'legal-document') {
          if (parsing2[4] == 'ids') {
            sendtomove.value = "";
            sendtomove.disabled = true;
          }
        }
    		// locations
    		if (parsing_cpanel == 'cpanel' && parsing_trx == 'datamaster' && parsing_menu == 'location') {
          sendtodelete.value = "";
          sendtodelete.disabled = true;
          sendtoedit.value = "";
          sendtoedit.disabled = true;
        }
    		// positions
    		if (parsing_cpanel == 'cpanel' && parsing_trx == 'datamaster' && parsing_menu == 'position') {
          sendtodelete.value = "";
          sendtodelete.disabled = true;
          sendtoedit.value = "";
          sendtoedit.disabled = true;
        }
        // faq category
    		if (parsing_cpanel == 'cpanel' && parsing_trx == 'datamaster' && parsing_menu == 'faq-category') {
          sendtodelete.value = "";
          sendtodelete.disabled = true;
          sendtoedit.value = "";
          sendtoedit.disabled = true;
        }
        // faqs
        if (parsing_cpanel == 'cpanel' && parsing_trx == 'faq') {
          sendtodelete.value = "";
          sendtodelete.disabled = true;
          sendtoedit.value = "";
          sendtoedit.disabled = true;
          sendtodetail.value = "";
          sendtodetail.disabled = true;
        }
    		// companies
    		if (parsing_cpanel == 'cpanel' && parsing_trx == 'monitoring' && parsing_menu == 'legal-document') {
          if (parsing2[4] == 'undefined' || parsing2[4] == null) {
            sendtodelete.value = "";
            sendtodelete.disabled = true;
          }
        }
      }
    }
  });
})
