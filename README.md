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

## Advantages of using cloud storage instead of local files in Moodle

By moving files to remote storage (such as AWS S3 or DigitalOcean Spaces), you gain several advantages compared to keeping them locally.

**Key benefits:**

1. **Server resource optimization:**
    * Moving files to the cloud reduces local disk usage and simplifies server operations.
    * When combined with a CDN (when applicable), part of the traffic can be served with lower load on the Moodle server.
2. **Scalability:**
    * Storage on S3/Spaces allows you to increase capacity without relying on expanding the server’s disk.
    * It makes it easier to scale storage as demand grows, without complex hardware migrations.
3. **Reliability:**
    * Cloud storage offers high availability and object durability.
    * It reduces the risk of downtime and data loss caused by disk failures on the local server.
4. **Performance:**
    * S3-compatible services can be integrated with a CDN to improve file delivery, especially for users far from the server.
    * In many scenarios, this improves load times and the user experience.
5. **Security:**
    * You can apply access control, policies, encryption, and access auditing at the bucket/Space level.
    * By storing files remotely, you can strengthen security practices and reduce the impact of incidents on the local server.

By choosing remote storage instead of Moodle’s local storage, you benefit from the scalability, reliability, performance, and security offered by S3/Spaces solutions. This improves the user experience and simplifies operations in larger environments (including cluster setups).

## AWS S3

1. **Create an AWS account:**
    * If you don’t already have one, create one at **aws.amazon.com**.
2. **Access the AWS Console:**
    * Log in to the AWS Management Console.
3. **Create a bucket in Amazon S3:**
    * In the console, go to Amazon S3 and create a new bucket.
    * Choose a unique name and select the desired region.
4. **Configure access permissions:**
    * Create/adjust IAM policies to allow Moodle to access the bucket with the minimum required permissions.

## DigitalOcean Spaces

1. **Create a DigitalOcean account:**
    * If you don’t already have one, create one at **digitalocean.com**.
2. **Access the DigitalOcean Console:**
    * Log in to the DigitalOcean dashboard.
3. **Create a Space:**
    * In the console, go to **“Spaces”** and create a new Space.
    * Choose a unique name and select the desired region.
4. **Generate access keys:**
    * Generate access keys/credentials with appropriate read and write permissions for the Space.

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
