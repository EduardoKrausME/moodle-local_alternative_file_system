# Alternative File System Location (local_alternative_file_system)

This plugin allows transferring files from **moodledata/filedir** to cloud storage and makes Moodle serve/read those files remotely.

Currently supported:

* **AWS S3**
* **DigitalOcean Spaces (S3 compatible)**

## Why use cloud storage with Moodle

By moving `filedir` to remote storage (S3/Spaces), you gain:

* **Scalability** (server disk is no longer a bottleneck)
* **Resilience/durability** of storage
* **Performance** via CDN (when applicable)
* **Simpler cluster operation** (webheads without shared disks)

## Implementation

After installing the plugin, you must edit the `config.php` file and include the following line before the call to `require_once( __DIR__ . '/lib/setup.php' );`.

```php
$CFG->alternative_file_system_class = "\\local_alternative_file_system\\external_file_system";
```

## Migration

There are **two main scenarios**:

### Scenario A) Migration from local filedir (moodledata/filedir → cloud)

This is the classic scenario:

1. Configure the destination in the plugin (S3/Spaces)
2. Use the plugin's built-in migration mechanism (existing routines/scripts)

## Scenario B) Import/Migration from tool_objectfs (ObjectFS → local_alternative_file_system)



### How the strategy works (without stopping the site)

The migration happens in 2 phases:

#### Phase 1 — Moodle continues using tool_objectfs (normal production)

While Moodle is configured with:

```php
$CFG->alternative_file_system_class = "\\tool_objectfs\\...";
```

In other words: you "duplicate" the objects to the new destination without interrupting the site.

#### Phase 2 — After copying finishes, switch the AFS to this plugin

When the report indicates that migration is complete (100% / no pending items), change `config.php` to:

```php
$CFG->alternative_file_system_class = "\\local_alternative_file_system\\external_file_system";
```

From that point on, Moodle will serve/read files directly through `local_alternative_file_system`.

## Monitoring / Progress Report

Use the plugin's report to track:

* total hashes
* migrated
* missing
* rate (last minutes)
* estimated ETA

Access:

* `https://MY-MOODLE/local/alternative_file_system/report-migrate.php`

> Tip: during Phase 1 (tool_objectfs active), this report helps you decide the right moment to switch `$CFG->alternative_file_system_class`.

## Best practices and notes

* Perform migrations during low-traffic periods.
* Adjust CRON batch sizes/timers according to environment size.
* After switching the AFS to this plugin, monitor the report for a while to ensure there is no "residual queue."
* If you use a CDN, validate headers/content-disposition according to your download policies.
