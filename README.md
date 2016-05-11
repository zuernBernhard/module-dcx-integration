# Module DC-X Integration
This is a collection of modules which allows to use DC-X media asset management
service as source for media image entities within a Drupal instance, by
importing them via a drag an drop interface.
By default it's meant to provide the only source for images on the Drupal site,
allows keeping track of usage of the imported images on article nodes and takes
care of the visibility of images in respect to configures online publishing
rights.

The DC-X module family consist of the following modules:

* dcx_integration
* dcx_migration
* dcx_article_archive
* dcx_dropzone_ui
* dcx_entity_browser
* dcx_notification
* dcx_integration_debug
* dcx_debug_json_client

## dcx_integration
* Provides all communication to DC-X "over the wire" using a customized version of the DC-X API client. No other module talks to DC-X directly. See service "dcx_integration.client".
* Provides a settings form for DC-X client credentials. See route "dcx_integration.json_client_settings".
* Provides an abstraction layer for DC-X documents by returning PHP objects based on Drupal\dcx_integration\Asset\BaseAsset instead of deserialized JSON array. See Drupal\dcx_integration\JsonClient::getObject().
* Provides a debug controller to inspect the communication between the DC-X server and Drupal. See route "dcx_integration.dcx_debug_controller_debug"

## dcx_migration
* Provides an import service which allows single or batched multiple import of DC-X import of images. This service integrates seemlessly with the migrate module - once an image is imported, it can be updated and rolled back via migrate. See service "dcx_migration.import".
* Provides simple form to import a media item by giving it's DC-X id as string. See route "dcx_migration.form".

## dcx_article_archive
* Provides functionality to archive node:article enities to DC-X including referenced to images referenced on the node. See the entity insert/update hooks in the module file.

## dcx_dropzone_ui
* Provides a render element "dcxdropzone" to allow importing images by drag and drop. See Drupal\dcx_dropzone_ui\Element\DcxDropzone.

## dcx_entity_browser
* Provide integration of the DC-X Dropzone element to EntityBrowser. See Drupal\dcx_entity_browser\Plugin\EntityBrowser\Widget\DcxDropzoneWidget.

## dcx_notification
* Provides a callback URL to allow DC-X to trigger update of entities to reflect changes to the data. Main use is to notify drupal media items if respective image document has changed. See route "dcx_notification.trigger"

# dcx_track_media_usage
* Provides usage tracking for images, i.e. it notifies the DC-X image document corresponding to an image use in Drupal of the fact that it's used. See entity insert/update hooks in the module file.
* Provides a plugin base service to discover media:image entities referenced on article node.

## dcx_integration_debug
* Provides a local, non-http, stupid and mainly useless mock DC-X service which will return an image asset for any DC-X id given. See class "DcxIntegrationDebugServiceProvider".
* Dev module, not intended for use in production.

## dcx_debug_json_client
* Provides a local, non-http, stupid and mainly useless mock DC-X API Client to allow inspection of URL parameters and data structures created while talking to the DC-X server. See class "DcxDebugJsonClientServiceProvider".
* Dev module, not intended for use in production.
