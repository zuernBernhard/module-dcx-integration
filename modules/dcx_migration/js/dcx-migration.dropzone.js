(function ($, Drupal, drupalSettings) {
  "use strict";

  Drupal.behaviors.dcxMigrationDropzone = {
    attach: function (context, settings) {

      var dropzone_id = drupalSettings.dcx_dropzone.dropzone_id,
              dropzone = $('#' + dropzone_id);

      dropzone.on('dragover', function (event) {
        event.preventDefault();
      });

      dropzone.on('drop', function (event) {
        event.preventDefault();
        var data = event.originalEvent.dataTransfer.getData('text/uri-list');

        $.ajax({
          url: "/dcx-migration/upload",
          method: 'POST',
          data: data

        }).done(function() {

        });

      });
    }
  };

})(jQuery, Drupal, drupalSettings);
