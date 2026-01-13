# Alternative File System Location

This plugin enables the transfer of all files from the directory **moodledata/filedir** to the cloud.

With this plugin, it's possible to transfer files to **AWS S3** or **DigitalOcean Spaces**.

## Advantages of Cloud Files over Local in Moodle

By transferring files to remote storage, such as AWS S3, DigitalOcean Spaces, or Google Cloud Storage, you'll enjoy several advantages compared to storing files locally.

**Here are some of the most significant advantages:**

1. **Server Resource Optimization:**
    - Transferring files to the cloud results in significant savings in server memory and processing.
    - This optimization occurs because file requests are directed to the Content Delivery Network (CDN) rather than being handled locally.
1. **Scalability:**
    - Cloud storage services offer virtually unlimited scalability, so opting for remote storage means you won't face local storage space restrictions.
    - As your storage needs grow, you can easily expand your storage capacity without the need for hardware upgrades or complicated migrations.
1. **Reliability:**
    - Cloud storage services ensure high levels of availability and durability.
    - This means your files will be accessible and protected against data loss due to hardware failures or other technical issues.
    - By storing your files remotely, you reduce the risk of data loss due to local server failures.
1. **Performance:**
    - Cloud storage services offer advanced content delivery network (CDN) capabilities, which can significantly improve file delivery performance to end-users in different geographical regions.
    - This results in faster loading times for users, especially those located far from the main server.
1. **Security:**
    - Cloud storage services offer advanced security features such as encryption of stored and in-transit data, access control, and monitoring of suspicious activities.
    - By storing your files remotely, you can benefit from these additional security measures to protect your data against unauthorized access and security breaches.

By opting to use remote files instead of local storage in Moodle, you can take advantage of the scalability, reliability, performance, flexibility, and security benefits offered by cloud storage services.

This improves the user experience, enhances resource management, and provides greater peace of mind regarding the security and availability of your files.

## Implementation

After installing the plugin, it is necessary to edit the file ``config.php`` and include the following line before the call ``require_once( __DIR__ . '/lib/setup.php' );``.

```php
$CFG->alternative_file_system_class = "\\local_alternative_file_system\\external_file_system";
```

This line changes the file system class to the plugin's. Make sure to add this line in the appropriate location within the ``config.php`` file to ensure the plugin operates correctly.

To complete the plugin installation and move your objects to the cloud, you should follow the steps below to configure it according to the service you chose:

### AWS S3
1. **Create an AWS account:**
    - If you don't already have one, create an AWS account at [aws.amazon.com](https://aws.amazon.com/).
2. **Access the AWS Console:**
    - Log in to the AWS Management Console.
3. **Create an Amazon S3 bucket**:
    - In the console, go to Amazon S3 and create a new bucket. Choose a unique name for your bucket and select the region where you want it to be hosted.
4. **Configure access permissions**:
    - Make sure to configure appropriate access policies for your bucket so it can be accessed by Moodle.

### DigitalOcean Spaces
1. **Create a DigitalOcean account:**
    - If you don't have one yet, create a DigitalOcean account at [digitalocean.com](https://m.do.co/c/64812c2d631b).
2. **Access the DigitalOcean Console:**
    - Log in to the DigitalOcean Console.
3. **Create a Space**:
    - In the console, go to "Spaces" and create a new space.
    - Choose a unique name for your space and select the region where you want it to be hosted.
4. **Generate an access token**:
    - Go to "API" in the DigitalOcean Console and generate a new access token with appropriate permissions to access your space.