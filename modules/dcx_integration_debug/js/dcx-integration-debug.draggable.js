(function ($, Drupal, drupalSettings) {
"use strict";

Drupal.behaviors.dcxIntegrationDebugDraggable = {
  attach: function(context, settings) {
   var draggables = $('.draggable .item');

   draggables.prop('draggable', true);

   $('.draggable .single').on('dragstart', function(event) {
     var data = "http://dev-dam.burda.com/dcx/document/doc6p8xj1nc4pgewiz9f2q?dnd%5Bmimetype%5D=image%2Fjpeg&dnd%5Bfile%5D=%2Fdevice_burda%2Fdev1%2F2016%2F04-11%2F00%2F9a%2Ffile6p8xj1qqe6o1aug1of2q.jpg&dnd%5Bxml%5D=%2Fdcx%2Fdocument_indesign%2Fdoc6p8xj1nc4pgewiz9f2q";
     event.originalEvent.dataTransfer.setData("text/uri-list", JSON.stringify(data));
   });
   $('.draggable .list').on('dragstart', function(event) {
     var data = [{'documenttype-image': 'document/docABC'}, {'documenttype-image': 'document/doc123'}];
     event.originalEvent.dataTransfer.setData("text/uri-list", JSON.stringify(data));
   });
  }
};

})(jQuery, Drupal, drupalSettings);
