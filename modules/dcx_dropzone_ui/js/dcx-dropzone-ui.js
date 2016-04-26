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


        var uri = decodeURIComponent(event.originalEvent.dataTransfer.getData('text/uri-list'));
        var data = uri.split('?')[0];
        data = [{'documenttype-image': data.substr(data.indexOf('document'))}];

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
