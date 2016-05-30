(function ($, Drupal, drupalSettings) {
  "use strict";

  Drupal.behaviors.dcxDropzoneUi = {
    attach: function (context, settings) {

      var dropzone_id = drupalSettings.dcx_dropzone.dropzone_id,
              dropzone = $('#' + dropzone_id);

      dropzone.on('dragover dragenter', function (event) {
        event.preventDefault();
        dropzone.addClass('is-dragover');
      });

      dropzone.on('dragleave dragend drop', function (event) {
        event.preventDefault();
        dropzone.removeClass('is-dragover');
      });

      dropzone.on('drop', function (event) {
        event.preventDefault();

        if (dropzone.hasClass('is-uploading')) return false;

        dropzone.addClass('is-uploading').removeClass('is-error');

        var uris = decodeURIComponent(event.originalEvent.dataTransfer.getData('text/plain')).split("\n");

        var data = [];
        for (var index = 0; index < uris.length; ++index) {

          var uri = uris[index];
          if (uri) {
            uri = uri.split('?')[0];
            data.push({'documenttype-image': uri.substr(uri.indexOf('document'))});
          }
        }

        $.ajax({
          url: "/dcx-migration/upload",
          method: 'POST',
          data: JSON.stringify(data)
        }).complete(function() {
          dropzone.removeClass('is-uploading');
        }).success(function(data) {
          dropzone.addClass( data.success == true ? 'is-success' : 'is-error' );
        });
      });
    }
  };

})(jQuery, Drupal, drupalSettings);
