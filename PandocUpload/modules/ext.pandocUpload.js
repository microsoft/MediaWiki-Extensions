$(document).ready(function() {

  function getExtension(filename) {
    return filename.split('.').pop();
  }

  // Allowed file extensions for conversion.
  var conversion_list = ["docx", "md", "markdown"];
  var TITLE_MIN_LENGTH = 4;
  var TITLE_MAX_LENGTH = 255;

  var file_input = $("#wpUploadFile");
  var file_name = $("#wpDestFile");
  var checkbox = $("#wpConvertToArticle");
  var textbox = $("#wpArticleTitle");
  var submit_form = $("#mw-upload-form");
  var ext;
  var invalid_regex = /[\/\'\"\$]/g;

  if (submit_form.length == 1) {
    submit_form.submit(function (event) {
      if (checkbox.prop("disabled") == true || (!checkbox.is(':checked'))) {
        return true;
      }
      var title = textbox.val();
      var results;
      if (title.length < TITLE_MIN_LENGTH || title.length > TITLE_MAX_LENGTH) {
        alert(mw.message('pandocupload-warning-title-length').text().replace("$1", TITLE_MIN_LENGTH).replace("$2", TITLE_MAX_LENGTH));
        return false;
      }
      if ((results = title.match(invalid_regex)) != null) {
        results = results.join(" ");
        alert(mw.message('pandocupload-warning-title-invalid-character').text().replace("$1", results));
        return false;
      }
      // TODO: AJAX request to check article existence
      // var api = new mw.Api();
      // api.get(
      //   {
      //       action: "query",
      //       titles: title,
      //       prop: "info",
      //       format: 'json'
      //   }
      // ).done(
      //   function(data){
      //     console.log(data);
      //     }
      //   }
      // );
      if (confirm(mw.message('pandocupload-confirm-upload').text())) {
        return true;
      } else {
        return false;
      }
    });
  }

  if (file_input.length == 1 && textbox.length == 1) {

    $("#wpUploadFile").change(function () {
      ext = getExtension(file_input.val()).toLowerCase();
      if (conversion_list.indexOf(ext) == -1) {
        checkbox.prop("disabled", true);
        textbox.prop("disabled", true);
      } else {
        checkbox.prop("disabled", false);
        textbox.prop("disabled", false);
      }
    });
    // for upload warning page workaround
  } else if (file_name.length == 1 && textbox.length == 1) {
    ext = getExtension(file_name.val()).toLowerCase();
    if (conversion_list.indexOf(ext) == -1) {
      checkbox.prop("disabled", true);
      textbox.prop("disabled", true);
    } else {
      checkbox.prop("disabled", false);
      textbox.prop("disabled", false);
    }
  }

});