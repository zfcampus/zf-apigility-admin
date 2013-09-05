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

### `rpc`

```javascript
{
    "controller_service_name": "name of the controller service; this is the identifier, and required",
    "accept_whitelist": [
        "(Optional)",
        "List",
        "of",
        "whitelisted",
        "Accept",
        "mediatypes"
    ],
    "content_type_whitelist": [
        "(Optional)",
        "List",
        "of",
        "whitelisted",
        "Content-Type",
        "mediatypes"
    ],
    "http_options": [
        "(Required)",
        "List",
        "of",
        "allowed",
        "Request methods"
    ],
    "route_match": "(Required) String indicating Segment route to match",
    "route_name": "(Only in representation) Name of route associated with endpoint",
    "selector": "(Optional) Content-Negotiation selector to use; Json by default"
}
```

### `rest`

```javascript
{
    "controller_service_name": "name of the controller service; this is the identifier, and required",
    "accept_whitelist": [
        "(Optional)",
        "List",
        "of",
        "whitelisted",
        "Accept",
        "mediatypes"
    ],
    "collection_class": "(Only in representation) Name of class representing collection",
    "collection_http_options": [
        "(Required)",
        "List",
        "of",
        "allowed",
        "Request methods",
        "on collections"
    ],
    "collection_query_whitelist": [
        "(Optional)",
        "List",
        "of",
        "whitelisted",
        "query string parameters",
        "to pass to resource for collections"
    ],
    "content_type_whitelist": [
        "(Optional)",
        "List",
        "of",
        "whitelisted",
        "Content-Type",
        "mediatypes"
    ],
    "entity_class": "(Only in representation) Name of class representing resource entity",
    "identifier_name": "(Optional) Name of route parameter and entity property representing the resource identifier; defaults to resource_name + _id",
    "module": "(Only in representation) Name of module in which resource resides",
    "page_size": "(Optional) Integer representing number of entities to return in a given page in a collection; defaults to 25",
    "page_size_param": "(Optional) Name of query string parameter used for pagination; defaults to 'page'",
    "resource_class": "(Only in representation) Name of class representing resource handling operations",
    "resource_http_options": [
        "(Required)",
        "List",
        "of",
        "allowed",
        "Request methods",
        "on individual resources"
    ],
    "route": "(Required) String indicating Segment route to match; defaults to /resource_name[/:identifier_name]",
    "route_name": "(Only in representation) Name of route associated with endpoint",
    "selector": "(Optional) Content-Negotiation selector to use; HalJson by default"
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

### `/admin/api/module[/:controller_service_name]/rpc`

This is the canonical endpoint for [RPC resources](#rpc).

- Accept: `application/json`

  Returns either a single [RPC resource](#rpc) (when a `controller_service_name`
  is provided) or a collection of RPC resources (when no
  `controller_service_name` is provided) on success.

- Content-Type: `application/json`

  Expects an object with the property "service_name" describing the endpoint to
  create:

  ```javascript
  {
    "service_name": "Status"
  }
  ```

  You may also provide any other options listed in the [RPC resource](#rpc).

- Collection Methods: `GET`, `POST`

- Resource Methods: `GET`, `PATCH`

- Errors: `application/api-problem+json`

### `/admin/api/module[/:controller_service_name]/rest`

This is the canonical endpoint for [REST resources](#rest).

- Accept: `application/json`

  Returns either a single [REST resource](#rest) (when a `controller_service_name`
  is provided) or a collection of REST resources (when no
  `controller_service_name` is provided) on success.

- Content-Type: `application/json`

  Expects an object with the property "resource_name" describing the module to create:

  ```javascript
  {
    "resource_name": "Status"
  }
  ```

  You may also provide any other options listed in the [REST resource](#rest).

- Collection Methods: `GET`, `POST`

- Resource Methods: `GET`, `PATCH`

- Errors: `application/api-problem+json`
