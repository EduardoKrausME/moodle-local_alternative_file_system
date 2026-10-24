# Changelog

This changelog covers the two most recent published releases of `local_alternative_file_system`. Changes are based on the differences between published Git tags; commits made after `1.3.11` are intentionally excluded.

## [1.3.11] - 2026-10-19

### Changed
- Replaced the plugin's SVG icon (`pix/icon.svg`) with a PNG icon (`pix/icon.png`).

### Release maintenance
- Added a concurrency group scoped to the Git ref in `.github/workflows/release.yml`, with in-progress releases preserved and release jobs queued.
- Updated the plugin's release metadata to version `1.3.11`.

**Full comparison:** [1.3.10...1.3.11](https://github.com/EduardoKrausME/moodle-local_alternative_file_system/compare/1.3.10...1.3.11)

## [1.3.10] - 2026-10-14

### Changed
- Explicitly declared the public `$file` property in `classes/storages/s3/S3Request.php`, rather than relying on an undeclared property.

### CI and release security
- Changed release-message parsing to pass the commit message through the `RELEASE_MESSAGE` environment variable, avoiding direct expansion of untrusted commit text into the shell script.
- Updated the CI PostgreSQL service from 16 to 17.
- Updated the CI MariaDB service from 10 to 11.4, including its configuration and health check.
- Updated the PHP 8.3 CI matrix entry from Moodle 5.2 to Moodle 5.3.
- Updated the plugin's release metadata to version `1.3.10`.

**Full comparison:** [1.3.9...1.3.10](https://github.com/EduardoKrausME/moodle-local_alternative_file_system/compare/1.3.9...1.3.10)
