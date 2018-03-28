# WordPress Centinel API for WordPress 4.4+

This package provides API for downloading the application log file, and dumping and downloading the database. It ships with authentication middleware which protects the API routes.

Centinel API is designed to work in combination with [**Centinel**](https://centinel.online) - centralized application management system. 

## Requirements

- PHP 5.6+
- WordPress 4.4+

## Installation

- {placeholder}

## Usage

After installing the plugin, go to `Settings > Centinel API Settings` in your WordPress Admin zone.
From there, copy `privateKey`, `encryptionKey` and `routePrefix` to [**Centinel**](https://centinel.online), and you're ready to schedule your application log checks and database backups.

You can read more about these options in [**WordPress documentation**](https://codex.wordpress.org/Debugging_in_WordPress)

### Settings

- `Private Key` - random string, used for authentication  
- `Encryption Key` - random string, used for additional security layer 
- `Route Prefix` - random string, prefixing the API routes  
- `Log Routes Enabled` - disable if you do not wish to send logs to Centinel
- `Database Routes Enabled` - disable if you do not wish to send database dumps to Centinel
- `Disable Time Based Authorization` - check this option in case of your server's and Centinel's datetime being out of sync which results in `Request time mismatch` or `Too many API calls` error
- `Zip Password` - password used when zipping the database dump
- `MySQL Settings` - various database dump options

All MySQL settings are optional.

Some MySQL settings will be ignored for PHP 5.6.  
For more details check [Spatie DB Dumper v1.5.1](https://github.com/spatie/db-dumper/tree/1.5.1)

For details on how to use the dump options check the installed version of the package.  
For PHP 7 that will be [Spatie DB Dumper v2.9](https://github.com/spatie/db-dumper/tree/2.9.0)

### API Routes

- [POST] `/{routePrefix}/create-log`  
- [POST] `/{routePrefix}/download-log`  
- [POST] `/{routePrefix}/dump-database`  
- [POST] `/{routePrefix}/download-database`

For more details check `/app/Controllers/CentinelApiApiController.php` controller.

### Application Logs

By default, WordPress does not write any errors to the log file. To change this, you can open up `wp-config.php` and set the following options:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

### Database Backups

[Spatie DB Dumper](https://github.com/spatie/db-dumper) is used to make database dumps. **MySQL** is supported, and requires `mysqldump` utility.

Centinel API will try to zip and password protect database dumps before sending them to Centinel. It will look for 7-Zip and Zip
libraries to do so. If neither library is available, dumps will be sent without being zipped and password protected.

Run `Check Zip Availability` in Centinel API Settings to see which library is available on your server. Note that Zip encryption algorithm is much less secure than that of 7-Zip. Ultimately it is up to you to decide which level of security is satisfactory. You can always opt out of backing up your database by disabling database backups in Centinel, and additionally, unchecking `Database Routes Enabled` in the settings.

### Authentication

For details check `/app/Middleware/CentinelApiAuthorizeRequest.php` middleware.

## License

Laravel Centinel API is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).
