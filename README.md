ZF API-1st Admin
================

This module provides the admin interface and API endpoints for the ZF API-1st
project.

API Resources
-------------

### `module`

```javascript
{
    "name": "normalized module name",
    "namespace": "PHP namespace of the module",
    "is_vendor": "boolean value indicating whether or not this is a vendor (3rd party) module",
    "rest": [
        "List",
        "Of",
        "Defined",
        "REST",
        "Controllers"
    ],
    "rpc": [
        "List",
        "Of",
        "Defined",
        "RPC",
        "Controllers"
    ],
}
```

API endpoints
-------------

### `/admin/api/config`

This endpoint is for examining the application configuration, and providing
overrides of individual values in it. All overrides are written to a single
file, `config/autoload/development.php`; you can override that location in your
configuration via the `zf-configuration/config-file` key.

- Accept: `application/json`, `application/vnd.zfcampus.v1.config+json`

  The first will deliver representations as a flat array of key/value pairs,
  with the keys being dot-separated values, just as you would find in INI.

  The second will deliver the configuration as a tree.

- Content-Type: `application/json`, `application/vnd.zfcampus.v1.config+json`

  The first expects key/value pairs, with keys being dot-separated values, as
  you would find in INI files.

  The second expects a nested array/object of configuration.

- Methods: `GET`, `PATCH`

- Errors: `application/api-problem+json`

### `/admin/api/config/module?module={module name}`

This operates exactly like the `/admin/api/config` endpoint, but expects a known
module name. When provided, it allows you to introspect and manipulate the
configuration file for that module.

### `/admin/api/module.enable`

This endpoint is for API-1st-enabling an existing module.

- Accept: `application/json`

  Returns a [Module resource](#module) on success.

- Content-Type: `application/json`

  Expects an object with the property "name" describing an existing ZF2 module.

  ```javascript
  {
  "name": "Status"
  }
  ```

- Methods: `PUT`

- Errors: `application/api-problem+json`

### `/admin/api/module[/:name]`

This is the canonical endpoint for [Module resources](#module).

- Accept: `application/json`

  Returns either a single [Module resource](#module) (when a `name` is provided)
  or a collection of Module resources (when no `name` is provided) on success.

- Content-Type: `application/json`

  Expects an object with the property "name" describing the module to create:

  ```javascript
  {
  "name": "Status"
  }
  ```

- Collection Methods: `GET`, `POST`

- Resource Methods: `GET`

- Errors: `application/api-problem+json`
