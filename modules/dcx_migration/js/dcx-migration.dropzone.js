(function ($, Drupal, drupalSettings) {
  "use strict";

  Drupal.behaviors.dcxMigrationDropzone = {
    attach: function (context, settings) {

      var dropzone_id = drupalSettings.dcx_dropzone.dropzone_id,
              value_name = drupalSettings.dcx_dropzone.value_name,
              dropzone = $('#' + dropzone_id),
              value_field = $('input[name="' + value_name + '"]')

      dropzone.on('dragover', function (event) {
        event.preventDefault();
      });

      dropzone.on('drop', function (event) {
        event.preventDefault();
        var data = event.originalEvent.dataTransfer.getData('text/uri-list');
        value_field.val(data);
        value_field.parents('form').submit();
      });
    }
  };

})(jQuery, Drupal, drupalSettings);
