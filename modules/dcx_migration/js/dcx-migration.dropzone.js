(function ($, Drupal, drupalSettings) {
"use strict";

Drupal.behaviors.dcxMigrationDropzone = {
  attach: function(context, settings) {
   var dropzone = $('#dcx-dropzone');

   dropzone.on('dragover', function(event) {
     event.preventDefault();
   });

   dropzone.on('drop', function(event) {
     event.preventDefault();
     console.log("->" + event.originalEvent.dataTransfer.getData("data"));
   });

  }
};

})(jQuery, Drupal, drupalSettings);
