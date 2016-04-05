# Module DCX Integration
By now this consist of three modules:

* dcx_integration
* dcx_migration
* dcx_integration_debug (which is named poorly and might change name soon)

## dcx_integration
* Provides a settings form for DC-X client credentials. See route "dcx_integration.json_client_settings".
* Provides an abstraction layer by offering an DC-X client as injectable service, which talks to DC-X, retrieves data but only returns PHP objects based on Drupal\dcx_integration\Asset\BaseAsset. See service "dcx_integration.client".
* Prototypically emmits notification messages to the DC-X integration service, whenever a node is saving entity references to a "media:image"-Entity. These messages have no effect by now. See dcx_integration.module.
* Provides a debug controller to inspect the communication between the DC-X server and Drupal. See route "dcx_integration.dcx_debug_controller_debug"
    
## dcx_migration
* Provides an import service which allows single or batched multiple import of DC-X import of images. This service integration seemlessly with the migrate module - once an image is imported, it can be updated and rolled back via migrate. See service "dcx_migration.import".
* Provides a form with prototypical HTML5 dropzone which triggers import using the "dcx_migration.import" service. See route "dcx_migration.import".

## dcx_integration_debug
* Provides a local, non-http, stupid and mainly useless mock DC-X service which will return an image asset for any DC-X id given. See class "DcxIntegrationDebugServiceProvider".
* Provides dummy draggables for the dropzone by altering the 'dcx_import_form'.
