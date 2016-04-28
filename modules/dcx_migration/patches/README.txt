28.04.2016, goechsler
Patch: source_plugins_have_a-2560795-64.patch
Issue: #2560795 Source plugins have a hidden dependency on migrate_drupal
Reasoning: We need this patch to avoid the dependency on migrate_drupal.
  Drupal core 8.1 introduced changes to migrate (see "#2668742
  Migrations are plugins instead of configuration entities") which lead
  to a inherit dependency on migrate_drupal. Module migrate_drupal poses
  other non-related requirements to a site it's meant to run on, like
  e.g. a database connection called 'migrate'.
Test: If DcxImportService::getMigrationExecutable() works without
  migrate_drupal enabled, you do not need this patch anymore.
