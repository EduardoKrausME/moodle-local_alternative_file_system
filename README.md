# Alternative File System Location (local_alternative_file_system)

This plugin allows transferring files from **moodledata/filedir** to cloud storage and makes Moodle serve/read those files remotely.

Currently supported:

* **AWS S3**
* **DigitalOcean Spaces**

## Why use cloud storage with Moodle

By moving `filedir` to remote storage (S3/Spaces), you gain:

* **Scalability** (server disk is no longer a bottleneck)
* **Resilience/durability** of storage
* **Performance** via CDN (when applicable)
* **Simpler cluster operation** (webheads without shared disks)

## Implementation

After installing the plugin, you must edit the `config.php` file and include the following line **before** the call to `require_once( __DIR__ . '/lib/setup.php' );`.

```php
$CFG->alternative_file_system_class = "\\local_alternative_file_system\\external_file_system";
```

So it becomes:

```php
$CFG->alternative_file_system_class = "\\local_alternative_file_system\\external_file_system";

require_once( __DIR__ . '/lib/setup.php' );
```

## Migration in two scenarios

### Scenario A) Migrating the local filedir (moodledata/filedir → cloud)

1. Configure the destination in the plugin by choosing between Amazon S3 or DigitalOcean Spaces
2. Follow the instructions on the plugin configuration page to migrate existing files to cloud storage

### Scenario B) Migrating from tool_objectfs → local_alternative_file_system

While Moodle is configured as `$CFG->alternative_file_system_class = "\\tool_objectfs\\...";`, open the `alternative_file_system_class` plugin settings and configure the plugin.

The plugin will automatically detect the `tool_objectfs` configuration, apply those settings to this plugin, and run the tests.

Once everything looks good, switch `$CFG->alternative_file_system_class` as shown in the **Implementation** section above.

From then on, Moodle will serve/read files directly through `local_alternative_file_system`.
